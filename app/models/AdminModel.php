<?php
class AdminModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    // Total de clientes registrados (sin contar admin)
    public function contarTotalClientes() {
        $this->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'cliente'");
        return $this->single()->total;
    }

    // Suma de ingresos de reservas CONFIRMADAS (todos los tiempos)
    public function sumarIngresosTotales() {
        $this->query("SELECT SUM(monto) as total FROM pagos WHERE estado = 'confirmada'");
        $r = $this->single();
        return $r->total ?? 0;
    }

    // Reservas pendientes
    public function contarReservasPendientes() {
        $this->query("SELECT COUNT(*) as total FROM pagos 
                      WHERE estado = 'pendiente' OR estado = '' OR estado IS NULL");
        return $this->single()->total;
    }

    // Próximas reservas confirmadas
    public function getProximasReservas($limit = 5) {
        $this->query("SELECT r.nombre_cumpleanero, h.fecha, pg.estado
                      FROM reservas r
                      INNER JOIN pagos pg ON r.id_reserva = pg.id_reserva
                      INNER JOIN horarios_disponibles h ON r.id_horario = h.id_horario
                      WHERE pg.estado = 'confirmada'
                      ORDER BY h.fecha DESC
                      LIMIT :limit");
        $this->bind(':limit', $limit);
        return $this->resultSet();
    }

    // Reservas pendientes más recientes, para el "pendientes por revisar" del dashboard
    public function getReservasPendientesRecientes($limit = 5) {
        $this->query("SELECT r.id_reserva, r.nombre_cumpleanero, h.fecha, pg.monto
                      FROM reservas r
                      INNER JOIN pagos pg ON r.id_reserva = pg.id_reserva
                      INNER JOIN horarios_disponibles h ON r.id_horario = h.id_horario
                      WHERE pg.estado = 'pendiente' OR pg.estado = '' OR pg.estado IS NULL
                      ORDER BY r.id_reserva DESC
                      LIMIT :limit");
        $this->bind(':limit', $limit);
        return $this->resultSet();
    }

    // Ingresos agrupados por día (últimos 7 días) para la gráfica
    public function getIngresosUltimos7Dias() {
        $this->query("SELECT
                        DATE_FORMAT(h.fecha, '%d/%m') as dia,
                        SUM(p.monto) as total_dia
                      FROM pagos p
                      INNER JOIN reservas r ON p.id_reserva = r.id_reserva
                      INNER JOIN horarios_disponibles h ON r.id_horario = h.id_horario
                      WHERE p.estado = 'confirmada'
                      GROUP BY DATE_FORMAT(h.fecha, '%Y-%m-%d')
                      ORDER BY h.fecha ASC");
        return $this->resultSet();
    }

    // Ingresos agrupados por día (rango de N días) para la gráfica del dashboard.
    // Solo trae los días que sí tuvieron ingresos - el controlador rellena
    // con 0 los días sin ventas para que la gráfica no quede con huecos.
    public function getIngresosPorDia($dias = 30) {
        $this->query("SELECT
                        DATE(h.fecha) as dia,
                        SUM(p.monto) as total_dia
                      FROM pagos p
                      INNER JOIN reservas r ON p.id_reserva = r.id_reserva
                      INNER JOIN horarios_disponibles h ON r.id_horario = h.id_horario
                      WHERE p.estado = 'confirmada'
                        AND h.fecha >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
                      GROUP BY DATE(h.fecha)
                      ORDER BY h.fecha ASC");
        $this->bind(':dias', $dias, PDO::PARAM_INT);
        return $this->resultSet();
    }

    // Ingresos confirmados del mes actual vs. el mes anterior (para el
    // indicador de variación % en la tarjeta de "Ingresos" del dashboard).
    public function getComparativoIngresosMensual() {
        $inicioMes    = date('Y-m-01');
        $inicioMesAnt = date('Y-m-01', strtotime('-1 month'));

        $this->query("SELECT
                        COALESCE(SUM(CASE WHEN h.fecha >= :inicio_mes THEN p.monto ELSE 0 END), 0) AS mes_actual,
                        COALESCE(SUM(CASE WHEN h.fecha >= :inicio_mes_ant AND h.fecha < :inicio_mes2 THEN p.monto ELSE 0 END), 0) AS mes_anterior
                      FROM pagos p
                      INNER JOIN reservas r ON p.id_reserva = r.id_reserva
                      INNER JOIN horarios_disponibles h ON r.id_horario = h.id_horario
                      WHERE p.estado = 'confirmada'");
        $this->bind(':inicio_mes', $inicioMes);
        $this->bind(':inicio_mes2', $inicioMes);
        $this->bind(':inicio_mes_ant', $inicioMesAnt);
        return $this->single();
    }

    // Cuántos eventos confirmados hay en los próximos 7 días + cuál es el
    // más próximo (para la tarjeta "Eventos esta semana").
    public function getResumenEventosProximos() {
        $this->query("SELECT COUNT(*) as total
                      FROM reservas r
                      INNER JOIN pagos pg ON r.id_reserva = pg.id_reserva
                      INNER JOIN horarios_disponibles h ON r.id_horario = h.id_horario
                      WHERE pg.estado = 'confirmada'
                        AND h.fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 6 DAY)");
        $totalSemana = $this->single()->total;

        $this->query("SELECT r.nombre_cumpleanero, h.fecha, h.hora_inicio
                      FROM reservas r
                      INNER JOIN pagos pg ON r.id_reserva = pg.id_reserva
                      INNER JOIN horarios_disponibles h ON r.id_horario = h.id_horario
                      WHERE pg.estado = 'confirmada' AND h.fecha >= CURDATE()
                      ORDER BY h.fecha ASC, h.hora_inicio ASC
                      LIMIT 1");
        $proximo = $this->single();

        return (object) ['total_semana' => $totalSemana, 'proximo' => $proximo ?: null];
    }

    // Ticket promedio de reservas confirmadas + tasa de conversión
    // (confirmadas / total de reservas que llegaron a pagos).
    public function getTicketPromedioYConversion() {
        $this->query("SELECT AVG(monto) as ticket FROM pagos WHERE estado = 'confirmada'");
        $ticket = $this->single()->ticket ?? 0;

        $this->query("SELECT COUNT(*) as total FROM pagos");
        $total = (int) $this->single()->total;

        $this->query("SELECT COUNT(*) as total FROM pagos WHERE estado = 'confirmada'");
        $confirmadas = (int) $this->single()->total;

        $tasa = $total > 0 ? round(($confirmadas / $total) * 100) : 0;

        return (object) ['ticket' => (float) $ticket, 'tasa_conversion' => $tasa];
    }

    // Reservas (de cualquier estado, menos canceladas) con evento dentro de
    // los próximos N días - es el timeline operativo de "qué hay que
    // montar" del dashboard. Incluye pendientes porque el horario ya está
    // ocupado desde que se reservó, sin importar si el pago se confirmó.
    public function getProximosEventosOperativos($dias = 7) {
        $this->query("SELECT r.id_reserva, r.nombre_cumpleanero, r.edad_cumpleanero, r.cantidad_personas,
                             r.extra_pintura, r.extra_destruccion, r.spotify_playlist_url,
                             h.fecha, h.hora_inicio,
                             p.nombre AS nombre_paquete,
                             COALESCE(NULLIF(pg.estado,''), 'pendiente') AS estado_pago,
                             COALESCE(NULLIF(pg.metodo_pago,''), 'yape') AS metodo_pago
                      FROM reservas r
                      INNER JOIN pagos pg ON r.id_reserva = pg.id_reserva
                      INNER JOIN horarios_disponibles h ON r.id_horario = h.id_horario
                      INNER JOIN paquetes p ON r.id_paquete = p.id_paquete
                      WHERE COALESCE(NULLIF(pg.estado,''), 'pendiente') != 'cancelada'
                        AND h.fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)
                      ORDER BY h.fecha ASC, h.hora_inicio ASC");
        $this->bind(':dias', $dias, PDO::PARAM_INT);
        return $this->resultSet();
    }

    // Reservas pendientes cuyo evento cae dentro de los próximos N días -
    // son las urgentes: si no se revisan pronto, el evento llega sin pago
    // confirmado. Se usa tanto para las Alertas como para el aviso en la
    // tarjeta de "Reservas pendientes".
    public function getReservasPendientesUrgentes($dias = 5, $limit = 4) {
        $this->query("SELECT r.id_reserva, r.nombre_cumpleanero, h.fecha, pg.monto
                      FROM reservas r
                      INNER JOIN pagos pg ON r.id_reserva = pg.id_reserva
                      INNER JOIN horarios_disponibles h ON r.id_horario = h.id_horario
                      WHERE COALESCE(NULLIF(pg.estado,''), 'pendiente') = 'pendiente'
                        AND h.fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)
                      ORDER BY h.fecha ASC
                      LIMIT :limit");
        $this->bind(':dias', $dias, PDO::PARAM_INT);
        $this->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->resultSet();
    }

    // Paquetes más reservados (todo el historial, sin contar canceladas).
    public function getPaquetesMasVendidos($limit = 5) {
        $this->query("SELECT p.nombre AS paquete, COUNT(r.id_reserva) AS total
                      FROM reservas r
                      INNER JOIN paquetes p ON r.id_paquete = p.id_paquete
                      INNER JOIN pagos pg ON r.id_reserva = pg.id_reserva
                      WHERE COALESCE(NULLIF(pg.estado,''), 'pendiente') != 'cancelada'
                      GROUP BY p.id_paquete, p.nombre
                      ORDER BY total DESC
                      LIMIT :limit");
        $this->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->resultSet();
    }

    // Reparto de métodos de pago elegidos (Yape/MP, Plin, Yape manual).
    public function getMetodosPago() {
        $this->query("SELECT COALESCE(NULLIF(metodo_pago,''), 'yape') AS metodo, COUNT(*) AS total
                      FROM pagos
                      WHERE COALESCE(NULLIF(estado,''), 'pendiente') != 'cancelada'
                      GROUP BY metodo
                      ORDER BY total DESC");
        return $this->resultSet();
    }

    /**
     * Arma el WHERE + los binds comunes a getReservasFiltradas() y
     * contarReservasFiltradas(), para no duplicar la lógica de filtros.
     */
    private function condicionesReservasFiltradas($filtros) {
        $estado       = $filtros['estado']       ?? 'all';
        $id_paquete   = $filtros['id_paquete']    ?? '';
        $fecha_desde  = $filtros['fecha_desde']   ?? '';
        $fecha_hasta  = $filtros['fecha_hasta']   ?? '';
        $buscar       = $filtros['buscar']        ?? '';

        $where  = [];
        $params = [];

        if ($estado !== 'all' && $estado !== '') {
            $where[] = "COALESCE(NULLIF(pg.estado, ''), 'pendiente') = :estado";
            $params[':estado'] = $estado;
        }
        if ($id_paquete !== '') {
            $where[] = "r.id_paquete = :id_paquete";
            $params[':id_paquete'] = (int) $id_paquete;
        }
        if ($fecha_desde !== '') {
            $where[] = "h.fecha >= :fecha_desde";
            $params[':fecha_desde'] = $fecha_desde;
        }
        if ($fecha_hasta !== '') {
            $where[] = "h.fecha <= :fecha_hasta";
            $params[':fecha_hasta'] = $fecha_hasta;
        }
        if ($buscar !== '') {
            $where[] = "(u.nombre LIKE :buscar OR u.correo LIKE :buscar OR r.nombre_cumpleanero LIKE :buscar)";
            $params[':buscar'] = '%' . $buscar . '%';
        }

        return [
            'sql'    => $where ? (' WHERE ' . implode(' AND ', $where)) : '',
            'params' => $params,
        ];
    }

    // Reservas filtradas para la tabla, con paginación
    public function getReservasFiltradas($filtros = [], $porPagina = 0, $pagina = 1) {
        $cond = $this->condicionesReservasFiltradas($filtros);

        $sql = "SELECT
                    r.id_reserva,
                    h.fecha,
                    h.hora_inicio,
                    r.nombre_cumpleanero,
                    r.edad_cumpleanero,
                    r.observaciones,
                    p.nombre AS nombre_paquete,
                    u.nombre AS nombre_cliente,
                    u.correo AS correo_cliente,
                    pg.monto,
                    COALESCE(NULLIF(pg.estado, ''), 'pendiente') AS estado_pago,
                    pg.ruta_captura,
                    pg.id_pago,
                    COALESCE(NULLIF(pg.metodo_pago, ''), 'yape') AS metodo_pago,
                    pg.mp_payment_id,
                    r.spotify_playlist_url
                FROM reservas r
                INNER JOIN usuarios u             ON r.id_usuario  = u.id_usuario
                INNER JOIN paquetes p             ON r.id_paquete  = p.id_paquete
                INNER JOIN pagos pg               ON r.id_reserva  = pg.id_reserva
                INNER JOIN horarios_disponibles h ON r.id_horario  = h.id_horario"
                . $cond['sql'] .
                " ORDER BY r.id_reserva DESC";

        if ($porPagina > 0) {
            $offset = max(0, ($pagina - 1) * $porPagina);
            $sql .= " LIMIT :limite OFFSET :offset";
        }

        $this->query($sql);

        foreach ($cond['params'] as $param => $valor) {
            $this->bind($param, $valor);
        }
        if ($porPagina > 0) {
            $this->bind(':limite', (int) $porPagina, PDO::PARAM_INT);
            $this->bind(':offset', (int) $offset, PDO::PARAM_INT);
        }

        return $this->resultSet();
    }

    // Total de reservas que cumplen los mismos filtros (para la paginación)
    public function contarReservasFiltradas($filtros = []) {
        $cond = $this->condicionesReservasFiltradas($filtros);

        $sql = "SELECT COUNT(*) AS total
                FROM reservas r
                INNER JOIN usuarios u             ON r.id_usuario  = u.id_usuario
                INNER JOIN paquetes p             ON r.id_paquete  = p.id_paquete
                INNER JOIN pagos pg               ON r.id_reserva  = pg.id_reserva
                INNER JOIN horarios_disponibles h ON r.id_horario  = h.id_horario"
                . $cond['sql'];

        $this->query($sql);
        foreach ($cond['params'] as $param => $valor) {
            $this->bind($param, $valor);
        }
        return (int) $this->single()->total;
    }

    public function actualizarEstadoReserva($id_reserva, $nuevo_estado)
    {
        try {

            $this->dbh->beginTransaction();

            // ---------- PAGOS ----------
            $this->query("
                UPDATE pagos
                SET estado = :estado
                WHERE id_reserva = :id
            ");

            $this->bind(':estado', $nuevo_estado);
            $this->bind(':id', $id_reserva);

            $this->execute();

            $filasPago = $this->rowCount();

            // ---------- RESERVAS ----------
            $this->query("
                UPDATE reservas
                SET estado = :estado
                WHERE id_reserva = :id
            ");

            $this->bind(':estado', $nuevo_estado);
            $this->bind(':id', $id_reserva);

            $this->execute();

            $filasReserva = $this->rowCount();

            $this->dbh->commit();

            error_log("Reserva {$id_reserva}");
            error_log("Pagos afectados: ".$filasPago);
            error_log("Reservas afectadas: ".$filasReserva);

            return ($filasPago > 0 || $filasReserva > 0);

        } catch (Exception $e) {

            if ($this->dbh->inTransaction()) {
                $this->dbh->rollBack();
            }

            error_log($e->getMessage());

            return false;
        }
    }

    // Obtener una reserva por ID (para enviar correo de confirmación)
    public function getReservaPorId($id_reserva) {
        $this->query("SELECT
                    r.id_reserva,
                    h.fecha,
                    h.hora_inicio,
                    r.nombre_cumpleanero,
                    r.cantidad_personas,
                    p.nombre AS nombre_paquete,
                    u.nombre AS nombre_cliente,
                    u.correo AS correo_cliente,
                    pg.monto
                FROM reservas r
                INNER JOIN usuarios u             ON r.id_usuario  = u.id_usuario
                INNER JOIN paquetes p             ON r.id_paquete  = p.id_paquete
                INNER JOIN pagos pg               ON r.id_reserva  = pg.id_reserva
                INNER JOIN horarios_disponibles h ON r.id_horario  = h.id_horario
                WHERE r.id_reserva = :id
                LIMIT 1");
        $this->bind(':id', (int)$id_reserva, PDO::PARAM_INT);
        return $this->single();
    }

    // Canciones que el cliente sugirió para la playlist de su fiesta
    // (para que la anfitriona las reproduzca el día del evento).
    public function getCancionesPorReserva($id_reserva) {
        $this->query("SELECT nombre, artista, enlace
                      FROM reserva_canciones
                      WHERE id_reserva = :id
                      ORDER BY id_cancion ASC");
        $this->bind(':id', (int) $id_reserva, PDO::PARAM_INT);
        return $this->resultSet();
    }

    // ── Notificaciones Firebase ──────────────────────────────────────────────

    public function guardarNotificacion($mensaje, $id_admin) {
        $this->query("INSERT INTO notificaciones_push (mensaje, id_admin, created_at)
                      VALUES (:mensaje, :id_admin, NOW())");
        $this->bind(':mensaje',  $mensaje);
        $this->bind(':id_admin', $id_admin);
        return $this->execute();
    }

    public function getHistorialNotificaciones($limite = 10) {
        $this->query("SELECT n.mensaje, n.created_at,
                             COALESCE(u.nombre, 'Admin') AS admin_nombre
                      FROM notificaciones_push n
                      LEFT JOIN usuarios u ON n.id_admin = u.id_usuario
                      ORDER BY n.created_at DESC
                      LIMIT :limite");
        $this->bind(':limite', $limite, PDO::PARAM_INT);
        return $this->resultSet();
    }

    // ── Códigos de promoción ─────────────────────────────────────────────────

    /**
     * Devuelve los códigos con filtro de estado, código y búsqueda por nombre/correo.
     */
    public function getCodigosFiltrados($estado = 'all', $buscar = '', $codigo = '') {
        $sql = "SELECT
                    c.id_codigo,
                    c.codigo,
                    c.estado,
                    c.fecha_generacion,
                    c.fecha_uso,
                    p.nombre  AS nombre_promocion,
                    p.puntos_necesarios,
                    u.nombre  AS nombre_usuario,
                    u.correo  AS correo_usuario
                FROM codigos_promocion c
                INNER JOIN promociones p ON p.id_promocion = c.id_promocion
                INNER JOIN usuarios    u ON u.id_usuario   = c.id_usuario
                WHERE 1=1";

        if ($estado !== 'all' && $estado !== '') {
            $sql .= " AND c.estado = :estado";
        }
        if ($codigo !== '') {
            $sql .= " AND c.codigo LIKE :codigo";
        }
        if ($buscar !== '') {
            $sql .= " AND (u.nombre LIKE :buscar OR u.correo LIKE :buscar)";
        }

        $sql .= " ORDER BY c.fecha_generacion DESC";

        $this->query($sql);

        if ($estado !== 'all' && $estado !== '') {
            $this->bind(':estado', $estado, PDO::PARAM_STR);
        }
        if ($codigo !== '') {
            $this->bind(':codigo', '%' . $codigo . '%', PDO::PARAM_STR);
        }
        if ($buscar !== '') {
            $this->bind(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
        }

        return $this->resultSet();
    }

    /**
     * Cambia el estado de un código (disponible ↔ usado).
     * Si pasa a "usado" registra fecha_uso; si vuelve a "disponible" la borra.
     */
    public function actualizarEstadoCodigo($id_codigo, $nuevo_estado) {
        if ($nuevo_estado === 'usado') {
            $this->query("UPDATE codigos_promocion
                          SET estado = 'usado', fecha_uso = NOW()
                          WHERE id_codigo = :id_codigo");
        } else {
            $this->query("UPDATE codigos_promocion
                          SET estado = 'disponible', fecha_uso = NULL
                          WHERE id_codigo = :id_codigo");
        }
        $this->bind(':id_codigo', $id_codigo, PDO::PARAM_INT);
        return $this->execute();
    }

    // ── CORREOS MASIVOS ──────────────────────────────────────────────────────

    /**
     * Devuelve todos los clientes registrados, con su total de reservas
     * y sus puntos acumulados (sumados desde la tabla partidas).
     * Si $buscar no está vacío, filtra por nombre o correo.
     */
    public function getClientesParaCorreo($buscar = '') {
        $sql = "SELECT u.id_usuario, u.nombre, u.correo,
                       COUNT(DISTINCT r.id_reserva) AS total_reservas,
                       COALESCE(SUM(pa.puntaje), 0) AS puntos
                FROM usuarios u
                LEFT JOIN reservas r ON r.id_usuario = u.id_usuario
                LEFT JOIN partidas pa ON pa.id_usuario = u.id_usuario
                WHERE u.rol = 'cliente'";

        if ($buscar !== '') {
            $sql .= " AND (u.nombre LIKE :buscar OR u.correo LIKE :buscar)";
        }

        $sql .= " GROUP BY u.id_usuario, u.nombre, u.correo ORDER BY u.nombre ASC";

        $this->query($sql);
        if ($buscar !== '') {
            $this->bind(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
        }
        return $this->resultSet();
    }

    /**
     * Devuelve clientes con reserva confirmada que tengan próxima fecha
     * (útil para la plantilla de recordatorio).
     */
    public function getClientesConReservaProxima() {
        $this->query("SELECT DISTINCT
                          u.id_usuario, u.nombre, u.correo,
                          r.nombre_cumpleanero,
                          h.fecha,
                          h.hora_inicio,
                          p.nombre AS nombre_paquete
                      FROM usuarios u
                      INNER JOIN reservas r  ON r.id_usuario  = u.id_usuario
                      INNER JOIN pagos   pg  ON pg.id_reserva = r.id_reserva
                      INNER JOIN horarios_disponibles h ON h.id_horario = r.id_horario
                      INNER JOIN paquetes p ON p.id_paquete = r.id_paquete
                      WHERE pg.estado = 'confirmada'
                        AND h.fecha >= CURDATE()
                      ORDER BY h.fecha ASC");
        return $this->resultSet();
    }

    /**
     * Devuelve clientes con puntos suficientes para canjear al menos
     * una promoción (usa el umbral mínimo registrado en promociones).
     */
    public function getClientesConPuntosCanjeables() {
        $this->query("SELECT u.id_usuario, u.nombre, u.correo,
                              COALESCE(SUM(pa.puntaje), 0) AS puntos
                      FROM usuarios u
                      LEFT JOIN partidas pa ON pa.id_usuario = u.id_usuario
                      WHERE u.rol = 'cliente'
                      GROUP BY u.id_usuario, u.nombre, u.correo
                      HAVING puntos >= (SELECT MIN(puntos_necesarios) FROM promociones)
                      ORDER BY puntos DESC");
        return $this->resultSet();
    }

    /**
     * Guarda el historial de correos enviados desde el admin.
     */
    public function guardarHistorialCorreo($id_admin, $plantilla, $destinatarios, $asunto) {
        $this->query("INSERT INTO historial_correos (id_admin, plantilla, destinatarios, asunto, enviado_at)
                      VALUES (:id_admin, :plantilla, :destinatarios, :asunto, NOW())");
        $this->bind(':id_admin',      $id_admin,      PDO::PARAM_INT);
        $this->bind(':plantilla',     $plantilla,     PDO::PARAM_STR);
        $this->bind(':destinatarios', $destinatarios, PDO::PARAM_INT);
        $this->bind(':asunto',        $asunto,        PDO::PARAM_STR);
        return $this->execute();
    }

    /**
     * Historial de correos enviados (últimos N).
     */
    public function getHistorialCorreos($limite = 15) {
        $this->query("SELECT hc.id, hc.plantilla, hc.destinatarios, hc.asunto, hc.enviado_at,
                             COALESCE(u.nombre, 'Admin') AS admin_nombre
                      FROM historial_correos hc
                      LEFT JOIN usuarios u ON u.id_usuario = hc.id_admin
                      ORDER BY hc.enviado_at DESC
                      LIMIT :limite");
        $this->bind(':limite', $limite, PDO::PARAM_INT);
        return $this->resultSet();
    }
}