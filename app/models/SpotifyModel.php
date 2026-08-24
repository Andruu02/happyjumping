<?php
/*
|--------------------------------------------------------------------------
| Modelo de Spotify (búsqueda de canciones para la playlist de la fiesta)
|--------------------------------------------------------------------------
| Reemplaza a Deezer. Usa el flujo "Client Credentials" de Spotify (Client
| ID + Client Secret, sin que el cliente que reserva tenga que loguearse a
| nada) para autenticar la búsqueda en /v1/search.
*/
class SpotifyModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    private function leerEnv($nombre) {
        $valor = trim(getenv($nombre) ?: '', " \t\n\r\0\x0B\"'");
        if ($valor === '') {
            $envPath = dirname(__DIR__, 2) . '/.env';
            if (file_exists($envPath)) {
                $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $l) {
                    if (strpos(trim($l), $nombre . '=') === 0) {
                        $valor = trim(substr(trim($l), strlen($nombre) + 1), " \t\n\r\0\x0B\"'");
                        break;
                    }
                }
            }
        }
        return $valor;
    }

    /**
     * Escribe/actualiza una sola variable en el .env (se usa para guardar
     * el refresh token que devuelve Spotify tras la autorización OAuth -
     * ver urlAutorizacion()/procesarCallback() más abajo). Reemplaza la
     * línea si ya existe, si no la agrega al final.
     */
    private function guardarEnv($nombre, $valor) {
        $envPath = dirname(__DIR__, 2) . '/.env';
        $lineaNueva = $nombre . '=' . $valor;

        $lines = file_exists($envPath) ? file($envPath, FILE_IGNORE_NEW_LINES) : [];
        $encontrada = false;
        foreach ($lines as $i => $l) {
            if (strpos(trim($l), $nombre . '=') === 0) {
                $lines[$i] = $lineaNueva;
                $encontrada = true;
                break;
            }
        }
        if (!$encontrada) {
            $lines[] = $lineaNueva;
        }

        file_put_contents($envPath, implode(PHP_EOL, $lines) . PHP_EOL);
        putenv($lineaNueva); // disponible también para el resto de este mismo request
    }

    /**
     * Pide un access token vía Client Credentials Flow. No hace falta
     * cachearlo entre requests: el buscador solo se llama cuando el
     * cliente aprieta buscar, no en cada tecla.
     */
    private function obtenerToken() {
        $clientId     = $this->leerEnv('SPOTIFY_CLIENT_ID');
        $clientSecret = $this->leerEnv('SPOTIFY_CLIENT_SECRET');
        if ($clientId === '' || $clientSecret === '') {
            return null;
        }

        $ch = curl_init('https://accounts.spotify.com/api/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query(['grant_type' => 'client_credentials']),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_TIMEOUT => 8,
        ]);
        $respuesta = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$respuesta) {
            return null;
        }

        $data = json_decode($respuesta, true);
        return $data['access_token'] ?? null;
    }

    /**
     * Busca canciones o playlists en el catálogo de Spotify (máx. 8
     * resultados). $tipo es 'track' o 'playlist'. Devuelve un array de
     * ['nombre','artista','enlace','imagen','spotify_id','tipo'], o un
     * array vacío si la búsqueda o la autenticación fallan.
     */
    public function buscarCanciones($texto, $tipo = 'track') {
        $tipo = $tipo === 'playlist' ? 'playlist' : 'track';

        $token = $this->obtenerToken();
        if (!$token) {
            return [];
        }

        $url = 'https://api.spotify.com/v1/search?' . http_build_query([
            'q'     => $texto,
            'type'  => $tipo,
            'limit' => 8,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
            CURLOPT_TIMEOUT        => 8,
        ]);
        $respuesta = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$respuesta) {
            return [];
        }

        $data  = json_decode($respuesta, true);
        $resultado = [];

        if ($tipo === 'playlist') {
            $items = $data['playlists']['items'] ?? [];
            foreach ($items as $playlist) {
                // La API a veces manda entradas null adentro de "items"
                // (playlists borradas/privadas que igual matchean la
                // búsqueda) - hay que saltarlas.
                if (!$playlist) continue;

                $imagenes = $playlist['images'] ?? [];
                $imagen = $imagenes[0]['url'] ?? '';

                $resultado[] = [
                    'nombre'     => $playlist['name'] ?? '',
                    'artista'    => 'Playlist de ' . ($playlist['owner']['display_name'] ?? 'Spotify'),
                    'enlace'     => $playlist['external_urls']['spotify'] ?? '',
                    'imagen'     => $imagen,
                    'spotify_id' => $playlist['id'] ?? '',
                    'tipo'       => 'playlist',
                ];
            }
            return $resultado;
        }

        $items = $data['tracks']['items'] ?? [];
        foreach ($items as $track) {
            $imagenes = $track['album']['images'] ?? [];
            // Spotify manda las imágenes de más grande a más chica; la
            // última suele ser la miniatura (64x64), ideal para la lista.
            $imagen = end($imagenes)['url'] ?? ($imagenes[0]['url'] ?? '');

            $resultado[] = [
                'nombre'     => $track['name'] ?? '',
                'artista'    => $track['artists'][0]['name'] ?? '',
                'enlace'     => $track['external_urls']['spotify'] ?? '',
                'imagen'     => $imagen,
                'spotify_id' => $track['id'] ?? '',
                'tipo'       => 'track',
            ];
        }

        return $resultado;
    }

    /* ======================================================================
     * A partir de acá: flujo OAuth "Authorization Code" con la cuenta
     * Premium del negocio. La búsqueda de arriba usa Client Credentials
     * (solo lectura, sin dueño); crear/editar playlists requiere que un
     * usuario real autorice la app una vez con permiso de escritura -
     * ese permiso se guarda como refresh token y de ahí en más se reusa
     * sin volver a pedir login.
     * ==================================================================== */

    /** true si ya se hizo la autorización una vez (hay refresh token guardado). */
    public function conectada() {
        return $this->leerEnv('SPOTIFY_REFRESH_TOKEN') !== '';
    }

    /** URL de login/autorización de Spotify a la que hay que mandar al admin. */
    public function urlAutorizacion() {
        $clientId = $this->leerEnv('SPOTIFY_CLIENT_ID');
        return 'https://accounts.spotify.com/authorize?' . http_build_query([
            'client_id'     => $clientId,
            'response_type' => 'code',
            'redirect_uri'  => URL_ROOT . '/admin/spotify-callback',
            'scope'         => 'playlist-modify-public playlist-modify-private',
            'show_dialog'   => 'true',
        ]);
    }

    /**
     * Intercambia el "code" que Spotify manda de vuelta al callback por un
     * refresh token permanente, y lo guarda en el .env. Devuelve true/false.
     */
    public function procesarCallback($code) {
        $clientId     = $this->leerEnv('SPOTIFY_CLIENT_ID');
        $clientSecret = $this->leerEnv('SPOTIFY_CLIENT_SECRET');

        $ch = curl_init('https://accounts.spotify.com/api/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type'   => 'authorization_code',
                'code'         => $code,
                'redirect_uri' => URL_ROOT . '/admin/spotify-callback',
            ]),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_TIMEOUT => 8,
        ]);
        $respuesta = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$respuesta) {
            return false;
        }

        $data = json_decode($respuesta, true);
        if (empty($data['refresh_token'])) {
            return false;
        }

        $this->guardarEnv('SPOTIFY_REFRESH_TOKEN', $data['refresh_token']);
        return true;
    }

    /**
     * Access token "de usuario" (con permiso de escritura), a partir del
     * refresh token guardado. Distinto de obtenerToken(), que es de solo
     * lectura y no sirve para crear/editar playlists.
     */
    private function obtenerTokenUsuario(&$error = null) {
        $clientId     = $this->leerEnv('SPOTIFY_CLIENT_ID');
        $clientSecret = $this->leerEnv('SPOTIFY_CLIENT_SECRET');
        $refreshToken = $this->leerEnv('SPOTIFY_REFRESH_TOKEN');
        if ($clientId === '' || $clientSecret === '' || $refreshToken === '') {
            $error = 'no conectado (falta autorizar Spotify desde el Dashboard del admin)';
            return null;
        }

        $ch = curl_init('https://accounts.spotify.com/api/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type'    => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . base64_encode($clientId . ':' . $clientSecret),
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_TIMEOUT => 8,
        ]);
        $respuesta = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($respuesta, true);

        if ($httpCode !== 200 || !$respuesta) {
            $error = 'no se pudo renovar el token (HTTP ' . $httpCode . ($data['error_description'] ?? $data['error'] ?? '' ? ': ' . ($data['error_description'] ?? $data['error']) : '') . ')';
            return null;
        }

        // Spotify a veces rota el refresh token al usarlo; si manda uno
        // nuevo hay que guardarlo o el actual deja de servir.
        if (!empty($data['refresh_token'])) {
            $this->guardarEnv('SPOTIFY_REFRESH_TOKEN', $data['refresh_token']);
        }

        if (empty($data['access_token'])) {
            $error = 'respuesta sin access_token';
            return null;
        }

        return $data['access_token'];
    }

    /**
     * Crea de verdad la playlist "Playlist de {nombre}" en la cuenta de
     * Spotify del negocio y le mete las canciones elegidas. Se llama recién
     * al finalizar la reserva (con el pago ya resuelto), no mientras el
     * cliente todavía está armando la lista, para no dejar playlists
     * huérfanas de reservas que nunca se completan.
     *
     * Devuelve la URL pública de la playlist si funcionó, o un string
     * 'error: ...' con el motivo si algo falló (nunca bloquea la reserva -
     * el llamador decide qué hacer con el error, pero al menos ya no
     * desaparece en silencio como antes).
     */
    public function crearPlaylistDesdeReserva($nombreCumpleanero, $canciones) {
        $token = $this->obtenerTokenUsuario($errorToken);
        if (!$token) {
            return 'error: ' . $errorToken;
        }

        // 1. ID de la cuenta dueña (la del negocio) - la API de creación
        // de playlists lo pide en la URL.
        $ch = curl_init('https://api.spotify.com/v1/me');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
            CURLOPT_TIMEOUT        => 8,
        ]);
        $respuesta = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 200 || !$respuesta) {
            return 'error: no se pudo leer la cuenta de Spotify (HTTP ' . $httpCode . ')';
        }
        $userId = json_decode($respuesta, true)['id'] ?? null;
        if (!$userId) {
            return 'error: la cuenta de Spotify no devolvió un ID de usuario';
        }

        // 2. Crear la playlist vacía.
        $ch = curl_init("https://api.spotify.com/v1/users/{$userId}/playlists");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode([
                'name'        => 'Playlist de ' . $nombreCumpleanero,
                'public'      => false,
                'description' => 'Armada por el cliente en happyjumpingperu.com',
            ]),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 8,
        ]);
        $respuesta = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode !== 201 || !$respuesta) {
            $data = json_decode($respuesta, true);
            $detalle = $data['error']['message'] ?? ('HTTP ' . $httpCode);
            return 'error: no se pudo crear la playlist (' . $detalle . ')';
        }
        $playlist    = json_decode($respuesta, true);
        $playlistId  = $playlist['id'] ?? null;
        $playlistUrl = $playlist['external_urls']['spotify'] ?? null;
        if (!$playlistId) {
            return 'error: la respuesta de Spotify no trajo un ID de playlist';
        }

        // 3. Agregarle las canciones. Los resultados de tipo "playlist"
        // (la pestaña de búsqueda de playlists) no se pueden meter como
        // canción suelta, así que se ignoran acá - igual quedan guardadas
        // en reserva_canciones para que la anfitriona sepa que se pidieron.
        $uris = [];
        foreach ($canciones as $cancion) {
            if (!empty($cancion['spotify_id']) && ($cancion['tipo'] ?? 'track') === 'track') {
                $uris[] = 'spotify:track:' . $cancion['spotify_id'];
            }
        }

        if (!empty($uris)) {
            $ch = curl_init("https://api.spotify.com/v1/playlists/{$playlistId}/tracks");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode(['uris' => $uris]),
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json',
                ],
                CURLOPT_TIMEOUT => 8,
            ]);
            curl_exec($ch);
            curl_close($ch);
            // No aborta si esto falla: la playlist ya existe y su link se
            // guarda igual, peor caso queda vacía y se llena a mano.
        }

        return $playlistUrl;
    }
}
