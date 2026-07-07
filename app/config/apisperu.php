<?php
/*
|--------------------------------------------------------------------------
| Configuración de APIsPERU (consulta de DNI / RENIEC)
|--------------------------------------------------------------------------
| https://apisperu.com/servicios/dniruc
|
| El token se envía como query param (?token=...), no como header
| Authorization, según el esquema de seguridad de su OpenAPI spec.
*/

define('APISPERU_TOKEN', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6ImFuZHJ1c3VsbGNhcmF5aHVhbWFuQGdtYWlsLmNvbSJ9.C4a3CQKD9ILkl6Z-oGxoXxg7HdzUSov2FpXlk6crlv8');
define('APISPERU_DNI_URL', 'https://dniruc.apisperu.com/api/v1/dni/');
