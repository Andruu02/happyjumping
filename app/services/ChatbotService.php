<?php
require_once '../app/core/Model.php';

class ChatbotService extends Model {
    
    public function procesarPregunta($pregunta) {
        // 1. Obtener clave del .env (Asegúrate de que tu sistema cargue variables de entorno)
        $apiKey = getenv('GEMINI_API_KEY');
        
        if (!$apiKey) {
            return ["respuesta" => "Error: No se encontró la API Key."];
        }

        // 2. Obtener contexto de la BD usando tus métodos de Model
        $datos = $this->obtenerDatosDashboard();

        // 3. IA Lógica
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$apiKey";
        $instrucciones = "Eres asistente de Happy Jumping. Datos actuales: Reservas pendientes: {$datos['pendientes']}, Ingresos mes: S/ {$datos['ingresos']}. Pregunta: '$pregunta'";

        $data = ["contents" => [["parts" => [["text" => $instrucciones]]]]];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        $respuestaIA = $result['candidates'][0]['content']['parts'][0]['text'] ?? "No pude conectar.";

        // 4. Guardar historial usando el método prepare que ya tienes en Model
        $this->query("INSERT INTO chatbot_historial (pregunta, respuesta) VALUES (:pregunta, :respuesta)");
        $this->bind(':pregunta', $pregunta);
        $this->bind(':respuesta', $respuestaIA);
        $this->execute();

        return ["respuesta" => $respuestaIA];
    }

    private function obtenerDatosDashboard() {
        // Usamos tus métodos 'query' y 'single' del Model
        $this->query("SELECT COUNT(*) as total FROM reservas WHERE estado = 'pendiente'");
        $pendientes = $this->single()->total;

        $this->query("SELECT SUM(monto) as total FROM pagos WHERE estado = 'confirmada' AND MONTH(fecha_pago) = MONTH(CURDATE())");
        $ingresos = $this->single()->total ?? '0.00';

        return ['pendientes' => $pendientes, 'ingresos' => $ingresos];
    }
}