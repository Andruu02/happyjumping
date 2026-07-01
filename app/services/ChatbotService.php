<?php
require_once '../app/core/Model.php';

class ChatbotService extends Model {

    public function procesarPregunta($pregunta) {
        // 1. Obtener API Key (Mantenemos tu lógica)
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

        // 2. Definir instrucciones (Evitamos el error de variable indefinida)
        $instrucciones = "Eres el asistente administrativo experto de Happy Jumping. 
        Pregunta del administrador: " . $pregunta;

        // 3. Preparar la estructura de datos correcta para la API v1beta
        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $instrucciones]
                    ]
                ]
            ]
        ];

        // 4. Llamada cURL con el modelo correcto
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) return ["error" => "Error cURL: " . $curlError];

        $result = json_decode($response, true);
        
        // Debug por si la API falla
        if (isset($result['error'])) {
            return ["error" => "Error API: " . $result['error']['message']];
        }

        $respuestaIA = $result['candidates'][0]['content']['parts'][0]['text'] ?? "No obtuve respuesta.";

        // 5. Guardar historial
        $this->guardarHistorial($pregunta, $respuestaIA, "Gemini Flash");

        return ["respuesta" => $respuestaIA];
    }

    private function guardarHistorial($pregunta, $respuesta, $contexto) {
        $this->query("INSERT INTO chatbot_historial (pregunta, respuesta, contexto_sql) VALUES (:pregunta, :respuesta, :contexto)");
        $this->bind(':pregunta', $pregunta);
        $this->bind(':respuesta', $respuesta);
        $this->bind(':contexto', json_encode(["fuente" => $contexto]));
        $this->execute();
    }
}