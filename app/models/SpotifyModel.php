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
}
