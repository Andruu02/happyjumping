<?php
/*
|--------------------------------------------------------------------------
| Configuración de la Base de Datos (CORREGIDA)
|--------------------------------------------------------------------------
| Todos los valores se pueden sobreescribir con variables de entorno, para
| poder apuntar a Cloud SQL (u otra BD) al desplegar en Cloud Run sin tocar
| este archivo. Si no se definen, se usan los valores de Hostinger
| (producción) de siempre.
*/

// --- CONEXIÓN PRINCIPAL (MySQL en Hostinger, por defecto) ---

/**
 * El host de la base de datos. 'localhost' casi siempre es correcto en Hostinger.
 */
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');

/**
 * Puerto de conexión (solo aplica cuando se conecta por TCP, no por socket).
 */
define('DB_PORT', getenv('DB_PORT') ?: '3306');

/**
 * Socket Unix de Cloud SQL (ej. /cloudsql/PROYECTO:REGION:INSTANCIA).
 * Si esta variable de entorno está definida, se usa socket Unix en vez de
 * host/puerto TCP — así se conecta Cloud Run a Cloud SQL.
 */
define('DB_SOCKET', getenv('DB_SOCKET') ?: '');

/**
 * El nombre de tu base de datos (¡CON el prefijo de Hostinger!)
 */
define('DB_NAME', getenv('DB_NAME') ?: 'u794501168_happyjumpingdb');

/**
 * El nombre de usuario para esa base de datos (¡CON el prefijo de Hostinger!)
 */
define('DB_USER', getenv('DB_USER') ?: 'u794501168_happyjumping');

/**
 * La contraseña para ese usuario de la base de datos.
 */
define('DB_PASS', getenv('DB_PASS') ?: 'HappyBd2025');

/**
 * El juego de caracteres de la base de datos.
 */
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

?>
