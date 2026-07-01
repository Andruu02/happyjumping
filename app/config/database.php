<?php
/*
|--------------------------------------------------------------------------
| Configuración de la Base de Datos (CORREGIDA)
|--------------------------------------------------------------------------
*/

// --- CONEXIÓN PRINCIPAL (MySQL en Hostinger) ---

/**
 * El host de la base de datos. 'localhost' casi siempre es correcto en Hostinger.
 */
define('DB_HOST', 'localhost');

/**
 * El nombre de tu base de datos (¡CON el prefijo de Hostinger!)
 */
define('DB_NAME', 'u794501168_happyjumpingdb');

/**
 * El nombre de usuario para esa base de datos (¡CON el prefijo de Hostinger!)
 * Reemplaza 'u794501168_tu_usuario' por el que viste en el hPanel.
 */
define('DB_USER', 'u794501168_happyjumping'); // <-- ESTE ES EL ERROR. Cambia 'happyjumping' por tu usuario real.

/**
 * La contraseña para ese usuario de la base de datos.
 * (La que acabas de re-establecer si no la recordabas).
 */
define('DB_PASS', 'HappyBd2025');

/**
 * El juego de caracteres de la base de datos.
 */
define('DB_CHARSET', 'utf8mb4');

?>