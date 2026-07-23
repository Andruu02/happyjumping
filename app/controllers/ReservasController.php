<?php
/*
|--------------------------------------------------------------------------
| Controlador de Reservas (VERSIÓN FINAL Y ESTABLE)
|--------------------------------------------------------------------------
| - CORREGIDO: Lógica de subida de archivos en finalizar() (Evita Warning y Error de Directorio).
| - CORREGIDO: Manejo de Warning de sesión 'usuario_rol'.
*/
class ReservasController extends Controller {

    private $paqueteModel;
    private $horarioModel; 
    private $reservaModel; 

    public function __construct() {
        $this->paqueteModel = $this->model('PaqueteModel');
        $this->horarioModel = $this->model('HorarioModel'); 
        $this->reservaModel = $this->model('ReservaModel'); 
    }

    /*
     * ======================================================
     * FUNCIÓN DE SEGURIDAD
     * ======================================================
     */
    private function proteger() {
        if (!isset($_SESSION['id_usuario'])) {
            header('Location: ' . URL_ROOT . '/usuarios/login');
            exit(); 
        }
        // Corrección de acceso a rol
        if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 'admin') {
            header('Location: ' . URL_ROOT . '/admin');
            exit(); 
        }
    }

    /**
     * PASO 1: Muestra Acordeón, Calendario, etc.
     */
    public function paso1() {
        $this->proteger(); 
        $paquetesDesdeDB = $this->paqueteModel->obtenerPaquetesActivos();
        
        $datos = [
            'titulo' => 'Reserva (Paso 1) - Happy&Jumping',
            'paquetes' => $paquetesDesdeDB 
        ];
        $this->view('reservas/paso1', $datos);
    }

    /**
     * PASO 2: Muestra el formulario de "Nombre" y "Edad"
     */
    public function paso2() {
        $this->proteger(); 
        $datos = [ 'titulo' => 'Reserva (Paso 2) - Detalles' ];
        $this->view('reservas/paso2', $datos);
    }
    
    /**
     * PASO 3: Muestra QR y formulario de subida
     */
    public function paso3() {
        $this->proteger(); 
        $datos = [ 'titulo' => 'Reserva (Paso 3) - Pago' ];
        $this->view('reservas/paso3', $datos);
    }
    
    /**
     * FUNCIÓN DE AJAX PARA EL CALENDARIO (del Paso 1)
     */
    public function getFechasOcupadas($ano = 0, $mes = 0) {
        if ($ano == 0 || $mes == 0) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Fecha inválida']);
            return;
        }
        $fechas = $this->horarioModel->getFechasOcupadas($ano, $mes);
        header('Content-Type: application/json');
        echo json_encode($fechas);
    }

    /**
     * FUNCIÓN DE AJAX: a qué horas está ocupado un día por un cumpleaños
     * (para que un visitante que quiera entrar de forma normal vea si el
     * local va a estar ocupado por una fiesta a cierta hora antes de ir;
     * las entradas normales se venden de forma presencial, esto es solo
     * informativo).
     */
    public function getHorariosOcupados($fecha = '') {
        header('Content-Type: application/json');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            echo json_encode(['error' => 'Fecha inválida']);
            return;
        }
        $horarios = $this->horarioModel->getHorariosPorFecha($fecha);
        $resultado = [];
        foreach ($horarios as $h) {
            $resultado[] = ['hora_inicio' => $h->hora_inicio, 'hora_fin' => $h->hora_fin];
        }
        echo json_encode($resultado);
    }

    /**
     * ACCIÓN FINAL: Recibe el POST del Paso 3 y guarda la reserva
     */
    public function finalizar() {
        $this->proteger(); 

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reserva_data'])) {
            
            $reserva_data = json_decode($_POST['reserva_data'], true);
            $ruta_captura_final = null;
            $error_subida = '';
            
            // --- 1. CONFIGURACIÓN DE RUTA DE SUBIDA ---
            // Directorio donde se guardarán los archivos (ruta absoluta)
            $directorio_destino = PUBLIC_ROOT . '/uploads/capturas/'; 

            // --- 2. PROCESAR SUBIDA DE ARCHIVO ---
            if (isset($_FILES['captura_pago']) && $_FILES['captura_pago']['error'] == UPLOAD_ERR_OK) {
                
                $file = $_FILES['captura_pago'];
                $fileTmpName = $file['tmp_name'];
                $fileSize = $file['size'];
                
                $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
                
                // Aseguramos que el directorio de subida exista
                if (!is_dir($directorio_destino)) {
                    if (!@mkdir($directorio_destino, 0777, true)) {
                        $error_subida = 'Error: No se pudo crear el directorio de subida. Verifica permisos.';
                    }
                }
                
                if (empty($error_subida)) {
                    if (in_array($fileExt, $allowed)) {
                        if ($fileSize < 5000000) { // Max 5MB
                            
                            // *** CORRECCIÓN CLAVE: GENERAMOS EL NOMBRE Y LA RUTA COMPLETA AQUÍ ***
                            $fileNameNew = "captura_" . $_SESSION['id_usuario'] . "_" . uniqid('', true) . "." . $fileExt;
                            $ruta_completa_destino = $directorio_destino . $fileNameNew;

                            if (move_uploaded_file($fileTmpName, $ruta_completa_destino)) {
                                // Guardamos solo el nombre del archivo para la DB
                                $ruta_captura_final = $fileNameNew; 
                            } else {
                                $error_subida = 'Error: No se pudo mover el archivo. Verifica los permisos de la carpeta /public/uploads/capturas/';
                            }
                            
                        } else {
                            $error_subida = 'Error: El archivo es muy grande (máx 5MB).';
                        }
                    } else {
                        $error_subida = 'Error: Tipo de archivo no permitido (solo JPG, PNG, PDF).';
                    }
                }
            } else {
                 $error_subida = 'Error: Debes subir la captura de pantalla del pago.';
            }
            
            // --- 3. VALIDAR Y GUARDAR ---
            if (!empty($error_subida)) {
                 // Si hubo error de subida, mostramos el mensaje de error y detenemos la ejecución
                 die($error_subida); 
            }

            // Si llegamos aquí, la subida fue exitosa y $ruta_captura_final está definida
            $datos_completos = [
                'id_usuario' => $_SESSION['id_usuario'],
                'id_paquete' => $reserva_data['id_paquete'],
                'cantidad' => $reserva_data['cantidad'],
                'extra_pintura' => $reserva_data['extra_pintura'],
                'extra_destruccion' => $reserva_data['extra_destruccion'],
                'total_calculado' => $reserva_data['total_calculado'],
                'fecha' => $reserva_data['fecha'],
                'hora_inicio' => $reserva_data['hora_inicio'],
                'duracion_minutos' => $reserva_data['duracion_minutos'],
                'nombre_cumpleanero' => $reserva_data['nombre_cumpleanero'],
                'edad_cumpleanero' => $reserva_data['edad_cumpleanero'],
                'observaciones' => $reserva_data['observaciones'],
                'ruta_captura' => $ruta_captura_final // Nombre del archivo guardado
            ];
            
            // Guardar en la DB
            if ($this->reservaModel->crearReservaCompleta($datos_completos)) {
                header('Location: ' . URL_ROOT . '/reservas/exito');
                exit();
            } else {
                // Si falla la DB, intentamos borrar el archivo que ya subimos.
                if ($ruta_captura_final && file_exists($directorio_destino . $ruta_captura_final)) {
                    unlink($directorio_destino . $ruta_captura_final);
                }
                die('Error fatal: No se pudo guardar la reserva. Contacta a soporte.');
            }

        } else {
            // Si no es POST o no tiene data, redireccionar
            header('Location: ' . URL_ROOT);
            exit();
        }
    }
    
    /** PÁGINA DE ÉXITO: Muestra el mensaje final */
    public function exito() {
        $this->proteger(); 
        $datos = [ 'titulo' => 'Reserva Completada' ];
        $this->view('reservas/exito', $datos);
    }
}