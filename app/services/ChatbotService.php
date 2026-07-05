<?php
require_once '../app/core/Model.php';

class ChatbotService extends Model {

    public function procesarPregunta($pregunta) {
        $apiKey = $this->obtenerApiKey();
        if (isset($apiKey['error'])) return $apiKey;
        $key = $apiKey['key'];

        // 1. Esquema detallado + REGLAS DE NEGOCIO para que la IA no se equivoque
        $esquema = "
TABLAS Y RELACIONES:
- usuarios(id_usuario, nombre, correo)
- reservas(id_reserva, id_usuario, id_paquete, estado, fecha_reserva, nombre_cumpleanero)
  * estado puede ser: 'pendiente', 'confirmado', 'cancelado'
- pagos(id_pago, id_reserva, monto, estado)
  * monto = dinero asociado a esa reserva (relacionar con reservas.id_reserva)
- paquetes(id_paquete, nombre, precio_semana, precio_fin_semana)

REGLAS DE NEGOCIO OBLIGATORIAS (aplícalas SIEMPRE, aunque el admin no las mencione explícitamente):
1. Una reserva solo representa dinero real o una venta efectiva cuando reservas.estado = 'confirmado'. El admin la confirma manualmente al verificar el comprobante de pago (Yape/Plin). Las reservas 'pendiente' o 'cancelado' NUNCA deben contarse como ingresos ni como ventas.
2. Para calcular INGRESOS, DINERO RECAUDADO, VENTAS EN SOLES o cualquier pregunta relacionada con dinero de la empresa: usa SUM(pagos.monto), haciendo INNER JOIN entre pagos y reservas por id_reserva, y SIEMPRE filtra WHERE reservas.estado = 'confirmado'.
3. Para saber qué cliente/usuario ha generado más dinero ('mejor cliente', 'quién más gastó', 'cliente más rentable'): une usuarios + reservas + pagos, filtra reservas.estado = 'confirmado', agrupa por usuario (GROUP BY usuarios.id_usuario), suma pagos.monto, ordena descendente (ORDER BY ... DESC) y usa LIMIT según lo pedido (LIMIT 1 si preguntan por 'el' cliente top).
4. Para PAQUETE MÁS VENDIDO, PAQUETE MÁS POPULAR, PAQUETE TOP o cuántas veces se reservó un paquete: une paquetes + reservas por id_paquete, filtra reservas.estado = 'confirmado' (una reserva pendiente o cancelada no es una venta real), agrupa por paquete (GROUP BY paquetes.id_paquete, paquetes.nombre), cuenta las reservas con COUNT(*), ordena descendente y usa LIMIT según lo pedido (LIMIT 1 si preguntan por 'el' paquete top/más vendido).
5. Si preguntan por reservas 'pendientes' o 'canceladas' explícitamente, ahí sí filtra por ese estado en vez de 'confirmado'.
6. Si la pregunta no especifica estado y no es sobre dinero ni ventas (ej. '¿cuántas reservas hay hoy?'), no apliques el filtro de estado a menos que sea razonable asumir que se refiere a reservas activas/confirmadas — en caso de duda, incluye todas y aclara los estados en la respuesta final.

EJEMPLOS DE CONSULTAS CORRECTAS:
- Pregunta: '¿Cuánto hemos recaudado en total?'
  SQL: SELECT SUM(p.monto) AS total FROM pagos p INNER JOIN reservas r ON r.id_reserva = p.id_reserva WHERE r.estado = 'confirmado'

- Pregunta: '¿Quién es el cliente que más dinero nos ha dado?'
  SQL: SELECT u.nombre, SUM(p.monto) AS total_gastado FROM usuarios u INNER JOIN reservas r ON r.id_usuario = u.id_usuario INNER JOIN pagos p ON p.id_reserva = r.id_reserva WHERE r.estado = 'confirmado' GROUP BY u.id_usuario, u.nombre ORDER BY total_gastado DESC LIMIT 1

- Pregunta: '¿Cuál es el paquete más vendido?'
  SQL: SELECT pa.nombre, COUNT(*) AS veces_reservado FROM paquetes pa INNER JOIN reservas r ON r.id_paquete = pa.id_paquete WHERE r.estado = 'confirmado' GROUP BY pa.id_paquete, pa.nombre ORDER BY veces_reservado DESC LIMIT 1
";

        // 2. Prompt con configuración de respuesta JSON (y advertencia de seguridad)
        $prompt = "Eres el asistente analista de Happy Jumping. Tu única función es consultar datos. $esquema
        Pregunta del admin: '$pregunta'.
        Si la pregunta requiere base de datos, responde SOLO con este JSON: {\"tipo\":\"sql\", \"query\":\"SELECT...\"}. NUNCA generes comandos de modificación. Aplica SIEMPRE las reglas de negocio indicadas arriba antes de construir el SQL.
        Si es un saludo o conversación, responde SOLO con este JSON: {\"tipo\":\"chat\", \"respuesta\":\"...\"}.
        No incluyas explicaciones previas, solo el JSON crudo.";

        $data = $this->armarPayload($prompt, true); // true = forzar JSON

        $respuestaCruda = $this->llamarApi($key, $data);
        $json = json_decode($this->extraerJson($respuestaCruda), true);

        if (!$json || !isset($json['tipo'])) {
            // Si hay un error de la API (ej. clave inválida), lo mostramos directamente
            if (isset($json['error'])) return ["respuesta" => $respuestaCruda];

            // Dejamos rastro en el log del servidor para poder diagnosticar qué mandó Groq
            error_log("ChatbotService: respuesta no parseable de Groq -> " . $respuestaCruda);

            // --- MODO DEBUG TEMPORAL ---
            // Muestra la respuesta cruda de Groq directo en el chat para poder ver
            // qué está devolviendo sin tener que ir a buscar logs del servidor.
            // Quita este bloque (y deja solo el return de abajo) una vez resuelto.
            return ["respuesta" => "[DEBUG] Groq respondió esto (no es JSON válido): " . $respuestaCruda];
            // return ["respuesta" => "Lo siento, tuve un problema analizando tu solicitud."];
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

            // Pedimos a la IA que traduzca el JSON crudo a una respuesta humana
            $promptTrad = "Pregunta del admin: '$pregunta'. Datos crudos de la BD: $datosBD.
            Instrucción: Redacta una respuesta natural, profesional y clara dando esta información. Si la pregunta era sobre dinero/ingresos o ventas/paquete más vendido, aclara que corresponde solo a reservas confirmadas. NO menciones la palabra JSON, SQL ni array.";

            $data = $this->armarPayload($promptTrad, false);
            $respuestaFinal = $this->llamarApi($apiKey, $data);

            $this->guardarHistorial($pregunta, $respuestaFinal, $sql);

            return ["respuesta" => $respuestaFinal];

        } catch (Exception $e) {
            return ["respuesta" => "Error al consultar la base de datos: " . $e->getMessage()];
        }
    }

    private function llamarApi($key, $data) {
        // Groq usa un endpoint estilo OpenAI: /openai/v1/chat/completions
        $url = "https://api.groq.com/openai/v1/chat/completions";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $key
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Groq suele responder muy rápido; 30s es holgado

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

        return $decoded['choices'][0]['message']['content'] ?? '{"error": "Respuesta vacía de Groq"}';
    }

    /**
     * Extrae el primer bloque JSON válido de un texto, sin importar si la IA
     * lo envolvió en fences de markdown (```json ... ```) o le agregó
     * explicaciones antes/después a pesar de las instrucciones del prompt.
     */
    private function extraerJson($texto) {
        $texto = trim($texto);

        // Caso simple: ya es JSON puro
        if (json_decode($texto, true) !== null) {
            return $texto;
        }

        // Quitar fences de markdown si existen
        $sinFences = preg_replace('/^```(json)?\s*/i', '', $texto);
        $sinFences = preg_replace('/\s*```$/', '', $sinFences);
        $sinFences = trim($sinFences);
        if (json_decode($sinFences, true) !== null) {
            return $sinFences;
        }

        // Último recurso: agarrar desde la primera { hasta la última }
        $inicio = strpos($texto, '{');
        $fin = strrpos($texto, '}');
        if ($inicio !== false && $fin !== false && $fin > $inicio) {
            $candidato = substr($texto, $inicio, $fin - $inicio + 1);
            if (json_decode($candidato, true) !== null) {
                return $candidato;
            }
        }

        // No se pudo extraer nada parseable; se devuelve tal cual para que
        // el error se registre con el texto original.
        return $texto;
    }

    private function armarPayload($prompt, $jsonMode = false) {
        $data = [
            "model" => "llama-3.3-70b-versatile",
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
        $key = trim(getenv('GROQ_API_KEY') ?: '', " \t\n\r\0\x0B\"'");
        if (empty($key) && file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $l) {
                if (strpos(trim($l), 'GROQ_API_KEY=') === 0) {
                    $key = trim(str_replace('GROQ_API_KEY=', '', trim($l)), " \t\n\r\0\x0B\"'");
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
        $this->bind(':c', $sql ? json_encode(["query" => $sql]) : json_encode(["fuente" => "Groq"]));

        try {
            $this->execute();
        } catch (Exception $e) {
            // Evitamos que un error al guardar el historial rompa la respuesta principal
            error_log("Error guardando historial del chatbot: " . $e->getMessage());
        }
    }
}