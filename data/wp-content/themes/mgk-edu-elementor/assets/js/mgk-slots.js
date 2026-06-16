/**
 * MGK S10 — Pick Slot client behaviour.
 *
 * Hold countdown timer, slot selection + hold (REST), selected-slot summary,
 * and the expired / just-taken states. NO business logic here — the server
 * (inc/mgk-slots.php + booking core) owns availability, hold validity and the
 * pay route; this only mirrors state and drives the timer.
 */
(function () {
    'use strict';

    function track(name, payload) {
        try {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push(Object.assign({ event: name, path: location.pathname }, payload || {}));
        } catch (e) {}
    }
    function pad(n) { return (n < 10 ? '0' : '') + n; }

    var restUrl = (window.mgkBookingData && window.mgkBookingData.restUrl) || '/wp-json/mgk/v1/';
    var nonce = (window.mgkBookingData && window.mgkBookingData.nonce) || '';

    /* ── Hold countdown ───────────────────────────────────── */
    var holdEl, mmEl, ssEl, expiredEl, timerId = null, remaining = 0;

    function startCountdown(secs) {
        if (!holdEl) { return; }
        remaining = Math.max(0, secs | 0);
        if (timerId) { clearInterval(timerId); }
        renderCountdown();
        if (remaining <= 0) { return handleExpired(); }
        timerId = setInterval(function () {
            remaining -= 1;
            renderCountdown();
            if (remaining <= 0) { clearInterval(timerId); handleExpired(); }
        }, 1000);
    }
    function renderCountdown() {
        if (mmEl) { mmEl.textContent = pad(Math.floor(remaining / 60)); }
        if (ssEl) { ssEl.textContent = pad(remaining % 60); }
    }
    function handleExpired() {
        track('slot_hold_expired', {});
        if (holdEl) { holdEl.classList.add('is-expired'); }
        if (expiredEl) { expiredEl.hidden = false; }
        var cta = document.querySelector('[data-confirm-cta]');
        if (cta) { cta.classList.add('is-disabled'); cta.setAttribute('aria-disabled', 'true'); cta.setAttribute('tabindex', '-1'); }
        // mgkHandleSlotExpired hook
        if (typeof window.mgkHandleSlotExpired === 'function') { try { window.mgkHandleSlotExpired(); } catch (e) {} }
    }
    window.mgkHandleSlotExpired = window.mgkHandleSlotExpired || function () {};

    /* ── Slot picker ──────────────────────────────────────── */
    function mgkRenderSelectedSlot(label) {
        var main = document.querySelector('[data-confirm-main]');
        if (!main) { return; }
        // Replace the time portion (between the day and " · with ") with the new label.
        var txt = main.textContent;
        var withIdx = txt.indexOf(' · with ');
        var firstDot = txt.indexOf(' · ');
        if (firstDot > -1 && withIdx > -1) {
            var day = txt.slice(0, firstDot);
            var withPart = txt.slice(withIdx);
            main.textContent = day + ' · ' + label + withPart;
        }
    }
    window.mgkRenderSelectedSlot = mgkRenderSelectedSlot;

    function setMsg(text) {
        var msg = document.querySelector('[data-slot-msg]');
        if (!msg) { return; }
        if (text) { msg.textContent = text; msg.hidden = false; } else { msg.hidden = true; }
    }

    // Parse the engine slot_key "tutorId:YYYY-MM-DD HH:MM:SS:YYYY-MM-DD HH:MM:SS"
    // into { tutor_id, start_at_utc, end_at_utc }. Returns null for legacy demo
    // ids (which contain no space / colon-datetime pattern).
    function parseSlotKey(slotId) {
        if (!slotId || slotId.indexOf(' ') === -1) { return null; }
        var first = slotId.indexOf(':');
        if (first === -1) { return null; }
        var tutorId = slotId.slice(0, first);
        var rest = slotId.slice(first + 1); // "START:END" where each is "YYYY-MM-DD HH:MM:SS"
        // The datetimes are 19 chars; split at char 19 (the colon between them).
        if (rest.length < 39) { return null; }
        var start = rest.slice(0, 19);
        var end = rest.slice(20);
        if (!/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(start) || !/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(end)) { return null; }
        return {
            tutor_id: parseInt(tutorId, 10),
            start_at_utc: start.replace(' ', 'T') + 'Z',
            end_at_utc: end.replace(' ', 'T') + 'Z'
        };
    }

    function mgkHoldSlot(slotId, btn, label) {
        var parsed = parseSlotKey(slotId);
        if (!window.fetch || !parsed) {
            // Legacy/demo slot id (no engine times) → visual-only select.
            return selectVisual(btn, label, slotId, '');
        }

        var ctaEl = document.querySelector('[data-confirm-cta]');
        var lead = ctaEl ? (ctaEl.getAttribute('data-lead') || '') : '';

        var body = {
            tutor_id: parsed.tutor_id,
            start_at_utc: parsed.start_at_utc,
            end_at_utc: parsed.end_at_utc,
            lesson_type: 'TRIAL',
            lead: lead,
            // Idempotency key so a double-click doesn't create two holds.
            idempotency_key: 'hold-' + slotId.replace(/[^0-9]/g, '')
        };

        fetch(restUrl + 'booking/hold', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
            body: JSON.stringify(body)
        }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, status: r.status, body: j }; }); })
          .then(function (res) {
              if (!res.ok) { return Promise.reject(res); }
              var j = res.body || {};
              var secs = (typeof j.countdown_seconds === 'number') ? j.countdown_seconds : 600;
              // Carry the real booking_id forward to S11 via the hold token slot.
              selectVisual(btn, label, slotId, j.booking_id ? String(j.booking_id) : '');
              startCountdown(secs);
              track('slot_hold_started', { slot_id: slotId, booking_id: j.booking_id, hold_remaining_seconds: secs });
          })
          .catch(function (res) {
              if (res && res.status === 409) {
                  var msg = (res.body && res.body.message) || 'That slot was just taken. Choose another time.';
                  setMsg(msg);
                  if (btn) { btn.classList.add('is-disabled'); btn.disabled = true; }
                  track('slot_unavailable_error', { slot_id: slotId });
                  return;
              }
              // Other failure → keep the prior selection; surface a soft message.
              setMsg('Could not hold that slot. Please try again.');
          });
    }
    window.mgkHoldSlot = mgkHoldSlot;

    function selectVisual(btn, label, slotId, bookingId) {
        document.querySelectorAll('.mgk-bk-slot.is-selected').forEach(function (b) {
            b.classList.remove('is-selected');
            b.removeAttribute('aria-pressed');
            var t = b.querySelector('.mgk-bk-slot-time');
            if (t) { t.textContent = t.textContent.replace(/\s*✓\s*$/, ''); }
        });
        if (btn) {
            btn.classList.add('is-selected');
            btn.setAttribute('aria-pressed', 'true');
            var t = btn.querySelector('.mgk-bk-slot-time');
            if (t && !/✓/.test(t.textContent)) { t.textContent = t.textContent + ' ✓'; }
        }
        setMsg('');
        mgkRenderSelectedSlot(label);

        // Update the confirm CTA href with the held slot.
        var cta = document.querySelector('[data-confirm-cta]');
        if (cta) {
            cta.classList.remove('is-disabled');
            cta.removeAttribute('aria-disabled');
            cta.removeAttribute('tabindex');
            var base = cta.getAttribute('data-pay-base') || cta.getAttribute('href');
            var lead = cta.getAttribute('data-lead') || '';
            var tutor = cta.getAttribute('data-tutor') || '';
            var q = [];
            if (lead) { q.push('lead=' + encodeURIComponent(lead)); }
            if (tutor) { q.push('tutor=' + encodeURIComponent(tutor)); }
            // Real engine booking id (preferred). Falls back to the raw slot id
            // for the legacy/demo path.
            if (bookingId) { q.push('booking=' + encodeURIComponent(bookingId)); }
            else { q.push('slot=' + encodeURIComponent(slotId)); }
            cta.setAttribute('href', base + (base.indexOf('?') > -1 ? '&' : '?') + q.join('&'));
        }
    }

    function mgkSelectSlot(slotId, btn, label) {
        track('slot_time_select', { slot_id: slotId });
        mgkHoldSlot(slotId, btn, label);
    }
    window.mgkSelectSlot = mgkSelectSlot;

    function mgkInitSlotPicker() {
        holdEl = document.querySelector('[data-mgk-hold]');
        if (holdEl) {
            var tEl = holdEl.querySelector('[data-hold-timer]');
            mmEl = tEl ? tEl.querySelector('[data-hold-mm]') : null;
            ssEl = tEl ? tEl.querySelector('[data-hold-ss]') : null;
            expiredEl = holdEl.querySelector('[data-hold-expired]');
            startCountdown(parseInt(holdEl.getAttribute('data-remaining'), 10) || 600);
        }

        var picker = document.querySelector('[data-mgk-slots]');
        if (picker) {
            picker.addEventListener('click', function (e) {
                var btn = e.target.closest('.mgk-bk-slot');
                if (!btn || btn.disabled || btn.classList.contains('is-disabled') || btn.classList.contains('is-selected')) { return; }
                mgkSelectSlot(btn.getAttribute('data-slot-id'), btn, btn.getAttribute('data-slot-label'));
            });

            // The server pre-selects the first available slot (and shows a running
            // hold countdown), but no hold exists until a click. Create the hold for
            // that pre-selected slot on load so the countdown is real and "Confirm &
            // pay" carries a real booking id (not a demo fallback). Only for real
            // engine slots; the legacy/demo path is left visual-only.
            var preSel = picker.querySelector('.mgk-bk-slot.is-selected');
            var cta = document.querySelector('[data-confirm-cta]');
            var alreadyHeld = cta && (cta.getAttribute('href') || '').indexOf('booking=') > -1;
            if (preSel && !alreadyHeld && parseSlotKey(preSel.getAttribute('data-slot-id'))) {
                mgkHoldSlot(preSel.getAttribute('data-slot-id'), preSel, preSel.getAttribute('data-slot-label'));
            }
        }

        // Day select — each day has its own real slot set, so navigate with
        // ?day=ISO and let the engine render that day's times. (Demo mode shared a
        // time set, but real availability differs per day.)
        var strip = document.querySelector('.mgk-bk-weekstrip');
        if (strip) {
            strip.addEventListener('click', function (e) {
                var day = e.target.closest('.mgk-bk-day.is-open');
                if (!day || day.classList.contains('is-active')) { return; }
                var iso = day.getAttribute('data-day') || '';
                track('slot_day_select', { day: iso });
                if (iso) {
                    // 'mgk_day' (not 'day') — 'day' is a reserved WP query var
                    // and corrupts the main query / mis-routes the page.
                    var u = new URL(window.location.href);
                    u.searchParams.set('mgk_day', iso);
                    window.location.href = u.toString();
                }
            });
        }

        track('booking_pick_slot_view', {});

        // Disabled CTA guard.
        document.addEventListener('click', function (e) {
            var cta = e.target.closest('[data-confirm-cta]');
            if (cta && cta.classList.contains('is-disabled')) { e.preventDefault(); }
            else if (cta) { track('slot_confirm_continue', {}); }
        });
    }
    window.mgkInitSlotPicker = mgkInitSlotPicker;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mgkInitSlotPicker);
    } else {
        mgkInitSlotPicker();
    }
}());
