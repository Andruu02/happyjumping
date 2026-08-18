<?php
/*
|--------------------------------------------------------------------------
| Controlador de Reservas (VERSIÓN FINAL Y ESTABLE)
|--------------------------------------------------------------------------
| - CORREGIDO: Lógica de subida de archivos en finalizar() (Evita Warning y Error de Directorio).
| - CORREGIDO: Manejo de Warning de sesión 'usuario_rol'.
*/
require_once APP_ROOT . '/services/MercadoPagoService.php';

class ReservasController extends Controller {

    private $paqueteModel;
    private $horarioModel;
    private $reservaModel;
    private $deezerModel;

    public function __construct() {
        $this->paqueteModel = $this->model('PaqueteModel');
        $this->horarioModel = $this->model('HorarioModel');
        $this->reservaModel = $this->model('ReservaModel');
        $this->deezerModel = $this->model('DeezerModel');
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
     * FLUJO DE RESERVA (todo en una sola página: paquete ya elegido, extras,
     * fecha/hora, detalles del cumpleañero y pago).
     *
     * El paquete YA debe venir elegido desde la sección "Cumpleaños" del
     * inicio (?paquete=ID) - aquí ya no se puede cambiar de paquete. Si no
     * llega un paquete válido, se manda de vuelta a esa sección para que
     * elija uno.
     */
    public function paso1() {
        $this->proteger();

        $idPaquete = isset($_GET['paquete']) ? (int) $_GET['paquete'] : 0;
        $paquete = null;

        foreach ($this->paqueteModel->obtenerPaquetesActivos() as $p) {
            if ((int) $p->id_paquete === $idPaquete) {
                $paquete = $p;
                break;
            }
        }

        if (!$paquete) {
            header('Location: ' . URL_ROOT . '/#cumpleanos');
            exit();
        }

        $datos = [
            'titulo'      => 'Reserva - Happy&Jumping',
            'paquete'     => $paquete,
            'mpPublicKey' => MercadoPagoService::publicKey(),
        ];
        $this->view('reservas/paso1', $datos);
    }

    /**
     * PASO 2 y PASO 3 ya no son ventanas aparte: todo el flujo vive en una
     * sola página (ver paso1()). Estas rutas se mantienen solo por si algún
     * enlace viejo (favorito, historial) apunta aquí, y redirigen al flujo.
     */
    public function paso2() {
        $this->proteger();
        header('Location: ' . URL_ROOT . '/reservas/paso1');
        exit();
    }

    public function paso3() {
        $this->proteger();
        header('Location: ' . URL_ROOT . '/reservas/paso1');
        exit();
    }
    
    /**
     * FUNCIÓN DE AJAX: busca canciones (Deezer) para la playlist de la
     * fiesta (Paso 2). Requiere sesión iniciada, igual que el resto del
     * flujo de reservas.
     */
    public function buscarCanciones() {
        $this->proteger();
        header('Content-Type: application/json');

        $texto = trim($_GET['q'] ?? '');
        if ($texto === '') {
            echo json_encode([]);
            return;
        }

        echo json_encode($this->deezerModel->buscarCanciones($texto));
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
     * AJAX del Paso 3: cobra un pago de Yape a través de Mercado Pago.
     * Recibe el token que el navegador ya generó (celular + OTP) con la
     * Public Key, y usa el Access Token (secreto) para crear el pago.
     * Devuelve el resultado; el front recién ahí decide si sigue a
     * finalizar() o le muestra el error al usuario.
     */
    public function pagarYape() {
        $this->proteger();
        header('Content-Type: application/json');

        $input  = json_decode(file_get_contents('php://input'), true);
        $token  = $input['token'] ?? '';
        $monto  = isset($input['monto']) ? (float) $input['monto'] : 0;
        $correo = $_SESSION['usuario_correo'] ?? '';

        if ($token === '' || $monto <= 0 || $correo === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Datos de pago incompletos.']);
            return;
        }

        $mp = new MercadoPagoService();
        $resultado = $mp->crearPagoYape($token, $monto, $correo, 'Reserva Happy Jumping');

        if (isset($resultado['error'])) {
            echo json_encode([
                'ok'    => false,
                'error' => $resultado['error'],
                'debug' => $resultado['debug'] ?? null,
            ]);
            return;
        }

        echo json_encode([
            'ok'            => true,
            'status'        => $resultado['status'],
            'status_detail' => $resultado['status_detail'],
            'mp_payment_id' => $resultado['id'],
        ]);
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

            // Yape (vía Mercado Pago) cobra solo y no necesita evidencia; Plin
            // es manual, así que la captura sí es obligatoria para poder
            // verificar el pago después.
            $metodo_pago_recibido_temprano = $reserva_data['metodo_pago'] ?? 'plin';
            $captura_es_obligatoria = $metodo_pago_recibido_temprano !== 'yape_mp';

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
            } elseif ($captura_es_obligatoria) {
                $error_subida = 'Error: Debes subir la captura de pantalla del pago por Plin.';
            }
            // Con Yape (vía Mercado Pago) no hace falta captura: el cobro ya se verificó solo.

            // --- 3. VALIDAR Y GUARDAR ---
            if (!empty($error_subida)) {
                 // Si hubo error de subida, mostramos el mensaje de error y detenemos la ejecución
                 die($error_subida); 
            }

            // --- 2.5. RESOLVER MÉTODO Y ESTADO DE PAGO (del lado del servidor) ---
            // No se confía en lo que mande el navegador para esto: alguien
            // podría mandar metodo_pago/estado "confirmada" a mano sin haber
            // pagado. Solo se acepta 'yape_mp' o 'plin'; y si es Yape, el
            // estado real se vuelve a consultar directo en Mercado Pago con
            // el id de pago recibido, nunca se confía en un status del front.
            $metodo_pago_recibido = $reserva_data['metodo_pago'] ?? 'plin';
            $metodo_pago = in_array($metodo_pago_recibido, ['yape_mp', 'plin'], true) ? $metodo_pago_recibido : 'plin';
            $mp_payment_id_final = null;
            $estado_pago = 'pendiente';

            if ($metodo_pago === 'yape_mp' && !empty($reserva_data['mp_payment_id'])) {
                $mp = new MercadoPagoService();
                $pago = $mp->consultarPago($reserva_data['mp_payment_id']);

                if (!isset($pago['error'])) {
                    $mp_payment_id_final = $pago['id'];
                    if ($pago['status'] === 'approved') {
                        $estado_pago = 'confirmada';
                    } elseif ($pago['status'] === 'rejected' || $pago['status'] === 'cancelled') {
                        $estado_pago = 'cancelada';
                    } else {
                        $estado_pago = 'pendiente'; // in_process / pending
                    }
                }
                // Si la consulta falla, queda 'pendiente' (el admin lo revisa a mano).
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
                'canciones' => $reserva_data['canciones'] ?? [],
                'ruta_captura' => $ruta_captura_final, // Nombre del archivo guardado (opcional)
                'metodo_pago' => $metodo_pago,
                'mp_payment_id' => $mp_payment_id_final,
                'estado_pago' => $estado_pago,
            ];

            // Guardar en la DB (devuelve el id_reserva nuevo, o false si falló)
            $id_reserva_nueva = $this->reservaModel->crearReservaCompleta($datos_completos);
            if ($id_reserva_nueva) {
                header('Location: ' . URL_ROOT . '/reservas/exito/' . $id_reserva_nueva);
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
    
    /**
     * PÁGINA DE ÉXITO: muestra el mensaje final y, si se pudo obtener el
     * detalle de la reserva recién creada, un botón para agregarla a Google
     * Calendar (link "Agregar a Google Calendar" con la fecha/hora ya
     * llenadas - no requiere ninguna API key ni login con Google).
     */
    public function exito($id_reserva = 0) {
        $this->proteger();

        $googleCalendarUrl = null;
        $detalle = $this->reservaModel->obtenerDetalleParaCalendario((int) $id_reserva, $_SESSION['id_usuario']);

        if ($detalle) {
            $googleCalendarUrl = $this->construirLinkGoogleCalendar($detalle);
        }

        $datos = [
            'titulo' => 'Reserva Completada',
            'googleCalendarUrl' => $googleCalendarUrl,
        ];
        $this->view('reservas/exito', $datos);
    }

    /**
     * Arma la URL "Agregar a Google Calendar" (google.com/calendar/render)
     * con los datos de la reserva ya llenados. El usuario solo tiene que
     * confirmar "Guardar" en Google Calendar - no hace falta ninguna
     * credencial ni integración OAuth para esto.
     */
    private function construirLinkGoogleCalendar($detalle) {
        $tzLima = new DateTimeZone('America/Lima');

        $inicio = new DateTime($detalle->fecha . ' ' . $detalle->hora_inicio, $tzLima);
        $fin    = new DateTime($detalle->fecha . ' ' . $detalle->hora_fin, $tzLima);

        $inicio->setTimezone(new DateTimeZone('UTC'));
        $fin->setTimezone(new DateTimeZone('UTC'));

        $rangoFechas = $inicio->format('Ymd\THis\Z') . '/' . $fin->format('Ymd\THis\Z');

        $texto = 'Fiesta de ' . $detalle->nombre_cumpleanero . ' - Happy&Jumping';
        $detalles = 'Paquete: ' . $detalle->paquete_nombre . "\n" .
                    'Cantidad de personas: ' . $detalle->cantidad_personas . "\n" .
                    'Recuerda llegar unos minutos antes. ¡Nos vemos en la fiesta!';
        $ubicacion = '-6.5058575638378375, -76.35724119120785'; // mismas coordenadas del mapa del inicio

        return 'https://www.google.com/calendar/render?action=TEMPLATE'
             . '&text=' . rawurlencode($texto)
             . '&dates=' . $rangoFechas
             . '&details=' . rawurlencode($detalles)
             . '&location=' . rawurlencode($ubicacion);
    }
}