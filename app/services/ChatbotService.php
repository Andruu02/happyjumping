<?php
require_once '../app/core/Model.php';

class ChatbotService extends Model {

    public function procesarPregunta($pregunta) {
        // 1. OBTENER LA API KEY DESDE EL .ENV (Ruta dinámica y segura)
        $envPath = dirname(__DIR__, 2) . '/.env'; 
        $apiKey = null;

        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), 'GEMINI_API_KEY=') === 0) {
                    $apiKey = str_replace('GEMINI_API_KEY=', '', trim($line));
                    // Limpiar comillas si es que las pusiste en el .env
                    $apiKey = trim($apiKey, "\"'"); 
                    break;
                }
            }
        }

        if (!$apiKey) $apiKey = getenv('GEMINI_API_KEY');

        if (empty($apiKey)) {
            return ["error" => "No se encontró la API Key. Verifica que el archivo .env esté en la raíz del proyecto."];
        }

        // 2. OBTENER DATOS EN TIEMPO REAL DE TU BD
        // Usamos tus propios métodos del Model.php
        $this->query("SELECT COUNT(*) as total FROM reservas WHERE estado = 'pendiente'");
        $resultadoPendientes = $this->single();
        $pendientes = $resultadoPendientes ? $resultadoPendientes->total : 0;

        $this->query("SELECT SUM(monto) as total FROM pagos WHERE estado = 'confirmada' AND MONTH(fecha_pago) = MONTH(CURDATE())");
        $resultadoIngresos = $this->single();
        $ingresos = $resultadoIngresos->total ?? '0.00';

        // 3. PREPARAR LA LLAMADA A LA IA (GEMINI)
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$apiKey";

        $instrucciones = "Eres un asistente administrativo experto del panel Happy Jumping. 
        Responde a la pregunta basándote de forma precisa en estos datos reales del sistema:
        - Reservas pendientes actualmente: $pendientes
        - Ingresos confirmados de este mes: S/ $ingresos
        
        Pregunta del administrador: $pregunta";

        $data = [
            "contents" => [["parts" => [["text" => $instrucciones]]]]
        ];

        // 4. EJECUTAR cURL PARA HABLAR CON GEMINI
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ["error" => "Error de conexión con IA: " . $error];
        }

        $result = json_decode($response, true);
        
        // Manejo de errores de la API de Google (ej. cuota excedida o clave inválida)
        if (isset($result['error'])) {
            return ["error" => "Error de la API: " . $result['error']['message']];
        }

        $respuestaIA = $result['candidates'][0]['content']['parts'][0]['text'] ?? "Lo siento, la IA no devolvió una respuesta válida.";

        // 5. GUARDAR EN EL HISTORIAL (Usando tu Model.php correctamente)
        $this->query("INSERT INTO chatbot_historial (pregunta, respuesta, contexto_sql) VALUES (:pregunta, :respuesta, :contexto)");
        $this->bind(':pregunta', $pregunta);
        $this->bind(':respuesta', $respuestaIA);
        $this->bind(':contexto', json_encode(["fuente" => "Gemini AI"]));
        $this->execute();

        // 6. DEVOLVER RESPUESTA AL JAVASCRIPT
        return ["respuesta" => $respuestaIA];
    }
}