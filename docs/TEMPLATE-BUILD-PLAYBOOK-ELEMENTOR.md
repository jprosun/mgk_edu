# MGK Template Build Playbook — ELEMENTOR EDITION
**Quy trình chuẩn xây dựng template Margick (WordPress + Hello Elementor child + Elementor)**

> Nguồn duy nhất (single source of truth) về **kiến trúc UI-editing** và **quy trình build** cho template Margick chạy trên **Elementor** (đối chiếu bản Flatsome ở `TEMPLATE-BUILD-PLAYBOOK.md`).
> Đọc file này trước khi build/extend bất kỳ template Elementor nào. Mục tiêu: **master template chuẩn chỉ để nhân ra 200-300 biến thể**.

Version: 1.0 · Ngày tạo: 2026-06-02 · Bối cảnh: rút ra từ `mgk-edu-elementor` (port Flatsome→Elementor của mgk_edu)

---

## 0. TL;DR (đọc 30 giây)

1. **Triết lý đã chốt (user 2026-06-02): PHP-native làm chủ đạo.** HTML sống MỘT chỗ trong PHP partial. Widget Elementor chỉ là *vỏ* gọi shortcode → partial. KHÔNG nhồi HTML vào builder, KHÔNG export `_elementor_data` JSON tĩnh.
2. **Dữ liệu** (tutor, review, giá, catalog…) → user vào **wp-admin** thêm/sửa/xóa (CPT/ACF). Elementor chỉnh **vỏ hiển thị** quanh DATA: filter, toolbar, label, button Apply/Book/View, cột, nền, spacing, style nút; **KHÔNG bao giờ** chạm nguồn data. Ranh giới cứng — xem §3.3.
3. **User tự do UI theo tầng**: CONTENT marketing mở rộng; DATA shell mở có kiểm soát; DATA core khóa. KHÔNG đụng query/order/review source.
4. Ba loại bề mặt: **CONTENT** (marketing → widget MGK, style đầy đủ), **DATA SHELL** (filter/toolbar/card shell/form shell → widget MGK giới hạn), **DATA CORE** (records/query/payment/review logic → PHP/wp-admin).
5. **Phân phối = generator seed lúc activate** (idempotent, guard `_mgk_layout_seeded`), version-control bằng code. Mỗi biến thể = 1 cấu hình generator, KHÔNG phải 1 file JSON tay.
6. **Release-ready = DB sạch → activate → UI khớp wireframe 100% trước khi user đụng gì.**

---

## 1. Khác biệt cốt lõi so với bản Flatsome

| | Flatsome (bản cũ) | **Elementor (bản này)** |
|---|---|---|
| Parent theme | `flatsome` | `hello-elementor` |
| Builder | UX Builder (`add_ux_builder_shortcode`, `ux_builder_setup`) | Elementor (`elementor/widgets/register`, `\Elementor\Widget_Base`) |
| Đăng ký element | shortcode = element | 1 `MGK_Elementor_Section_Widget` (config-driven) / section |
| Layout lưu ở đâu | `post_content` = chuỗi shortcode | `_elementor_data` JSON meta (+ `_elementor_edit_mode=builder`) |
| Builder-mode detect | `[mgk_` trong content | `mgk_is_built_with_elementor($post_id)` |
| Style controls | option text trong panel | **tab Style đầy đủ** (typography/align/color/bg/padding/margin/border/shadow/hover) |

**Bất biến giữ nguyên từ bản Flatsome:** "shortcode wraps partial" (1 nguồn HTML), CONTENT/DATA, generator direction A (seed 1 lần, idempotent), Locked Core + Editable Shell.

---

## 2. Kiến trúc widget (file `inc/mgk-elementor.php`)

### 2.1 Một class generic, config-driven
- `MGK_Elementor_Section_Widget extends \Elementor\Widget_Base` — **một class** cho mọi section, hành vi suy ra từ config.
- **GOTCHA load-order (đã sửa, BẮT BUỘC nhớ):** class KHÔNG được định nghĩa ở file scope. `functions.php` require file này RẤT SỚM (trước khi Elementor load `Widget_Base`) → top-level `class extends \Elementor\Widget_Base` sẽ fatal / bị bỏ qua. → Định nghĩa class trong hàm `mgk_elementor_define_widget_class()`, gọi lazy từ `add_action('elementor/loaded', …)` VÀ đầu callback `elementor/widgets/register`.
- Đăng ký: `add_action('elementor/widgets/register', fn($wm)=> foreach sections: $wm->register(new MGK_Elementor_Section_Widget([], ['mgk_config'=>$cfg])))`.
- Category: `add_action('elementor/elements/categories_registered', …->add_category('mgk-edu', …))`.
- **Rehydration**: khi Elementor render lại từ DB, nó tạo instance MỚI với `$data` (có `widgetType`), KHÔNG truyền lại `mgk_config`. → `get_name()` đọc `get_data('widgetType')`; `mgk_get_config()` fallback qua `mgk_elementor_section_config($name)`. (Element_Base ctor KHÔNG gọi get_name() → an toàn.)
- Elementor 4.x (Atomic Editor) VẪN hỗ trợ classic `Widget_Base` — đã verify live (4.1.1).

### 2.2 render() — vẫn một nguồn HTML
```
render():
  - nếu config có 'repeater' + có rows do user nhập → render partial TRỰC TIẾP với items (mgk_render_part), bypass shortcode (vì mảng to)
  - nếu repeater rỗng / không có → do_shortcode( build_shortcode(tag, atts) ) → partial
  → cả hai đường đều ra CÙNG partial = markup không bao giờ lệch
```
- `content_template()` rỗng → Elementor render server-side (preview = front-end).
- Att rỗng → bỏ → shortcode fallback `mgk_site_setting()` (giữ "giống bản gốc khi chưa sửa").

### 2.3 Config mỗi section (mảng trong `mgk_elementor_sections()`)
```php
[
  'tag' => 'mgk_hero', 'title' => 'MGK · Hero', 'icon' => 'eicon-banner',
  'controls' => [ 'eyebrow'=>['type'=>'text','label'=>..,'default'=>$s('hero_eyebrow')], ... ],  // text atts
  'repeater' => [ 'control'=>'items','partial'=>'steps','map'=>'pairs'|'assoc','pair_order'=>[..],
                  'fields'=>[..], 'defaults'=>$rows ],   // optional: chia nhỏ item
  'style_targets' => [ 'heading'=>['label'=>..,'selector'=>'.x','features'=>[..]], ... ],  // tab Style
]
```

---

## 3. Style controls (chia nhỏ tới từng phần)

### 3.1 Cơ chế
- `style_targets` = mảng các "target", mỗi target = 1 section trong tab Style, map tới 1 selector **tương đối** (Elementor tự bọc `{{WRAPPER}}` → chỉ ảnh hưởng instance đó).
- `mgk_register_style_section()` đọc `features` để emit control:
  - `typography` → Group_Control_Typography
  - `align` → CHOOSE (text-align)
  - `color` → COLOR (color)
  - `background` → COLOR (background-color)
  - `padding`/`margin` → DIMENSIONS
  - `border` → Group_Control_Border + radius DIMENSIONS
  - `shadow` → Group_Control_Box_Shadow
  - `hover` → COLOR text + bg trên `{{WRAPPER}} <sel>:hover`
- Helpers: `mgk_content_targets($outer,$heading,$sub,$extra=[])`, `mgk_data_targets($outer,$heading=null,$sub=null)`, `mgk_style_text($label,$sel,$features)`, `mgk_style_button($label,$sel)`.

### 3.2 Chính sách CONTENT vs DATA (user đã chốt)
- **CONTENT** (marketing copy): chia **từng phần tử** — heading, sub-heading, eyebrow, từng item (step title/body, card title/body, question/answer), từng button, input, chip… Section Box có cả border/shadow/hover. Ví dụ Hero = 11 mục; how_process = 8 mục; steps = 6; why = 5.
- **DATA SHELL** (filter, toolbar, empty state, button label/style, form shell, card shell, section layout): được mở trong Elementor bằng widget/shell controls có kiểm soát. Cho sửa chữ hiển thị, alignment, màu, spacing, button, columns; KHÔNG cho sửa nguồn record/query.
- **DATA CORE** (teacher records, review records, booking/order/payment flow, SQL/query source): khóa trong PHP/wp-admin. KHÔNG có repeater nhập tay teacher/review/order trong Elementor.
- **DATA grid / pricing logic / table**: chỉ mở phần vỏ phù hợp như Heading (+ Sub), Section Box, button style, filter shell, columns. KHÔNG cho typography từng record nếu dễ phá layout → giữ presentation nhất quán, logic không vỡ. Ví dụ subjects grid = heading+section; featured_tutors = heading+sub+section; teacher listing = filter/toolbar/button/card-shell.
- Ngoại lệ hợp lý: trust_stats cho style value/label (chúng LÀ nội dung); spotlight (single curated tutor) cho eyebrow/name/2 button.
- **DATA pages** (S02 listing, S03 profile, S07-S12 booking…): KHÔNG mở raw page template để user kéo vỡ logic. Thay vào đó phải có **DATA-shell widget/template**: user sửa filter, button Apply, CTA labels, spacing, màu, form shell; data thật vẫn đọc từ wp-admin/PHP.

### 3.3 DATA sống ở wp-admin — Elementor chỉ chỉnh *vỏ*, KHÔNG bao giờ chạm *nguồn* (BẮT BUỘC nhớ)

**Nguyên tắc:** dữ liệu thật được tạo/sửa/xóa ở **wp-admin** (CPT/ACF). Elementor chỉ chỉnh **vỏ bao quanh** DATA (filter, toolbar, nhãn, cột, tiêu đề, nền, khoảng cách, style nút) — **không bao giờ** đụng vào source data. Đây là ranh giới cứng giữa *logic* (khóa) và *trình bày* (mở giới hạn).

**Luồng end-to-end (ví dụ Teacher Grid):**

| Tầng | Ai làm | Làm gì |
|---|---|---|
| **wp-admin** | Admin | Teachers → Add New Teacher → điền name / avatar / price / rating / subjects → **Save**. Đây là NGUỒN dữ liệu duy nhất. |
| **Frontend (PHP)** | Code | Teacher Grid widget **query CPT `teacher`** → `teacher-card` partial render từng teacher. Markup + logic query nằm trong PHP, 1 nguồn. |
| **Elementor (user)** | User cuối | CHỈ chỉnh shell: filter label, button Apply/Clear/View/Book, toolbar title, empty copy, grid columns, background, spacing, button style, card shell style. KHÔNG thêm/sửa/xóa teacher, KHÔNG đổi query, KHÔNG đụng source data. |

→ Thêm 1 teacher = vào wp-admin, **không** mở Elementor. Đổi giao diện lưới = vào Elementor, **không** chạm data. Hai việc tách bạch hoàn toàn.

**Hệ quả cho cấu hình widget:** DATA-shell widget chỉ expose `style_targets` và controls kiểu *vỏ* (heading + sub + section box + filter shell + toolbar + button + columns + card shell), KHÔNG expose control nội dung từng record và KHÔNG có repeater nhập-tay item (repeater chỉ dùng cho CONTENT marketing như steps/why/faq — xem §4). Selector style ưu tiên nhắm phần khung (`.mgk-section-head`, `.mgk-filter-*`, `.mgk-toolbar-*`, `.mgk-...-grid`, nút), không biến teacher/review/order thành content tĩnh.

**Ví dụ đúng cho Teacher List:** user được sửa chữ `Filters`, `Apply`, `Clear all`, layout sidebar, màu nút Apply, spacing toolbar, số cột desktop/tablet/mobile. User KHÔNG được sửa danh sách teacher trong Elementor; thêm/sửa teacher vẫn qua MGK Tutors/wp-admin.

### 3.4 GOTCHA môi trường
- Elementor cần ghi CSS vào `wp-content/uploads/elementor/css/` → nếu thiếu dir + quyền → `file_put_contents Failed to open stream`. FIX: `mkdir -p` + `chown` về user webserver (image production Margick = `nginx`; image public Apache cũ = `www-data`).
- Sau khi đổi style settings: regenerate CSS = `\Elementor\Core\Files\CSS\Post::create($id)->update()` và/hoặc `\Elementor\Plugin::$instance->files_manager->clear_cache()`.

### 3.5 RULE BẤT BIẾN — DATA controls presentation, not data (áp dụng MỌI DATA widget)

> **DATA không bao giờ được sửa trực tiếp trong Elementor. Elementor chỉ điều khiển *cách* DATA được hiển thị.**

**3 lớp:**

| Lớp | Gồm gì | Sửa ở đâu |
|---|---|---|
| **DATA CORE** | teacher name, description, price, rating, review, availability, query, pagination, booking, payment, nonce | **wp-admin / CPT / ACF / PHP** — KHÔNG đưa lên Elementor edit |
| **DATA SHELL** | show/hide field, label tĩnh, số cột, layout card, button label, filter label, empty-state copy | **Elementor — safe controls** |
| **STYLE SHELL** | typography, color, spacing, border, radius, shadow, width/max-width từng phần tử data | **Elementor Style tab** |

**Ví dụ Teacher Card:** description thật nằm wp-admin. User không muốn hiện → KHÔNG xóa data, chỉ tắt `Show description = No`. Data vẫn còn trong DB; UI không render. Name/avatar/rating/price vẫn hiện.

**Rule ngắn (thuộc lòng):**
> **Elementor controls presentation, not data. · wp-admin controls data. · PHP controls logic.**

**Khi làm MỌI DATA widget/card, LUÔN thêm control an toàn:**
- Show/hide: avatar / title / description / rating / price / tags / button / badge / meta
- Style từng field
- Layout / card / columns / gap / alignment

**KHÔNG BAO GIỜ thêm control cho:** sửa trực tiếp data text động · raw query tùy ý · booking/payment rule · nonce/submission flow · review calculation · permission logic · field binding tùy ý.

**Mục tiêu:** user custom giao diện rất linh hoạt, nhưng data vẫn dynamic, logic vẫn khóa, template vẫn maintain được khi scale nhiều site.

> Hệ quả kỹ thuật: control show/hide = `SWITCHER` (default `yes`) → `render()` đọc `$settings['show_xxx']` để bọc `if`; KHÔNG đổi nguồn data, chỉ quyết định render hay không. Style/layout vẫn theo `style_targets` (§3.1). Repeater chỉ cho CONTENT marketing (steps/why/faq — §4), KHÔNG cho record động.

---

## 4. Repeater (chia section nhiều item)

- Section có danh sách item (stats/steps/why/faq) → thêm `repeater` vào config → `\Elementor\Repeater` control (tab Content).
- **Empty → fallback data mặc định** (mgk_site_home_*/mgk_get_faqs) → out-of-the-box không đổi. Defaults prefill từ chính các hàm đó.
- `map`: `assoc` (item = mảng key như stats `[value,label]`, faq `[q,a]`) hoặc `pairs` (item = `[0,1]` cho partial đọc `$x[0]/$x[1]` như steps/why).
- `mgk_elementor_repeater_items()` map rows → shape partial; bỏ row rỗng hết.
- Partial sửa 1 dòng: `$x = (!empty($args['items']) && is_array($args['items'])) ? $args['items'] : <default fn>()`.

---

## 5. Generator + phân phối (direction A — KHÔNG đổi)

- `inc/mgk-generator.php`: `wp mgk gen-layouts` → emit `seed/seed-layouts.php`.
- Elementor: layout = `_elementor_data` JSON (section>column(100%)>widget/block, settings rỗng → fallback). ID hex tất định `md5('mgk-elementor:'.seed)` (không random → diff sạch).
- `mgk_seed_layout()` ghi 3 meta (`_elementor_data`, `_elementor_edit_mode=builder`, `_elementor_version`), idempotent guard `_mgk_layout_seeded`, skip page user đã build.
- **Mỗi biến thể (trong 300) = 1 cấu hình generator** (đổi danh sách section/thứ tự + tokens), KHÔNG phải file JSON tay. Đổi design = sửa PHP + bump version → chỉ áp install MỚI.

---

## 6. Design system (nền móng cho 300 template) — ĐANG TRIỂN KHAI

> Đây là phần "đặc biệt chú ý". Quyết định: **tokens là 1 nguồn, bắc cầu 2 chiều sang Elementor Global.**

Kế hoạch (chưa hoàn tất — xem mục TODO):
1. **CSS tokens** (`assets/css/mgk-tokens.css`): `--mgk-accent`, `--mgk-fg`, `--mgk-font`, spacing… = nguồn thật, version-control.
2. **Bridge → Elementor Global Colors/Fonts**: map token ↔ Global để user đổi brand 1 chỗ trong Elementor → cả site đổi, mà vẫn giữ token gốc trong code. (Cơ chế: set Elementor Kit defaults từ token lúc seed, + filter cho phép Global override token qua CSS var.)
3. **Component library 2 tầng**: PHP partials (`template-parts/components/`: card, button, accordion, cta-band, pricing-card, subject-card) + CSS component classes (`.mgk-btn-*`, `.mgk-card`, …). Widget MGK tái dùng chúng → 300 template share 1 bộ component.
4. **Factory** `child-theme-base/` chứa phần chung; mỗi template chỉ viết sections + data logic + seed riêng.

---

## 7. Quy trình build 8 bước (Elementor)

0. `new-template.sh` (cần cập nhật cho Elementor: parent hello-elementor, inner dir tên theme).
1. Phân loại screens CONTENT/DATA (quy tắc §1.2 bản Flatsome).
2. DATA pages: PHP template + `get_template_part()` + logic trong `inc/` cho DATA CORE; đồng thời tạo DATA-shell widget/template cho filter, toolbar, CTA, form shell, spacing/style.
3. CONTENT sections: shortcode wraps partial (1 nguồn HTML).
4. Đăng ký widget Elementor: thêm vào `mgk_elementor_sections()` (controls + repeater + style_targets). Builder-mode trong page templates dùng `mgk_is_built_with_elementor()`.
5. Generator: `wp mgk gen-layouts` → seed-layouts.php (Elementor JSON), thêm vào manifest seed_files.
6. Customizer cho DATA pages (text nhãn/màu/toggle).
7. QA release-ready: DB sạch → activate → khớp wireframe 100%; user kéo-thả CONTENT không vỡ; DATA shell sửa được; DATA core khóa; re-seed không đè; CSS Elementor ghi được.
8. Bundle & ship (cập nhật bundle.sh cho inner dir mới).

---

## 8. Trạng thái mgk-edu-elementor (mốc 2026-06-02)

- Sandbox chạy: `mgk_edu_elementor_run/` (docker compose riêng, **port 8090**, WP php8.2-apache + mariadb). Admin test/test. DB import từ mgk_edu + 109 theme_mods `mgk_*` đã sync từ 8080.
- 46 widget đăng ký, category "MGK Edu". 4 trang CONTENT (home/subjects/how-it-works/pricing) đã seed `_elementor_data` (BUILDER MODE), render khớp bản gốc section-for-section.
- Repeater: trust_stats/steps/why/faq (empty→fallback). Verified.
- Style controls chi tiết: CONTENT per-element (hero 11, steps 6, why 5, how_process 8…), DATA heading+section. Verified CSS sinh đúng + scoped.
- Code nguồn: `mgk_edu/packages/mgk-edu-elementor/mgk-edu-elementor/`.

### TODO còn lại
- (B) Design system bridge tokens↔Elementor Global (mục §6) — CHƯA làm.
- DATA-shell widgets cho S02 teacher listing, S03 teacher profile, S07-S12 booking/payment: mở filter/button/form shell trong Elementor nhưng khóa record/query/order source.
- Cập nhật `new-template.sh` + `bundle.sh` cho cấu trúc Elementor (inner dir, parent).
- (Tùy chọn) "vùng mở" tường minh: cho phép user kéo widget Elementor gốc vào khu vực giữa các section MGK.

---

## 9. Tham chiếu
- Bản Flatsome: `docs/TEMPLATE-BUILD-PLAYBOOK.md`.
- Widget engine: `packages/mgk-edu-elementor/mgk-edu-elementor/inc/mgk-elementor.php`.
- Generator: `…/inc/mgk-generator.php`. Sandbox: `mgk_edu_elementor_run/`.
- Memory: `project-mgk-edu-elementor-variant` (chi tiết kỹ thuật + gotchas).

*Tài liệu sống — cập nhật khi quy trình đổi.*
