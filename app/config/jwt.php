<?php
/*
|--------------------------------------------------------------------------
| Configuración de JWT
|--------------------------------------------------------------------------
| JWT_SECRET firma y valida los tokens que la app móvil recibe al hacer
| login (POST /api/login) y debe enviar como "Authorization: Bearer <token>"
| en el resto de peticiones a la API.
|
| IMPORTANTE: si cambias este valor, todos los tokens ya emitidos dejarán
| de ser válidos (los usuarios tendrán que volver a iniciar sesión).
*/

define('JWT_SECRET', '2929741b3ef3dfcd07534aac8506276176b83c8ab1ce729e362bf77057c9c70d');

// Tiempo de vida del token, en segundos (7 días)
define('JWT_TTL', 60 * 60 * 24 * 7);
