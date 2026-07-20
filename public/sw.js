// Service Worker: recibe las notificaciones push y las muestra como
// notificaciones reales del sistema operativo (no como un popup dentro
// de la página), incluso si el sitio está cerrado.

self.addEventListener('push', function (event) {
    let datos = {};
    try { datos = event.data ? event.data.json() : {}; } catch (e) { datos = {}; }

    const titulo = datos.title || 'Happy Jumping Peru';
    const opciones = {
        body: datos.body || '',
        icon: datos.icon || '/img/logo_happy_contorno.webp',
        badge: '/img/logo_happy_contorno.webp',
        data: { url: datos.url || '/' },
    };

    event.waitUntil(self.registration.showNotification(titulo, opciones));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    const url = (event.notification.data && event.notification.data.url) || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (lista) {
            for (const cliente of lista) {
                if (cliente.url === url && 'focus' in cliente) return cliente.focus();
            }
            if (clients.openWindow) return clients.openWindow(url);
        })
    );
});
