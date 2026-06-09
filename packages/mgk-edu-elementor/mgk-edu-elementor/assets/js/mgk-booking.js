/**
 * MGK Booking flow — Batch 2 (S07-S12).
 * Depends on mgkBookingData (localized via wp_localize_script).
 */
(function () {
    'use strict';

    var cfg = window.mgkBookingData || {};

    document.addEventListener('DOMContentLoaded', function () {
        initRequestForm();
        initSlotPicker();
    });

    /* ── Request form (S07) ─────────────────────────────────── */

    function initRequestForm() {
        var form    = document.getElementById('js-mgk-request-form');
        var submit  = document.getElementById('js-request-submit');
        var success = document.getElementById('js-request-success');
        var viewBtn = document.getElementById('js-view-tutors-link');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            clearErrors(form);

            var payload  = serializeForm(form);
            var restUrl  = form.dataset.restUrl || (cfg.restUrl + 'leads');
            var tutorsUrl = form.dataset.tutorsUrl || cfg.tutorsUrl || '/';

            if (!validateRequired(form, ['parent_name', 'phone', 'subject', 'level'])) return;
            if (!form.querySelector('[name="consent"]').checked) {
                showError(form, 'consent', 'Please agree to be contacted.');
                return;
            }

            submit.classList.add('is-loading');
            submit.disabled = true;

            fetch(restUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce':   cfg.nonce || ''
                },
                body: JSON.stringify(payload)
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                submit.classList.remove('is-loading');
                submit.disabled = false;

                if (res && res.id) {
                    form.hidden    = true;
                    success.hidden = false;

                    var params = new URLSearchParams();
                    params.set('lead_id', res.id);
                    if (payload.subject) params.set('subject', payload.subject);
                    if (payload.level)   params.set('level',   payload.level);
                    if (payload.budget)  params.set('budget',  payload.budget);
                    viewBtn.href = tutorsUrl + '?' + params.toString();
                } else {
                    var errors = (res && res.data) ? res.data : {};
                    Object.keys(errors).forEach(function (field) {
                        showError(form, field, errors[field]);
                    });
                    if (!Object.keys(errors).length) {
                        showGlobalError(form, (res && res.message) || 'An error occurred. Please try again.');
                    }
                }
            })
            .catch(function () {
                submit.classList.remove('is-loading');
                submit.disabled = false;
                showGlobalError(form, 'Network error. Please check your connection and try again.');
            });
        });
    }

    /* ── Slot picker (S10) ───────────────────────────────────── */

    function initSlotPicker() {
        var picker    = document.getElementById('mgk-slot-picker');
        var slotWrap  = document.getElementById('js-slot-selected-wrap');
        var summary   = document.getElementById('js-slot-selected-summary');
        var labelText = document.getElementById('js-selected-label-text');
        var slotIdIn  = document.getElementById('js-slot-id-input');
        var checkout  = document.getElementById('js-slot-checkout-form');
        var countdown = document.getElementById('js-slot-countdown');
        if (!picker) return;

        var countdownTimer  = null;
        var countdownSecs   = 0;
        var currentSlotId   = null;

        picker.addEventListener('click', function (e) {
            var btn = e.target.closest('.mgk-slot-btn');
            if (!btn || btn.disabled || btn.classList.contains('is-taken')) return;

            var slotId  = btn.dataset.slotId;
            var slotTime = btn.dataset.slotTime;
            var slotDay  = btn.dataset.slotDay;
            var tutor    = picker.dataset.tutor;
            var leadId   = parseInt(picker.dataset.lead) || 0;

            // Deselect previous
            picker.querySelectorAll('.mgk-slot-btn.is-selected').forEach(function (b) {
                b.classList.remove('is-selected');
            });

            btn.classList.add('is-loading');
            btn.disabled = true;

            var holdUrl = (cfg.restUrl || '/wp-json/mgk/v1/') + 'slots/' + encodeURIComponent(slotId) + '/hold';

            fetch(holdUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce':   cfg.nonce || ''
                },
                body: JSON.stringify({ lead_id: leadId })
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                btn.classList.remove('is-loading');
                btn.disabled = false;

                if (res && res.status === 'held') {
                    btn.classList.add('is-selected');
                    currentSlotId  = slotId;
                    slotIdIn.value = slotId;
                    labelText.textContent = slotDay + ', ' + slotTime;
                    summary.hidden  = false;
                    checkout.hidden = false;
                    startCountdown(res.expires_in || 600, countdown);
                } else {
                    // Mark as unavailable
                    btn.classList.add('is-taken');
                    btn.disabled = true;
                    var badge = btn.querySelector('.mgk-slot-badge');
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'mgk-slot-badge';
                        btn.appendChild(badge);
                    }
                    badge.textContent = 'Taken';
                }
            })
            .catch(function () {
                btn.classList.remove('is-loading');
                btn.disabled = false;
            });
        });

        function startCountdown(secs, el) {
            clearInterval(countdownTimer);
            countdownSecs = secs;

            function tick() {
                countdownSecs--;
                if (countdownSecs <= 0) {
                    clearInterval(countdownTimer);
                    el.textContent = 'Hold expired — please re-select a slot.';
                    el.classList.add('is-expiring');
                    summary.hidden  = true;
                    checkout.hidden = true;
                    slotIdIn.value  = '';
                    currentSlotId   = null;
                    if (picker) {
                        picker.querySelectorAll('.mgk-slot-btn.is-selected').forEach(function (b) {
                            b.classList.remove('is-selected');
                        });
                    }
                    return;
                }
                var m = Math.floor(countdownSecs / 60);
                var s = countdownSecs % 60;
                var label = 'Slot held for ' + m + ':' + (s < 10 ? '0' : '') + s;
                el.textContent = label;
                el.classList.toggle('is-expiring', countdownSecs <= 60);
            }

            tick();
            countdownTimer = setInterval(tick, 1000);
        }
    }

    /* ── Helpers ─────────────────────────────────────────────── */

    function serializeForm(form) {
        var payload = {};
        var data    = new FormData(form);

        data.forEach(function (value, key) {
            // Strip hidden WP nonce fields
            if (key === 'mgk_booking_nonce' || key === '_wpnonce') return;

            // Collect checkboxes with the same name into a comma-separated string
            if (payload[key]) {
                payload[key] = payload[key] + ', ' + value;
            } else {
                payload[key] = value;
            }
        });

        return payload;
    }

    function clearErrors(form) {
        form.querySelectorAll('.mgk-form-error').forEach(function (el) {
            el.textContent = '';
        });
        form.querySelectorAll('.is-error').forEach(function (el) {
            el.classList.remove('is-error');
        });
    }

    function showError(form, field, message) {
        var errorEl = form.querySelector('#rf-err-' + field) || form.querySelector('[id$="err-' + field + '"]');
        var inputEl = form.querySelector('[name="' + field + '"]');
        if (errorEl) errorEl.textContent = message;
        if (inputEl) inputEl.classList.add('is-error');
    }

    function showGlobalError(form, message) {
        var footer = form.querySelector('.mgk-form-footer');
        if (!footer) return;
        var el = footer.querySelector('.mgk-form-global-error');
        if (!el) {
            el = document.createElement('p');
            el.className = 'mgk-form-error mgk-form-global-error';
            footer.prepend(el);
        }
        el.textContent = message;
    }

    function validateRequired(form, fields) {
        var valid = true;
        fields.forEach(function (name) {
            var el = form.querySelector('[name="' + name + '"]');
            if (!el || !el.value.trim()) {
                showError(form, name, 'This field is required.');
                valid = false;
            }
        });
        return valid;
    }

})();
