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
define('URL_ROOT', 'https://happyjumpingperu.com');
// ------------------------------------------

// 3. Cargar la configuración y el núcleo
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/config/jwt.php';
require_once APP_ROOT . '/core/App.php';
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Model.php';
require_once APP_ROOT . '/core/JwtHandler.php';

// 5. Iniciar la aplicación
$app = new App();

?>