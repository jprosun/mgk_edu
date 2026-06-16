/**
 * MGK Request Match (S07) — client behaviour.
 *
 * Supports TWO layouts from the same code:
 *   A) Composite — one <form> (widgets mgk_request_match / mgk_request_fields).
 *      Progressive enhancement: posts natively without JS; with JS it does a
 *      REST submit with native-POST fallback.
 *   B) Split — each field is its own Elementor widget (no <form>); the Submit
 *      widget (button[type=button], [data-submit-scope]) gathers every .mgk-rq
 *      field in its scope (nearest Elementor Section, else the page) and POSTs
 *      to the locked REST endpoint, with a hidden-form fallback.
 *
 * NO business logic lives here — the server (inc/mgk-forms.php) re-validates
 * and owns lead creation, SLA and masking. The confirmation countdown reads
 * the server-stamped SLA due time.
 */
(function () {
    'use strict';

    function track(name, payload) {
        try {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push(Object.assign({ event: name, path: location.pathname }, payload || {}));
        } catch (e) {}
    }

    function timeToMin(v) {
        var m = /^(\d{1,2}):(\d{2})$/.exec(v || '');
        return m ? parseInt(m[1], 10) * 60 + parseInt(m[2], 10) : -1;
    }

    function normalizeSgPhone(raw) {
        var d = (raw || '').replace(/[^\d]/g, '');
        if (d.length === 10 && d.indexOf('65') === 0) { d = d.slice(2); }
        return /^[689]\d{7}$/.test(d) ? ('+65' + d) : '';
    }

    // Country-aware E.164 (mirrors mgk_normalize_phone() on the server). The
    // server is the source of truth; this is only for instant client feedback.
    function normalizePhone(raw, country) {
        var d = (raw || '').replace(/[^\d]/g, '');
        if (country === 'VN') {
            if (d.length >= 11 && d.indexOf('84') === 0) { d = d.slice(2); }
            d = d.replace(/^0+/, '');
            return /^[35789]\d{8}$/.test(d) ? ('+84' + d) : '';
        }
        return normalizeSgPhone(raw);
    }

    /**
     * Resolve the DOM scope that contains the request fields for a given root.
     * - <form> root → the form itself (Layout A).
     * - submit-scope root inside an Elementor Section → that Section (Layout B,
     *   so sibling field widgets are included).
     * - otherwise → the page main / document.
     */
    function resolveScope(root) {
        if (root.tagName === 'FORM') { return root; }
        if (root.hasAttribute('data-submit-scope')) {
            var sec = root.closest('.elementor-section, .e-con, section, .mgk-main, #content');
            if (sec) { return sec; }
        }
        // Composite non-form wrapper (e.g. .mgk-rq holding everything).
        if (root.querySelector('.mgk-rq-field')) { return root; }
        return document;
    }

    /* ── STATE 1: form / fields ────────────────────────────── */
    function initForm(root) {
        var scope = resolveScope(root);
        var nativeForm = (root.tagName === 'FORM') ? root : null;
        var submitBtn = scope.querySelector('#js-rq-submit') || root.querySelector('#js-rq-submit');
        if (!submitBtn && !nativeForm) { return; }

        var started = false;
        track('request_page_view', {});
        function markStart() {
            if (started) { return; }
            started = true;
            track('request_form_start', {});
        }
        scope.addEventListener('focusin', markStart, { once: true });

        // UTM source → hidden field (wherever it lives in scope).
        var utm = scope.querySelector('#rq_source_utm');
        if (utm) {
            var qs = new URLSearchParams(location.search);
            var src = qs.get('utm_source') || qs.get('utm_campaign') || document.referrer || '';
            utm.value = src.slice(0, 180);
        }

        /* Day chips → toggle hidden inputs */
        Array.prototype.forEach.call(scope.querySelectorAll('.mgk-rq-chip'), function (chip) {
            chip.addEventListener('click', function () {
                var day = chip.getAttribute('data-day');
                var input = scope.querySelector('[data-day-input="' + day + '"]');
                var on = !chip.classList.contains('is-on');
                chip.classList.toggle('is-on', on);
                chip.setAttribute('aria-pressed', on ? 'true' : 'false');
                if (input) { input.disabled = !on; }
                clearError('preferred_days');
                track('request_field_change', { field: 'preferred_days' });
            });
        });

        /* Dual-range budget slider (data-budget may live anywhere in scope) */
        var budgetWrap = scope.querySelector('[data-budget]');
        if (budgetWrap) { initBudget(scope, budgetWrap); }

        /* Char counter */
        var note = scope.querySelector('[data-note-input]');
        var noteCount = scope.querySelector('[data-note-count]');
        if (note && noteCount) {
            var upd = function () { noteCount.textContent = String((note.value || '').length); };
            note.addEventListener('input', upd);
            upd();
        }

        /* field-change tracking */
        Array.prototype.forEach.call(scope.querySelectorAll('select, #rq_phone'), function (el) {
            el.addEventListener('change', function () {
                track('request_field_change', { field: el.name || el.id });
                clearError(el.name);
            });
        });

        function q(sel) { return scope.querySelector(sel); }
        function val(sel) { var el = q(sel); return el ? el.value : ''; }

        function setError(field, msg) {
            var box = scope.querySelector('[data-err="' + field + '"]');
            var fieldEl = scope.querySelector('.mgk-rq-field[data-field="' + field + '"]');
            if (box) { box.textContent = msg || ''; }
            if (fieldEl) { fieldEl.classList.toggle('is-invalid', !!msg); }
        }
        function clearError(field) {
            if (!field) { return; }
            setError(field.replace(/\[\]$/, ''), '');
        }

        function collect() {
            var days = [];
            Array.prototype.forEach.call(scope.querySelectorAll('[data-day-input]'), function (i) {
                if (!i.disabled) { days.push(i.value); }
            });
            var bmin = q('[data-budget-min-field]');
            var bmax = q('[data-budget-max-field]');
            var consent = q('#rq_consent');
            return {
                child_name: val('#rq_child_name'),
                child_level: val('#rq_level'),
                subject: val('#rq_subject'),
                preferred_days: days,
                time_from: val('#rq_time_from'),
                time_to: val('#rq_time_to'),
                budget_min: bmin ? bmin.value : '',
                budget_max: bmax ? bmax.value : '',
                note: val('#rq_note'),
                email: val('#rq_email'),
                pdpa_consent: consent ? consent.checked : false,
                source_utm: utm ? utm.value : ''
            };
        }

        // Only validate fields actually present in this scope (split layouts may
        // omit optional fields). Required fields are validated when present; if a
        // required field is missing from the DOM the server still enforces it.
        function present(field) { return !!scope.querySelector('.mgk-rq-field[data-field="' + field + '"]'); }

        function validate(data) {
            var errs = {};
            if (present('child_name') && !(data.child_name && data.child_name.trim())) { errs.child_name = 'Please enter your child’s name.'; }
            if (present('child_level') && !data.child_level) { errs.child_level = 'Please select your child’s level.'; }
            if (present('subject') && !data.subject) { errs.subject = 'Please select a subject.'; }
            if (present('preferred_days')) {
                if (!data.preferred_days.length) { errs.preferred_days = 'Pick at least one preferred day.'; }
                else if (timeToMin(data.time_from) >= timeToMin(data.time_to)) { errs.time_to = 'Start time must be before end time.'; }
            }
            if (present('budget') && data.budget_min !== '' && data.budget_max !== '') {
                if (parseInt(data.budget_min, 10) > parseInt(data.budget_max, 10)) { errs.budget = 'Minimum must be below maximum.'; }
            }
            if (present('email') && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email || '')) {
                errs.email = 'Enter a valid email address.';
            }
            if (present('pdpa_consent') && !data.pdpa_consent) { errs.pdpa_consent = 'Please agree to the PDPA Notice to continue.'; }
            return errs;
        }

        // General (non-field) submit error, shown by the button.
        function submitError(msg) {
            if (!submitBtn) { window.alert(msg); return; }
            var box = scope.querySelector('.mgk-rq-submit-error');
            if (!box) {
                box = document.createElement('p');
                box.className = 'mgk-rq-submit-error mgk-rq-err';
                box.setAttribute('aria-live', 'polite');
                box.style.cssText = 'color:#b32d2e;margin:8px 0 0;';
                (submitBtn.parentNode || scope).insertBefore(box, submitBtn.nextSibling);
            }
            box.textContent = msg || '';
        }

        function setLoading(on) {
            if (!submitBtn) { return; }
            submitBtn.disabled = on;
            var lbl = submitBtn.querySelector('.mgk-rq-submit-label');
            var ldg = submitBtn.querySelector('.mgk-rq-submit-loading');
            if (lbl) { lbl.hidden = on; }
            if (ldg) { ldg.hidden = !on; }
        }

        // Submit URLs come from the form (Layout A) or the submit scope (Layout B).
        var cfgEl = nativeForm || scope.querySelector('[data-submit-scope]') || root;
        var restUrl = cfgEl.getAttribute('data-rest-url');
        var nonce = cfgEl.getAttribute('data-nonce');
        var confirmUrl = cfgEl.getAttribute('data-confirm-url') || location.pathname;
        var submitting = false;

        function doSubmit(ev) {
            var data = collect();
            ['child_name', 'child_level', 'subject', 'preferred_days', 'time_to', 'budget', 'note', 'email', 'pdpa_consent']
                .forEach(function (f) { setError(f, ''); });
            var errs = validate(data);

            if (Object.keys(errs).length) {
                if (ev) { ev.preventDefault(); }
                Object.keys(errs).forEach(function (f) { setError(f, errs[f]); });
                track('request_form_error', { fields: Object.keys(errs).join(',') });
                var firstInvalid = scope.querySelector('.mgk-rq-field.is-invalid');
                if (firstInvalid) { firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
                return false;
            }

            if (!restUrl || !window.fetch || submitting) {
                // Layout A native <form> will proceed; Layout B builds a fallback form.
                if (ev) { ev.preventDefault(); }
                setLoading(true);
                fallbackPost(data);
                return false;
            }

            if (ev) { ev.preventDefault(); }
            submitting = true;
            setLoading(true);
            track('request_form_submit', {
                level: data.child_level, subject: data.subject,
                has_budget: (data.budget_min !== '') ? 1 : 0,
                selected_days_count: data.preferred_days.length,
                source_utm: data.source_utm
            });

            var payload = {
                child_name: data.child_name,
                child_level: data.child_level, subject: data.subject,
                preferred_days: data.preferred_days,
                time_from: data.time_from, time_to: data.time_to,
                budget_min: data.budget_min, budget_max: data.budget_max,
                note: data.note, email: data.email,
                pdpa_consent: data.pdpa_consent ? 'yes' : 'no',
                source_utm: data.source_utm, parent_name: 'Parent'
            };

            submitError('');
            fetch(restUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce || '' },
                body: JSON.stringify(payload)
            }).then(function (r) {
                return r.json().catch(function () { return {}; }).then(function (body) {
                    return { ok: r.ok, status: r.status, body: body };
                });
            }).then(function (res) {
                if (res.ok) {
                    track('request_form_success', { level: data.child_level, subject: data.subject });
                    var token = res.body && res.body.token ? res.body.token : '';
                    location.href = confirmUrl + (confirmUrl.indexOf('?') > -1 ? '&' : '?') + 'mgk_lead=' + encodeURIComponent(token);
                    return;
                }
                // Validation errors → show inline + STAY (never blind native-POST:
                // that loses input and can hit nonce expiry → ?mgk_err=expired).
                submitting = false;
                setLoading(false);
                if (res.status === 422 && res.body && res.body.errors) {
                    Object.keys(res.body.errors).forEach(function (f) { setError(f, res.body.errors[f]); });
                    var firstInvalid = scope.querySelector('.mgk-rq-field.is-invalid');
                    if (firstInvalid) { firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
                    track('request_form_error', { reason: 'validation', fields: Object.keys(res.body.errors).join(',') });
                    return;
                }
                // Nonce/expired (403) or server (500) → clear, retryable message.
                track('request_form_error', { reason: 'server', status: res.status });
                submitError((res.body && res.body.message) || 'Something went wrong — please try again.');
            }).catch(function () {
                // True transport failure (offline) → fall back to the locked handler.
                submitting = false;
                setLoading(false);
                track('request_form_error', { reason: 'rest_failed' });
                if (nativeForm) { nativeForm.submit(); } else { fallbackPost(data); }
            });
            return false;
        }

        // No-REST / REST-failed fallback for the split layout: build a hidden form
        // and POST so the locked server handler (template_redirect) runs.
        function fallbackPost(data) {
            var f = document.createElement('form');
            f.method = 'post';
            f.action = confirmUrl;
            f.style.display = 'none';
            function add(name, value) {
                var i = document.createElement('input');
                i.type = 'hidden'; i.name = name; i.value = value;
                f.appendChild(i);
            }
            add('mgk_action', 'request_match');
            // nonce field name expected by the server handler.
            var srvNonce = (scope.querySelector('#mgk_request_nonce') || {}).value || '';
            if (srvNonce) { add('mgk_request_nonce', srvNonce); }
            add('child_name', data.child_name);
            add('child_level', data.child_level);
            add('subject', data.subject);
            data.preferred_days.forEach(function (d) { add('preferred_days[]', d); });
            add('time_from', data.time_from);
            add('time_to', data.time_to);
            add('budget_min', data.budget_min);
            add('budget_max', data.budget_max);
            add('note', data.note);
            add('email', data.email);
            add('pdpa_consent', data.pdpa_consent ? 'yes' : '');
            add('source_utm', data.source_utm);
            document.body.appendChild(f);
            f.submit();
        }

        if (nativeForm) {
            nativeForm.addEventListener('submit', doSubmit);
        }
        if (submitBtn) {
            submitBtn.addEventListener('click', function (ev) {
                // In Layout A the button is type=submit; let the form submit handler run.
                if (nativeForm && submitBtn.type === 'submit') { return; }
                doSubmit(ev);
            });
        }

        // If a prior non-JS fallback POST bounced back with ?mgk_err=…, explain it
        // (the form is fresh + submittable now — don't leave a cryptic query param).
        var em = location.search.match(/[?&]mgk_err=([a-z]+)/);
        if (em && submitBtn) {
            var msgs = {
                expired: 'Your session timed out before that submitted. Please review your details and submit again.',
                invalid: 'Please check the highlighted fields and submit again.',
                server: 'Something went wrong on our side. Please try again.'
            };
            submitError(msgs[em[1]] || 'Please submit again.');
        }
    }

    function initBudget(scope, wrap) {
        var minI = wrap.querySelector('[data-budget-min-input]');
        var maxI = wrap.querySelector('[data-budget-max-input]');
        var fill = wrap.querySelector('[data-budget-fill]');
        var label = wrap.querySelector('[data-budget-label]');
        var minF = wrap.querySelector('[data-budget-min-field]');
        var maxF = wrap.querySelector('[data-budget-max-field]');
        if (!minI || !maxI) { return; }

        var lo = parseInt(minI.min, 10), hi = parseInt(minI.max, 10), touched = false;

        function render() {
            var a = parseInt(minI.value, 10), b = parseInt(maxI.value, 10);
            if (a > b) {
                if (this === maxI) { a = b; minI.value = a; }
                else { b = a; maxI.value = b; }
            }
            var pa = ((a - lo) / (hi - lo)) * 100, pb = ((b - lo) / (hi - lo)) * 100;
            if (fill) { fill.style.left = pa + '%'; fill.style.width = (pb - pa) + '%'; }
            if (label) { label.textContent = '$' + a + ' - $' + b + ' / hr'; }
            if (touched) {
                if (minF) { minF.value = a; }
                if (maxF) { maxF.value = b; }
            }
        }
        ['input', 'change'].forEach(function (ev) {
            minI.addEventListener(ev, function () { touched = true; render.call(minI); });
            maxI.addEventListener(ev, function () { touched = true; render.call(maxI); });
        });
        render();
    }

    /* ── STATE 2: confirmation countdown ──────────────────── */
    function initConfirm(el) {
        track('request_confirm_view', {});
        var due = parseInt(el.getAttribute('data-sla-due'), 10);
        var timer = el.querySelector('[data-countdown]');
        if (!due || !timer) { return; }
        var h = timer.querySelector('[data-cd-h]');
        var m = timer.querySelector('[data-cd-m]');
        var s = timer.querySelector('[data-cd-s]');
        var pad = function (n) { return (n < 10 ? '0' : '') + n; };

        function tick() {
            var diff = Math.max(0, Math.floor((due - Date.now()) / 1000));
            if (h) { h.textContent = pad(Math.floor(diff / 3600)); }
            if (m) { m.textContent = pad(Math.floor((diff % 3600) / 60)); }
            if (s) { s.textContent = pad(diff % 60); }
            if (diff <= 0 && window._mgkRqCd) { clearInterval(window._mgkRqCd); }
        }
        tick();
        window._mgkRqCd = setInterval(tick, 1000);
    }

    /* ── Boot ─────────────────────────────────────────────── */
    function boot() {
        // Init exactly one form controller. Prefer a native <form>; else the
        // split-layout submit scope; else any composite wrapper.
        var root = document.querySelector('#js-mgk-request-form')
            || document.querySelector('[data-submit-scope]')
            || document.querySelector('[data-mgk-request]');
        if (root) { initForm(root); }

        var confirm = document.querySelector('[data-mgk-confirm]');
        if (confirm) { initConfirm(confirm); }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
}());
