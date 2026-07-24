<?php

/*
 * =========================================
 * MODO DEBUG: Mostrar todos los errores
 * =========================================
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// =========================================

/*
|--------------------------------------------------------------------------
| HAPPYJUMPING - PUNTO DE ENTRADA ÚNICO
|--------------------------------------------------------------------------
*/

// 1. Iniciar la sesión
session_start();

// 2. Definir constantes de rutas
// Ruta a la carpeta 'app' (ahora está en el directorio padre, fuera de public)
define('APP_ROOT', dirname(__DIR__) . '/app');
// Ruta a la carpeta 'public' (este mismo directorio)
define('PUBLIC_ROOT', __DIR__);

// --- ¡EL CAMBIO MÁS IMPORTANTE ESTÁ AQUÍ! ---
// La URL base de tu sitio ya NO incluye "/public"
// Se detecta dinámicamente (host + protocolo de la petición) para que
// funcione igual en producción (happyjumpingperu.com), en Cloud Run
// (*.run.app) o en local. Si se define la variable de entorno URL_ROOT,
// esa tiene prioridad (útil para forzar un dominio específico).
if (getenv('URL_ROOT')) {
    define('URL_ROOT', rtrim(getenv('URL_ROOT'), '/'));
} else {
    $esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $protocolo = $esHttps ? 'https' : 'http';
    $host      = $_SERVER['HTTP_HOST'] ?? 'happyjumpingperu.com';
    define('URL_ROOT', $protocolo . '://' . $host);
}
// ------------------------------------------

// 3. Cargar la configuración y el núcleo
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/config/jwt.php';
require_once APP_ROOT . '/config/apisperu.php';
require_once APP_ROOT . '/config/spotify.php';
require_once APP_ROOT . '/core/App.php';
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Model.php';
require_once APP_ROOT . '/core/JwtHandler.php';

// 5. Iniciar la aplicación
$app = new App();

?>