<?php
require_once '../app/core/Model.php';

class ChatbotService extends Model {
    public function procesarPregunta($pregunta) {
        // 1. LÓGICA PARA LEER EL ARCHIVO .env NATIVAMENTE
        $apiKey = null;
        // Subimos dos niveles desde app/services/ para llegar a la raíz donde está el .env
        $envPath = dirname(__DIR__, 2) . '/.env'; 
        
        if (file_exists($envPath)) {
            $env = parse_ini_file($envPath);
            $apiKey = $env['GEMINI_API_KEY'] ?? null;
        } else {
            // Fallback por si está configurado en las variables de entorno del servidor
            $apiKey = getenv('GEMINI_API_KEY'); 
        }

        if (empty($apiKey)) {
            return ["error" => "Error técnico: La clave API no está configurada en el servidor. Verifica que el archivo .env exista."];
        }

        // 2. PREPARAR LA LLAMADA A LA IA
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$apiKey";

        $instrucciones = "Eres un asistente administrativo del panel Happy Jumping. 
        Pregunta del admin: $pregunta";

        $data = [
            "contents" => [["parts" => [["text" => $instrucciones]]]]
        ];

        // 3. EJECUTAR cURL
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
        $respuestaIA = $result['candidates'][0]['content']['parts'][0]['text'] ?? "No pude conectar con la IA correctamente.";

        // 4. GUARDAR HISTORIAL
        $stmt = $this->db->prepare("INSERT INTO chatbot_historial (pregunta, respuesta) VALUES (?, ?)");
        $stmt->execute([$pregunta, $respuestaIA]);

        return ["respuesta" => $respuestaIA];
    }
}