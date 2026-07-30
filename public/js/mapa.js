// Mapa interactivo de la sección "Ubícanos" (Google Maps JavaScript API).
// window.iniciarMapaUbicanos se pasa como &callback= del script de la API,
// que lo invoca automáticamente en cuanto google.maps está listo.

const HJ_MAPA_LAT = -6.5058575638378375;
const HJ_MAPA_LNG = -76.35724119120785;

// Estilo suave: oculta el ruido de iconos/etiquetas de negocios ajenos y
// resalta parques/agua con los tonos de marca (celeste/verde).
const HJ_MAPA_ESTILOS = [
    { featureType: 'poi.business', stylers: [{ visibility: 'off' }] },
    { featureType: 'poi', elementType: 'labels.icon', stylers: [{ visibility: 'off' }] },
    { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#cdeffd' }] },
    { featureType: 'poi.park', elementType: 'geometry', stylers: [{ color: '#d7f5e3' }] },
    { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#ffffff' }] },
];

window.iniciarMapaUbicanos = function () {
    const contenedor = document.getElementById('mapa-ubicanos');
    if (!contenedor) return;

    const ubicacion = { lat: HJ_MAPA_LAT, lng: HJ_MAPA_LNG };

    const mapa = new google.maps.Map(contenedor, {
        center: ubicacion,
        zoom: 16,
        styles: HJ_MAPA_ESTILOS,
        streetViewControl: false,
        mapTypeControl: false,
        fullscreenControl: false,
    });

    const marcador = new google.maps.Marker({
        position: ubicacion,
        map: mapa,
        title: 'Happy Jumping Perú',
        icon: {
            url: window.HJ_URL_ROOT + '/img/logo_happy_contorno.webp',
            scaledSize: new google.maps.Size(48, 48),
        },
    });

    const infoWindow = new google.maps.InfoWindow({
        content:
            '<div style="font-family:\'Nunito\',sans-serif; text-align:center; padding:4px 6px;">' +
                '<strong style="color:#7b2ff7; font-family:\'Fredoka\',sans-serif;">Happy Jumping Perú</strong><br>' +
                '<a href="https://www.google.com/maps/dir/?api=1&destination=' + HJ_MAPA_LAT + ',' + HJ_MAPA_LNG + '" ' +
                   'target="_blank" rel="noopener" style="color:#ff3c8d; font-weight:700; text-decoration:none;">' +
                    '📍 Cómo llegar' +
                '</a>' +
            '</div>',
    });

    marcador.addListener('click', () => infoWindow.open(mapa, marcador));
    infoWindow.open(mapa, marcador);
};
