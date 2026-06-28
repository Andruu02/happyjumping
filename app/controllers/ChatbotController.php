<?php
require_once '../app/core/Controller.php';
require_once '../app/services/ChatbotService.php';

class ChatbotController extends Controller {
    private $chatbotService;

    public function __construct() {
        // Asegúrate de que la conexión a la BD se inicializa aquí si tu MVC lo requiere
        $this->chatbotService = new ChatbotService();
    }

    // Esta función recibe el POST de tu chatbot_admin.js
    public function enviar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Capturar el JSON enviado desde JS
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Extraer la pregunta (tu JS también envía el 'historial', pero por ahora usaremos solo la pregunta)
            $pregunta = strtolower(trim($data['pregunta']));

            // Pasar la pregunta al servicio
            $resultado = $this->chatbotService->procesarPregunta($pregunta);

            // Devolver la respuesta en formato JSON para que el JS la lea
            header('Content-Type: application/json');
            echo json_encode($resultado);
        }
    }
}