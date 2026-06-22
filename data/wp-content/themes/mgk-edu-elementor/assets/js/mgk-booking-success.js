/**
 * MGK S12 — Booking Confirmation client behaviour.
 *
 * Manage-booking modals (reschedule / cancel-refund / add-to-calendar), the
 * reschedule slot picker, and safe tracking. NO business logic here — the server
 * (inc/mgk-confirmation.php) owns confirmation, contact-unlock, refund tiers,
 * reschedule limits, calendar generation; this only drives the modal UI.
 */
(function () {
    'use strict';

    function track(name, payload) {
        try {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push(Object.assign({ event: name, path: location.pathname }, payload || {}));
        } catch (e) {}
    }

    /* ── Modal open / close ───────────────────────────────── */
    function modalEl(key) { return document.querySelector('[data-mgk-modal="' + key + '"]'); }

    function openModal(key) {
        var m = modalEl(key);
        if (!m) { return; }
        m.hidden = false;
        document.body.classList.add('mgk-cf-modal-open');
        var focusable = m.querySelector('button, a, input');
        if (focusable) { try { focusable.focus(); } catch (e) {} }
        track('confirm_modal_open', { modal: key });
    }
    function closeModal(m) {
        if (!m) { return; }
        m.hidden = true;
        if (!document.querySelector('.mgk-cf-modal:not([hidden])')) {
            document.body.classList.remove('mgk-cf-modal-open');
        }
    }
    function closeAll() {
        Array.prototype.forEach.call(document.querySelectorAll('.mgk-cf-modal:not([hidden])'), closeModal);
    }

    function initModals() {
        // Open triggers (manage buttons).
        document.addEventListener('click', function (e) {
            var opener = e.target.closest('[data-mgk-modal-open]');
            if (opener) {
                e.preventDefault();
                openModal(opener.getAttribute('data-mgk-modal-open'));
                return;
            }
            // Close triggers (overlay, ×, Keep booking).
            var closer = e.target.closest('[data-mgk-modal-close]');
            if (closer) {
                e.preventDefault();
                closeModal(closer.closest('.mgk-cf-modal'));
            }
        });
        // Esc closes.
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { closeAll(); }
        });
    }

    /* ── Engine wiring (real cancel / reschedule) ─────────── */
    var restUrl = (window.mgkBookingData && window.mgkBookingData.restUrl) || '/wp-json/mgk/v1/';
    var nonce   = (window.mgkBookingData && window.mgkBookingData.nonce) || '';

    function manageRoot() { return document.querySelector('[data-booking-id]'); }
    function bookingId() {
        var r = manageRoot();
        return r ? parseInt(r.getAttribute('data-booking-id') || '0', 10) : 0;
    }
    function setFb(node, msg, ok) {
        if (!node) { return; }
        node.hidden = !msg;
        node.textContent = msg || '';
        node.style.color = ok ? '#1a7f37' : '#b32d2e';
    }

    /* ── Reschedule slot picker → POST /booking/{id}/reschedule ─── */
    function initReschedule() {
        var modal = modalEl('reschedule');
        if (!modal) { return; }
        var slots = Array.prototype.slice.call(modal.querySelectorAll('[data-mgk-resched-slot]'));
        slots.forEach(function (s) {
            s.addEventListener('click', function () {
                slots.forEach(function (o) { o.classList.remove('is-active'); o.removeAttribute('aria-pressed'); });
                s.classList.add('is-active');
                s.setAttribute('aria-pressed', 'true');
            });
        });
        var confirm = modal.querySelector('[data-mgk-confirm-resched]');
        var fb = modal.querySelector('[data-mgk-resched-fb]');
        if (!confirm) { return; }
        confirm.addEventListener('click', function () {
            var active = modal.querySelector('[data-mgk-resched-slot].is-active');
            var id = bookingId();
            var start = active ? active.getAttribute('data-start') : '';
            var end   = active ? active.getAttribute('data-end') : '';
            track('reschedule_confirm', { slot: active ? active.getAttribute('data-mgk-resched-slot') : '' });

            // Demo/preview (no real booking or slot data) → just close.
            if (!id || !start || !end || !window.fetch) { closeModal(modal); return; }

            confirm.disabled = true;
            setFb(fb, 'Moving your lesson…', true);
            fetch(restUrl + 'booking/' + id + '/reschedule', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify({ start_at_utc: start, end_at_utc: end })
            }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
              .then(function (res) {
                  confirm.disabled = false;
                  if (res.ok && res.body && res.body.rescheduled) {
                      setFb(fb, '✓ Lesson moved. Refreshing…', true);
                      setTimeout(function () { window.location.reload(); }, 1200);
                  } else {
                      setFb(fb, (res.body && res.body.message) || 'Could not reschedule — try another time.', false);
                  }
              })
              .catch(function () { confirm.disabled = false; setFb(fb, 'Network error — please try again.', false); });
        });
    }

    /* ── Cancel & refund → POST /booking/{id}/cancel ─────── */
    function initCancel() {
        var modal = modalEl('cancel-refund');
        if (!modal) { return; }
        var confirm = modal.querySelector('[data-mgk-confirm-cancel]');
        var fb = modal.querySelector('[data-mgk-cancel-fb]');
        if (!confirm) { return; }
        confirm.addEventListener('click', function () {
            var id = bookingId();
            track('cancel_refund_confirm', {});
            if (!id || !window.fetch) { closeModal(modal); return; }

            confirm.disabled = true;
            setFb(fb, 'Cancelling…', true);
            fetch(restUrl + 'booking/' + id + '/cancel', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify({ reason: '' })
            }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
              .then(function (res) {
                  confirm.disabled = false;
                  var b = res.body || {};
                  if (res.ok && b.cancelled) {
                      var refunded = (typeof b.refund_amount === 'number') ? b.refund_amount : 0;
                      var msg = refunded > 0
                          ? '✓ Cancelled. $' + refunded.toFixed(2) + ' refunded (' + (b.tier || '') + '). Redirecting…'
                          : '✓ Cancelled.' + (b.tier === 'none' ? ' No refund (within ' + 0 + 'h tier).' : '') + ' Redirecting…';
                      setFb(fb, msg, true);
                      var root = manageRoot();
                      var dash = (root && root.getAttribute('data-dashboard')) || '';
                      setTimeout(function () { window.location.href = dash || window.location.href; }, 1600);
                  } else {
                      setFb(fb, (b && b.message) || 'Could not cancel — please contact support.', false);
                  }
              })
              .catch(function () { confirm.disabled = false; setFb(fb, 'Network error — please try again.', false); });
        });
    }

    /* ── Calendar: the .ics button can also open the modal ──
       The primary "Add to calendar (.ics)" downloads directly; provide the
       multi-option modal when a [data-mgk-modal-open="calendar"] trigger exists. */

    function init() {
        initModals();
        initReschedule();
        initCancel();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
