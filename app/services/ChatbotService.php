<?php
require_once '../app/core/Model.php';

class ChatbotService extends Model {

    public function procesarPregunta($pregunta) {
        // 1. OBTENCIÓN DE API KEY
        $envPath = dirname(__DIR__, 2) . '/.env'; 
        $apiKey = '';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), 'GEMINI_API_KEY=') === 0) {
                    $valor = str_replace('GEMINI_API_KEY=', '', trim($line));
                    $apiKey = trim($valor, " \t\n\r\0\x0B\"'");
                    break;
                }
            }
        }
        if (empty($apiKey)) $apiKey = trim(getenv('GEMINI_API_KEY'), " \t\n\r\0\x0B\"'");
        if (empty($apiKey)) return ["error" => "La API Key está vacía."];

        // 2. ESQUEMA DE BD (Para que la IA sepa qué consultar)
        $esquema = "Tablas: usuarios(id_usuario, nombre), reservas(id_reserva, id_usuario, id_paquete, estado, fecha_reserva), pagos(id_pago, id_reserva, monto, estado), paquetes(id_paquete, nombre, precio_semana).";

        // 3. PROMPT ESTRUCTURADO
        $instrucciones = "Eres el asistente administrativo de Happy Jumping. Esquema: $esquema. Pregunta: '$pregunta'. Devuelve JSON: si es chat -> {\"tipo\":\"chat\", \"respuesta\":\"...\"}, si es consulta BD -> {\"tipo\":\"sql\", \"query\":\"SELECT ...\"}";

        $data = [
            "contents" => [["parts" => [["text" => $instrucciones]]]],
            "generationConfig" => [
                "response_mime_type" => "application/json"
            ]
        ];

        // 4. LLAMADA A LA API
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;
        $response = $this->llamarApi($url, $data);

        if (isset($response['error'])) return ["error" => $response['error']];

        $respuestaIA = json_decode($response, true);
        $contenido = json_decode($respuestaIA['candidates'][0]['content']['parts'][0]['text'], true);

        // 5. DECISIÓN: ¿SQL O CHAT?
        if ($contenido['tipo'] === 'chat') {
            $this->guardarHistorial($pregunta, $contenido['respuesta'], null);
            return ["respuesta" => $contenido['respuesta']];
        } else {
            return $this->ejecutarYTraducirSQL($contenido['query'], $pregunta, $apiKey);
        }
    }

    private function ejecutarYTraducirSQL($sql, $pregunta, $apiKey) {
        if (stripos(trim($sql), 'SELECT') !== 0) return ["respuesta" => "Solo consultas de lectura."];

        try {
            $this->query($sql);
            $resultados = json_encode($this->resultSet());
            
            $promptTrad = "Pregunta: '$pregunta'. Datos BD: $resultados. Responde como asistente administrativo de forma natural.";
            $data = ["contents" => [["parts" => [["text" => $promptTrad]]]]];
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;
            
            $res = json_decode($this->llamarApi($url, $data), true);
            $respuestaFinal = $res['candidates'][0]['content']['parts'][0]['text'];

            $this->guardarHistorial($pregunta, $respuestaFinal, $sql);
            return ["respuesta" => $respuestaFinal];
        } catch (Exception $e) {
            return ["respuesta" => "Error SQL: " . $e->getMessage()];
        }
    }

    private function llamarApi($url, $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $resp = curl_exec($ch);
        curl_close($ch);
        return $resp;
    }

    private function guardarHistorial($pregunta, $respuesta, $contexto) {
        $this->query("INSERT INTO `chatbot_historial` (`pregunta`, `respuesta`, `contexto_sql`) VALUES (:pregunta, :respuesta, :contexto)");
        $this->bind(':pregunta', $pregunta);
        $this->bind(':respuesta', $respuesta);
        $this->bind(':contexto', $contexto ? json_encode(["query" => $contexto]) : json_encode(["fuente" => "Gemini"]));
        $this->execute();
    }
}