<?php
/*
|--------------------------------------------------------------------------
| Google Maps JavaScript API — mapa interactivo de la sección "Ubícanos"
|--------------------------------------------------------------------------
| Clave del proyecto de Google Cloud (con "Maps JavaScript API" habilitada
| y facturación activa). Restríngela por dominio (HTTP referrers) a
| happyjumpingperu.com para que nadie más pueda usarla.
*/
define('GOOGLE_MAPS_API_KEY', getenv('GOOGLE_MAPS_API_KEY') ?: '');
