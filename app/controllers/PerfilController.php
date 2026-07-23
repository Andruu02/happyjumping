<?php
/*
|--------------------------------------------------------------------------
| Controlador de Perfil
|--------------------------------------------------------------------------
*/

class PerfilController extends Controller {

    private $perfilModel;
    private $paqueteModel;

    public function __construct() {
        // 1. Proteger la página: Si no está logueado, ¡fuera!
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: ' . URL_ROOT . '/usuarios/login');
            exit();
        }

        // 2. Cargar los modelos
        $this->perfilModel  = $this->model('PerfilModel');
        $this->paqueteModel = $this->model('PaqueteModel');
    }

    /**
     * Muestra la página principal del perfil (Mis Reservas), con filtros
     * y paginación (10 por página).
     * URL: /perfil
     */
    public function index() {
        $id_usuario = $_SESSION['id_usuario'];

        $filtros = [
            'estado'      => $_GET['estado']      ?? 'all',
            'id_paquete'  => $_GET['id_paquete']   ?? '',
            'fecha_desde' => $_GET['fecha_desde']  ?? '',
            'fecha_hasta' => $_GET['fecha_hasta']  ?? '',
        ];

        $porPagina    = 10;
        $pagina       = max(1, (int) ($_GET['pagina'] ?? 1));
        $totalReservas = $this->perfilModel->contarReservasPorUsuario($id_usuario, $filtros);
        $totalPaginas  = max(1, (int) ceil($totalReservas / $porPagina));
        $pagina        = min($pagina, $totalPaginas);

        $reservas = $this->perfilModel->getReservasPorUsuario($id_usuario, $filtros, $porPagina, $pagina);

        $datos = [
            'titulo'        => 'Mi Perfil - Happy&Jumping',
            'reservas'      => $reservas,
            'paquetes'      => $this->paqueteModel->obtenerPaquetesActivos(),
            'filtros'       => $filtros,
            'pagina'        => $pagina,
            'totalPaginas'  => $totalPaginas,
            'totalReservas' => $totalReservas,
        ];

        // 3. Cargar la vista (¡esta sí usa header y footer!)
        $this->view('perfil/index', $datos);
    }

    // (Aquí podríamos añadir más funciones como 'editarDatos', etc.)
}
?>