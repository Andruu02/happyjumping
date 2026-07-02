<?php

// ===== CORS =====
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-App-Key");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

class ApiController extends Controller {

    private $usuarioModel;

    public function __construct() {
        $this->verificarAppKey(); // se ejecuta ANTES de cualquier método (index, login, etc.)
        $this->usuarioModel = $this->model('UsuarioModel');
    }

    /*
    |--------------------------------------------------------------------------
    | SEGURIDAD — Verifica que la petición venga de la app móvil
    |--------------------------------------------------------------------------
    */
    private function verificarAppKey() {
        $headers = getallheaders();

        // getallheaders() puede devolver las claves con distinta capitalización
        // según el servidor, así que normalizamos
        $headers = array_change_key_case($headers, CASE_LOWER);

        $apiKeyRecibida = $headers['x-app-key'] ?? null;
        $apiKeyValida   = getenv('API_APP_KEY');

        if (!$apiKeyValida) {
            // Si no configuraste la variable de entorno, bloquea todo por seguridad
            http_response_code(500);
            exit;
        }

        if (!$apiKeyRecibida || !hash_equals($apiKeyValida, $apiKeyRecibida)) {
            http_response_code(404); // simula que la ruta no existe
            exit; // no se imprime absolutamente nada
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX — requerido por el router
    | GET /api
    |--------------------------------------------------------------------------
    */
    public function index() {
        echo json_encode([
            "success" => true,
            "message" => "API Happy Jumping"
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    | POST /api/login
    |--------------------------------------------------------------------------
    */
    public function login() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(["success" => false, "message" => "Método no permitido"]);
            return;
        }

        $input    = json_decode(file_get_contents("php://input"), true);
        $correo   = $input['correo']   ?? '';
        $password = $input['password'] ?? '';

        if (empty($correo) || empty($password)) {
            echo json_encode(["success" => false, "message" => "Campos incompletos"]);
            return;
        }

        $usuario = $this->usuarioModel->login($correo, $password);

        if ($usuario) {
            echo json_encode([
                "success" => true,
                "usuario" => [
                    "id"     => $usuario->id_usuario,
                    "nombre" => $usuario->nombre,
                    "correo" => $usuario->correo
                ]
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Credenciales incorrectas"]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PROMOCIONES
    | GET /api/promociones
    |--------------------------------------------------------------------------
    */
    public function promociones() {

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(["success" => false, "message" => "Método no permitido"]);
            return;
        }

        $promocionModel = $this->model('PromocionModel');
        $promociones    = $promocionModel->obtenerPromociones();

        echo json_encode(["success" => true, "data" => $promociones]);
    }

    /*
    |--------------------------------------------------------------------------
    | PARTIDAS
    | POST /api/partidas                       → guardar partida
    | GET  /api/partidas/puntos/{id_usuario}   → puntos totales
    | GET  /api/partidas/{id_usuario}          → historial
    |--------------------------------------------------------------------------
    */
    public function partidas($param1 = null, $param2 = null) {

        $partidaModel = $this->model('PartidaModel');

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $param1 === 'puntos' && $param2) {
            $puntos = $partidaModel->obtenerPuntosTotales((int) $param2);
            echo json_encode(["success" => true, "puntos_totales" => $puntos]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $param1 && !$param2) {
            $historial = $partidaModel->obtenerHistorial((int) $param1);
            echo json_encode(["success" => true, "data" => $historial]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents("php://input"), true);

            $id_usuario   = $input['id_usuario']   ?? null;
            $id_personaje = $input['id_personaje'] ?? null;
            $juego        = $input['juego']        ?? null;
            $puntaje      = $input['puntaje']      ?? null;

            if (!$id_usuario || !$id_personaje || !$juego || is_null($puntaje)) {
                echo json_encode(["success" => false, "message" => "Datos incompletos"]);
                return;
            }

            if (!in_array($juego, ['catcher', 'runner'])) {
                echo json_encode(["success" => false, "message" => "Juego inválido"]);
                return;
            }

            $resultado = $partidaModel->guardarPartida(
                (int) $id_usuario,
                (int) $id_personaje,
                $juego,
                (int) $puntaje
            );

            if ($resultado) {
                echo json_encode(["success" => true, "message" => "Partida guardada"]);
            } else {
                echo json_encode(["success" => false, "message" => "Error al guardar"]);
            }

            return;
        }

        echo json_encode(["success" => false, "message" => "Solicitud inválida"]);
    }

    /*
    |--------------------------------------------------------------------------
    | CÓDIGOS DE PROMOCIÓN
    | POST /api/codigos-promocion/canjear      → canjear promoción
    | GET  /api/codigos-promocion/{id_usuario} → mis códigos
    |--------------------------------------------------------------------------
    */
    public function codigosPromocion($param1 = null) {

        $promocionModel = $this->model('PromocionModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $param1 === 'canjear') {
            $input        = json_decode(file_get_contents("php://input"), true);
            $id_usuario   = $input['id_usuario']   ?? null;
            $id_promocion = $input['id_promocion'] ?? null;

            if (!$id_usuario || !$id_promocion) {
                echo json_encode(["success" => false, "message" => "Datos incompletos"]);
                return;
            }

            $resultado = $promocionModel->canjearPromocion(
                (int) $id_usuario,
                (int) $id_promocion
            );

            if ($resultado) {
                echo json_encode(["success" => true, "data" => $resultado]);
            } else {
                echo json_encode(["success" => false, "message" => "Puntos insuficientes o error al canjear"]);
            }

            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $param1) {
            $codigos = $promocionModel->obtenerCodigosDeUsuario((int) $param1);
            echo json_encode(["success" => true, "data" => $codigos]);
            return;
        }

        echo json_encode(["success" => false, "message" => "Solicitud inválida"]);
    }

    /*
    |--------------------------------------------------------------------------
    | PERSONAJES
    | GET /api/personajes
    |--------------------------------------------------------------------------
    */
    public function personajes() {

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(["success" => false, "message" => "Método no permitido"]);
            return;
        }

        $personajeModel = $this->model('PersonajeModel');
        $personajes     = $personajeModel->obtenerPersonajes();

        echo json_encode(["success" => true, "data" => $personajes]);
    }
}