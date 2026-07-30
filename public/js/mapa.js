// Mapa interactivo de la sección "Ubícanos" (Leaflet + OpenStreetMap, sin
// API key ni cuenta de Google — 100% gratis).

const HJ_MAPA_LAT = -6.5058575638378375;
const HJ_MAPA_LNG = -76.35724119120785;

(function () {
    const contenedor = document.getElementById('mapa-ubicanos');
    if (!contenedor || typeof L === 'undefined') return;

    const mapa = L.map(contenedor, {
        center: [HJ_MAPA_LAT, HJ_MAPA_LNG],
        zoom: 16,
        scrollWheelZoom: false,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(mapa);

    const icono = L.icon({
        iconUrl: window.HJ_URL_ROOT + '/img/logo_happy_contorno.webp',
        iconSize: [48, 48],
        iconAnchor: [24, 48],
        popupAnchor: [0, -48],
    });

    const marcador = L.marker([HJ_MAPA_LAT, HJ_MAPA_LNG], { icon: icono }).addTo(mapa);

    marcador.bindPopup(
        '<div style="font-family:\'Nunito\',sans-serif; text-align:center; padding:2px 4px;">' +
            '<strong style="color:#7b2ff7; font-family:\'Fredoka\',sans-serif;">Happy Jumping Perú</strong><br>' +
            '<a href="https://www.google.com/maps/dir/?api=1&destination=' + HJ_MAPA_LAT + ',' + HJ_MAPA_LNG + '" ' +
               'target="_blank" rel="noopener" style="color:#ff3c8d; font-weight:700; text-decoration:none;">' +
                '📍 Cómo llegar' +
            '</a>' +
        '</div>'
    ).openPopup();
})();
