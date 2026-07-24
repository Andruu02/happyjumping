<?php
/*
|--------------------------------------------------------------------------
| Modelo de Deezer (búsqueda de canciones para la playlist de la fiesta)
|--------------------------------------------------------------------------
| Reemplaza a la integración con Spotify: Deezer expone una búsqueda 100%
| pública (sin token, sin Client ID/Secret, sin cuenta premium) en
| https://api.deezer.com/search - un simple GET.
*/
class DeezerModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Busca canciones en el catálogo de Deezer (máx. 8 resultados).
     * Devuelve un array de ['nombre','artista','enlace','imagen'], o un
     * array vacío si la búsqueda falla.
     */
    public function buscarCanciones($texto) {
        $url = 'https://api.deezer.com/search?' . http_build_query([
            'q'     => $texto,
            'limit' => 8,
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return [];
        }

        $data = json_decode($response, true);
        $items = $data['data'] ?? [];

        $resultado = [];
        foreach ($items as $track) {
            $resultado[] = [
                'nombre'  => $track['title'] ?? '',
                'artista' => $track['artist']['name'] ?? '',
                'enlace'  => $track['link'] ?? '',
                'imagen'  => $track['album']['cover_small'] ?? '',
            ];
        }

        return $resultado;
    }
}
