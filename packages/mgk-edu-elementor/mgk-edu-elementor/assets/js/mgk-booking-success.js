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

    /* ── Reschedule slot picker (in-modal, no navigation) ─── */
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
        if (confirm) {
            confirm.addEventListener('click', function () {
                var active = modal.querySelector('[data-mgk-resched-slot].is-active');
                track('reschedule_confirm', { slot: active ? active.getAttribute('data-mgk-resched-slot') : '' });
                // No real reschedule in this phase — confirm + close.
                closeModal(modal);
            });
        }
    }

    /* ── Cancel & refund confirm (preview only) ───────────── */
    function initCancel() {
        var modal = modalEl('cancel-refund');
        if (!modal) { return; }
        var confirm = modal.querySelector('[data-mgk-confirm-cancel]');
        if (confirm) {
            confirm.addEventListener('click', function () {
                track('cancel_refund_confirm', {});
                // No real cancel in this phase — confirm + close.
                closeModal(modal);
            });
        }
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
