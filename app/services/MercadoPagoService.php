<?php
/**
 * Cobro de Yape vía la Checkout API de Mercado Pago.
 * El token (celular + OTP) se genera en el navegador con la Public Key
 * (segura de exponer); acá solo se usa el Access Token (secreto) para
 * crear el pago del lado del servidor.
 */
class MercadoPagoService {

    private static function leerEnv($nombre) {
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

    public static function publicKey() {
        return self::leerEnv('MP_PUBLIC_KEY');
    }

    private static function accessToken() {
        return self::leerEnv('MP_ACCESS_TOKEN');
    }

    /**
     * Crea el pago en Mercado Pago con el token de Yape ya generado en el
     * navegador. Devuelve ['id','status','status_detail'] o ['error'=>...].
     */
    public function crearPagoYape($token, $monto, $email, $descripcion = 'Reserva Happy Jumping') {
        $accessToken = self::accessToken();
        if ($accessToken === '') {
            return ['error' => 'Falta configurar las credenciales de Mercado Pago en el servidor.'];
        }

        // JSON_PRESERVE_ZERO_FRACTION: sin esto, PHP manda montos redondos
        // (1.00, 250.00) como "1" / "250" en el JSON (sin decimales), y la
        // API de Mercado Pago los rechaza con "Invalid value for
        // transaction_amount" porque espera un número con decimales.
        $payload = json_encode([
            'token'              => $token,
            'transaction_amount' => (float) $monto,
            'description'        => $descripcion,
            'installments'       => 1,
            'payment_method_id'  => 'yape',
            'payer'              => ['email' => $email],
        ], JSON_PRESERVE_ZERO_FRACTION);

        $ch = curl_init('https://api.mercadopago.com/v1/payments');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
                'X-Idempotency-Key: ' . bin2hex(random_bytes(16)),
            ],
            CURLOPT_TIMEOUT => 20,
        ]);
        $respuesta = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorCurl = curl_error($ch);
        curl_close($ch);

        if ($respuesta === false) {
            return ['error' => 'No se pudo conectar con Mercado Pago: ' . $errorCurl];
        }

        $data = json_decode($respuesta, true);

        // OJO: cuando Mercado Pago rechaza la petición, su respuesta de
        // error también trae un campo "status" (el código HTTP del error,
        // ej. 400) - no alcanza con `isset($data['status'])` para saber si
        // el pago se creó bien, hay que mirar el código HTTP real.
        if ($httpCode < 200 || $httpCode >= 300 || !isset($data['id'])) {
            return ['error' => $this->extraerMensajeError($data)];
        }

        return [
            'id'            => $data['id'],
            'status'        => $data['status'],        // approved | in_process | rejected
            'status_detail' => $data['status_detail'] ?? '',
        ];
    }

    /**
     * Arma un mensaje legible a partir de una respuesta de error de
     * Mercado Pago (trae "message" y, casi siempre, una lista "cause" con
     * el detalle real de qué campo/dato vino mal).
     */
    private function extraerMensajeError($data) {
        $mensaje = $data['message'] ?? 'Mercado Pago devolvió una respuesta inesperada.';
        if (!empty($data['cause']) && is_array($data['cause'])) {
            $detalles = array_filter(array_map(function ($c) {
                return $c['description'] ?? ($c['code'] ?? '');
            }, $data['cause']));
            if ($detalles) {
                $mensaje .= ' (' . implode('; ', $detalles) . ')';
            }
        }
        return $mensaje;
    }

    /**
     * Vuelve a consultar un pago por su id directo en Mercado Pago. Se usa
     * en finalizar() para no confiar en el estado que mande el navegador
     * (alguien podría mandar "approved" a mano sin haber pagado) - la única
     * fuente de verdad del estado real es la propia API de Mercado Pago.
     */
    public function consultarPago($idPago) {
        $accessToken = self::accessToken();
        if ($accessToken === '') {
            return ['error' => 'Falta configurar las credenciales de Mercado Pago en el servidor.'];
        }

        $ch = curl_init('https://api.mercadopago.com/v1/payments/' . urlencode($idPago));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
            CURLOPT_TIMEOUT        => 15,
        ]);
        $respuesta = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorCurl = curl_error($ch);
        curl_close($ch);

        if ($respuesta === false) {
            return ['error' => 'No se pudo conectar con Mercado Pago: ' . $errorCurl];
        }

        $data = json_decode($respuesta, true);
        if ($httpCode < 200 || $httpCode >= 300 || !isset($data['id'])) {
            return ['error' => $this->extraerMensajeError($data)];
        }

        return [
            'id'     => $data['id'],
            'status' => $data['status'],
        ];
    }
}
