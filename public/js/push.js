// Suscribe el dispositivo a las notificaciones push. Dos caminos:
// 1) Automático y silencioso si el sitio está instalado como app (PWA) -
//    quien lo instaló claramente quiere la experiencia completa.
// 2) Un botón flotante para cualquier visitante normal desde el navegador,
//    ya que casi nadie llega a "instalar" el sitio por su cuenta y antes
//    de esto no había ninguna forma de que un visitante común se suscribiera
//    (por eso el admin nunca tenía a quién mandarle notificaciones).
// Requiere que window.HJ_URL_ROOT y window.HJ_VAPID_PUBLIC_KEY ya estén definidos.

(function () {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

    const LS_DESCARTADO = 'hj_push_descartado';

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

    /** Pide permiso (si hace falta) y suscribe. Devuelve true si quedó suscrito. */
    async function suscribir() {
        const registro = await registrar();
        let sub = await registro.pushManager.getSubscription();
        if (sub) return true; // ya suscrito

        if (Notification.permission === 'denied') return false;

        const permiso = await Notification.requestPermission();
        if (permiso !== 'granted') return false;

        sub = await registro.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(window.HJ_VAPID_PUBLIC_KEY),
        });

        await fetch(window.HJ_URL_ROOT + '/api/push/suscribir', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(sub.toJSON()),
        });
        return true;
    }

    // Caso 1: el usuario ya tiene la app instalada, o la acaba de instalar.
    async function suscribirSiHaceFalta() {
        try { await suscribir(); } catch (e) { /* silencioso, es automático */ }
    }
    document.addEventListener('DOMContentLoaded', function () {
        if (estaInstalada()) suscribirSiHaceFalta();
    });
    window.addEventListener('appinstalled', suscribirSiHaceFalta);

    // Caso 2: botón flotante para visitantes normales (navegador, sin instalar nada).
    document.addEventListener('DOMContentLoaded', function () {
        if (estaInstalada()) return; // ya se suscribe solo en el caso 1
        if (Notification.permission !== 'default') return; // ya contestó antes (sí o no)
        if (localStorage.getItem(LS_DESCARTADO) === '1') return;

        const prompt = document.getElementById('hj-push-prompt');
        const btnActivar = document.getElementById('hj-push-activar');
        const btnCerrar = document.getElementById('hj-push-cerrar');
        if (!prompt || !btnActivar || !btnCerrar) return;

        setTimeout(function () {
            prompt.classList.remove('hidden');
            requestAnimationFrame(function () { prompt.classList.add('mostrar'); });
        }, 2500);

        btnActivar.addEventListener('click', async function () {
            btnActivar.disabled = true;
            const ok = await suscribir().catch(function () { return false; });
            prompt.classList.remove('mostrar');
            if (!ok) localStorage.setItem(LS_DESCARTADO, '1');
        });

        btnCerrar.addEventListener('click', function () {
            localStorage.setItem(LS_DESCARTADO, '1');
            prompt.classList.remove('mostrar');
        });
    });
})();
