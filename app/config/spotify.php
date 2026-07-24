<?php
/*
|--------------------------------------------------------------------------
| Configuración de Spotify (búsqueda de canciones para la playlist sugerida)
|--------------------------------------------------------------------------
| Se usa el flujo "Client Credentials" de Spotify: solo sirve para BUSCAR
| en el catálogo público (no para crear/editar playlists reales, ni requiere
| que ningún usuario inicie sesión con su cuenta de Spotify).
|
| Cómo conseguir el Client ID / Client Secret:
| 1. Entra a https://developer.spotify.com/dashboard y loguéate (o crea una
|    cuenta gratuita de Spotify si no tienes).
| 2. "Create app" -> ponle un nombre (ej. "Happy Jumping - Playlist") y
|    cualquier descripción.
| 3. En "Redirect URIs" pon cualquier URL de tu dominio, por ejemplo
|    https://happyjumpingperu.com/ (Spotify exige llenar ese campo aunque
|    este flujo no lo use).
| 4. Guarda la app, entra a "Settings" y copia el "Client ID" y el
|    "Client Secret".
| 5. Pega esos dos valores abajo (o defínelos como variables de entorno
|    SPOTIFY_CLIENT_ID / SPOTIFY_CLIENT_SECRET, igual que la conexión a la
|    base de datos).
*/

define('SPOTIFY_CLIENT_ID', getenv('SPOTIFY_CLIENT_ID') ?: '47fbe4c8935c44cb865e6a61a5def213');
define('SPOTIFY_CLIENT_SECRET', getenv('SPOTIFY_CLIENT_SECRET') ?: '0475ee8b4ef74b42995e95574f667a52');
