<?php
/*
|--------------------------------------------------------------------------
| Controlador de Inicio (Página principal)
|--------------------------------------------------------------------------
*/

class InicioController extends Controller {

    private $paqueteModel;

    public function __construct() {
        $this->paqueteModel = $this->model('PaqueteModel');
    }

    /**
     * Página principal: incluye, en una sola página con scroll, el
     * contenido de Entradas, Cumpleaños y Conócenos (antes eran rutas
     * separadas en PaquetesController/InicioController::conocenos()).
     */
    public function index() {
        $datos = [
            'titulo'      => 'Happy&Jumping - Diversión sin límites',
            'active_page' => 'inicio',
            'paquetes'    => $this->paqueteModel->obtenerPaquetesActivos(),
        ];

        $this->view('inicio/index', $datos);
    }
}
?>