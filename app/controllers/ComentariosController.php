<?php
/*
|--------------------------------------------------------------------------
| Controlador de Comentarios (sección tipo blog del inicio)
|--------------------------------------------------------------------------
*/

class ComentariosController extends Controller {

    private $comentarioModel;

    public function __construct() {
        $this->comentarioModel = $this->model('ComentarioModel');
    }

    /**
     * Recibe el POST del formulario de comentarios del inicio. Solo
     * usuarios con sesión iniciada pueden comentar; los comentarios se
     * ven públicamente sin necesidad de iniciar sesión.
     */
    public function agregar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id_usuario'])) {
            $texto = trim($_POST['comentario'] ?? '');
            if ($texto !== '' && mb_strlen($texto) <= 500) {
                $this->comentarioModel->crearComentario($_SESSION['id_usuario'], $texto);
            }
        }

        header('Location: ' . URL_ROOT . '/#comentarios');
        exit();
    }
}
?>
