// Suscribe el dispositivo a las notificaciones push automáticamente,
// sin botón visible: el admin envía notificaciones desde /admin/notificaciones
// y le llegan al celular de quien tenga el sitio instalado como app.
// Requiere que window.HJ_URL_ROOT y window.HJ_VAPID_PUBLIC_KEY ya estén definidos.

(function () {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const raw = atob(base64);
        const arr = new Uint8Array(raw.length);
        for (let i = 0; i < raw.length; i++) arr[i] = raw.charCodeAt(i);
        return arr;
    }

    function estaInstalada() {
        return window.matchMedia('(display-mode: standalone)').matches
            || window.navigator.standalone === true; // iOS
    }

    async function registrar() {
        return navigator.serviceWorker.register(window.HJ_URL_ROOT + '/sw.js');
    }

    async function suscribirSiHaceFalta() {
        const registro = await registrar();
        let sub = await registro.pushManager.getSubscription();
        if (sub) return; // ya suscrito

        if (Notification.permission === 'denied') return;

        const permiso = await Notification.requestPermission();
        if (permiso !== 'granted') return;

        sub = await registro.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(window.HJ_VAPID_PUBLIC_KEY),
        });

        await fetch(window.HJ_URL_ROOT + '/api/push/suscribir', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(sub.toJSON()),
        });
    }

    // Caso 1: el usuario ya tiene la app instalada (abre el sitio desde el
    // ícono agregado a su pantalla de inicio).
    document.addEventListener('DOMContentLoaded', function () {
        if (estaInstalada()) suscribirSiHaceFalta();
    });

    // Caso 2: el usuario acaba de instalarla ahora mismo.
    window.addEventListener('appinstalled', suscribirSiHaceFalta);
})();
