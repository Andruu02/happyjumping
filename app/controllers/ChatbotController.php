<?php
// app/controllers/ChatbotController.php
require_once '../app/core/Controller.php';
require_once '../app/services/ChatbotService.php';

class ChatbotController extends Controller {
    public function enviar() {
        // Recibir datos del JS
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        $pregunta = $data['pregunta'] ?? '';
        
        if (empty($pregunta)) {
            echo json_encode(["error" => "No se recibió ninguna pregunta."]);
            return;
        }

        // Instanciar servicio y procesar
        $servicio = new ChatbotService();
        $resultado = $servicio->procesarPregunta($pregunta);
        
        header('Content-Type: application/json');
        echo json_encode($resultado);
    }
}