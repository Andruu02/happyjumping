<?php
require_once '../app/core/Model.php';

class ChatbotService extends Model {

    public function procesarPregunta($pregunta) {
        $apiKey = $this->obtenerApiKey();
        if (isset($apiKey['error'])) return $apiKey;
        $key = $apiKey['key'];

        // 1. Esquema detallado para que la IA entienda tus tablas
        $esquema = "Tablas: 
        usuarios(id_usuario, nombre, correo), 
        reservas(id_reserva, id_usuario, id_paquete, estado, fecha_reserva, nombre_cumpleanero), 
        pagos(id_pago, id_reserva, monto, estado), 
        paquetes(id_paquete, nombre, precio_semana, precio_fin_semana).";

        // 2. Prompt con configuración de respuesta JSON
        $prompt = "Eres el asistente administrativo de Happy Jumping. Esquema: $esquema. Pregunta: '$pregunta'. 
        Si la pregunta requiere base de datos, responde SOLO con este JSON: {\"tipo\":\"sql\", \"query\":\"SELECT...\"}. 
        Si es un saludo o conversación, responde SOLO con este JSON: {\"tipo\":\"chat\", \"respuesta\":\"...\"}.
        No incluyas explicaciones previas, solo el JSON.";

        $data = [
            "contents" => [["parts" => [["text" => $prompt]]]],
            "generationConfig" => ["response_mime_type" => "application/json"]
        ];

        $respuestaCruda = $this->llamarApi($key, $data);
        $json = json_decode($respuestaCruda, true);

        if (!$json || !isset($json['tipo'])) {
            return ["respuesta" => "Lo siento, tuve un problema analizando tu solicitud."];
        }

        if ($json['tipo'] === 'chat') {
            $this->guardarHistorial($pregunta, $json['respuesta'], null);
            return ["respuesta" => $json['respuesta']];
        } else {
            return $this->ejecutarYTraducirSQL($json['query'], $pregunta, $key);
        }
    }

    private function ejecutarYTraducirSQL($sql, $pregunta, $apiKey) {
        if (stripos(trim($sql), 'SELECT') !== 0) {
            return ["respuesta" => "Solo puedo realizar consultas de lectura."];
        }

        try {
            $this->query($sql);
            $datosBD = json_encode($this->resultSet());
            
            $promptTrad = "Pregunta: '$pregunta'. Datos de BD: $datosBD. Responde al administrador de forma natural y profesional.";
            $data = ["contents" => [["parts" => [["text" => $promptTrad]]]]];
            
            $respuestaFinal = $this->llamarApi($apiKey, $data);
            $this->guardarHistorial($pregunta, $respuestaFinal, $sql);
            
            return ["respuesta" => $respuestaFinal];
        } catch (Exception $e) {
            return ["respuesta" => "Error de consulta: " . $e->getMessage()];
        }
    }

    private function llamarApi($key, $data) {
        // Volvemos al modelo exacto que SÍ te funcionó hace unos momentos
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $key;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $resp = curl_exec($ch);
        
        // --- DEPURACIÓN: Capturar errores de cURL ---
        if(curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return '{"error": "cURL Error: ' . $error . '"}';
        }
        
        curl_close($ch);
        
        // --- DEPURACIÓN: Ver si la API devolvió un error JSON ---
        $decoded = json_decode($resp, true);
        if (isset($decoded['error'])) {
            return '{"error": "API Error: ' . $decoded['error']['message'] . '"}';
        }
        
        return $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '{"error": "Respuesta vacía de Gemini"}';
    }

    private function obtenerApiKey() {
        $envPath = dirname(__DIR__, 2) . '/.env';
        $key = trim(getenv('GEMINI_API_KEY') ?: '', " \t\n\r\0\x0B\"'");
        if (empty($key) && file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $l) {
                if (strpos(trim($l), 'GEMINI_API_KEY=') === 0) {
                    $key = trim(str_replace('GEMINI_API_KEY=', '', trim($l)), " \t\n\r\0\x0B\"'");
                    break;
                }
            }
        }
        return empty($key) ? ["error" => "No API Key"] : ["key" => $key];
    }

    private function guardarHistorial($pregunta, $respuesta, $sql) {
        $this->query("INSERT INTO chatbot_historial (pregunta, respuesta, contexto_sql) VALUES (:p, :r, :c)");
        $this->bind(':p', $pregunta);
        $this->bind(':r', $respuesta);
        $this->bind(':c', $sql ? json_encode(["query" => $sql]) : json_encode(["fuente" => "Gemini"]));
        $this->execute();
    }
}