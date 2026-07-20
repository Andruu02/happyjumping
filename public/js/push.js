// Activa las notificaciones push del navegador (bandeja del sistema).
// Requiere que window.HJ_URL_ROOT y window.HJ_VAPID_PUBLIC_KEY ya estén definidos.

(function () {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

    const btn = document.getElementById('btn-notificaciones');

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const raw = atob(base64);
        const arr = new Uint8Array(raw.length);
        for (let i = 0; i < raw.length; i++) arr[i] = raw.charCodeAt(i);
        return arr;
    }

    function actualizarBoton(suscrito) {
        if (!btn) return;
        btn.classList.toggle('activa', suscrito);
        btn.setAttribute('title', suscrito ? 'Desactivar notificaciones' : 'Activar notificaciones');
        btn.innerHTML = suscrito ? '<i class="bi bi-bell-fill"></i>' : '<i class="bi bi-bell"></i>';
    }

    async function registrar() {
        return navigator.serviceWorker.register(window.HJ_URL_ROOT + '/sw.js');
    }

    async function estadoActual() {
        const registro = await registrar();
        const sub = await registro.pushManager.getSubscription();
        actualizarBoton(!!sub);
        return sub;
    }

    async function suscribir() {
        const permiso = await Notification.requestPermission();
        if (permiso !== 'granted') return;

        const registro = await registrar();
        const sub = await registro.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(window.HJ_VAPID_PUBLIC_KEY),
        });

        await fetch(window.HJ_URL_ROOT + '/api/push/suscribir', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(sub.toJSON()),
        });

        actualizarBoton(true);
    }

    async function desuscribir() {
        const registro = await registrar();
        const sub = await registro.pushManager.getSubscription();
        if (!sub) return;

        await fetch(window.HJ_URL_ROOT + '/api/push/desuscribir', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ endpoint: sub.endpoint }),
        });
        await sub.unsubscribe();
        actualizarBoton(false);
    }

    if (btn) {
        btn.addEventListener('click', async function () {
            const registro = await registrar();
            const sub = await registro.pushManager.getSubscription();
            if (sub) {
                await desuscribir();
            } else {
                await suscribir();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', estadoActual);
})();
