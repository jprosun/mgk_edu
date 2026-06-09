(function () {
    'use strict';

    var selected = [];
    var maxCompare = 3;

    document.addEventListener('DOMContentLoaded', function () {
        initCountdowns();
        window.mgkInitProposalCompare();
    });

    function initCountdowns() {
        var timers = document.querySelectorAll('.mgk-proposal-expiry[data-expiry]');
        if (!timers.length) return;

        function tick() {
            timers.forEach(function (box) {
                var target = parseInt(box.dataset.expiry, 10) * 1000;
                var out = box.querySelector('.js-mgk-proposal-countdown');
                if (!target || !out) return;
                var diff = Math.max(0, Math.floor((target - Date.now()) / 1000));
                if (diff <= 0) {
                    out.textContent = 'Expired';
                    box.classList.add('is-expired');
                    return;
                }
                var h = Math.floor(diff / 3600);
                var m = Math.floor((diff % 3600) / 60);
                var s = diff % 60;
                out.textContent = pad(h) + ':' + pad(m) + ':' + pad(s);
            });
        }

        tick();
        setInterval(tick, 1000);
    }

    function pad(n) {
        return n < 10 ? '0' + n : String(n);
    }

    window.mgkInitProposalCompare = function () {
        selected = [];
        document.querySelectorAll('.js-mgk-proposal-card').forEach(function (card) {
            var data = readCompareData(card);
            if (!data) return;
            if (card.dataset.defaultCompare === '1' && selected.length < maxCompare) {
                selected.push(data);
                card.classList.add('is-compare-selected');
            }
        });

        document.addEventListener('click', function (event) {
            var compareBtn = event.target.closest('.js-mgk-proposal-compare');
            if (compareBtn) {
                event.preventDefault();
                var card = compareBtn.closest('.js-mgk-proposal-card');
                if (card) window.mgkToggleProposalCompare(card.dataset.tutorId);
            }

            var toggle = event.target.closest('.js-mgk-compare-toggle');
            if (toggle) {
                event.preventDefault();
                window.mgkExpandCompareDrawer();
            }

            var demo = event.target.closest('.mgk-proposal-demo');
            if (demo) {
                event.preventDefault();
                showToast('Demo preview placeholder.');
            }
        });

        window.mgkRenderCompareDrawer();
    };

    window.mgkToggleProposalCompare = function (tutorId) {
        var card = document.querySelector('.js-mgk-proposal-card[data-tutor-id="' + cssEscape(tutorId) + '"]');
        if (!card) return;
        var data = readCompareData(card);
        if (!data) return;

        var index = selected.findIndex(function (item) { return String(item.id) === String(data.id); });
        if (index >= 0) {
            selected.splice(index, 1);
            card.classList.remove('is-compare-selected');
        } else {
            if (selected.length >= maxCompare) {
                showToast('You can compare up to 3 tutors.');
                return;
            }
            selected.push(data);
            card.classList.add('is-compare-selected');
        }
        window.mgkRenderCompareDrawer();
    };

    window.mgkRenderCompareDrawer = function () {
        var drawer = document.querySelector('.js-mgk-proposal-drawer');
        if (!drawer) return;

        var count = drawer.querySelector('.js-mgk-compare-count');
        var head = drawer.querySelector('.js-mgk-compare-head-row');
        var body = drawer.querySelector('.js-mgk-compare-table-body');
        if (count) count.textContent = String(selected.length);

        drawer.hidden = selected.length < 1;
        if (!head || !body || selected.length < 1) return;

        head.innerHTML = '<tr><th>Attribute</th>' + selected.map(function (item) {
            return '<th>' + escapeHtml(item.name) + '</th>';
        }).join('') + '</tr>';

        var rows = [
            ['Rating', 'rating'],
            ['Rate / Trial', 'rateTrial'],
            ['Experience', 'experience']
        ];

        body.innerHTML = rows.map(function (row) {
            return '<tr><td>' + escapeHtml(row[0]) + '</td>' + selected.map(function (item) {
                return '<td>' + escapeHtml(item[row[1]] || '') + '</td>';
            }).join('') + '</tr>';
        }).join('');
    };

    window.mgkClearCompare = function () {
        selected = [];
        document.querySelectorAll('.js-mgk-proposal-card.is-compare-selected').forEach(function (card) {
            card.classList.remove('is-compare-selected');
        });
        window.mgkRenderCompareDrawer();
    };

    window.mgkExpandCompareDrawer = function () {
        var drawer = document.querySelector('.js-mgk-proposal-drawer');
        if (!drawer) return;
        drawer.classList.toggle('is-collapsed');
    };

    function readCompareData(card) {
        try {
            return JSON.parse(card.dataset.compare || '{}');
        } catch (e) {
            return null;
        }
    }

    function showToast(message) {
        var toast = document.querySelector('.js-mgk-proposal-toast');
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('is-visible');
        clearTimeout(showToast.timer);
        showToast.timer = setTimeout(function () {
            toast.classList.remove('is-visible');
        }, 2400);
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function cssEscape(value) {
        if (window.CSS && window.CSS.escape) return window.CSS.escape(String(value));
        return String(value).replace(/"/g, '\\"');
    }
})();
