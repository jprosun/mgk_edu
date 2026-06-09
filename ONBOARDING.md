# MGK Template Factory — Onboarding (đọc đầu tiên)

> **Mục đích file này:** cửa vào DUY NHẤT cho bất kỳ agent/người mới. Đọc xong file này là đủ hiểu tư duy, chiến lược, structure, và biết đọc tiếp doc nào. Đừng đọc 47KB memory ngay — đọc file này trước, rồi mới đào sâu theo link.

Cập nhật: 2026-06-04

---

## 0. TL;DR — đọc 60 giây

- **Mục tiêu:** một **master template chuẩn** để nhân ra **200–300 biến thể đa ngành** (edu, fashion, F&B…), mỗi site là một WordPress hoàn chỉnh (DB + UI seed sẵn), giao cho khách.
- **Builder = Elementor (bản FREE).** Flatsome đã BỎ. Pro KHÔNG dùng (xem §4).
- **Triết lý cốt lõi (BẤT BIẾN):** **PHP-native shell + Locked Core + Editable Shell.**
  - HTML sống MỘT chỗ trong PHP partial. Widget Elementor chỉ là *vỏ mỏng* gọi `do_shortcode()` → partial. KHÔNG nhồi HTML vào builder.
  - **Data** (tutor, review, giá, catalog…) → user sửa ở **wp-admin** (CPT/ACF). KHÔNG sửa được trong editor.
  - **Giao diện** (typography/color/border/radius/shadow/spacing/width của từng phần tử) → user chỉnh trong **Elementor Style tab**. KHÔNG có ô text nào cho data.
- **Phân phối = generator seed lúc activate** (idempotent, guard `_mgk_layout_seeded`), version bằng Git. Mỗi biến thể = 1 config code, KHÔNG phải 1 file JSON tay.
- **Release-ready = DB sạch → activate → UI khớp wireframe 100% TRƯỚC khi user đụng.**

---

## 1. Tư duy của chủ dự án (đọc kỹ — đây là "đúng/sai" khi review)

Diễn đạt nguyên văn của chủ dự án, đã xác nhận nhiều lần:

> "Cho người dùng 1 template **khá hoàn chỉnh cả database + UI**. User sửa thêm theo cá nhân thôi chứ **user không phải tạo từ đầu logic dữ liệu**."

> "Khóa data" = user **không sửa data ở editor**, mà phải vào **wp-admin** add/delete/alter. Editor chỉ cho chỉnh **giao diện**.

Suy ra các nguyên tắc khi làm/review:

1. **3 loại bề mặt** — phân loại mọi thứ vào 1 trong 3:
   - **CONTENT** (marketing copy: hero, CTA, FAQ…) → widget MGK, Style ĐẦY ĐỦ + có control sửa text.
   - **DATA SHELL** (vỏ quanh data: filter, toolbar, search bar, card layout, button, label) → widget MGK, Style đầy đủ nhưng **chỉ label tĩnh**, KHÔNG sửa data.
   - **DATA CORE** (records, query, pagination, sort, compare, booking, nonce, payment, review calc) → **PHP/wp-admin, KHÓA tuyệt đối**, không bao giờ lên editor.
2. **Quy tắc thẻ DATA (đã chốt):** thẻ data (teacher card, review card, story card…) = **Style từng phần tử + 0 control text**. User đổi typography/màu/border/radius/shadow/width của tên/giá/tag/bio… nhưng KHÔNG đổi nội dung (nội dung từ wp-admin). Style control chỉ sinh CSS scoped → không thể đổi text → an toàn để mở.
3. **Chia nhỏ để dễ sửa:** mỗi section/card-chrome = 1 widget riêng, user chọn/đổi-thứ-tự/ẩn/style từng khối. NHƯNG nếu state dính chặt (filter↔URL↔pagination, form↔nonce↔order) thì giữ logic trong 1 renderer chung (memoized) để không lệch state — xem §3.
4. **Khi mở quyền sửa cho user:** luôn tự hỏi "cái này là CONTENT / DATA-SHELL / DATA-CORE?" → quyết định cho sửa text hay chỉ Style hay khóa hẳn.

---

## 1.5. RULE BẤT BIẾN — DATA không bao giờ sửa trong Elementor (đọc kỹ, áp dụng mọi DATA widget)

> **Trong MGK Template Factory, DATA không bao giờ được sửa trực tiếp trong Elementor. Elementor chỉ điều khiển *cách* DATA được hiển thị.**

**3 lớp khi đụng một widget/card có data:**

1. **DATA CORE** — dữ liệu thật + logic thật: teacher name, description, price, rating, review, availability, query, pagination, booking, payment, nonce. → Chỉ sửa trong **wp-admin / CPT / ACF / PHP**. KHÔNG đưa lên Elementor để edit text/logic.
2. **DATA SHELL** — vỏ hiển thị quanh data: show/hide field, label tĩnh, số cột, layout card, button label, filter label, empty-state copy. → Chỉnh trong **Elementor bằng safe controls**.
3. **STYLE SHELL** — typography, color, spacing, border, radius, shadow, width/max-width của từng phần tử data. → Chỉnh trong **Elementor Style tab**.

**Ví dụ Teacher Card:**
- Description thật nằm trong wp-admin.
- User KHÔNG muốn hiện description → **KHÔNG xóa data** trong Elementor, chỉ tắt control `Show description = No`.
- Data description **vẫn còn trong DB**, chỉ là UI không render phần đó. Các field khác (name, avatar, rating, price) vẫn hiện bình thường.

**Rule ngắn (thuộc lòng):**
> **Elementor controls presentation, not data. · wp-admin controls data. · PHP controls logic.**

**Khi làm MỌI DATA widget/card, LUÔN thêm các control an toàn:**
- Show/hide: avatar / title / description / rating / price / tags / button / badge / meta
- Style từng field
- Layout / card / columns / gap / alignment

**KHÔNG BAO GIỜ thêm control cho:**
- sửa trực tiếp data text động
- raw query tùy ý
- booking / payment rule
- nonce / submission flow
- review calculation
- permission logic
- field binding tùy ý

**Mục tiêu:** user custom giao diện rất linh hoạt, nhưng **data vẫn dynamic, logic vẫn khóa, template vẫn maintain được khi scale nhiều site.**

---

## 2. Structure dự án (cây thư mục + vai trò)

> **★ Từ 2026-06-04: workspace chính là `mgk_edu_elementor/`** (đứng độc lập, đầy đủ wp/theme/docs/scripts, chạy image production Margick port 8091). Sandbox Elementor cũ `mgk_edu_elementor_run/` (port 8090, image public) **ĐÃ XÓA** sau khi state migrate đủ sang đây. `mgk_edu/` (Flatsome, port 8080) GIỮ LẠI làm lịch sử — KHÔNG làm việc trên đó.

```
margick-template/
├── ONBOARDING.md                ← file này (cửa vào)
├── mgk_edu_elementor/           ★★ WORKSPACE CHÍNH (Elementor, image production, port 8091)
│   ├── README.md                vòng đời stack + cách tạo biến thể (đọc cùng file này)
│   ├── docker-compose.yml        image magicak-wordpress-wp-template:latest, port 8091
│   ├── run.sh / restore-db.sh / rebuild.sh   start / nạp DB / lưu state
│   ├── new-template.sh / bundle.sh           clone biến thể / đóng gói zip
│   ├── my-local-override.cnf / php-fpm-local-override.conf / uploads.ini / wordpress_src/wp-config.php
│   ├── db.sql                    DB seed (dump state thật)
│   ├── data/                     webroot mount (full WP core + wp-content) — RUNTIME
│   │   └── wp-content/themes/mgk-edu-elementor/   (COPY runtime — sync từ SOURCE)
│   ├── docs/                     TEMPLATE-BUILD-PLAYBOOK-ELEMENTOR + SRS + ARCHITECTURE-magicak-wordpress
│   └── packages/
│       └── mgk-edu-elementor/mgk-edu-elementor/   ★ SOURCE master (Hello Elementor child)
│           ├── functions.php          (load order — require inc/* ; render-files TRƯỚC mgk-elementor.php)
│           ├── inc/
│           │   ├── mgk-elementor.php       ★ đăng ký TẤT CẢ widget + Style controls (config-driven)
│           │   ├── mgk-generator.php       ★ build _elementor_data + `wp mgk gen-layouts` + layout map
│           │   ├── mgk-listing-render.php  (S02 single-source renderer + 6 sub-shortcodes)
│           │   ├── mgk-profile-render.php  (S03 single-source renderer + 14 sub-shortcodes)
│           │   ├── mgk-states-render.php   (9 state shortcodes)
│           │   ├── mgk-content-sections.php(S04/S05/S06 shortcodes wrap partial)
│           │   ├── mgk-sections.php        (S01 home + topbar shortcodes)
│           │   ├── mgk-commerce.php        (Woo edu-relabel + order flow)
│           │   ├── mgk-booking.php         (request/proposals/slot shortcodes)
│           │   ├── mgk-site-settings.php   (MGK Site Settings admin + logo + menu helpers)
│           │   ├── mgk-setup.php           (theme support + elementor_cpt_support fix)
│           │   ├── mgk-cpts.php / mgk-acf-fields.php / mgk-db-tutors.php (DATA CORE)
│           │   └── …
│           ├── template-parts/             ★ HTML sống ở ĐÂY (sections/ components/ states/) — 1 nguồn
│           ├── page-*.php / single-*.php / front-page.php (BUILDER vs DEFAULT mode)
│           ├── assets/css/ (mgk-*.css; mgk-tokens.css = design tokens; mgk-woo.css)
│           ├── schemas/ (per-category: edu.php, fashion.php… — đa ngành)
│           └── seed/ (manifest.json + seed-*.php + seed-layouts.php GENERATED)
└── mgk_edu/                     (LỊCH SỬ — Flatsome, port 8080) — không làm việc trên đây
                                  (mgk_edu_elementor_run/ sandbox cũ port 8090 ĐÃ XÓA 2026-06-04)
```

**`mgk-edu-elementor` là master THỬ NGHIỆM** để rút ra tư duy/structure chuẩn. Mọi template về sau clone từ đây (`./new-template.sh <slug> <category>`) — Elementor, PHP-native, 3 bề mặt, generator.

---

## 3. Luồng làm việc bắt buộc (sync + lint + verify)

Theme KHÔNG bind-mount. Sửa SOURCE → phải sync sang RUNTIME thì container mới thấy.

```
SOURCE  = mgk_edu_elementor/packages/mgk-edu-elementor/mgk-edu-elementor/
RUNTIME = mgk_edu_elementor/data/wp-content/themes/mgk-edu-elementor/
Container = mgk-edu-el  (port 8091, image production Margick có wp-cli)
WP-CLI    = docker exec mgk-edu-el wp --allow-root --path=/var/www/html …
```

Mỗi lần đổi code:
1. Sửa ở SOURCE.
2. `cp` file sang RUNTIME (PHP không có host binary — lint trong container).
3. `docker exec mgk-edu-el php -l /var/www/html/wp-content/themes/mgk-edu-elementor/<file>`
4. Verify front-end bằng `curl` (đếm widget/card/leak) + headless puppeteer (`/tmp/uxbcap/*.js`) cho editor.
5. Đổi layout seed → `wp mgk gen-layouts` (ghi vào RUNTIME) → `cp seed-layouts.php` ngược lại SOURCE. (Hoặc `./rebuild.sh` sync ngược RUNTIME→SOURCE + dump DB.)
6. Seed lại page test → set `_mgk_layout_seeded` (NẾU không sẽ bị auto-seed ghi đè) → `files_manager->clear_cache()` + `wp_cache_flush()`.
7. Cuối: `diff -rq SOURCE RUNTIME` phải SẠCH.

**GOTCHA hay gặp** (chi tiết trong memory `project-mgk-edu-elementor-variant`):
- Class widget phải định nghĩa LAZY trong hàm (không file-scope) vì require sớm hơn Elementor load.
- `mg_teacher` (CPT) phải có trong option `elementor_cpt_support` mới mở editor được.
- Sau khi sửa `_elementor_data` tay phải clear Elementor cache.
- DATA page split 2-cột (filter|results) phải seed vào MỘT section 2-column, không xếp section dọc.

---

## 4. Quyết định đã chốt về Elementor Free vs Pro (đừng bàn lại nếu không có lý do mới)

- **Dùng FREE.** Đã phân tích kỹ với chủ dự án:
  - Custom CSS / custom Woo / khóa-data / template-hoàn-chỉnh / user-sửa-thêm / 300 template đa ngành / maintain tập trung → **Free + code-in-theme làm được hết.**
  - Pro = builder-native (user tự dựng từ đầu, Loop Builder, Dynamic Tags) → **NGƯỢC** mô hình locked-shell. Pro KHÔNG cho "sửa data tại UI" (data vẫn ở wp-admin cả 2 bản) — nó chỉ cho kéo-thả *cách hiển thị*, đúng cái ta đang khóa.
  - 300 site dùng 1 license Pro = vi phạm điều khoản → **mất update bảo mật** (code GPL dùng lại được nhưng key chỉ hợp lệ cho số site đã mua).
  - **Free KHÔNG kém bảo mật hơn Pro** — cùng codebase, update tự động qua wordpress.org, bề mặt tấn công nhỏ hơn. Nguy hiểm nhất là Pro-không-update.
- **Custom Elementor = qua HOOK trong theme**, KHÔNG fork code lõi `plugins/elementor/` (fork = mất update bảo mật + bị đè khi update). Mọi tùy biến nằm trong `inc/mgk-elementor.php` của mình.

---

## 5. Đã làm xong (trên bản thử nghiệm) — để agent mới không làm lại

- 79 widget MGK (Free). Cả 6 page chính chia nhỏ thành section/widget riêng: Home 15, Teachers(S02) 6, Profile(S03) 14, Subjects 9, How 11, Pricing 10.
- Thẻ DATA (teacher card, review, story, package…) có Style per-element + 0 control text (đổi giao diện, không đổi data).
- Button có thêm Width/Max-width. States 9 widget. Woo relabel edu (cart/checkout/account + page titles).
- S02 listing: 2-cột filter|results native, promo+related nằm trong cột results dưới grid.
- Generator + seed-layouts (8 pages), guard tag chưa đăng ký.

## 6. Việc đang dở / TODO (ưu tiên)

1. **Header/Footer + Logo cho user sửa** (đang bàn, chưa làm) — hướng đã chốt FREE:
   - Logo → bridge sang **WP Customizer Site Identity** (chuẩn `custom_logo`) + giữ MGK Site Settings.
   - Header/Footer → widget MGK + control đổi logo/menu/màu/nút (sửa giao diện, không phá cấu trúc). KHÔNG Pro Theme Builder.
2. **Design-system token ↔ Elementor Global Colors/Fonts bridge** (`mgk-tokens.css` ↔ Kit) — cho đa ngành. Chưa làm.
3. ~~Bundle/new-template script cho Elementor~~ ✅ **XONG (2026-06-04)** — `mgk_edu_elementor/new-template.sh` (clone master → `packages/<slug>/<slug>/`, đổi Theme Name + manifest slug/category, sinh schema rỗng), `bundle.sh` (check `Template: hello-elementor` + `required_plugins` + seed_files → zip), `run.sh`/`restore-db.sh`/`rebuild.sh`.
4. Dọn gọn memory `project-mgk-edu-elementor-variant` (47KB, theo dòng thời gian) nếu cần.

---

## 7. Đọc tiếp gì (theo thứ tự)

1. **`mgk_edu/docs/TEMPLATE-BUILD-PLAYBOOK-ELEMENTOR.md`** — kỹ thuật chi tiết: widget class, render(), config, Style helpers, generator, BUILDER vs DEFAULT mode, mọi GOTCHA. **Bắt buộc trước khi code.**
2. **Memory `project-mgk-edu-elementor-variant`** — nhật ký mọi thứ đã làm + bug đã sửa + verify. Tra khi cần chi tiết một việc cụ thể.
3. **`mgk_edu/docs/FLATSOME-SHORTCODE-AUDIT.md`** — checklist nội dung từng page cần convert (đọc như danh sách việc, bỏ qua phần cơ chế Flatsome).
4. **`mgk_edu/docs/mgk_edu_wordpress_full_package_srs_*.md`** — SRS chức năng (FR groups, state machines) nếu cần hiểu nghiệp vụ edu.
5. Memory khác: `project-mgk-edu-build-playbook`, `project-mgk-edu-fr-status`, `project-mgk-edu-design-gaps`, `project-mgk-edu-api-integrations`, `project-mgk-edu-state-machines`.

---

## 8. Luật vàng khi làm việc với chủ dự án

- **Hỏi trước khi đụng gì ảnh hưởng trực tiếp tới system/DB** (nguyên văn: "cần hỏi gì thì hãy hỏi trước khi quyết định làm gì ảnh hưởng trực tiếp đến system").
- **Đây là Elementor, KHÔNG phải Flatsome** — đừng nhầm lẫn (đã từng nhầm).
- Khi tách/mở quyền sửa: luôn phân loại CONTENT / DATA-SHELL / DATA-CORE trước.
- Verify bằng bằng chứng thật (curl đếm, CSS sinh ra, screenshot) — đừng nói "xong" khi chưa kiểm.
