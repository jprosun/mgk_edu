(function () {
    function qsa(selector, root) {
        return Array.prototype.slice.call((root || document).querySelectorAll(selector));
    }

    function num(value, fallback) {
        var parsed = parseFloat(value);
        return Number.isFinite(parsed) ? parsed : fallback;
    }

    function fmt(value) {
        var rounded = Math.round(value * 10) / 10;
        return Math.abs(rounded - Math.round(rounded)) < 0.01 ? String(Math.round(rounded)) : rounded.toFixed(1);
    }

    function cardData(card) {
        return {
            rating: num(card.dataset.rating, 0),
            photos: parseInt(card.dataset.photos || '0', 10) || 0,
            teaching: num(card.dataset.teaching, 0),
            patience: num(card.dataset.patience, 0),
            punctuality: num(card.dataset.punctuality, 0),
            communication: num(card.dataset.communication, 0)
        };
    }

    function avg(items, key) {
        var values = items.map(function (item) { return item[key]; }).filter(function (value) { return value > 0; });
        if (!values.length) return 0;
        return values.reduce(function (sum, value) { return sum + value; }, 0) / values.length;
    }

    function summarize(cards) {
        var data = cards.map(cardData);
        var breakdown = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };
        data.forEach(function (item) {
            var bucket = Math.max(1, Math.min(5, Math.floor(item.rating || 1)));
            breakdown[bucket] += 1;
        });
        return {
            count: data.length,
            rating: avg(data, 'rating'),
            teaching: avg(data, 'teaching'),
            patience: avg(data, 'patience'),
            punctuality: avg(data, 'punctuality'),
            communication: avg(data, 'communication'),
            breakdown: breakdown
        };
    }

    function updateSummary(root, cards) {
        var summary = summarize(cards);
        var max = Math.max(1, summary.breakdown[1], summary.breakdown[2], summary.breakdown[3], summary.breakdown[4], summary.breakdown[5]);
        var score = root.querySelector('[data-mgk-review-score]');
        var count = root.querySelector('[data-mgk-review-count]');

        if (score) score.textContent = summary.count ? fmt(summary.rating) : '0';
        if (count) count.textContent = String(summary.count);

        qsa('[data-mgk-rating-row]', root).forEach(function (row) {
            var stars = parseInt(row.getAttribute('data-mgk-rating-row'), 10);
            var value = summary.breakdown[stars] || 0;
            var bar = row.querySelector('[data-mgk-rating-bar]');
            var label = row.querySelector('[data-mgk-rating-count]');
            if (bar) bar.style.setProperty('--rating-count', String(Math.max(4, Math.round((value / max) * 100))));
            if (label) label.textContent = String(value);
        });

        ['teaching', 'patience', 'punctuality', 'communication'].forEach(function (key) {
            var target = root.querySelector('[data-mgk-breakout="' + key + '"]');
            if (target) target.textContent = summary.count ? fmt(summary[key]) : '0';
        });
    }

    function visibleCards(root) {
        return qsa('[data-mgk-review-card]', root).filter(function (card) {
            return !card.classList.contains('is-hidden');
        });
    }

    function isExpanded(root) {
        return root.getAttribute('data-mgk-reviews-expanded') === 'true';
    }

    function clearSelected(root) {
        qsa('[data-mgk-review-card]', root).forEach(function (card) {
            card.classList.remove('is-active');
            card.removeAttribute('aria-pressed');
        });
    }

    function applyFilter(root, filter) {
        clearSelected(root);
        qsa('[data-mgk-review-filter]', root).forEach(function (button) {
            button.classList.toggle('is-active', button.getAttribute('data-mgk-review-filter') === filter);
        });

        qsa('[data-mgk-review-card]', root).forEach(function (card) {
            var data = cardData(card);
            var show = filter === 'all'
                || (filter === '5' && Math.floor(data.rating) >= 5)
                || (filter === 'photos' && data.photos > 0);
            if (filter === 'all' && !isExpanded(root) && card.hasAttribute('data-mgk-review-extra')) {
                show = false;
            }
            card.classList.toggle('is-hidden', !show);
        });

        updateSummary(root, visibleCards(root));
    }

    function revealAll(root) {
        root.setAttribute('data-mgk-reviews-expanded', 'true');
        qsa('[data-mgk-review-card]', root).forEach(function (card) {
            card.classList.remove('is-hidden');
        });
        qsa('[data-mgk-review-filter]', root).forEach(function (button) {
            button.classList.toggle('is-active', button.getAttribute('data-mgk-review-filter') === 'all');
        });
        clearSelected(root);
        updateSummary(root, visibleCards(root));

        var seeAll = root.querySelector('[data-mgk-review-see-all]');
        if (seeAll) {
            seeAll.textContent = 'All reviews shown';
            seeAll.setAttribute('aria-disabled', 'true');
            seeAll.classList.add('is-disabled');
        }
    }

    document.addEventListener('click', function (event) {
        var filter = event.target.closest('[data-mgk-review-filter]');
        if (filter) {
            applyFilter(filter.closest('[data-mgk-reviews]'), filter.getAttribute('data-mgk-review-filter'));
            return;
        }

        var seeAll = event.target.closest('[data-mgk-review-see-all]');
        if (seeAll) {
            event.preventDefault();
            var root = seeAll.closest('[data-mgk-reviews]');
            revealAll(root);
            return;
        }

        var card = event.target.closest('[data-mgk-review-card]');
        if (card) {
            var container = card.closest('[data-mgk-reviews]');
            clearSelected(container);
            card.classList.add('is-active');
            card.setAttribute('aria-pressed', 'true');
            updateSummary(container, [card]);
        }
    });

    document.addEventListener('keydown', function (event) {
        var card = event.target.closest('[data-mgk-review-card]');
        if (!card || (event.key !== 'Enter' && event.key !== ' ')) return;
        event.preventDefault();
        card.click();
    });

    document.addEventListener('DOMContentLoaded', function () {
        qsa('[data-mgk-reviews]').forEach(function (root) {
            updateSummary(root, visibleCards(root));
        });
    });
}());
