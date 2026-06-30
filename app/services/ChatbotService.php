<?php
require_once '../app/core/Model.php';

class ChatbotService extends Model {
    public function procesarPregunta($pregunta) {
        $apiKey = getenv('GEMINI_API_KEY'); // Se lee de tu .env
        
        if (!$apiKey) {
            return ["error" => "Error de configuración: API KEY no encontrada."];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$apiKey";
        $data = [
            "contents" => [["parts" => [["text" => "Eres un asistente de Happy Jumping. Responde esto: " . $pregunta]]]]
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
            return ["error" => "CURL Error: " . $error];
        }

        $result = json_decode($response, true);
        $texto = $result['candidates'][0]['content']['parts'][0]['text'] ?? "Error procesando IA.";
        
        return ["respuesta" => $texto];
    }
}