/**
 * MGK S11 — Pay client behaviour.
 *
 * Sequential section reveal, payment-method toggle (PayNow QR / Card), terms
 * gating of the pay CTA, and the post-submit demo state machine
 * (processing → success → redirect to S12, with failed / mismatch panels).
 *
 * NO business logic here — the server (inc/mgk-pay.php) owns the order summary,
 * discount stack, reference, surcharge, state set and routes; this only mirrors
 * state and drives the UI. The slot-hold countdown is owned by mgk-slots.js.
 */
(function () {
    'use strict';

    function track(name, payload) {
        try {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push(Object.assign({ event: name, path: location.pathname }, payload || {}));
        } catch (e) {}
    }

    /* ── Sequential reveal ────────────────────────────────────
       Each [data-reveal] section fades/slides in one after another so the page
       "builds up" when the parent lands here from S10. Honors reduced-motion. */
    function revealSections(root) {
        var sections = Array.prototype.slice.call(root.querySelectorAll('[data-reveal]'));
        if (!sections.length) { return; }
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        sections.forEach(function (s) { s.classList.add('mgk-reveal'); });
        if (reduce) {
            sections.forEach(function (s) { s.classList.add('is-shown'); });
            return;
        }
        sections.forEach(function (s, i) {
            setTimeout(function () {
                s.classList.add('is-shown');
                track('pay_section_reveal', { index: i });
            }, 140 * i + 80);
        });
    }

    /* ── Payment method toggle ────────────────────────────── */
    function initMethodToggle(scope) {
        var btns = Array.prototype.slice.call(scope.querySelectorAll('[data-pay-method]'));
        var panels = Array.prototype.slice.call(scope.querySelectorAll('[data-pay-panel]'));
        function activate(method) {
            btns.forEach(function (b) {
                var on = b.getAttribute('data-pay-method') === method;
                b.classList.toggle('is-active', on);
                b.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            panels.forEach(function (p) {
                var k = p.getAttribute('data-pay-panel');
                // Only toggle the two method panels here; status panels are JS-driven.
                if (k === 'paynow' || k === 'card') {
                    p.classList.toggle('is-active', k === method);
                }
            });
            updateCtaLabel(method);
        }
        btns.forEach(function (b) {
            b.addEventListener('click', function () {
                var m = b.getAttribute('data-pay-method');
                activate(m);
                track('pay_method_select', { method: m });
            });
        });
        return { activate: activate, current: function () {
            var a = scope.querySelector('[data-pay-method].is-active');
            return a ? a.getAttribute('data-pay-method') : 'paynow';
        } };
    }

    /* ── CTA label reflects the active method ─────────────── */
    function updateCtaLabel(method) {
        var cta = document.querySelector('[data-mgk-pay-cta]');
        if (!cta) { return; }
        var span = cta.querySelector('[data-pay-cta-label]');
        if (!span) { return; }
        var amt = cta.getAttribute('data-amount');
        var money = amt ? ('$' + parseFloat(amt).toFixed(2)) : '';
        var via = method === 'card' ? 'Card' : 'PayNow';
        span.textContent = 'Pay ' + money + ' with ' + via + ' →';
    }

    /* ── Terms gating ─────────────────────────────────────── */
    function initTermsGate() {
        var check = document.querySelector('[data-mgk-pay-terms]');
        var cta = document.querySelector('[data-mgk-pay-cta]');
        if (!cta) { return; }
        function sync() {
            var ok = !check || check.checked;
            cta.classList.toggle('is-disabled', !ok);
            if (ok) { cta.removeAttribute('aria-disabled'); cta.removeAttribute('tabindex'); }
            else { cta.setAttribute('aria-disabled', 'true'); cta.setAttribute('tabindex', '-1'); }
        }
        if (check) {
            check.addEventListener('change', function () {
                sync();
                track('pay_terms_toggle', { accepted: check.checked });
            });
        }
        sync();
    }

    /* ── Status panels (post-submit state machine) ────────── */
    function showStatus(scope, state) {
        ['paynow', 'card', 'processing', 'success', 'failed', 'mismatch'].forEach(function (k) {
            var p = scope.querySelector('[data-pay-panel="' + k + '"]');
            if (p) { p.classList.toggle('is-active', k === state); }
        });
    }
    function restoreMethod(scope, methodCtl) {
        showStatus(scope, ''); // clear all
        methodCtl.activate(methodCtl.current());
    }

    var restUrl = (window.mgkBookingData && window.mgkBookingData.restUrl) || '/wp-json/mgk/v1/';
    var nonce = (window.mgkBookingData && window.mgkBookingData.nonce) || '';

    function initSubmit(scope, methodCtl) {
        var cta = document.querySelector('[data-mgk-pay-cta]');
        if (!cta) { return; }
        cta.addEventListener('click', function (e) {
            if (cta.classList.contains('is-disabled')) { e.preventDefault(); return; }
            e.preventDefault();
            track('pay_submit', { method: methodCtl.current(), amount: cta.getAttribute('data-amount') });

            var bookingId = parseInt(cta.getAttribute('data-booking-id') || '0', 10);

            // No real booking (demo/preview) → keep the demo state machine.
            if (!bookingId || !window.fetch) {
                showStatus(scope, 'processing');
                track('pay_processing', {});
                setTimeout(function () {
                    showStatus(scope, 'success');
                    track('pay_success', {});
                    setTimeout(function () { window.location.href = cta.getAttribute('href'); }, 1100);
                }, 1600);
                return;
            }

            // Real engine: create a Stripe Checkout session, then hand off to it.
            // (Mock mode returns a local URL that auto-confirms + lands on S12;
            // live mode returns the real Stripe Checkout URL.)
            showStatus(scope, 'processing');
            track('pay_processing', {});
            fetch(restUrl + 'booking/' + bookingId + '/create-stripe-checkout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify({ method: methodCtl.current(), return_url: window.location.origin + '/trial-confirmed/' })
            }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, status: r.status, body: j }; }); })
              .then(function (res) {
                  if (!res.ok || !res.body || !res.body.checkout_url) { return Promise.reject(res); }
                  // Hand off to checkout. Do NOT mark success here — confirmation
                  // comes only from the webhook (S12 polls for it).
                  window.location.href = res.body.checkout_url;
              })
              .catch(function (res) {
                  var msg = (res && res.body && res.body.message) || '';
                  // Hold expired (410) → bounce back to slot picker.
                  if (res && res.status === 410) {
                      showStatus(scope, 'failed');
                      track('pay_failed', { reason: 'hold_expired' });
                      return;
                  }
                  showStatus(scope, 'failed');
                  track('pay_failed', { message: msg });
              });
        });

        // Retry from the failed panel returns to the method choice.
        var retry = scope.querySelector('[data-pay-retry]');
        if (retry) {
            retry.addEventListener('click', function () {
                restoreMethod(scope, methodCtl);
                track('pay_retry', {});
            });
        }
    }

    /* ── PayNow QR (real EMVCo payload, drawn client-side) ── */
    function initPayNowQR() {
        var box = document.querySelector('[data-mgk-pay-qr]');
        if (!box) { return; }
        var bookingId = parseInt(box.getAttribute('data-booking-id') || '0', 10);
        if (!bookingId || !window.fetch || !window.QRCode) { return; } // demo: keep placeholder

        fetch(restUrl + 'booking/' + bookingId + '/paynow-qr', {
            headers: { 'X-WP-Nonce': nonce }
        }).then(function (r) { return r.ok ? r.json() : Promise.reject(r); })
          .then(function (res) {
              if (!res || !res.payload) { return; }
              box.innerHTML = ''; // clear the "PayNow QR" placeholder
              // qrcodejs renders into the element; level M, auto-sized.
              new window.QRCode(box, {
                  text: res.payload,
                  width: 200, height: 200,
                  correctLevel: window.QRCode.CorrectLevel.M
              });
              box.setAttribute('aria-hidden', 'false');
              box.setAttribute('role', 'img');
              box.setAttribute('aria-label', 'PayNow QR for ' + (res.reference || 'your booking'));
          })
          .catch(function () { /* leave placeholder on failure */ });
    }

    function init() {
        // Composite render wraps everything in [data-mgk-pay]; the Elementor
        // split-widget build has no shared wrapper, so fall back to the document.
        var root = document.querySelector('[data-mgk-pay]') || document;
        revealSections(root);

        var scope = document.querySelector('[data-mgk-pay-method]');
        if (scope) {
            var methodCtl = initMethodToggle(scope);
            initSubmit(scope, methodCtl);
        }
        initTermsGate();
        initPayNowQR();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
