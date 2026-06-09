(function () {
    function setupPackageCards() {
        var cards = Array.prototype.slice.call(document.querySelectorAll('[data-mgk-package-card]'));
        if (!cards.length) {
            return;
        }

        cards.forEach(function (card) {
            card.addEventListener('click', function () {
                cards.forEach(function (item) {
                    item.classList.toggle('is-selected', item === card);
                });
            });

            card.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    card.click();
                }
            });
        });
    }

    function setupComparisonCards() {
        var cards = Array.prototype.slice.call(document.querySelectorAll('[data-mgk-comparison-card]'));
        if (!cards.length) {
            return;
        }

        cards.forEach(function (card) {
            card.addEventListener('click', function () {
                cards.forEach(function (item) {
                    item.classList.toggle('is-selected', item === card);
                });
            });

            card.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    card.click();
                }
            });
        });
    }

    setupPackageCards();
    setupComparisonCards();

    var root = document.querySelector('[data-mgk-pricing-calculator]');
    if (!root) {
        return;
    }

    var rates = {
        preschool: { part_time: [25, 35], full_time: [35, 45], ex_moe: [45, 55], premium: [55, 65] },
        p1p4: { part_time: [30, 40], full_time: [35, 45], ex_moe: [45, 55], premium: [55, 70] },
        p5p6: { part_time: [35, 50], full_time: [45, 65], ex_moe: [70, 90], premium: [90, 120] },
        sec: { part_time: [40, 55], full_time: [55, 75], ex_moe: [75, 95], premium: [95, 120] },
        jcib: { part_time: [60, 80], full_time: [80, 110], ex_moe: [110, 140], premium: [140, 200] }
    };
    var tierLabels = { part_time: 'Part-time', full_time: 'Full-time', ex_moe: 'Ex-MOE', premium: 'Premium' };
    var discounts = { single: 1, 8: 0.95, 16: 0.9 };

    function checked(name) {
        return root.querySelector('input[name="' + name + '"]:checked');
    }

    function money(value) {
        return '$' + Math.round(value).toLocaleString();
    }

    function levelQueryValue(value) {
        return {
            preschool: 'Preschool',
            p1p4: 'Primary',
            p5p6: 'Primary',
            sec: 'Secondary',
            jcib: 'JC'
        }[value] || value;
    }

    function tierQueryValue(value) {
        return tierLabels[value] || value;
    }

    function updateSearchUrl(level, subject, tier, range) {
        var cta = root.querySelector('[data-mgk-pricing-search]');
        if (!cta) {
            return;
        }

        var url = new URL(root.getAttribute('data-listing-url') || cta.href, window.location.origin);
        url.searchParams.set('subject', subject.value);
        url.searchParams.set('level', levelQueryValue(level.value));
        url.searchParams.set('tier', tierQueryValue(tier.value));
        url.searchParams.set('budget_min', String(range[0]));
        url.searchParams.set('budget_max', String(range[1]));
        url.searchParams.set('source', 'pricing_calculator');
        cta.href = url.toString();
    }

    function update() {
        var level = checked('level');
        var subject = checked('subject');
        var tier = checked('tier');
        var duration = checked('duration');
        var frequency = checked('frequency');
        var pack = checked('package');
        var error = root.querySelector('[data-mgk-pricing-error]');

        if (!level || !subject || !tier || !duration || !frequency || !pack || Number(frequency.value) <= 0) {
            if (error) {
                error.classList.add('is-visible');
            }
            root.classList.add('has-error');
            return;
        }

        if (error) {
            error.classList.remove('is-visible');
        }
        root.classList.remove('has-error');

        var range = rates[level.value][tier.value];
        var hours = Number(duration.value);
        var perWeek = Number(frequency.value);
        var discount = discounts[pack.value];
        var lessons = pack.value === 'single' ? 1 : Number(pack.value);
        var minLesson = range[0] * hours * discount;
        var maxLesson = range[1] * hours * discount;
        var minMonth = minLesson * perWeek * 4;
        var maxMonth = maxLesson * perWeek * 4;
        var minTotal = minLesson * lessons;
        var maxTotal = maxLesson * lessons;
        var minSaved = (range[0] * hours * lessons) - minTotal;
        var maxSaved = (range[1] * hours * lessons) - maxTotal;

        root.querySelector('[data-mgk-price-title]').textContent = level.dataset.label + ' ' + subject.value + ' · ' + tierLabels[tier.value] + ' tutor';
        root.querySelector('[data-mgk-price-subtitle]').textContent = hours + 'h x ' + perWeek + ' week x ' + (pack.value === 'single' ? 'pay-as-you-go' : pack.value + '-lesson package');
        root.querySelector('[data-mgk-per-lesson]').textContent = money(minLesson) + ' - ' + money(maxLesson);
        root.querySelector('[data-mgk-hourly]').textContent = '$' + range[0] + '-$' + range[1] + '/hr x ' + hours + 'h x ' + discount + ' package discount';
        root.querySelector('[data-mgk-monthly]').textContent = money(minMonth) + ' - ' + money(maxMonth);
        root.querySelector('[data-mgk-package-label]').textContent = pack.value === 'single' ? 'Single lesson total' : 'Total ' + pack.value + '-lesson package';
        root.querySelector('[data-mgk-total]').textContent = money(minTotal) + ' - ' + money(maxTotal);
        root.querySelector('[data-mgk-savings]').textContent = pack.value === 'single' ? 'No package discount selected' : 'Save ' + money(minSaved) + '-' + money(maxSaved) + ' vs single';
        root.querySelector('[data-mgk-trial]').textContent = money(minLesson * 0.6);
        updateSearchUrl(level, subject, tier, range);
    }

    root.addEventListener('change', update);
    root.addEventListener('submit', function (event) {
        event.preventDefault();
        update();
    });
    update();
}());
