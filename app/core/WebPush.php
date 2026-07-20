<?php
/*
 * Envío de notificaciones push del sistema (Web Push / RFC 8291 + RFC 8188
 * "aes128gcm" + VAPID / RFC 8292), sin dependencias externas: solo usa la
 * extensión openssl que ya trae PHP. Esto hace que el navegador muestre la
 * notificación en la bandeja del sistema operativo aunque el sitio esté
 * cerrado, en vez de un aviso dentro de la página.
 */
class WebPush {

    /** Genera un par de llaves VAPID nuevas (usar una sola vez para configurar el proyecto). */
    public static function generarLlavesVapid() {
        $llave   = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        $detalle = openssl_pkey_get_details($llave)['ec'];

        return [
            'publicKey'  => self::b64url("\x04" . $detalle['x'] . $detalle['y']),
            'privateKey' => self::b64url($detalle['d']),
        ];
    }

    /**
     * Envía una notificación a una suscripción push.
     * $subscripcion = ['endpoint'=>.., 'p256dh'=>.., 'auth'=>..]
     * Devuelve true si el push service la aceptó, 'expirada' si la
     * suscripción ya no es válida (para borrarla de la BD), o false si falló.
     */
    public static function enviar($subscripcion, $payload, $vapidPublicKey, $vapidPrivateKey, $vapidSubject) {
        $endpoint = $subscripcion['endpoint'];

        $cuerpo = self::cifrarPayload(
            $payload,
            self::b64urlDecode($subscripcion['p256dh']),
            self::b64urlDecode($subscripcion['auth'])
        );

        $jwt = self::firmarVapidJwt(self::origenDe($endpoint), $vapidSubject, $vapidPrivateKey);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $cuerpo,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/octet-stream',
                'Content-Encoding: aes128gcm',
                'TTL: 43200',
                'Authorization: vapid t=' . $jwt . ', k=' . $vapidPublicKey,
            ],
        ]);
        curl_exec($ch);
        $codigo = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($codigo === 404 || $codigo === 410) return 'expirada';
        return $codigo >= 200 && $codigo < 300;
    }

    // ── Cifrado del payload (RFC 8291 / content-encoding aes128gcm) ──────────
    private static function cifrarPayload($payload, $uaPublicRaw, $authSecret) {
        $efimera        = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        $efimeraDetalle = openssl_pkey_get_details($efimera)['ec'];
        $asPublicRaw    = "\x04" . $efimeraDetalle['x'] . $efimeraDetalle['y'];

        $uaPubKey   = self::publicKeyFromRaw($uaPublicRaw);
        $ecdhSecret = openssl_pkey_derive($uaPubKey, $efimera, 32);

        $keyInfo = "WebPush: info\x00" . $uaPublicRaw . $asPublicRaw;
        $prkKey  = self::hkdfExtract($authSecret, $ecdhSecret);
        $ikm     = self::hkdfExpand($prkKey, $keyInfo, 32);

        $salt = random_bytes(16);
        $prk  = self::hkdfExtract($salt, $ikm);

        $cek   = self::hkdfExpand($prk, "Content-Encoding: aes128gcm\x00", 16);
        $nonce = self::hkdfExpand($prk, "Content-Encoding: nonce\x00", 12);

        // 0x02 = delimitador de "último registro" (single record, sin relleno)
        $texto = openssl_encrypt($payload . "\x02", 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag);

        $encabezado = $salt . pack('N', 4096) . chr(strlen($asPublicRaw)) . $asPublicRaw;
        return $encabezado . $texto . $tag;
    }

    // ── VAPID: JWT firmado con ES256 (RFC 8292) ───────────────────────────────
    private static function firmarVapidJwt($audiencia, $subject, $vapidPrivateKeyB64) {
        $encabezado = self::b64url(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $claims     = self::b64url(json_encode([
            'aud' => $audiencia,
            'exp' => time() + 12 * 3600,
            'sub' => $subject,
        ]));
        $entrada = $encabezado . '.' . $claims;

        $llavePrivada = self::privateKeyFromRawD(self::b64urlDecode($vapidPrivateKeyB64));
        openssl_sign($entrada, $firmaDer, $llavePrivada, OPENSSL_ALGO_SHA256);

        return $entrada . '.' . self::b64url(self::derSigARaw($firmaDer));
    }

    private static function origenDe($endpoint) {
        $partes = parse_url($endpoint);
        return $partes['scheme'] . '://' . $partes['host'] . (isset($partes['port']) ? ':' . $partes['port'] : '');
    }

    // ── HKDF (RFC 5869), en dos pasos porque necesitamos el PRK intermedio ───
    private static function hkdfExtract($salt, $ikm) {
        return hash_hmac('sha256', $ikm, $salt, true);
    }

    private static function hkdfExpand($prk, $info, $longitud) {
        return substr(hash_hmac('sha256', $info . "\x01", $prk, true), 0, $longitud);
    }

    // ── Reconstrucción de llaves EC (P-256) a partir de sus valores crudos ───
    // Los navegadores entregan p256dh/auth y nosotros guardamos VAPID como
    // los valores crudos base64url (x||y para la pública, d para la privada);
    // openssl solo trabaja con PEM, así que armamos el DER mínimo necesario.
    private static function publicKeyFromRaw($xy65) {
        $x = substr($xy65, 1, 32);
        $y = substr($xy65, 33, 32);
        $punto     = "\x04" . $x . $y;
        $bitstring = "\x03" . self::derLen(strlen($punto) + 1) . "\x00" . $punto;
        $oidEc     = "\x06\x07\x2a\x86\x48\xce\x3d\x02\x01";
        $oidCurva  = "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
        $algoritmo = "\x30" . self::derLen(strlen($oidEc) + strlen($oidCurva)) . $oidEc . $oidCurva;
        $cuerpo    = $algoritmo . $bitstring;
        $der       = "\x30" . self::derLen(strlen($cuerpo)) . $cuerpo;

        return openssl_pkey_get_public(self::pem($der, 'PUBLIC KEY'));
    }

    private static function privateKeyFromRawD($d) {
        $octeto  = "\x04\x20" . $d;
        $version = "\x02\x01\x01";
        $oid     = "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
        $params  = "\xa0" . self::derLen(strlen($oid)) . $oid;
        $cuerpo  = $version . $octeto . $params;
        $der     = "\x30" . self::derLen(strlen($cuerpo)) . $cuerpo;

        return openssl_pkey_get_private(self::pem($der, 'EC PRIVATE KEY'));
    }

    private static function derLen($len) {
        if ($len < 128) return chr($len);
        $bytes = '';
        while ($len > 0) { $bytes = chr($len & 0xff) . $bytes; $len >>= 8; }
        return chr(0x80 | strlen($bytes)) . $bytes;
    }

    private static function pem($der, $etiqueta) {
        return "-----BEGIN {$etiqueta}-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END {$etiqueta}-----\n";
    }

    // ── ECDSA: DER (que produce openssl_sign) → r||s crudo (que exige JWS) ───
    private static function derSigARaw($der) {
        $pos = 1; // saltar tag SEQUENCE
        $pos += self::derLenLeer($der, $pos, $noUsado);

        $r = self::derIntLeer($der, $pos);
        $s = self::derIntLeer($der, $pos);
        return $r . $s;
    }

    private static function derLenLeer($der, $pos, &$len) {
        $b = ord($der[$pos]);
        if (!($b & 0x80)) { $len = $b; return 1; }
        $n = $b & 0x7f;
        $len = 0;
        for ($i = 0; $i < $n; $i++) { $len = ($len << 8) | ord($der[$pos + 1 + $i]); }
        return 1 + $n;
    }

    private static function derIntLeer($der, &$pos) {
        $pos++; // tag INTEGER (0x02)
        $avance = self::derLenLeer($der, $pos, $len);
        $pos += $avance;
        $valor = substr($der, $pos, $len);
        $pos += $len;

        $valor = ltrim($valor, "\x00");
        return str_pad($valor, 32, "\x00", STR_PAD_LEFT);
    }

    // ── Base64url ──────────────────────────────────────────────────────────
    public static function b64url($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function b64urlDecode($data) {
        $data .= str_repeat('=', (4 - strlen($data) % 4) % 4);
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
