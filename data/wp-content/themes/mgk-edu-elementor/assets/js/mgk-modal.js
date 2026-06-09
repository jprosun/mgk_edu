(function () {
    'use strict';

    /* ── Overlay helpers ──────────────────────────────────── */

    function openOverlay(el) {
        if (!el) return;
        el.classList.add('is-open');
        el.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        var first = el.querySelector(
            'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );
        if (first) first.focus();
    }

    function closeOverlay(el) {
        if (!el) return;
        el.classList.remove('is-open');
        el.setAttribute('aria-hidden', 'true');
        var remaining = document.querySelector('.mgk-modal-overlay.is-open, .mgk-sheet-overlay.is-open');
        if (!remaining) document.body.style.overflow = '';
    }

    /* ── Event delegation ─────────────────────────────────── */

    document.addEventListener('click', function (e) {
        // Open modal by ID
        var openModal = e.target.closest('[data-mgk-modal-open]');
        if (openModal) {
            openOverlay(document.getElementById(openModal.getAttribute('data-mgk-modal-open')));
            return;
        }

        // Open sheet by ID
        var openSheet = e.target.closest('[data-mgk-sheet-open]');
        if (openSheet) {
            openOverlay(document.getElementById(openSheet.getAttribute('data-mgk-sheet-open')));
            return;
        }

        // Close via explicit button or .mgk-modal-close
        var closeBtn = e.target.closest('[data-mgk-modal-close], [data-mgk-sheet-close], .mgk-modal-close');
        if (closeBtn) {
            closeOverlay(closeBtn.closest('.mgk-modal-overlay, .mgk-sheet-overlay'));
            return;
        }

        // Close on backdrop click (clicking the overlay itself, not its children)
        if (e.target === e.currentTarget) return;
        if (
            e.target.classList.contains('mgk-modal-overlay') ||
            e.target.classList.contains('mgk-sheet-overlay')
        ) {
            closeOverlay(e.target);
        }
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        var open = document.querySelector('.mgk-modal-overlay.is-open, .mgk-sheet-overlay.is-open');
        if (open) closeOverlay(open);
    });

    /* ── Toast ────────────────────────────────────────────── */

    var toastStack = null;

    function getStack() {
        if (!toastStack) {
            toastStack = document.createElement('div');
            toastStack.className = 'mgk-toast-stack';
            toastStack.setAttribute('aria-live', 'polite');
            document.body.appendChild(toastStack);
        }
        return toastStack;
    }

    function removeToast(el) {
        if (!el || !el.parentNode) return;
        clearTimeout(el._mgkTimer);
        el.classList.add('is-leaving');
        el.addEventListener('animationend', function () {
            if (el.parentNode) el.parentNode.removeChild(el);
        }, { once: true });
    }

    function toast(message, type, duration) {
        type     = type     || 'info';
        duration = duration || 4000;

        var el = document.createElement('div');
        el.className = 'mgk-toast mgk-toast-' + type;
        el.setAttribute('role', 'status');
        el.innerHTML =
            '<span class="mgk-toast-message">' + message + '</span>' +
            '<button class="mgk-toast-dismiss" aria-label="Dismiss">×</button>';

        el.querySelector('.mgk-toast-dismiss').addEventListener('click', function () {
            removeToast(el);
        });

        getStack().appendChild(el);
        el._mgkTimer = setTimeout(function () { removeToast(el); }, duration);
    }

    /* ── Public API ───────────────────────────────────────── */

    window.mgkModal = { open: openOverlay, close: closeOverlay };
    window.mgkToast = toast;
}());
