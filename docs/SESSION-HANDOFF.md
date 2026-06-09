# MGK Template Factory — Session Handoff (đọc đầu tiên ở chat mới)

> **Mục đích:** Một file để chat/agent mới hiểu **(0) tư duy FACTORY** — không chỉ template edu này mà cách nhân ra 200–300 template đa ngành; **(1) tư duy thiết kế** "local → user sửa trên Elementor"; **(2) luồng công việc đã làm** (S07→S12); **(3) đọc tiếp file nào**. Cập nhật: 2026-06-08.
>
> **edu chỉ là MASTER THỬ NGHIỆM** để rút ra tư duy/structure chuẩn. Mọi template về sau clone từ đây (`./new-template.sh <slug> <category>`). Đọc Tầng 0 để hiểu phần "về sau".

---

## 0. Đọc theo thứ tự này

**Tầng 0 — TƯ DUY FACTORY (cho MỌI template về sau, không chỉ edu):**
1. `/home/developer/MARGICK/margick-template/ONBOARDING.md` — cửa vào duy nhất: mục tiêu 200–300 biến thể, Free-not-Pro, 3 bề mặt, generator.
2. `…/memory/project_mgk_stack_decisions.md` — CHỐT: tất cả template = Elementor FREE; custom qua hook không fork lõi; PHP-native + generator cho 200–300 template đa ngành.
3. `…/memory/project_mgk_edu_build_playbook.md` — Locked Core + Editable Shell, CONTENT vs DATA pages, generator direction.
4. `mgk_edu_elementor/README.md` — vòng đời stack + **cách nhân biến thể** (`new-template.sh`, `bundle.sh`, SOURCE/RUNTIME).
5. `mgk_edu_elementor/docs/ARCHITECTURE-magicak-wordpress.md` — kiến trúc image production (layer build, runtime) — đọc khi cần hiểu hạ tầng.
6. `mgk_edu_elementor/new-template.sh` + `bundle.sh` — script clone master → biến thể mới + đóng gói zip (đọc để hiểu cơ chế factory).
7. `packages/…/schemas/_common.php` + `schemas/edu.php` — schema đa ngành (mỗi category 1 file): cách tách data theo ngành.

**Tầng 1 — RULE thiết kế bất biến (BẮT BUỘC trước khi code):**
1. `…/memory/feedback_data_controls_presentation.md` — RULE: Elementor = presentation, wp-admin = data, PHP = logic.
2. `…/memory/feedback_focus_elementor_only.md` — chỉ làm trên mgk_edu_elementor.
3. `mgk_edu_elementor/docs/TEMPLATE-BUILD-PLAYBOOK-ELEMENTOR.md` — kỹ thuật widget/Style/generator/GOTCHA.
4. `…/memory/project_mgk_edu_elementor_variant.md` — nhật ký kỹ thuật + mọi GOTCHA đã gặp.

**Tầng 2 — Luồng nghiệp vụ đã làm (template edu cụ thể):**
`…/memory/` → `project_screen_nav_map`, `project_db_schema_mapping`, `project_s07_s08_backend`, `project_booking_flow_s09_s11`, `project_request_section_widget_pattern`, `project_mgk_elementor_workspace`.

**Tầng 3 — Code mẫu (đọc 1 bộ S07 là hiểu pattern 3 lớp):**
`packages/mgk-edu-elementor/mgk-edu-elementor/` →
- `functions.php` (load order)
- `inc/mgk-request-fields.php` (widget vỏ = DATA SHELL)
- `inc/mgk-forms.php` (DATA CORE = logic khóa)
- `template-parts/sections/request/request-fields.php` (HTML = 1 nguồn)
- `inc/mgk-generator.php` (seed `_elementor_data` lúc activate) + `seed/manifest.json` (slug/category/required_plugins)
- ⚠️ `inc/mgk-elementor.php` (4157 dòng) — **KHÔNG nạp full**, chỉ grep phần cần.

(`…/memory/` = `/home/developer/.claude/projects/-home-developer-MARGICK-margick-template/memory`)

---

## 0.5. TƯ DUY FACTORY — vì sao mọi template về sau giống nhau (đọc kỹ)

- **Mục tiêu cuối:** 1 master template chuẩn → nhân ra **200–300 biến thể đa ngành** (edu, fashion, F&B…). Mỗi site = 1 WordPress hoàn chỉnh (DB + UI seed sẵn), giao cho khách.
- **Builder = Elementor FREE** (không Pro — xem §4 ONBOARDING). Custom qua **hook trong theme**, KHÔNG fork lõi Elementor.
- **Bất biến cho mọi ngành:**
  - HTML sống 1 chỗ trong PHP partial; widget Elementor chỉ là vỏ mỏng `do_shortcode()`.
  - 3 bề mặt CONTENT / DATA-SHELL / DATA-CORE áp dụng y hệt cho mọi ngành.
  - Data → wp-admin (CPT/ACF); options dropdown từ taxonomy (KHÔNG hardcode).
  - Phân phối = generator seed `_elementor_data` lúc activate (idempotent, guard `_mgk_layout_seeded`); version bằng Git.
- **Cái thay đổi theo ngành = chỉ phần "schema" + "content".** Cơ chế (widget vỏ / render / generator / 3 bề mặt / workflow) giữ NGUYÊN. → `schemas/<category>.php` định nghĩa data theo ngành; clone master bằng `new-template.sh <slug> <category>` (đổi Theme Name + manifest slug/category, sinh schema rỗng).
- **edu = master thử nghiệm.** Khi làm template ngành khác: KHÔNG bắt đầu từ số 0 — clone edu, thay schema + content, giữ toàn bộ cơ chế.

---

## 1. TƯ DUY CỐT LÕI (đây là "đúng/sai" khi review)

> Giao user một template **hoàn chỉnh cả DATABASE + UI**. User **không tạo logic từ đầu**, chỉ sửa thêm. **"Khóa data"** = user KHÔNG sửa data trong editor, phải vào **wp-admin** add/edit. Editor chỉ cho chỉnh **giao diện**.

**3 bề mặt — phân loại mọi thứ vào 1 trong 3:**
- **CONTENT** (marketing: hero, CTA, FAQ) → widget MGK, Style đầy đủ + sửa được text.
- **DATA SHELL** (vỏ quanh data: filter, label, button, card layout) → widget MGK, Style đầy đủ, **label tĩnh**, KHÔNG sửa data.
- **DATA CORE** (records, query, booking, payment, nonce, review calc) → **PHP/wp-admin, KHÓA tuyệt đối**, không lên editor.

**Rule thuộc lòng:** *Elementor controls presentation · wp-admin controls data · PHP controls logic.*

**Cơ chế "local → user sửa Elementor" (pattern 3 lớp):**
```
HTML sống 1 chỗ:  template-parts/…/x.php          (1 nguồn duy nhất)
        ↑ do_shortcode()
DATA SHELL:  inc/…-render.php / -fields.php        (widget vỏ mỏng, forward SAFE copy + Style)
        ↑ đăng ký widget
Elementor:  inc/mgk-elementor.php                  (config-driven, Style controls per-element)
DATA CORE:  inc/…-forms.php / -proposals.php …     (validate, query, state — KHÓA)
Phân phối:  inc/mgk-generator.php                  (seed _elementor_data lúc activate, guard _mgk_layout_seeded)
```
→ User kéo-thả/đổi Style trên Elementor; data vẫn dynamic từ wp-admin; logic vẫn khóa.

---

## 2. MÔI TRƯỜNG & WORKFLOW (bắt buộc)

- Workspace: `/home/developer/MARGICK/margick-template/mgk_edu_elementor/`
- Container `mgk-edu-el` · **port 8091** · có wp-cli
- **SOURCE** `packages/mgk-edu-elementor/mgk-edu-elementor/` → **RUNTIME** `data/wp-content/themes/mgk-edu-elementor/`
- WP-CLI: `docker exec mgk-edu-el wp --allow-root --path=/var/www/html …`

**Mỗi lần đổi code:** sửa SOURCE → `cp` sang RUNTIME → `docker exec mgk-edu-el php -l <runtime file>` → verify (curl đếm / `wp eval`) → `diff -q SOURCE RUNTIME` phải sạch → `wp cache flush`.

**GOTCHA xuyên suốt:** trang dựng Elementor (`page-blank.php` + builder mode) render `_elementor_data` và **bỏ qua** shortcode trong post_content. Logic PHP muốn thắng phải gate qua `the_content` filter **priority 20** (sau Elementor prio 9) HOẶC ưu tiên trong page template trước `the_content()`. Đã xảy ra ở S07 confirm, S08 gate, S09 vs S15.

---

## 3. LUỒNG NGHIỆP VỤ ĐÃ LÀM (S07 → S12)

```
S01–S06 (Discovery)  — nút "Find Tutor" → S07
   ↓
S07 Request Match  (/request-match/)  — form chỉ EMAIL (bỏ phone)
   ↓ submit → lead = pending_review · KHÔNG gửi mail (chờ duyệt)
[wp-admin] meta-box "Review Request" trên Lead:
   Accept → email phụ huynh (link S08) + lead→qualified
   Reject → closed_lost, im lặng
   ↓
S08 Proposals  (/tutor-proposals/?token=)  — đọc mg_proposal thật, magic link, gate empty-state
   ↓ Select tutor
S09 Select  (/parent/trial/?lead=&tutor=)  — lead PROPOSED→ACCEPTED, giá BR-01 rate×0.6
   ↓
S10 Slot (/book-slot/) → S11 Pay (/trial-pay/) → S12 Confirm (/trial-confirmed/)
   ↓ S12 paid → email phụ huynh + file .ics đính kèm (Add to calendar trong inbox)
```

**State machine** (`inc/mgk-states.php`): captured → pending_review → qualified → matched → proposed → accepted → slot_held → … ; expired (cron 48h) ; closed_lost.

---

## 4. EMAIL FLOW (chốt cuối — quan trọng)

**Engine:** `wp_mail()` → plugin **WP Mail SMTP 4.8.0** → PHPMailer → **Gmail SMTP** `smtp.gmail.com:587` TLS.
- From: `justson280304@gmail.com` (App Password đã set) · admin_email cùng địa chỉ.
- Đổi provider (SendGrid/Mailgun/SES) chỉ sửa config plugin, **không sửa code**.

**Nguyên tắc:** KHÔNG tự gửi khi submit. **Mọi mail ra ngoài đều SAU khi admin duyệt.**

**3 email:** (1) Accept lead → link S08 · (2) Send Proposals → link S08 · (3) S12 booking confirmed → email + **.ics đính kèm**.

**Đã tắt:** WhatsApp engine (`inc/mgk-whatsapp.php`, giữ code, hook đã comment) · SMS chưa làm.

---

## 5. DATABASE (CPT + ACF, 1 site = 1 tenant, bỏ tenant_id)

- LIVE: `mg_teacher`(26), `mg_review`(11), taxonomy `mgk_subject`/`mgk_level`.
- MỚI: `mg_plan`(4), `mg_parent`(1), `mg_child`(2), `mg_lead`, `mg_proposal`(3). Seed: `seed/seed-ops-data.php` (manifest v1.5).
- Lead mẫu để test: **#428 "Mrs Chen — P5 Math"**, token `mgk-sample-chen`, 3 proposal thật.
- CHƯA build: booking/lesson/lesson_log/payment/enrolment + P1 (tutor_account, admin_user, notification, commission_config, audit_log).

---

## 6. URL TEST NHANH

- S07 form: `http://localhost:8091/request-match/`
- S07 confirm: `http://localhost:8091/request-match/?mgk_lead=mgk-sample-chen`
- S08: `http://localhost:8091/tutor-proposals/?token=mgk-sample-chen`
- S09: `http://localhost:8091/parent/trial/?lead=mgk-sample-chen&tutor=ms-sim-pei-hua`
- wp-admin Leads: `http://localhost:8091/wp-admin/edit.php?post_type=mg_lead`
- WhatsApp settings (tắt): `http://localhost:8091/wp-admin/admin.php?page=mgk-whatsapp`

---

## 7. VIỆC CÒN DỞ / LÀM TIẾP

1. **Nút gán proposal nhanh trên màn Lead** — admin chọn 3-5 tutor không phải vào CPT Proposals tạo tay (đề xuất ưu tiên).
2. **S10 lưu slot timestamp thật** → .ics + email đúng giờ (hiện demo cố định 2026-01-24 4:00-5:30pm).
3. Build tiếp CPT chưa có: booking → payment → enrolment → lesson/lesson_log.
4. S13–S16 (dashboard/messages/renewal/review) nối DB thật (hiện một phần demo).
5. Production: cân nhắc đổi Gmail SMTP → SendGrid/Mailgun/SES (Gmail ~500 mail/ngày).

---

## 8. LUẬT VÀNG

- **Hỏi trước khi đụng gì ảnh hưởng system/DB.**
- Đây là **Elementor, KHÔNG phải Flatsome.**
- Verify bằng bằng chứng thật (curl đếm, wp eval, php -l) — không nói "xong" khi chưa kiểm.
- Phân loại CONTENT / DATA-SHELL / DATA-CORE trước khi mở quyền sửa.
- DATA không bao giờ sửa trong Elementor; dropdown options PHẢI từ taxonomy wp-admin (không hardcode).
