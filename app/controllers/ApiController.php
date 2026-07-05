<?php

// ===== CORS =====
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

class ApiController extends Controller {

    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = $this->model('UsuarioModel');
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
            "message" => "API Happy Jumping",
            "endpoints" => [
                "POST /api/login",
                "GET  /api/promociones",
                "GET  /api/personajes",
                "POST /api/partidas",
                "GET  /api/partidas/{id_usuario}",
                "GET  /api/partidas/puntos/{id_usuario}",
                "POST /api/codigos-promocion/canjear",
                "GET  /api/codigos-promocion/{id_usuario}"
            ]
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
            $token = JwtHandler::encode([
                "id_usuario" => $usuario->id_usuario,
                "correo"     => $usuario->correo
            ]);

            echo json_encode([
                "success" => true,
                "token"   => $token,
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
    | AUTENTICACIÓN (JWT / Bearer token)
    |--------------------------------------------------------------------------
    | Corta la ejecución con 401 si no viene un token válido. Se debe llamar
    | al inicio de cada endpoint que no deba ser público.
    */
    private function authenticate() {
        $header = $this->getAuthorizationHeader();

        if (!$header || stripos($header, 'Bearer ') !== 0) {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "No autorizado. Debes iniciar sesión."]);
            exit();
        }

        $token   = trim(substr($header, 7));
        $payload = JwtHandler::decode($token);

        if (!$payload) {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Token inválido o expirado."]);
            exit();
        }

        return $payload;
    }

    private function getAuthorizationHeader() {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return trim($_SERVER['HTTP_AUTHORIZATION']);
        }

        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
        }

        if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            if (isset($requestHeaders['Authorization'])) {
                return trim($requestHeaders['Authorization']);
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | PROMOCIONES
    | GET /api/promociones
    |--------------------------------------------------------------------------
    */
    public function promociones() {

        $this->authenticate();

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

        $this->authenticate();

        $partidaModel = $this->model('PartidaModel');

        // GET /api/partidas/puntos/1  → $param1='puntos', $param2='1'
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $param1 === 'puntos' && $param2) {
            $puntos = $partidaModel->obtenerPuntosTotales((int) $param2);
            echo json_encode(["success" => true, "puntos_totales" => $puntos]);
            return;
        }

        // GET /api/partidas/1  → $param1='1', $param2=null
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $param1 && !$param2) {
            $historial = $partidaModel->obtenerHistorial((int) $param1);
            echo json_encode(["success" => true, "data" => $historial]);
            return;
        }

        // POST /api/partidas
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

        $this->authenticate();

        $promocionModel = $this->model('PromocionModel');

        // POST /api/codigos-promocion/canjear
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

        // GET /api/codigos-promocion/1
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

        $this->authenticate();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(["success" => false, "message" => "Método no permitido"]);
            return;
        }

        $personajeModel = $this->model('PersonajeModel');
        $personajes     = $personajeModel->obtenerPersonajes();

        echo json_encode(["success" => true, "data" => $personajes]);
    }
}