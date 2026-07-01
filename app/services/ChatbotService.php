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

        // 2. Prompt con configuración de respuesta JSON (y advertencia de seguridad)
        $prompt = "Eres el asistente analista de Happy Jumping. Tu única función es consultar datos. Esquema: $esquema. Pregunta: '$pregunta'. 
        Si la pregunta requiere base de datos, responde SOLO con este JSON: {\"tipo\":\"sql\", \"query\":\"SELECT...\"}. NUNCA generes comandos de modificación.
        Si es un saludo o conversación, responde SOLO con este JSON: {\"tipo\":\"chat\", \"respuesta\":\"...\"}.
        No incluyas explicaciones previas, solo el JSON crudo.";

        $data = $this->armarPayload($prompt, true); // true = forzar JSON

        $respuestaCruda = $this->llamarApi($key, $data);
        $json = json_decode($this->limpiarJson($respuestaCruda), true);

        if (!$json || !isset($json['tipo'])) {
            // Si hay un error de la API (ej. clave inválida), lo mostramos directamente
            if (isset($json['error'])) return ["respuesta" => $respuestaCruda];

            // Dejamos rastro en el log del servidor para poder diagnosticar qué mandó Grok
            error_log("ChatbotService: respuesta no parseable de Grok -> " . $respuestaCruda);

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
        // --- BLINDAJE DE SEGURIDAD EXTREMO ---
        $sqlUpper = strtoupper(trim($sql));
        
        // 1. Debe empezar obligatoriamente con SELECT
        if (strpos($sqlUpper, 'SELECT') !== 0) {
            return ["respuesta" => "Acción denegada: Solo estoy autorizado para realizar consultas de lectura (SELECT)."];
        }

        // 2. No debe contener ninguna palabra destructiva en ninguna parte de la consulta
        $prohibidas = ['DELETE', 'UPDATE', 'DROP', 'INSERT', 'ALTER', 'CREATE', 'GRANT', 'TRUNCATE', 'REPLACE'];
        foreach ($prohibidas as $palabra) {
            // Usamos \b para asegurar que es la palabra exacta y no parte de un nombre (ej. tabla "drop_points")
            if (preg_match('/\b' . $palabra . '\b/', $sqlUpper)) {
                return ["respuesta" => "Acción denegada: Se detectó un comando no permitido por seguridad ($palabra)."];
            }
        }
        // --- FIN DEL BLINDAJE ---

        try {
            // Ejecutamos la consulta validada
            $this->query($sql);
            $datosBD = json_encode($this->resultSet());
            
            // Si no hay datos, le ahorramos trabajo a la IA
            if ($datosBD === "[]" || empty($this->resultSet())) {
                $respuestaVacia = "No encontré ningún registro en la base de datos para esa consulta.";
                $this->guardarHistorial($pregunta, $respuestaVacia, $sql);
                return ["respuesta" => $respuestaVacia];
            }
            
            // Pedimos a Gemini que traduzca el JSON crudo a una respuesta humana
            $promptTrad = "Pregunta del admin: '$pregunta'. Datos crudos de la BD: $datosBD. 
            Instrucción: Redacta una respuesta natural, profesional y clara dando esta información. NO menciones la palabra JSON, SQL ni array.";
            
            $data = $this->armarPayload($promptTrad, false);
            $respuestaFinal = $this->llamarApi($apiKey, $data);
            
            $this->guardarHistorial($pregunta, $respuestaFinal, $sql);
            
            return ["respuesta" => $respuestaFinal];
            
        } catch (Exception $e) {
            return ["respuesta" => "Error al consultar la base de datos: " . $e->getMessage()];
        }
    }

    private function llamarApi($key, $data) {
        // xAI (Grok) usa un endpoint estilo OpenAI: /v1/chat/completions
        $url = "https://api.x.ai/v1/chat/completions";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $key
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Grok suele responder rápido; 30s es holgado

        $resp = curl_exec($ch);

        // Capturar errores de cURL
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return '{"error": "cURL Error: ' . $error . '"}';
        }

        curl_close($ch);

        $decoded = json_decode($resp, true);
        if (isset($decoded['error'])) {
            $msg = is_array($decoded['error']) ? ($decoded['error']['message'] ?? json_encode($decoded['error'])) : $decoded['error'];
            return '{"error": "API Error: ' . addslashes($msg) . '"}';
        }

        return $decoded['choices'][0]['message']['content'] ?? '{"error": "Respuesta vacía de Grok"}';
    }

    /**
     * A veces los modelos (incluido Grok) envuelven el JSON en fences de markdown
     * (```json ... ```) aunque se pida response_format json_object. Esto lo limpia
     * antes de json_decode().
     */
    private function limpiarJson($texto) {
        $texto = trim($texto);
        // Quita ```json al inicio y ``` al final (o solo ``` en cualquiera de los dos)
        $texto = preg_replace('/^```(json)?\s*/i', '', $texto);
        $texto = preg_replace('/\s*```$/', '', $texto);
        return trim($texto);
    }

    /**
     * Construye el payload estilo OpenAI/xAI a partir de un prompt de texto plano.
     * $jsonMode = true fuerza response_format json_object (para el paso de clasificación).
     */
    private function armarPayload($prompt, $jsonMode = false) {
        $data = [
            "model" => "grok-4.3",
            "messages" => [
                ["role" => "user", "content" => $prompt]
            ],
            "temperature" => 0.3
        ];
        if ($jsonMode) {
            $data["response_format"] = ["type" => "json_object"];
        }
        return $data;
    }

    private function obtenerApiKey() {
        $envPath = dirname(__DIR__, 2) . '/.env';
        $key = trim(getenv('XAI_API_KEY') ?: '', " \t\n\r\0\x0B\"'");
        if (empty($key) && file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $l) {
                if (strpos(trim($l), 'XAI_API_KEY=') === 0) {
                    $key = trim(str_replace('XAI_API_KEY=', '', trim($l)), " \t\n\r\0\x0B\"'");
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
        $this->bind(':c', $sql ? json_encode(["query" => $sql]) : json_encode(["fuente" => "Grok"]));
        
        try {
            $this->execute();
        } catch (Exception $e) {
            // Evitamos que un error al guardar el historial rompa la respuesta principal
            error_log("Error guardando historial del chatbot: " . $e->getMessage());
        }
    }
}