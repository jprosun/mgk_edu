(function () {
    function pushEvent(name, payload) {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push(Object.assign({
            event: name,
            path: window.location.pathname
        }, payload || {}));
    }

    document.addEventListener('click', function (event) {
        var target = event.target.closest('[data-mgk-event]');
        if (!target) {
            return;
        }

        pushEvent(target.getAttribute('data-mgk-event'), {
            label: target.textContent.trim(),
            href: target.getAttribute('href') || ''
        });
    });

    document.addEventListener('submit', function (event) {
        var target = event.target.closest('[data-mgk-event]');
        if (!target) {
            return;
        }

        pushEvent(target.getAttribute('data-mgk-event'), {
            form: target.getAttribute('class') || ''
        });
    });
}());
