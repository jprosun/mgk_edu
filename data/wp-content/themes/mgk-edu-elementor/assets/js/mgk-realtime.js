/**
 * MGK real-time client.
 * If a provider (Pusher) is configured → live WebSocket. Otherwise → poll the
 * REST state endpoint. Either way it keeps the notification badge current, so
 * the experience is "live" today and upgrades to true push when you add keys.
 *
 * Updates any element marked [data-mgk-notif-badge] with the unread count, and
 * dispatches a `mgk:realtime` DOM event others can listen to.
 */
(function () {
    'use strict';
    var cfg = window.mgkRealtime || {};

    function setBadge(n) {
        var els = document.querySelectorAll('[data-mgk-notif-badge]');
        Array.prototype.forEach.call(els, function (el) {
            n = parseInt(n, 10) || 0;
            el.textContent = n > 0 ? (n > 99 ? '99+' : String(n)) : '';
            el.style.display = n > 0 ? '' : 'none';
            el.setAttribute('data-count', n);
        });
    }
    function emit(event, data) {
        try { document.dispatchEvent(new CustomEvent('mgk:realtime', { detail: { event: event, data: data } })); } catch (e) {}
    }

    // ── Polling fallback (always available) ──
    function poll() {
        if (!cfg.pollUrl || !window.fetch) { return; }
        fetch(cfg.pollUrl, { headers: { 'X-WP-Nonce': cfg.nonce || '' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (j) { if (j && typeof j.unread !== 'undefined') { setBadge(j.unread); } })
            .catch(function () {});
    }

    // ── Pusher (loaded only when configured) ──
    function startPusher() {
        var s = document.createElement('script');
        s.src = 'https://js.pusher.com/8.2/pusher.min.js';
        s.onload = function () {
            try {
                var p = new window.Pusher(cfg.key, { cluster: cfg.cluster || 'ap1', forceTLS: true });
                var ch = p.subscribe(cfg.channel);
                ch.bind('message.received', function (d) { poll(); emit('message.received', d); });
                ch.bind('lesson.logged', function (d) { poll(); emit('lesson.logged', d); });
            } catch (e) { startPolling(); }
        };
        s.onerror = startPolling;
        document.head.appendChild(s);
    }

    var timer = null;
    function startPolling() {
        poll();
        if (timer) { return; }
        timer = setInterval(function () {
            if (document.visibilityState === 'visible') { poll(); }
        }, cfg.pollMs || 25000);
    }

    function init() {
        poll(); // initial badge sync regardless of transport
        if (cfg.enabled && cfg.key) { startPusher(); } else { startPolling(); }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
