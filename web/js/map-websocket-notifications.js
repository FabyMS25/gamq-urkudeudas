(function (window, document, $) {
    'use strict';

    var wsUrl = window.UrkuMapWebSocketUrl;
    var reconnectTimer = null;
    var reconnectDelay = 3000;
    var reloadTimer = null;
    var socket = null;

    function shouldHandle(event) {
        return event && (
            event.resource === 'graderia_silla' ||
            event.resource === 'sitio_eventual' ||
            event.event === 'connection.ready'
        );
    }

    function reloadPjax() {
        if (!$ || !$.pjax) {
            return;
        }

        clearTimeout(reloadTimer);
        reloadTimer = setTimeout(function () {
            if ($('#crud-datatable-pjax').length) {
                $.pjax.reload({
                    container: '#crud-datatable-pjax',
                    timeout: 10000,
                    push: false,
                    replace: false
                });
            }
        }, 400);
    }

    function dispatchLocalEvent(payload) {
        var event;

        if (typeof window.CustomEvent === 'function') {
            event = new CustomEvent('urku:map-notification', { detail: payload });
        } else {
            event = document.createEvent('CustomEvent');
            event.initCustomEvent('urku:map-notification', false, false, payload);
        }

        document.dispatchEvent(event);
    }

    function handleMessage(message) {
        var payload;

        try {
            payload = JSON.parse(message.data);
        } catch (error) {
            return;
        }

        if (!shouldHandle(payload)) {
            return;
        }

        dispatchLocalEvent(payload);

        if (payload.resource === 'graderia_silla' || payload.resource === 'sitio_eventual') {
            reloadPjax();
        }
    }

    function connect() {
        if (!wsUrl || !window.WebSocket) {
            return;
        }

        socket = new WebSocket(wsUrl);

        socket.onmessage = handleMessage;
        socket.onclose = function () {
            clearTimeout(reconnectTimer);
            reconnectTimer = setTimeout(connect, reconnectDelay);
        };
        socket.onerror = function () {
            socket.close();
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', connect);
    } else {
        connect();
    }
})(window, document, window.jQuery);
