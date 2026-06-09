(function () {
    function qs(selector, root) {
        return (root || document).querySelector(selector);
    }

    function qsa(selector, root) {
        return Array.prototype.slice.call((root || document).querySelectorAll(selector));
    }

    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('[data-mgk-menu-toggle]');
        if (toggle) {
            var nav = qs('#mgk-mobile-nav');
            var expanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            if (nav) {
                nav.hidden = expanded;
            }
        }

        var faqButton = event.target.closest('[data-mgk-faq-button]');
        if (faqButton) {
            var item = faqButton.closest('.mgk-faq-item');
            var open = item.classList.toggle('is-open');
            faqButton.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        var tab = event.target.closest('[data-mgk-tab]');
        if (tab) {
            var tabs = tab.closest('[data-mgk-tabs]');
            if (tabs) {
                var target = tab.getAttribute('aria-controls');
                qsa('[data-mgk-tab]', tabs).forEach(function (button) {
                    var active = button === tab;
                    button.classList.toggle('is-active', active);
                    button.setAttribute('aria-selected', active ? 'true' : 'false');
                    button.setAttribute('tabindex', active ? '0' : '-1');
                });
                qsa('[data-mgk-tab-panel]', tabs).forEach(function (panel) {
                    panel.hidden = panel.id !== target;
                });
            }
        }
    });

    document.addEventListener('keydown', function (event) {
        var tab = event.target.closest('[data-mgk-tab]');
        if (!tab || ['ArrowLeft', 'ArrowRight', 'Home', 'End'].indexOf(event.key) === -1) {
            return;
        }
        var tabs = qsa('[data-mgk-tab]', tab.closest('[data-mgk-tabs]'));
        var index = tabs.indexOf(tab);
        if (event.key === 'ArrowLeft') {
            index = index <= 0 ? tabs.length - 1 : index - 1;
        } else if (event.key === 'ArrowRight') {
            index = index >= tabs.length - 1 ? 0 : index + 1;
        } else if (event.key === 'Home') {
            index = 0;
        } else if (event.key === 'End') {
            index = tabs.length - 1;
        }
        event.preventDefault();
        tabs[index].focus();
        tabs[index].click();
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (form.matches('[data-mgk-search-form]')) {
            qsa('input, select, textarea', form).forEach(function (field) {
                if (!String(field.value || '').trim()) {
                    field.disabled = true;
                }
            });
        }

        if (form.matches('[data-mgk-newsletter]')) {
            event.preventDefault();
            var email = qs('input[type="email"]', form);
            var note = qs('[data-mgk-form-message]', form);
            if (!email || !email.validity.valid) {
                if (note) {
                    note.textContent = 'Enter a valid email address.';
                    note.classList.add('is-visible');
                }
                return;
            }
            if (note) {
                note.textContent = 'Guide reserved. Check your inbox shortly.';
                note.classList.add('is-visible');
            }
            form.classList.add('is-success');
        }

        if (form.matches('[data-mgk-validate]')) {
            var fields = qsa('[required]', form);
            var ready = fields.every(function (field) {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    return field.checked;
                }

                return String(field.value || '').trim().length > 0;
            });
            if (!ready) {
                event.preventDefault();
                var warning = qs('[data-mgk-form-message]', form);
                if (warning) {
                    warning.classList.add('is-visible');
                }
            }
        }
    });

    var sticky = qs('[data-mgk-mobile-sticky]');
    if (sticky) {
        var updateSticky = function () {
            var active = document.activeElement;
            var inField = active && ['INPUT', 'SELECT', 'TEXTAREA'].indexOf(active.tagName) !== -1;
            var mobile = window.matchMedia('(max-width: 767px)').matches;
            sticky.classList.toggle('is-visible', mobile && window.scrollY > 400 && !inField);
        };
        window.addEventListener('scroll', updateSticky, { passive: true });
        window.addEventListener('resize', updateSticky);
        document.addEventListener('focusin', updateSticky);
        document.addEventListener('focusout', function () {
            window.setTimeout(updateSticky, 50);
        });
        updateSticky();
    }

    qsa('[data-mgk-trial-consent]').forEach(function (checkbox) {
        var form = checkbox.form;
        var button = form ? qs('[data-mgk-consent-submit]', form) : null;
        var updateConsent = function () {
            if (button) {
                button.disabled = !checkbox.checked;
            }
        };

        checkbox.addEventListener('change', updateConsent);
        updateConsent();
    });
}());
