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

    /* ── Email capture (account auto-create) ──────────────── */
    function isValidEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test((v || '').trim()); }
    function payEmailInput() { return document.querySelector('[data-mgk-pay-email]'); }
    function emailFeedback(input) {
        var host = (input.closest && input.closest('.mgk-pay-account')) || input.parentNode;
        var fb = host.querySelector('[data-mgk-email-fb]');
        if (!fb) {
            fb = document.createElement('p');
            fb.setAttribute('data-mgk-email-fb', '');
            fb.style.cssText = 'font-size:12px;margin:4px 0 0;';
            host.appendChild(fb);
        }
        return fb;
    }

    /* Save the email to the booking on blur, so it sticks even if the booking is
       later completed via an admin force-confirm. The pay CTA also sends it
       (atomic with checkout). Reuses booking id from the CTA. */
    function initEmailCapture() {
        var input = payEmailInput();
        var cta = document.querySelector('[data-mgk-pay-cta]');
        if (!input || !cta || !window.fetch) { return; }
        var bookingId = parseInt(cta.getAttribute('data-booking-id') || '0', 10);
        if (!bookingId) { return; } // demo/preview: nothing to persist to
        var lastSaved = '';
        input.addEventListener('blur', function () {
            var email = (input.value || '').trim();
            if (!email || email === lastSaved || !isValidEmail(email)) { return; }
            var fb = emailFeedback(input);
            fb.textContent = 'Saving…'; fb.style.color = '#646970';
            fetch(restUrl + 'booking/' + bookingId + '/attach-contact', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify({ email: email })
            }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
              .then(function (res) {
                  if (res.ok && res.body && res.body.saved) {
                      lastSaved = email;
                      fb.textContent = '✓ Saved — we’ll send your booking + sign-in link here.';
                      fb.style.color = '#1a7f37';
                      track('pay_email_saved', {});
                  } else {
                      fb.textContent = (res.body && res.body.message) || 'Could not save email.';
                      fb.style.color = '#b32d2e';
                  }
              })
              .catch(function () {
                  fb.textContent = 'Could not save email — check your connection.';
                  fb.style.color = '#b32d2e';
              });
        });
    }

    function initSubmit(scope, methodCtl) {
        var cta = document.querySelector('[data-mgk-pay-cta]');
        if (!cta) { return; }
        cta.addEventListener('click', function (e) {
            if (cta.classList.contains('is-disabled')) { e.preventDefault(); return; }
            e.preventDefault();
            track('pay_submit', { method: methodCtl.current(), amount: cta.getAttribute('data-amount') });

            var bookingId = parseInt(cta.getAttribute('data-booking-id') || '0', 10);

            // A real booking needs a valid email — that's how the parent account +
            // passwordless sign-in link get created (FR-BOOK-07). Nudge, don't pay.
            var emailEl = payEmailInput();
            var email = emailEl ? (emailEl.value || '').trim() : '';
            if (bookingId && emailEl && !isValidEmail(email)) {
                var efb = emailFeedback(emailEl);
                efb.textContent = 'Please enter a valid email so we can send your booking + sign-in link.';
                efb.style.color = '#b32d2e';
                if (emailEl.scrollIntoView) { emailEl.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
                emailEl.focus();
                track('pay_email_required', {});
                return;
            }

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
                body: JSON.stringify({ method: methodCtl.current(), return_url: window.location.origin + '/trial-confirmed/', email: email })
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

    /* ── Voucher: apply / replace / remove → re-quote booking ─────────────
       The server re-quotes and persists the new price to the booking, so the
       Stripe charge equals what we render here. One voucher per order: applying
       a code replaces any previous one; the button toggles to "Remove". */
    function initVoucher() {
        var box = document.querySelector('[data-mgk-voucher]');
        if (!box || !window.fetch) { return; }
        var summary = box.closest('[data-booking-id]') || document.querySelector('[data-booking-id]');
        var bookingId = parseInt((summary && summary.getAttribute('data-booking-id')) || '0', 10);
        if (!bookingId) { return; } // preview / no real booking

        var input = box.querySelector('[data-mgk-voucher-code]');
        var btn   = box.querySelector('[data-mgk-voucher-apply]');
        var fb    = box.querySelector('[data-mgk-voucher-fb]');
        if (!input || !btn) { return; }

        function setFb(msg, ok) {
            if (!fb) { return; }
            fb.hidden = !msg;
            fb.innerHTML = msg || '';
            fb.style.color = ok ? '#1a7f37' : '#b32d2e';
        }
        function applied() { return btn.textContent.trim().toLowerCase() === 'remove'; }

        function renderRows(rows) {
            var host = document.querySelector('[data-mgk-bd-rows]');
            if (!host) { return; }
            host.innerHTML = (rows || []).map(function (r) {
                var cls = 'mgk-bk-bd-row' + (r.accent ? ' is-accent' : '') + (r.strong ? ' is-strong' : '');
                return '<div class="' + cls + '"><span class="mgk-bk-bd-label"></span>' +
                       '<span class="mgk-bk-bd-value"></span></div>';
            }).join('');
            // Fill text via textContent to avoid injection.
            var nodes = host.querySelectorAll('.mgk-bk-bd-row');
            (rows || []).forEach(function (r, i) {
                if (!nodes[i]) { return; }
                nodes[i].querySelector('.mgk-bk-bd-label').textContent = r.label || '';
                nodes[i].querySelector('.mgk-bk-bd-value').textContent = r.value || '';
            });
        }
        function syncPayCtaAmount(total, totalStr) {
            var cta = document.querySelector('[data-mgk-pay-cta]');
            if (!cta) { return; }
            if (typeof total !== 'undefined' && total !== null && total !== '') {
                cta.setAttribute('data-amount', parseFloat(total).toFixed(2));
            }
            var label = cta.querySelector('[data-pay-cta-label]');
            if (!label || !totalStr) { return; }
            var active = document.querySelector('[data-pay-method].is-active');
            var method = active ? active.getAttribute('data-pay-method') : 'paynow';
            var via = method === 'card' ? 'Card' : 'PayNow';
            label.textContent = 'Pay ' + totalStr + ' with ' + via + ' →';
        }

        btn.addEventListener('click', function () {
            var removing = applied();
            var code = removing ? '' : (input.value || '').trim().toUpperCase();
            if (!removing && !code) { setFb('Enter a voucher code', false); return; }

            btn.disabled = true;
            setFb('Checking…', true);
            fetch(restUrl + 'booking/' + bookingId + '/apply-voucher', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
                body: JSON.stringify({ code: code })
            }).then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
              .then(function (res) {
                  btn.disabled = false;
                  var b = res.body || {};
                  if (!res.ok) { setFb((b && b.message) || 'Could not apply voucher.', false); return; }

                  // Update the breakdown + totals from the authoritative re-quote.
                  renderRows(b.rows);
                  var st = document.querySelector('[data-mgk-subtotal]');
                  var tt = document.querySelector('[data-mgk-total]');
                  if (st) { st.textContent = b.subtotal || st.textContent; }
                  if (tt) { tt.textContent = b.total_str || tt.textContent; }
                  // Keep the pay CTA's amount label (if any) in sync.
                  var ctaAmt = document.querySelector('[data-mgk-pay-amount]');
                  if (ctaAmt && b.total_str) { ctaAmt.textContent = b.total_str; }
                  syncPayCtaAmount(b.total, b.total_str);

                  if (code === '') {
                      input.value = '';
                      btn.textContent = 'Apply';
                      setFb('Voucher removed', true);
                      track('voucher_removed', {});
                  } else if (b.applied) {
                      input.value = b.applied;
                      btn.textContent = 'Remove';
                      setFb('✓ Voucher <strong>' + b.applied + '</strong> applied', true);
                      track('voucher_applied', { code: b.applied });
                  } else {
                      btn.textContent = 'Apply';
                      setFb(b.voucher_error || 'Voucher not applicable', false);
                  }
              })
              .catch(function () { btn.disabled = false; setFb('Network error — try again.', false); });
        });
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
        initEmailCapture();
        initVoucher();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
