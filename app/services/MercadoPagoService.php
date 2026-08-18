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

        $payload = json_encode([
            'token'              => $token,
            'transaction_amount' => (float) $monto,
            'description'        => $descripcion,
            'installments'       => 1,
            'payment_method_id'  => 'yape',
            'payer'              => ['email' => $email],
        ]);

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
        $errorCurl = curl_error($ch);
        curl_close($ch);

        if ($respuesta === false) {
            return ['error' => 'No se pudo conectar con Mercado Pago: ' . $errorCurl];
        }

        $data = json_decode($respuesta, true);
        if (!isset($data['status'])) {
            $mensaje = $data['message'] ?? 'Mercado Pago devolvió una respuesta inesperada.';
            return ['error' => $mensaje];
        }

        return [
            'id'            => $data['id'],
            'status'        => $data['status'],        // approved | in_process | rejected
            'status_detail' => $data['status_detail'] ?? '',
        ];
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
        $errorCurl = curl_error($ch);
        curl_close($ch);

        if ($respuesta === false) {
            return ['error' => 'No se pudo conectar con Mercado Pago: ' . $errorCurl];
        }

        $data = json_decode($respuesta, true);
        if (!isset($data['status'])) {
            return ['error' => $data['message'] ?? 'No se pudo verificar el pago en Mercado Pago.'];
        }

        return [
            'id'     => $data['id'],
            'status' => $data['status'],
        ];
    }
}
