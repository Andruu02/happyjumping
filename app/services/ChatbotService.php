<?php
require_once '../app/core/Model.php';

class ChatbotService extends Model {

    public function procesarPregunta($pregunta) {
        // =====================================================================
        // 1. OBTENER Y LIMPIAR LA API KEY 
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
        Tablas disponibles en MySQL:
        1. usuarios (id_usuario, correo, rol, fecha_registro, nombre, is_verificado)
        2. reservas (id_reserva, id_usuario, id_paquete, id_horario, cantidad_personas, extra_pintura, extra_destruccion, fecha_reserva, estado, observaciones, nombre_cumpleanero, edad_cumpleanero)
        3. pagos (id_pago, id_reserva, monto, fecha_pago, estado)
        4. paquetes (id_paquete, nombre, descripcion, precio_semana, precio_fin_semana, duracion, estado)
        5. horarios_disponibles (id_horario, fecha, hora_inicio, hora_fin, disponible)
        6. promociones (id_promocion, nombre, puntos_necesarios)
        7. vista_puntos_usuario (id_usuario, nombre, correo, puntos_totales, partidas_jugadas, mejor_puntaje)
        ";

        // =====================================================================
        // 3. PRIMER PROMPT: ENRUTAMIENTO Y TEXT-TO-SQL
        // =====================================================================
        $prompt1 = "Eres el asistente administrativo experto de Happy Jumping.
        Esquema de BD: $esquema
        Pregunta del administrador: '$pregunta'
        
        REGLAS:
        - Si es un saludo, despedida o pregunta general, responde en este JSON: {\"tipo\":\"chat\", \"respuesta\":\"tu respuesta amigable\"}
        - Si la pregunta requiere buscar datos en la base de datos, genera una consulta SQL válida (SOLO usa SELECT, usa JOIN si es necesario, limita a 10 resultados) en este JSON: {\"tipo\":\"sql\", \"query\":\"SELECT ...\"}
        
        OBLIGATORIO: Devuelve SOLO el JSON crudo.";

        // Llamamos a Gemini
        $respuestaCruda = $this->llamarGemini($prompt1, $apiKey);
        
        // --- LA SOLUCIÓN: EXTRAER ESTRICTAMENTE EL JSON ---
        $json = null;
        $inicio = strpos($respuestaCruda, '{');
        $fin = strrpos($respuestaCruda, '}');
        
        if ($inicio !== false && $fin !== false) {
            $jsonString = substr($respuestaCruda, $inicio, $fin - $inicio + 1);
            $json = json_decode($jsonString, true);
        }

        if (!$json || !isset($json['tipo'])) {
            // Si quieres ver qué dijo Gemini que rompió el JSON, temporalmente puedes cambiar el mensaje a: return ["respuesta" => "Debug: " . $respuestaCruda];
            return ["respuesta" => "Lo siento, tuve un problema analizando tu consulta. Intentemos de nuevo."];
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
            return ["respuesta" => "Por seguridad, solo estoy autorizado a realizar consultas de lectura en la base de datos."];
        }

        try {
            $this->query($sql);
            $resultados = $this->resultSet();
            
            $datosBD = json_encode($resultados);

            $prompt2 = "Pregunta del administrador: '$pregunta'.
            Resultado de la base de datos: $datosBD.
            
            Instrucción: Redacta una respuesta natural, profesional y útil para el administrador.
            - Si el resultado está vacío ([]), di amablemente que no se encontraron registros.
            - NUNCA menciones la palabra 'JSON', 'array', 'SQL' ni 'base de datos'.
            - Interpreta los datos y dalos de forma conversacional.";

            $respuestaFinal = $this->llamarGemini($prompt2, $apiKey);

            $this->guardarHistorial($pregunta, $respuestaFinal, $sql);

            return ["respuesta" => $respuestaFinal];

        } catch (Exception $e) {
            return ["respuesta" => "Ocurrió un error al consultar los datos en el sistema: " . $e->getMessage()];
        }
    }

    /**
     * =====================================================================
     * HELPER: ENVIAR PETICIÓN cURL A GEMINI
     * =====================================================================
     */
    private function llamarGemini($prompt, $apiKey) {
        // NOTA: Se actualizó el modelo a "gemini-1.5-flash"
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;
        
        $data = [
            "contents" => [["parts" => [["text" => $prompt]]]]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return "Error de conexión cURL: " . $error;
        }

        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            return "Error de la API: " . $result['error']['message'];
        }

        return $result['candidates'][0]['content']['parts'][0]['text'] ?? "Error en la IA.";
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