<?php
/*
|--------------------------------------------------------------------------
| JwtHandler
|--------------------------------------------------------------------------
| Implementación mínima de JWT (HS256) sin dependencias externas, para
| autenticar a la app móvil frente a la API de HappyJumping.
*/

class JwtHandler {

    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data) {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Genera un token firmado para el payload dado.
     */
    public static function encode(array $payload, $ttl = null) {
        $ttl = $ttl ?? JWT_TTL;

        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        $payload['iat'] = time();
        $payload['exp'] = time() + $ttl;

        $segments   = [];
        $segments[] = self::base64UrlEncode(json_encode($header));
        $segments[] = self::base64UrlEncode(json_encode($payload));

        $signingInput = implode('.', $segments);
        $signature    = hash_hmac('sha256', $signingInput, JWT_SECRET, true);
        $segments[]   = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }

    /**
     * Valida la firma y expiración del token.
     * Devuelve el payload (array) si es válido, o false si no lo es.
     */
    public static function decode($jwt) {
        if (!$jwt || substr_count($jwt, '.') !== 2) {
            return false;
        }

        list($headerB64, $payloadB64, $signatureB64) = explode('.', $jwt);

        $signingInput       = $headerB64 . '.' . $payloadB64;
        $expectedSignature  = hash_hmac('sha256', $signingInput, JWT_SECRET, true);
        $actualSignature    = self::base64UrlDecode($signatureB64);

        if (!hash_equals($expectedSignature, $actualSignature)) {
            return false;
        }

        $payload = json_decode(self::base64UrlDecode($payloadB64), true);

        if (!$payload || !isset($payload['exp']) || $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }
}
