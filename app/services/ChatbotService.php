<?php
require_once '../app/core/Model.php';

class ChatbotService extends Model {

    public function procesarPregunta($pregunta) {
        // =====================================================================
        // 1. OBTENER API KEY
        // =====================================================================
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

        if (empty($apiKey)) {
            $apiKey = trim(getenv('GEMINI_API_KEY'), " \t\n\r\0\x0B\"'");
        }

        if (empty($apiKey)) {
            return ["error" => "La API Key está vacía. Verifica tu archivo .env"];
        }

        // =====================================================================
        // 2. DEFINIR EL ESQUEMA DE TU BASE DE DATOS
        // =====================================================================
        $esquema = "
        Tablas en MySQL de Happy Jumping:
        1. usuarios (id_usuario, correo, rol, fecha_registro, nombre, is_verificado)
        2. reservas (id_reserva, id_usuario, id_paquete, id_horario, cantidad_personas, extra_pintura, extra_destruccion, fecha_reserva, estado, observaciones, nombre_cumpleanero, edad_cumpleanero)
        3. pagos (id_pago, id_reserva, monto, fecha_pago, estado)
        4. paquetes (id_paquete, nombre, descripcion, precio_semana, precio_fin_semana, duracion, estado)
        5. horarios_disponibles (id_horario, fecha, hora_inicio, hora_fin, disponible)
        6. promociones (id_promocion, nombre, puntos_necesarios)
        ";

        // =====================================================================
        // 3. PRIMER PROMPT: ENRUTAMIENTO Y TEXT-TO-SQL
        // =====================================================================
        $prompt1 = "Eres el asistente administrativo de Happy Jumping.
        Esquema de la base de datos: $esquema
        Pregunta del administrador: '$pregunta'
        
        DEBES devolver un objeto JSON con esta estructura exacta:
        - Si es un saludo o no requiere base de datos: {\"tipo\":\"chat\", \"respuesta\":\"tu respuesta amigable\"}
        - Si requiere datos: {\"tipo\":\"sql\", \"query\":\"SELECT ... LIMIT 10\"}";

        // CAMBIO AQUÍ: Usamos el modelo exacto que te funcionaba
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey;
        
        $data = [
            "contents" => [["parts" => [["text" => $prompt1]]]],
            "generationConfig" => [
                "response_mime_type" => "application/json"
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) return ["error" => "Error cURL: " . $error];

        $result = json_decode($response, true);
        if (isset($result['error'])) return ["error" => "Error API: " . $result['error']['message']];

        $respuestaCruda = $result['candidates'][0]['content']['parts'][0]['text'] ?? "";
        $json = json_decode(trim($respuestaCruda), true);

        if (!$json || !isset($json['tipo'])) {
            $this->guardarHistorial($pregunta, $respuestaCruda, null);
            return ["respuesta" => $respuestaCruda];
        }

        // =====================================================================
        // 4. LÓGICA DE DECISIÓN
        // =====================================================================
        if ($json['tipo'] === 'chat') {
            $this->guardarHistorial($pregunta, $json['respuesta'], null);
            return ["respuesta" => $json['respuesta']];
            
        } elseif ($json['tipo'] === 'sql') {
            return $this->ejecutarYTraducirSQL($json['query'], $pregunta, $apiKey);
        }

        return ["respuesta" => "No comprendí la solicitud."];
    }

    /**
     * =====================================================================
     * FASE 2: EJECUCIÓN Y TRADUCCIÓN HUMANA
     * =====================================================================
     */
    private function ejecutarYTraducirSQL($sql, $pregunta, $apiKey) {
        if (stripos(trim($sql), 'SELECT') !== 0) {
            return ["respuesta" => "Por seguridad, solo realizo consultas de lectura (SELECT)."];
        }

        try {
            $this->query($sql);
            $resultados = $this->resultSet();
            $datosBD = json_encode($resultados);

            $prompt2 = "Pregunta del administrador: '$pregunta'.
            Resultado BD: $datosBD.
            Instrucción: Redacta una respuesta natural para el administrador. NO menciones 'JSON', 'array' ni 'SQL'. Si está vacío, dilo amablemente.";

            // CAMBIO AQUÍ TAMBIÉN: Usamos el modelo exacto que te funcionaba
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey;
            
            $data = ["contents" => [["parts" => [["text" => $prompt2]]]]];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);
            $respuestaFinal = $result['candidates'][0]['content']['parts'][0]['text'] ?? "Error leyendo los datos.";

            $this->guardarHistorial($pregunta, $respuestaFinal, $sql);

            return ["respuesta" => $respuestaFinal];

        } catch (Exception $e) {
            return ["respuesta" => "Error de SQL: " . $e->getMessage()];
        }
    }

    /**
     * =====================================================================
     * HELPER: GUARDAR HISTORIAL
     * =====================================================================
     */
    private function guardarHistorial($pregunta, $respuesta, $contexto_sql) {
        $this->query("INSERT INTO chatbot_historial (pregunta, respuesta, contexto_sql) VALUES (:pregunta, :respuesta, :contexto)");
        $this->bind(':pregunta', $pregunta);
        $this->bind(':respuesta', $respuesta);
        
        $jsonContexto = $contexto_sql ? json_encode(["query" => $contexto_sql]) : json_encode(["fuente" => "Gemini Chat"]);
        $this->bind(':contexto', $jsonContexto);
        
        $this->execute();
    }
}