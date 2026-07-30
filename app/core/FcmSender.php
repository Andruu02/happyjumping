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
     * registrado (para borrarlo de la BD), o false si falló por otro motivo.
     */
    public static function enviar($token, $titulo, $cuerpo, $url) {
        $credenciales = self::credenciales();
        if (!$credenciales) return false;

        $accessToken = self::obtenerAccessToken($credenciales);
        if (!$accessToken) return false;

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

        if ($codigo === 404 || $codigo === 400) {
            $cuerpoResp = json_decode($respuesta, true);
            $estado = $cuerpoResp['error']['status'] ?? '';
            if (in_array($estado, ['NOT_FOUND', 'UNREGISTERED', 'INVALID_ARGUMENT'])) return 'expirada';
        }

        return $codigo >= 200 && $codigo < 300;
    }

    private static function credenciales() {
        if (!defined('FIREBASE_CREDENTIALS') || !file_exists(FIREBASE_CREDENTIALS)) return false;
        return json_decode(file_get_contents(FIREBASE_CREDENTIALS), true);
    }

    private static function obtenerAccessToken($credenciales) {
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
        $respuesta = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $respuesta['access_token'] ?? false;
    }

    private static function b64url($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
