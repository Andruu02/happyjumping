<?php
require_once '../app/core/Model.php';

class ChatbotService extends Model {
    // El constructor de Model ya debería inicializar $this->db
    public function __construct() {
        parent::__construct(); // Esto inicializa la conexión $this->db
    }

    public function procesarPregunta($pregunta) {
        // RUTA SEGURA: Apunta directo al directorio raíz del proyecto
        $envPath = $_SERVER['DOCUMENT_ROOT'] . '/.env'; 
        
        $apiKey = null;
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), 'GEMINI_API_KEY=') === 0) {
                    $apiKey = str_replace('GEMINI_API_KEY=', '', trim($line));
                    break;
                }
            }
        }

        // Si falla, intentamos con getenv() como respaldo
        if (!$apiKey) $apiKey = getenv('GEMINI_API_KEY');

        // DEBUG: Si esto sigue fallando, descomenta la línea de abajo para ver qué pasa
        //return ["error" => "Ruta buscada: " . $envPath . " - API Key encontrada: " . ($apiKey ? "SI" : "NO")];

        if (empty($apiKey)) {
            return ["error" => "No se encontró la API Key en el archivo .env"];
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