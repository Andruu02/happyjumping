<?php
require_once '../app/core/Model.php';

class ChatbotService extends Model {

    public function procesarPregunta($pregunta) {
        // 1. OBTENER Y LIMPIAR LA API KEY EXTREMADAMENTE BIEN
        $envPath = dirname(__DIR__, 2) . '/.env'; 
        $apiKey = '';

        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), 'GEMINI_API_KEY=') === 0) {
                    $valor = str_replace('GEMINI_API_KEY=', '', trim($line));
                    // Limpiamos espacios, saltos de línea, retornos de carro y comillas
                    $apiKey = trim($valor, " \t\n\r\0\x0B\"'");
                    break;
                }
            }
        }

        // Si no la encontró en el archivo, busca en el servidor
        if (empty($apiKey)) {
            $apiKey = trim(getenv('GEMINI_API_KEY'), " \t\n\r\0\x0B\"'");
        }

        if (empty($apiKey)) {
            return ["error" => "La API Key está vacía. Verifica tu archivo .env"];
        }

        // 2. OBTENER DATOS EN TIEMPO REAL
        $this->query("SELECT COUNT(*) as total FROM reservas WHERE estado = 'pendiente'");
        $resultadoPendientes = $this->single();
        $pendientes = $resultadoPendientes ? $resultadoPendientes->total : 0;

        $this->query("SELECT SUM(monto) as total FROM pagos WHERE estado = 'confirmada' AND MONTH(fecha_pago) = MONTH(CURDATE()) AND YEAR(fecha_pago) = YEAR(CURDATE())");
        $resultadoIngresos = $this->single();
        $ingresos = $resultadoIngresos->total ?? '0.00';

        // 3. PREPARAR LA LLAMADA A LA IA
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

        $instrucciones = "Eres un asistente administrativo experto del panel Happy Jumping. 
        Responde a la pregunta basándote de forma precisa en estos datos reales del sistema:
        - Reservas pendientes actualmente: $pendientes
        - Ingresos confirmados de este mes: S/ $ingresos
        
        Pregunta del administrador: $pregunta";

        $data = [
            "contents" => [["parts" => [["text" => $instrucciones]]]]
        ];

        // 4. EJECUTAR cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ["error" => "Error de conexión cURL: " . $error];
        }

        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            return ["error" => "Error de la API: " . $result['error']['message']];
        }

        $respuestaIA = $result['candidates'][0]['content']['parts'][0]['text'] ?? "Lo siento, la IA no devolvió una respuesta válida.";

        // 5. GUARDAR EN EL HISTORIAL
        $this->query("INSERT INTO chatbot_historial (pregunta, respuesta, contexto_sql) VALUES (:pregunta, :respuesta, :contexto)");
        $this->bind(':pregunta', $pregunta);
        $this->bind(':respuesta', $respuestaIA);
        $this->bind(':contexto', json_encode(["fuente" => "Gemini AI"]));
        $this->execute();

        return ["respuesta" => $respuestaIA];
    }
}