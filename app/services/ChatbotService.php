<?php
require_once '../app/core/Model.php';

class ChatbotService extends Model {
    
    public function procesarPregunta($pregunta) {
        // Obtenemos la clave desde el entorno, no quemada en el código
        $apiKey = getenv('GEMINI_API_KEY');

        // TEST RÁPIDO: Quita el comentario a la siguiente línea para probar
        // die("La clave leída es: " . $apiKey); 

        if (empty($apiKey)) {
            return ["error" => "La clave API no se cargó. Revisa tu archivo .env"];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$apiKey";

        // 2. Obtenemos datos clave para que la IA tenga contexto real de tu BD
        $datos = $this->obtenerDatosDashboard();

        // 3. Prompt (Instrucciones) que le damos a la IA
        $instrucciones = "Eres el asistente administrativo de Happy Jumping Peru. 
        Responde basándote estrictamente en estos datos actuales:
        - Reservas pendientes hoy: {$datos['pendientes']}
        - Ingresos confirmados este mes: S/ {$datos['ingresos']}
        
        Pregunta del admin: '$pregunta'. 
        Si la pregunta requiere un dato que no está aquí, intenta responder de forma amable como asistente administrativo.";

        // 4. Llamada a la API
        $data = ["contents" => [["parts" => [["text" => $instrucciones]]]]];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ["error" => "No se pudo conectar con el servicio de IA."];
        }

        $result = json_decode($response, true);
        $respuestaIA = $result['candidates'][0]['content']['parts'][0]['text'] ?? "No pude generar una respuesta.";

        // 5. Guardar en historial
        $this->guardarHistorial($pregunta, $respuestaIA, json_encode(["fuente" => "Gemini AI"]));

        return ["respuesta" => $respuestaIA];
    }

    // Método privado para traer datos de la BD y alimentar a la IA
    private function obtenerDatosDashboard() {
        // Reservas pendientes
        $stmt1 = $this->db->query("SELECT COUNT(*) as total FROM reservas WHERE estado = 'pendiente'");
        $pendientes = $stmt1->fetch(PDO::FETCH_ASSOC)['total'];

        // Ingresos del mes
        $stmt2 = $this->db->query("SELECT SUM(monto) as total FROM pagos WHERE estado = 'confirmada' AND MONTH(fecha_pago) = MONTH(CURDATE())");
        $ingresos = $stmt2->fetch(PDO::FETCH_ASSOC)['total'] ?? '0.00';

        return ['pendientes' => $pendientes, 'ingresos' => $ingresos];
    }

    private function guardarHistorial($pregunta, $respuesta, $contexto) {
        $sql = "INSERT INTO chatbot_historial (pregunta, respuesta, contexto_sql) VALUES (:pregunta, :respuesta, :contexto)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':pregunta' => $pregunta,
            ':respuesta' => $respuesta,
            ':contexto' => $contexto
        ]);
    }
}