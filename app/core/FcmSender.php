<?php
/*
 * Envío de notificaciones push nativas a la app Flutter vía Firebase Cloud
 * Messaging (API HTTP v1). Reutiliza la misma cuenta de servicio ya usada
 * para Realtime Database (config/firebase.php → FIREBASE_CREDENTIALS): esa
 * cuenta ya trae permiso de Cloud Messaging por defecto, no hace falta
 * generar credenciales nuevas.
 */
class FcmSender {

    /**
     * Envía una notificación a un token FCM.
     * Devuelve true si FCM la aceptó, 'expirada' si el token ya no está
     * registrado (para borrarlo de la BD), o un string 'error: ...' con el
     * detalle si falló por otro motivo (credenciales, permisos, proyecto
     * equivocado, etc.) - antes devolvía `false` sin más detalle, y eso
     * hacía que el admin viera el mismo aviso genérico de "no hay
     * dispositivos" aunque sí hubiera uno, solo que el envío fallaba en
     * silencio.
     */
    public static function enviar($token, $titulo, $cuerpo, $url) {
        $credenciales = self::credenciales();
        if (!$credenciales) {
            return 'error: falta el archivo de credenciales de Firebase en el servidor (firebase_credentials.json)';
        }

        $accessToken = self::obtenerAccessToken($credenciales, $errorToken);
        if (!$accessToken) {
            return 'error: no se pudo autenticar con Firebase' . ($errorToken ? " ({$errorToken})" : '');
        }

        $mensaje = [
            'message' => [
                'token'        => $token,
                'notification' => ['title' => $titulo, 'body' => $cuerpo],
                'data'         => ['url' => (string) $url],
            ],
        ];

        $ch = curl_init("https://fcm.googleapis.com/v1/projects/{$credenciales['project_id']}/messages:send");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($mensaje),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
        ]);
        $respuesta = curl_exec($ch);
        $codigo    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $cuerpoResp = json_decode($respuesta, true);

        if ($codigo === 404 || $codigo === 400) {
            $estado = $cuerpoResp['error']['status'] ?? '';
            if (in_array($estado, ['NOT_FOUND', 'UNREGISTERED', 'INVALID_ARGUMENT'])) return 'expirada';
        }

        if ($codigo >= 200 && $codigo < 300) return true;

        $detalle = $cuerpoResp['error']['message'] ?? ('HTTP ' . $codigo);
        return 'error: ' . $detalle;
    }

    private static function credenciales() {
        if (!defined('FIREBASE_CREDENTIALS') || !file_exists(FIREBASE_CREDENTIALS)) return false;
        return json_decode(file_get_contents(FIREBASE_CREDENTIALS), true);
    }

    private static function obtenerAccessToken($credenciales, &$error = null) {
        $ahora   = time();
        $header  = self::b64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = self::b64url(json_encode([
            'iss'   => $credenciales['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $ahora,
            'exp'   => $ahora + 3600,
        ]));

        $entrada = $header . '.' . $payload;
        openssl_sign($entrada, $firma, $credenciales['private_key'], 'SHA256');
        $jwt = $entrada . '.' . self::b64url($firma);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $crudo = curl_exec($ch);
        curl_close($ch);
        $respuesta = json_decode($crudo, true);

        if (!isset($respuesta['access_token'])) {
            $error = $respuesta['error_description'] ?? $respuesta['error'] ?? 'respuesta inesperada de Google';
            return false;
        }

        return $respuesta['access_token'];
    }

    private static function b64url($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
