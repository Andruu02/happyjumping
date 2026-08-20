<?php
class AdminController extends Controller {

    private $adminModel;

    public function __construct() {
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
            if (isset($_SESSION['id_usuario'])) {
                header('Location: ' . URL_ROOT . '/perfil');
            } else {
                header('Location: ' . URL_ROOT . '/usuarios/login');
            }
            exit();
        }
        $this->adminModel = $this->model('AdminModel');
    }

    public function index() {
        $ingresosChart = $this->adminModel->getIngresosUltimos7Dias();
        $chartLabels = [];
        $chartData   = [];
        foreach ($ingresosChart as $dia) {
            $chartLabels[] = date('d/m', strtotime($dia->dia));
            $chartData[]   = $dia->total_dia;
        }
        $spotify = $this->model('SpotifyModel');
        $datos = [
            'titulo'             => 'Dashboard - Admin',
            'totalClientes'      => $this->adminModel->contarTotalClientes(),
            'ingresosTotales'    => $this->adminModel->sumarIngresosTotales(),
            'reservasPendientes' => $this->adminModel->contarReservasPendientes(),
            'proximasReservas'   => $this->adminModel->getProximasReservas(),
            'chartLabels'        => json_encode($chartLabels),
            'chartData'          => json_encode($chartData),
            'spotifyConectado'   => $spotify->conectada(),
        ];
        $this->view('admin/index', $datos);
    }

    /**
     * Manda al admin a loguearse con la cuenta Premium del negocio en
     * Spotify y autorizar permiso de "crear/editar playlists" (una sola
     * vez - el refresh token que devuelve Spotify queda guardado en el
     * .env y de ahí en más ya no hace falta volver a loguearse).
     */
    public function spotifyConectar() {
        $spotify = $this->model('SpotifyModel');
        header('Location: ' . $spotify->urlAutorizacion());
        exit();
    }

    /**
     * Callback al que Spotify redirige después del login/autorización.
     */
    public function spotifyCallback() {
        $spotify = $this->model('SpotifyModel');
        $ok = false;

        if (!empty($_GET['code'])) {
            $ok = $spotify->procesarCallback($_GET['code']);
        }

        header('Location: ' . URL_ROOT . '/admin?spotify=' . ($ok ? 'ok' : 'error'));
        exit();
    }

    public function reservas() {
        $mensaje = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['id_reserva'])
            && isset($_POST['estado'])) {

            $id_reserva   = (int) $_POST['id_reserva'];
            $nuevo_estado = trim($_POST['estado']);
            $estados_ok   = ['pendiente', 'confirmada', 'cancelada'];

            if ($id_reserva > 0 && in_array($nuevo_estado, $estados_ok)) {

                $ok = $this->adminModel->actualizarEstadoReserva($id_reserva, $nuevo_estado);

                if ($ok) {
                    $mensaje = [
                        'tipo'  => 'success',
                        'texto' => 'Reserva #' . $id_reserva . ' cambiada a <strong>' . strtoupper($nuevo_estado) . '</strong>.'
                    ];

                    if ($nuevo_estado === 'confirmada') {
                        $reserva = $this->adminModel->getReservaPorId($id_reserva);
                        if ($reserva) {
                            require_once APP_ROOT . '/core/Mailer.php';
                            $enviado = Mailer::enviarConfirmacion(
                                $reserva,
                                $reserva->correo_cliente,
                                $reserva->nombre_cliente
                            );
                            if ($enviado) {
                                $mensaje['texto'] .= ' <small class="ms-2">✉️ Correo enviado al cliente.</small>';
                            } else {
                                $mensaje['texto'] .= ' <small class="ms-2 text-warning">⚠️ No se pudo enviar el correo.</small>';
                            }
                        }
                    }

                } else {
                    $mensaje = [
                        'tipo'  => 'danger',
                        'texto' => 'No se pudo guardar en la base de datos.'
                    ];
                }

            } else {
                $mensaje = [
                    'tipo'  => 'warning',
                    'texto' => 'Datos invalidos — ID: ' . $id_reserva . ', Estado: "' . htmlspecialchars($nuevo_estado) . '"'
                ];
            }
        }

        $filtros = [
            'estado'      => isset($_GET['estado'])      ? trim($_GET['estado'])      : 'all',
            'id_paquete'  => isset($_GET['id_paquete'])  ? trim($_GET['id_paquete'])  : '',
            'fecha_desde' => isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : '',
            'fecha_hasta' => isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : '',
            'buscar'      => isset($_GET['buscar'])      ? trim($_GET['buscar'])      : '',
        ];

        $porPagina  = 15;
        $pagina     = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
        $total      = $this->adminModel->contarReservasFiltradas($filtros);
        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $pagina     = min($pagina, $totalPaginas);

        $reservas = $this->adminModel->getReservasFiltradas($filtros, $porPagina, $pagina);
        $paquetes = $this->model('PaqueteModel')->obtenerPaquetesActivos();

        $datos = [
            'titulo'        => 'Gestionar Reservas',
            'reservas'      => $reservas,
            'paquetes'      => $paquetes,
            'filtros'       => $filtros,
            'estado_filtro' => $filtros['estado'],
            'pagina'        => $pagina,
            'totalPaginas'  => $totalPaginas,
            'totalReservas' => $total,
            'mensaje'       => $mensaje
        ];
        $this->view('admin/reservas', $datos);
    }

    public function actualizarEstadoReserva($id_reserva = null) {
        header('Location: ' . URL_ROOT . '/admin/reservas');
        exit;
    }

    /**
     * FUNCIÓN DE AJAX: canciones que el cliente sugirió para la playlist de
     * su fiesta (se muestran en el modal "Ver" de Gestión de Reservas, para
     * que la anfitriona sepa qué reproducir el día del evento).
     */
    public function cancionesReserva($id_reserva = 0) {
        header('Content-Type: application/json');
        echo json_encode($this->adminModel->getCancionesPorReserva((int) $id_reserva));
    }

    // ── CÓDIGOS DE PROMOCIÓN ─────────────────────────────────────────────────
    public function codigos() {
        $mensaje = null;

        // POST: cambiar estado de un código
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['id_codigo'])
            && isset($_POST['estado'])) {

            $id_codigo    = (int) $_POST['id_codigo'];
            $nuevo_estado = trim($_POST['estado']);
            $estados_ok   = ['disponible', 'usado'];

            if ($id_codigo > 0 && in_array($nuevo_estado, $estados_ok)) {
                $ok = $this->adminModel->actualizarEstadoCodigo($id_codigo, $nuevo_estado);
                if ($ok) {
                    $mensaje = [
                        'tipo'  => 'success',
                        'texto' => 'Código #' . $id_codigo . ' marcado como <strong>' . strtoupper($nuevo_estado) . '</strong>.'
                    ];
                } else {
                    $mensaje = [
                        'tipo'  => 'danger',
                        'texto' => 'No se pudo actualizar el código.'
                    ];
                }
            } else {
                $mensaje = [
                    'tipo'  => 'warning',
                    'texto' => 'Datos inválidos.'
                ];
            }
        }

        $estado_filtro      = isset($_GET['estado']) ? $_GET['estado']       : 'all';
        $buscar             = isset($_GET['buscar'])  ? trim($_GET['buscar']) : '';
        $codigo_filtro      = isset($_GET['codigo'])  ? trim(strtoupper($_GET['codigo'])) : '';
        $codigos            = $this->adminModel->getCodigosFiltrados($estado_filtro, $buscar, $codigo_filtro);

        $datos = [
            'titulo'        => 'Códigos Canjeados - Admin',
            'codigos'       => $codigos,
            'estado_filtro' => $estado_filtro,
            'buscar'        => $buscar,
            'codigo_filtro' => $codigo_filtro,
            'mensaje'       => $mensaje,
        ];
        $this->view('admin/codigos', $datos);
    }
    // ────────────────────────────────────────────────────────────────────────

    public function notificaciones() {
        $resultado       = null;
        $mensajeAnterior = '';

        $plantillas = [
            ['emoji' => '❤️',  'titulo' => 'San Valentín',  'mensaje' => '¡Hoy por San Valentín, 2x1 en entradas! ❤️'],
            ['emoji' => '🎉',  'titulo' => 'Promo del día',  'mensaje' => '¡Hoy lunes: 2x1 en todas las tarifas! 🎉'],
            ['emoji' => '🎂',  'titulo' => 'Cumpleaños',     'mensaje' => '¡Celebra tu cumple con nosotros y obten descuentos! 🎂'],
            ['emoji' => '🏆',  'titulo' => 'Fin de semana',  'mensaje' => '¡Viernes! 50% de descuento en nuestras tarifas. 🏆'],
        ];

        $pushModel = $this->model('PushModel');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['mensaje'] ?? ''))) {
            $mensaje = trim(strip_tags($_POST['mensaje']));

            if (strlen($mensaje) > 200) {
                $resultado = ['tipo' => 'warning', 'texto' => 'El mensaje no puede superar los 200 caracteres.'];
            } else {
                require_once APP_ROOT . '/core/WebPush.php';
                require_once APP_ROOT . '/config/vapid.php';
                require_once APP_ROOT . '/core/FcmSender.php';
                require_once APP_ROOT . '/config/firebase.php';

                $payload = json_encode([
                    'title' => 'Happy Jumping Peru 🎈',
                    'body'  => $mensaje,
                    'url'   => URL_ROOT,
                ]);

                $enviados = 0;
                foreach ($pushModel->obtenerSuscripciones() as $sub) {
                    $r = WebPush::enviar(
                        ['endpoint' => $sub->endpoint, 'p256dh' => $sub->p256dh, 'auth' => $sub->auth],
                        $payload,
                        VAPID_PUBLIC_KEY,
                        VAPID_PRIVATE_KEY,
                        VAPID_SUBJECT
                    );
                    if ($r === 'expirada') {
                        $pushModel->eliminarSuscripcion($sub->endpoint);
                    } elseif ($r === true) {
                        $enviados++;
                    }
                }

                foreach ($pushModel->obtenerTokensFcm() as $t) {
                    $r = FcmSender::enviar($t->token, 'Happy Jumping Peru 🎈', $mensaje, URL_ROOT);
                    if ($r === 'expirada') {
                        $pushModel->eliminarTokenFcm($t->token);
                    } elseif ($r === true) {
                        $enviados++;
                    }
                }

                $this->adminModel->guardarNotificacion($mensaje, $_SESSION['id_usuario'] ?? 0);

                if ($enviados > 0) {
                    $resultado = ['tipo' => 'success', 'texto' => "✅ Notificación enviada a <strong>{$enviados}</strong> dispositivo(s)."];
                } else {
                    $mensajeAnterior = $mensaje;
                    $resultado = ['tipo' => 'warning', 'texto' => '⚠️ No hay dispositivos suscritos a las notificaciones todavía.'];
                }
            }
        }

        $totalWebPush = $pushModel->contarSuscripciones();
        $totalFcm     = $pushModel->contarTokensFcm();

        $datos = [
            'titulo'          => 'Notificaciones - Admin',
            'plantillas'      => $plantillas,
            'historial'       => $this->adminModel->getHistorialNotificaciones(),
            'resultado'       => $resultado,
            'mensajeAnterior' => $mensajeAnterior,
            'totalSuscritos'  => $totalWebPush + $totalFcm,
            'totalWebPush'    => $totalWebPush,
            'totalFcm'        => $totalFcm,
        ];

        $this->view('admin/notificaciones', $datos);
    }

    // ── CORREOS MASIVOS ──────────────────────────────────────────────────────
    public function correos() {
        require_once APP_ROOT . '/core/Mailer.php';

        $resultado = null;

        // ─── POST: enviar correos ────────────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plantilla'])) {

            $plantilla          = trim($_POST['plantilla']);
            $destinatarios_ids  = isset($_POST['destinatarios']) ? (array)$_POST['destinatarios'] : [];
            $clientes           = [];

            // Si la plantilla es "recordatorio", traemos TODAS las reservas próximas
            // de una sola vez ANTES del foreach, para no hacer una query SQL
            // por cada cliente mientras se están enviando correos por SMTP
            // (eso agotaba la conexión a la BD y causaba "MySQL server has gone away").
            $proximas_index = [];
            if ($plantilla === 'recordatorio') {
                $proximas = $this->adminModel->getClientesConReservaProxima();
                foreach ($proximas as $p) {
                    $proximas_index[$p->id_usuario] = $p;
                }
            }

            // Si se marcó "todos", obtener todos los correos
            if (isset($_POST['todos']) && $_POST['todos'] === '1') {
                $clientes = $this->adminModel->getClientesParaCorreo('');
            } else {
                if (empty($destinatarios_ids)) {
                    $resultado = ['tipo' => 'warning', 'texto' => '⚠️ Debes seleccionar al menos un destinatario.'];
                } else {
                    $clientes_todos = $this->adminModel->getClientesParaCorreo('');
                    $clientes = array_filter($clientes_todos, function($c) use ($destinatarios_ids) {
                        return in_array($c->id_usuario, $destinatarios_ids);
                    });
                }
            }

            // La plantilla "recordatorio" solo debe llegar a clientes con una
            // reserva confirmada próxima: se descarta a cualquier otro destinatario.
            if ($resultado === null && $plantilla === 'recordatorio') {
                $clientes = array_filter($clientes, function($c) use ($proximas_index) {
                    return isset($proximas_index[$c->id_usuario]);
                });
            }

            if ($resultado === null && empty($clientes)) {
                $resultado = ['tipo' => 'warning', 'texto' => '⚠️ No hay destinatarios para enviar.'];
            }

            if ($resultado === null) {

                $enviados   = 0;
                $fallidos   = 0;
                $asunto_log = '';

                foreach ($clientes as $cliente) {
                    try {
                        $ok = false;

                        switch ($plantilla) {

                            case 'recordatorio':
                                $reserva = isset($proximas_index[$cliente->id_usuario]) ? $proximas_index[$cliente->id_usuario] : null;
                                if ($reserva) {
                                    $ok = Mailer::enviarRecordatorioReserva($cliente->correo, $cliente->nombre, $reserva);
                                    $asunto_log = '🎂 Recordatorio de reserva próxima';
                                }
                                break;

                            case 'promo':
                                $detalle = isset($_POST['detalle_promo']) ? trim($_POST['detalle_promo']) : '';
                                if (empty($detalle)) { $detalle = '¡Oferta especial disponible esta semana!'; }
                                $ok = Mailer::enviarPromoEspecial($cliente->correo, $cliente->nombre, $detalle);
                                $asunto_log = '🎉 Promoción especial';
                                break;

                            case 'puntos':
                                $puntos = isset($cliente->puntos) ? $cliente->puntos : 0;
                                $ok = Mailer::enviarRecordatorioPuntos($cliente->correo, $cliente->nombre, $puntos);
                                $asunto_log = '🏆 Recordatorio de puntos acumulados';
                                break;

                            case 'personalizado':
                                $asunto_custom = isset($_POST['asunto_custom']) ? trim($_POST['asunto_custom']) : 'Mensaje de Happy Jumping Peru';
                                $cuerpo_custom = isset($_POST['cuerpo_custom'])  ? trim($_POST['cuerpo_custom'])  : '';
                                if (empty($cuerpo_custom)) {
                                    $resultado = ['tipo' => 'warning', 'texto' => '⚠️ El cuerpo del mensaje no puede estar vacío.'];
                                    break;
                                }
                                $ok = Mailer::enviarMensajePersonalizado($cliente->correo, $cliente->nombre, $asunto_custom, $cuerpo_custom);
                                $asunto_log = $asunto_custom;
                                break;
                        }

                        if ($ok) $enviados++; else $fallidos++;

                    } catch (Exception $e) {
                        $fallidos++;
                    }
                }

                if ($resultado === null) {
                    if ($enviados > 0 && !empty($asunto_log)) {
                        $this->adminModel->guardarHistorialCorreo(
                            $_SESSION['usuario_id'] ?? 0,
                            $plantilla,
                            $enviados,
                            $asunto_log
                        );
                    }

                    if ($enviados > 0 && $fallidos === 0) {
                        $resultado = ['tipo' => 'success', 'texto' => "✅ Se enviaron <strong>{$enviados}</strong> correos correctamente."];
                    } elseif ($enviados > 0) {
                        $resultado = ['tipo' => 'warning', 'texto' => "⚠️ Se enviaron <strong>{$enviados}</strong> correos. Fallaron <strong>{$fallidos}</strong>."];
                    } else {
                        $resultado = ['tipo' => 'danger', 'texto' => "❌ No se pudo enviar ningún correo. Revisa la configuración SMTP."];
                    }
                }
            }
        }

        $buscar_clientes    = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        $clientes_lista     = $this->adminModel->getClientesParaCorreo($buscar_clientes);
        $historial          = $this->adminModel->getHistorialCorreos();

        // IDs de clientes con reserva confirmada próxima: la plantilla
        // "recordatorio" solo puede enviarse a ellos.
        $ids_recordatorio = array_map(function($p) {
            return $p->id_usuario;
        }, $this->adminModel->getClientesConReservaProxima());

        $datos = [
            'titulo'            => 'Correos Masivos — Admin',
            'clientes'          => $clientes_lista,
            'historial'         => $historial,
            'buscar'            => $buscar_clientes,
            'resultado'         => $resultado,
            'ids_recordatorio'  => $ids_recordatorio,
        ];

        $this->view('admin/correos', $datos);
    }
}