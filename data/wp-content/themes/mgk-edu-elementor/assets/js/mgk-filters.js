(function () {
    function qs(selector, root) {
        return (root || document).querySelector(selector);
    }

    function qsa(selector, root) {
        return Array.prototype.slice.call((root || document).querySelectorAll(selector));
    }

    function debounce(fn, ms) {
        var timer;
        return function () {
            clearTimeout(timer);
            timer = setTimeout(fn, ms);
        };
    }

    function updateCompareState() {
        var checked = qsa('[data-mgk-compare]:checked');
        var drawer = qs('[data-mgk-compare-drawer]');
        var count = qs('[data-mgk-compare-count]');

        qsa('[data-mgk-compare]').forEach(function (input) {
            input.disabled = !input.checked && checked.length >= 3;
        });

        if (count) {
            count.textContent = String(checked.length);
        }
        if (drawer) {
            drawer.classList.toggle('is-visible', checked.length > 0);
        }
    }

    document.addEventListener('click', function (event) {
        var open = event.target.closest('[data-mgk-filter-open]');
        if (open) {
            var drawer = qs('[data-mgk-filter-drawer]');
            if (drawer) {
                drawer.hidden = false;
                document.body.classList.add('mgk-filtering');
            }
        }

        if (event.target.closest('[data-mgk-filter-close]')) {
            var sheet = qs('[data-mgk-filter-drawer]');
            if (sheet) {
                sheet.hidden = true;
                document.body.classList.remove('mgk-filtering');
            }
        }

        var closeCompare = event.target.closest('[data-mgk-compare-close]');
        if (closeCompare) {
            var compareDrawer = qs('[data-mgk-compare-drawer]');
            if (compareDrawer) {
                compareDrawer.classList.remove('is-visible');
            }
        }

        var view = event.target.closest('[data-mgk-view]');
        if (view) {
            qsa('[data-mgk-view]').forEach(function (button) {
                button.classList.toggle('is-active', button === view);
            });
            var results = qs('[data-mgk-results]');
            if (results) {
                results.classList.toggle('is-list', view.getAttribute('data-mgk-view') === 'list');
            }
        }
    });

    var debouncedCountUpdate = debounce(function () {
        var countEl = qs('[data-mgk-result-count]');
        if (countEl) {
            countEl.setAttribute('data-updating', '1');
        }
        // REST count endpoint wired here in Batch 2 (FR-DISC-06)
    }, 300);

    document.addEventListener('change', function (event) {
        if (event.target.closest('[data-mgk-filter-form]') && !event.target.matches('[data-mgk-compare]') && !event.target.matches('[data-mgk-sort]')) {
            debouncedCountUpdate();
        }

        if (event.target.matches('[data-mgk-compare]')) {
            if (event.target.checked && qsa('[data-mgk-compare]:checked').length > 3) {
                event.target.checked = false;
            }
            updateCompareState();
        }

        if (event.target.matches('[data-mgk-sort]')) {
            var url = new URL(window.location.href);
            url.searchParams.set('sort', event.target.value);
            window.location.href = url.toString();
        }
    });

    document.addEventListener('DOMContentLoaded', updateCompareState);
}());
