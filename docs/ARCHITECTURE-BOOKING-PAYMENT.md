# Kiến trúc Booking & Payment — mgk-edu-elementor

> **Phiên bản:** Booking Engine Phase 0.5
> **Cập nhật:** 2026-06-11
> **Phạm vi:** Toàn bộ luồng đặt lịch học thử (trial) — từ chọn slot → giữ chỗ → thanh toán → xác nhận.
> **Trạng thái:** Backend DONE + đã verify end-to-end (MOCK mode). Để nhận tiền thật chỉ cần cắm Stripe key + UEN PayNow thật, không cần viết thêm code payment.

---

## 0. TL;DR

- **Source of truth = 4 bảng custom** (`mgk_bookings`, `mgk_slot_block_locks`, `mgk_payments`, `mgk_booking_events`), **không** dùng CPT làm gốc. `mg_booking` CPT chỉ là bản mirror 1 chiều để xem trong wp-admin.
- **Chống double-book** dựa vào `UNIQUE KEY (tutor_post_id, block_start_at_utc)` trên bảng locks — không dựa vào pre-read. Hai request tranh nhau cùng slot, đúng 1 cái thắng ở câu INSERT.
- **Quy tắc vàng:** booking chỉ thành `CONFIRMED` từ **webhook đã verify** (hoặc admin override). Không bao giờ tin redirect trình duyệt.
- **Frontend hoàn toàn "không đáng tin"** — chỉ gọi REST `mgk/v1`, mọi quyết định ở server. Giá cũng do server tự tính (`server-authoritative price`).
- **2 cổng thanh toán:** Stripe (auto-confirm qua webhook) + PayNow QR (đối soát thủ công, không auto-confirm).
- **2 chế độ Stripe:** MOCK (chưa có key, test full flow) ↔ LIVE (dán key, không sửa code).

---

## 1. Tổng quan phân lớp

```
┌─────────────────────────────────────────────────────────────┐
│ FRONTEND (Elementor widgets + JS)                            │
│  S10 book-slot · S11 trial-pay · S12 trial-confirmed         │
│  assets/js/mgk-pay.js  →  CHỈ gọi REST, không đụng DB         │
└───────────────────────────┬─────────────────────────────────┘
                            │ HTTP (REST namespace mgk/v1)
┌───────────────────────────▼─────────────────────────────────┐
│ REST LAYER — booking-rest.php                                │
│  availability · hold · attach-contact · create-checkout      │
│  paynow-qr · release · stripe/webhook · booking/{id} · cancel│
└───────────────────────────┬─────────────────────────────────┘
                            │ gọi engine functions (mgk_engine_*, mgk_stripe_*)
┌───────────────────────────▼─────────────────────────────────┐
│ ENGINE (DATA CORE — LOCKED)                                  │
│  locks (atomic hold) · availability · payment-stripe ·       │
│  paynow · cron (expiry) · events (audit) · mirror            │
└───────────────────────────┬─────────────────────────────────┘
                            │ SQL (transaction + UNIQUE constraint)
┌───────────────────────────▼─────────────────────────────────┐
│ DATA — 4 bảng custom (source of truth)                       │
│  mgk_bookings · mgk_slot_block_locks · mgk_payments ·        │
│  mgk_booking_events        ──mirror──>  CPT mg_booking        │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. File & vai trò (`inc/booking/`)

Thứ tự nạp trong `functions.php` (dòng 96–105):

| # | File | Vai trò | Hàm chính |
|---|------|---------|-----------|
| 1 | `booking-schema.php` | Định nghĩa 4 bảng + tên bảng; tự `dbDelta` khi đổi version | `mgk_booking_table()`, `mgk_booking_install_schema()` |
| 2 | `booking-events.php` | Ghi audit log + idempotency webhook | `mgk_log_booking_event()`, `mgk_mark_webhook_processed()` |
| 3 | `booking-view.php` | Helper thời gian / timezone / đọc row | `mgk_get_booking_row()`, `mgk_booking_now_utc()`, `mgk_booking_tz()` |
| 4 | `booking-availability.php` | Tính slot trống + lazy-filter hold hết hạn | `mgk_engine_available_slots()`, `mgk_get_active_lock_block_set()`, `mgk_expand_to_blocks()` |
| 5 | `booking-locks.php` | **Trái tim** — atomic hold / release / promote lock | `mgk_engine_hold_slot()`, `mgk_engine_release_hold()`, `mgk_engine_promote_locks_to_booking()` |
| 6 | `booking-cron.php` | Cron 60s flip HELD hết hạn → EXPIRED (housekeeping) | `mgk_engine_expire_holds()` |
| 7 | `booking-paynow.php` | QR EMVCo + cấu hình method nào active | `mgk_payment_config()`, `mgk_paynow_build_payload()` |
| 8 | `booking-payment-stripe.php` | Checkout + webhook handler + verify chữ ký | `mgk_stripe_create_checkout()`, `mgk_stripe_handle_event()`, `mgk_stripe_confirm_paid()` |
| 9 | `booking-rest.php` | Toàn bộ REST endpoint `mgk/v1` | `mgk_rest_*` |
| 10 | `booking-mirror.php` | Mirror 1 chiều booking → CPT `mg_booking` | `mgk_mirror_booking_to_cpt()` |
| + | `booking-admin*.php` | UI admin: force-confirm, cancel-refund, list bookings | — |

---

## 3. Data model — 4 bảng custom

> Tất cả datetime lưu **UTC**, cột đặt tên `*_utc`. Một site = một agency (KHÔNG có `tenant_id`).

### 3.1 `wp_mgk_bookings` — source of truth
Một row mỗi lần đặt (trial / package / reschedule).

Cột chính: `id`, `booking_code` (MGK-YYYYMMDD-XXXXXX), `tutor_post_id`, `lead_id`, `parent_user_id`, `student_name`, `subject`, `lesson_type`, `slot_key`, `start_at_utc`, `end_at_utc`, `timezone`, `status`, `payment_status`, `price_amount`, `currency`, `idempotency_key`, `hold_expires_at_utc`, `confirmed_at_utc`, `cancelled_at_utc`.

Khóa quan trọng:
- `UNIQUE uniq_booking_code` — mã booking duy nhất.
- `UNIQUE uniq_idempotency` — chống tạo trùng hold khi double-submit.
- `KEY idx_hold_expires (status, hold_expires_at_utc)` — quét hold hết hạn nhanh.

### 3.2 `wp_mgk_slot_block_locks` — chống double-book (Painpoint A)
Một row mỗi **block 15 phút** mà một booking đang chiếm.

Cột: `tutor_post_id`, `booking_id`, `block_start_at_utc`, `lock_type` (`HOLD`|`BOOKING`), `expires_at_utc` (NULL với BOOKING = vĩnh viễn).

**Khóa quyết định:**
```sql
UNIQUE KEY uniq_active_block (tutor_post_id, block_start_at_utc)
```
- Chỉ chứa `(tutor, block)` — **KHÔNG** gồm `lock_type`/`status`.
- Row tồn tại **chỉ khi** block đang bị chiếm. Release/expiry = **DELETE row** (không flip sang RELEASED) → tránh lỗi "Duplicate entry" khi release lần 2; lịch sử nằm ở bảng events.

### 3.3 `wp_mgk_payments` — liên kết Stripe + idempotency
Một row mỗi lần thanh toán.

Cột: `booking_id`, `provider` (`STRIPE`|`PAYNOW`), `provider_checkout_session_id`, `provider_payment_intent_id`, `latest_webhook_event_id`, `amount`, `currency`, `status` (`PENDING`/`PAID`/`SUCCEEDED`/`FAILED`/`EXPIRED`), `paid_at_utc`, `failed_at_utc`.

Khóa: `UNIQUE uniq_checkout_session`, `UNIQUE uniq_payment_intent` — một session/intent map đúng một payment.

### 3.4 `wp_mgk_booking_events` — audit log + webhook idempotency
Một row mỗi sự kiện trong vòng đời booking.

Cột: `booking_id`, `actor_type` (`PARENT`/`SYSTEM`/`WEBHOOK`/`ADMIN`), `actor_id`, `event_type`, `old_status`, `new_status`, `provider`, `provider_event_id`, `metadata_json`.

**Khóa quyết định:** `UNIQUE uniq_provider_event (provider, provider_event_id)` — webhook gửi trùng = no-op an toàn (idempotent).

### 3.5 `mg_booking` (CPT) — mirror 1 chiều (Painpoint D)
Không phải source of truth. Cập nhật qua hook `mgk_booking_confirmed` / `mgk_booking_status_changed` để admin/dashboard đọc bằng API WordPress quen thuộc.

---

## 4. State machine

### 4.1 Booking status
```
                ┌──────────────── (cron / lazy / user release) ──────┐
                ▼                                                     │
   HELD ──pay (create-checkout)──> PENDING_PAYMENT ──webhook OK──> CONFIRMED + PAID
    │                                   │                              │
    │ hết hạn hold (10 phút)            │ webhook failed:              │ (admin)
    ▼                                   │  - hold còn → giữ PENDING     ▼
  EXPIRED                               │  - hold hết → FAILED_PAYMENT  CANCELLED / RESCHEDULED
  (xóa HOLD lock)                       │
                                        └─ lệch tiền HOẶC mất slot → MANUAL_REVIEW
                                           (payment vẫn PAID, nhưng không confirm)
```

### 4.2 payment_status
`PENDING → PAID` (Stripe webhook) · `→ FAILED` · `→ EXPIRED`. PayNow: payment row dùng `SUCCEEDED` khi admin đối soát.

### 4.3 Lock lifecycle
```
INSERT HOLD lock (expires = now + 10ph)  ──confirm──>  UPDATE lock_type=BOOKING, expires=NULL
                  │                                       (hoặc re-INSERT nếu đã bị cron xóa)
                  └── release/expiry ──> DELETE row
```

---

## 5. Atomic hold — cơ chế chống double-book

`mgk_engine_hold_slot()` chạy trong **một transaction**:

```
START TRANSACTION
 1. DELETE các HOLD lock hết hạn của tutor này        (lazy cleanup, painpoint C)
 2. INSERT booking row (status = HELD)
 3. INSERT 1 lock row mỗi block 15 phút của slot
      └─ nếu BẤT KỲ insert nào đụng uniq_active_block
         → block đã có người giữ → ROLLBACK → 409 SLOT_ALREADY_TAKEN
            (kèm tối đa 3 slot thay thế gần đó)
 4. Stamp hold_expires_at + COMMIT + ghi event SLOT_HELD
```

**Tính đúng đắn đến từ câu UNIQUE INSERT ở bước 3, KHÔNG từ pre-read.** Hai hold đồng thời đều tới bước 3, đúng một cái thắng. (Đã verify thực tế: 5 concurrent → 1 winner.)

Các đặc tính:
- **Giá server-authoritative:** nếu client không gửi giá hợp lệ, server tính lại trial price từ rate của tutor (`mgk_engine_trial_price_for_tutor`) — đúng bằng giá S09/S11 hiển thị và bằng số tiền charge.
- **Idempotency:** cùng `idempotency_key` → trả về booking cũ thay vì tạo mới (chống double-submit).
- **Hold TTL = 600s** (`MGK_HOLD_SECONDS`, 10 phút).
- **Lazy expiry:** một HOLD lock đã `expires_at_utc < now` được coi như **đã biến mất** ngay khi đọc availability/hold, kể cả khi cron chưa kịp xóa row. → **Tính đúng đắn KHÔNG phụ thuộc WP-Cron.**

---

## 6. Luồng REST (`mgk/v1`)

| Method | Endpoint | Bước | Mô tả | Auth |
|--------|----------|------|-------|------|
| GET | `/tutors/{id}/availability` | S10 | Slot trống (lazy-filter hold hết hạn) | public |
| POST | `/booking/hold` | S10 | Tạo HELD + lock + countdown | public |
| GET | `/booking/{id}` | S11/S12 | Poll trạng thái (public-safe view) | public |
| POST | `/booking/{id}/attach-contact` | S11 | Lưu email/phone/name (cho tạo account) | public |
| POST | `/booking/{id}/create-stripe-checkout` | S11 | Tạo Checkout → trả `checkout_url` | public |
| GET | `/booking/{id}/paynow-qr` | S11 | Trả payload QR EMVCo (vẽ client-side) | public |
| POST | `/booking/{id}/release` | S11 | Bỏ hold khi rời trang (giải phóng slot ngay) | public |
| POST | `/stripe/webhook` | S11→S12 | Stripe gọi → verify chữ ký → confirm | signature |
| POST | `/booking/{id}/cancel` | S12+ | Hủy booking | logged-in (đang STUB 501) |

> Các endpoint public dùng `booking_id` làm khóa truy cập. Production hardening (hold-token gắn với browser đang trả tiền) được theo dõi cho cả flow, không riêng endpoint nào.

### Frontend điều phối — `assets/js/mgk-pay.js`
1. (blur email) `POST attach-contact` — lưu email kể cả khi parent hoàn tất bằng admin force-confirm.
2. Chọn **Card** → `POST create-stripe-checkout` → `window.location = checkout_url`.
3. Chọn **PayNow** → `GET paynow-qr` → vẽ QR bằng qrcode.js.
4. Sau khi trả → `/trial-confirmed/` poll `GET /booking/{id}` đến khi `CONFIRMED`.

---

## 7. Stripe — 3 chế độ (tự chuyển, không sửa code)

Quyết định bởi `mgk_stripe_is_live()` = đã có secret key hay chưa.

### MOCK (chưa có key — **trạng thái hiện tại**)
- `create-checkout` tổng hợp `session_id` giả + `checkout_url` trỏ về `/trial-pay/?mgk_mock_pay=<session>`.
- `template_redirect` bắt param `mgk_mock_pay` → **tự bắn event giả** `checkout.session.completed` qua đúng `mgk_stripe_handle_event()` → confirm → redirect `/trial-confirmed/`.
- Cho phép test full flow hold→pay→confirm **không cần Stripe key**.

### DIRECT (có platform/direct secret key, chưa connect agency)
- `create-checkout` gọi thật `POST https://api.stripe.com/v1/checkout/sessions` qua `wp_remote_post` (thin client, **không SDK**).
- Dùng `stripe_secret` trực tiếp; phù hợp dev/admin nâng cao hoặc migration.

### CONNECT (production-facing cho agency)
- Agency bấm **Connect Stripe** trong `MGK Site Settings` → Stripe OAuth/onboarding → site lưu `stripe_connect_account_id`.
- `create-checkout` vẫn gọi Stripe Checkout bằng platform key, nhưng thêm header `Stripe-Account: acct_...`.
- Parent thanh toán trên Stripe Checkout; charge nằm dưới connected account của agency.
- Event Connect có top-level `account`; booking resolve bằng metadata `booking_id`/`booking_code`.
- Stripe redirect người dùng → thanh toán → gọi `/stripe/webhook`.
- Webhook verify **HMAC-SHA256** (`t=…,v1=…`) + replay window 300s (`mgk_stripe_verify_signature`).

### Settings (theme_mod, fallback wp-option)
`stripe_secret`, `stripe_publishable`, `stripe_webhook_secret`, `stripe_connect_client_id`, `stripe_connect_account_id`.

### Dev local test với Connect
1. Trong Stripe Dashboard sandbox, lấy platform `sk_test_...`, `pk_test_...`, Connect `client_id` (`ca_...`).
2. Vào `MGK Site Settings`:
   - tick `Enable Card (Stripe)`
   - nhập `Stripe Connect client ID`
   - nhập `Platform secret key`
   - nhập `Platform publishable key`
3. Thêm redirect URI trong Stripe Connect settings:
   `http://localhost:8091/wp-admin/admin-post.php?action=mgk_stripe_connect_callback`
4. Bấm **Connect Stripe** trên site để lưu `acct_...`.
5. Chạy Stripe CLI cho Connect events:
   `stripe listen --forward-connect-to http://localhost:8091/wp-json/mgk/v1/stripe/webhook`
6. Copy `whsec_...` từ CLI vào `Stripe webhook secret`.
7. Tạo booking → chọn Card → dùng thẻ test `4242 4242 4242 4242` → webhook confirm `CONFIRMED/PAID`.

---

## 8. Xác nhận an toàn — `mgk_stripe_confirm_paid()`

Khi nhận `checkout.session.completed` / `payment_intent.succeeded`:

```
1. Idempotency claim qua events (uniq_provider_event) — gửi trùng = no-op
2. Resolve booking từ metadata.booking_id → session_id → client_reference_id
3. Payment → PAID LUÔN (tiền đã nhận thật), idempotent

   START TRANSACTION
   4. SELECT status FOR UPDATE (re-read tránh stale race)
      - đã CONFIRMED → COMMIT, thoát (race-safe)
   5. Kiểm tra amount_total == giá kỳ vọng?
   6. mgk_stripe_slot_lost()? — block của slot này có bị booking KHÁC chiếm,
      hoặc có booking khác đã CONFIRMED overlap?
   7. Nếu (lệch tiền) HOẶC (mất slot):
         → status = MANUAL_REVIEW (payment_status vẫn PAID) → KHÔNG double-book
      Ngược lại:
         → status = CONFIRMED, payment_status = PAID
         → promote HOLD lock → BOOKING lock (vĩnh viễn); nếu lock đã bị cron
           xóa thì re-INSERT BOOKING lock
   COMMIT
   8. Fire do_action('mgk_booking_confirmed') → mirror sang CPT + downstream
```

Các event khác: `payment_intent.payment_failed` → FAILED (giữ PENDING nếu hold còn, hoặc FAILED_PAYMENT); `checkout.session.expired` → payment EXPIRED.

---

## 9. PayNow vs Stripe

| | Stripe | PayNow |
|---|--------|--------|
| Auto-confirm | ✅ qua webhook đã verify | ❌ **không** — đối soát thủ công |
| Booking sau khi trả | `CONFIRMED` | giữ `PENDING_PAYMENT` đến khi admin force-confirm |
| QR | — | EMVCo chuẩn SG (UEN, type 2), vẽ client-side qrcode.js |
| Verify | HMAC-SHA256 signature | CRC16-CCITT trên payload (chỉ đảm bảo QR đúng, không xác nhận tiền) |

**`mgk_payment_config()`** là nguồn sự thật method nào dùng được: *enabled (intent) + đã cấu hình đầy đủ (config) mới ACTIVE*:
- PayNow active ⟺ enabled **AND** UEN hợp lệ.
- Stripe active ⟺ enabled (chạy MOCK nếu chưa có key); LIVE ⟺ có key.

PayNow **không có tín hiệu trả về** khi tiền vào tài khoản → bản chất phải đối soát thủ công (đã ghi rõ trong code). QR đúng chuẩn và scan được; reconciliation thủ công cho tới khi nối một PayNow-collection gateway.

---

## 10. Cron & độ bền

`mgk_cron_expire_holds` chạy mỗi 60s (`mgk_minute` schedule):
- Flip HELD hết hạn → EXPIRED (bản ghi durable).
- Xóa HOLD lock cũ (housekeeping; BOOKING lock giữ nguyên).
- Ghi event `HOLD_EXPIRED` + hook `mgk_booking_hold_expired` (điểm nối notify).

> **Quan trọng (painpoint C):** tính đúng đắn KHÔNG phụ thuộc cron. Slot tự do ngay lúc đọc/hold nhờ lazy-filter + in-transaction DELETE. Cron chỉ làm sạch + tạo bản ghi/notify.

---

## 11. Trạng thái runtime hiện tại (đã verify 2026-06-11)

| Hạng mục | Trạng thái |
|----------|-----------|
| Container | `mgk-edu-el` (port 8091), healthy |
| Stripe mode | **MOCK / DIRECT / CONNECT** (CONNECT là hướng production-facing) |
| PayNow | active, UEN test `202412345A` |
| Method hiển thị | PayNow QR `[INSTANT]` + Card (Stripe) `[TEST]` |
| 4 bảng DB | đều OK, có dữ liệu thật |
| SOURCE ↔ RUNTIME | in sync |
| E2E test | HELD → PENDING_PAYMENT → webhook → **CONFIRMED + PAID** ✓ |
| Dữ liệu thật | booking `MGK-SAMPLE-CHEN` = CONFIRMED + PAID (PayNow) |

---

## 12. Gap / TODO để "chạy thật" (tiền thật)

- [ ] Platform owner cấu hình **Stripe Connect client ID** + platform keys.
- [ ] Agency bấm **Connect Stripe** để nối connected account.
- [ ] Set **webhook signing secret** cho endpoint `/wp-json/mgk/v1/stripe/webhook`; local Connect test dùng `stripe listen --forward-connect-to ...`.
- [ ] Thay **PayNow UEN** test bằng UEN thật của Margick.
- [ ] **S11 tạo `wp_user`** hiện còn STUB (chưa `wp_insert_user`) — tạo/link account khi confirm (FR-BOOK-07 / BR-22).
- [ ] **S12 `.ics` + PDF e-invoice** còn stub.
- [ ] `POST /booking/{id}/cancel` còn trả 501 (cancel/refund tier BR-07 chưa nối REST, hiện làm qua admin).
- [ ] PayNow-collection gateway để auto-reconcile (hiện thủ công).

---

## Phụ lục — Hằng số & quy ước

- `MGK_HOLD_SECONDS = 600` (hold 10 phút)
- `MGK_BOOKING_SCHEMA_VERSION = 0.5.0`
- Block = **15 phút**; một slot = N block liên tiếp (`mgk_expand_to_blocks`)
- Timezone hiển thị: `Asia/Singapore`; lưu trữ: UTC
- Booking code: `MGK-YYYYMMDD-XXXXXX`
- REST namespace: `mgk/v1` (tách khỏi route demo cũ ở `mgk-rest.php`)
- Prefix hàm engine: `mgk_engine_*` (tránh clash với `mgk_get_available_slots` cũ), `mgk_stripe_*`, `mgk_paynow_*`
