<?php
/*
|--------------------------------------------------------------------------
| Modelo de Spotify (búsqueda de canciones para la playlist de la fiesta)
|--------------------------------------------------------------------------
| Usa el flujo "Client Credentials" de Spotify: solo permite BUSCAR en el
| catálogo público. No crea ni modifica playlists reales, así que no
| requiere que ningún usuario inicie sesión con su cuenta de Spotify.
*/
class SpotifyModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Devuelve un token de acceso de la app (cacheado en sesión mientras
     * siga vigente, para no pedir uno nuevo en cada búsqueda).
     */
    private function obtenerTokenAcceso() {
        if (!empty($_SESSION['spotify_token']) && !empty($_SESSION['spotify_token_expira']) && $_SESSION['spotify_token_expira'] > time()) {
            return $_SESSION['spotify_token'];
        }

        $ch = curl_init('https://accounts.spotify.com/api/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['grant_type' => 'client_credentials']));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode(SPOTIFY_CLIENT_ID . ':' . SPOTIFY_CLIENT_SECRET),
            'Content-Type: application/x-www-form-urlencoded',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        $data = json_decode($response, true);
        if (empty($data['access_token'])) {
            return null;
        }

        $_SESSION['spotify_token'] = $data['access_token'];
        $_SESSION['spotify_token_expira'] = time() + (int) ($data['expires_in'] ?? 3600) - 30;

        return $data['access_token'];
    }

    /**
     * Busca canciones en el catálogo de Spotify (máx. 8 resultados).
     * Devuelve un array de ['nombre','artista','spotify_url','imagen'], o
     * un array vacío si la búsqueda falla.
     */
    public function buscarCanciones($texto) {
        $token = $this->obtenerTokenAcceso();
        if (!$token) {
            return [];
        }

        $url = 'https://api.spotify.com/v1/search?' . http_build_query([
            'q'      => $texto,
            'type'   => 'track',
            'limit'  => 8,
            'market' => 'PE',
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return [];
        }

        $data = json_decode($response, true);
        $items = $data['tracks']['items'] ?? [];

        $resultado = [];
        foreach ($items as $track) {
            $artistas = [];
            foreach (($track['artists'] ?? []) as $artista) {
                $artistas[] = $artista['name'];
            }

            $imagenes = $track['album']['images'] ?? [];
            $imagen = '';
            if (!empty($imagenes)) {
                // La última suele ser la miniatura más chica (~64x64), ideal para la lista.
                $imagen = end($imagenes)['url'] ?? $imagenes[0]['url'];
            }

            $resultado[] = [
                'nombre'      => $track['name'] ?? '',
                'artista'     => implode(', ', $artistas),
                'spotify_url' => $track['external_urls']['spotify'] ?? '',
                'imagen'      => $imagen,
            ];
        }

        return $resultado;
    }
}
