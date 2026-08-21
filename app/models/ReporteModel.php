<?php
class ReporteModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Reservas con todos los datos relevantes.
     * Acepta filtros opcionales: estado y rango de fechas.
     */
    public function getReservasParaReporte($estado = 'all', $fecha_desde = '', $fecha_hasta = '') {
        $sql = "SELECT
                    r.id_reserva,
                    h.fecha,
                    h.hora_inicio,
                    h.hora_fin,
                    r.nombre_cumpleanero,
                    r.edad_cumpleanero,
                    r.cantidad_personas,
                    r.observaciones,
                    p.nombre           AS paquete,
                    u.nombre           AS cliente,
                    u.correo           AS correo_cliente,
                    pg.monto,
                    COALESCE(NULLIF(pg.estado,''), 'pendiente') AS estado_pago
                FROM reservas r
                INNER JOIN usuarios u             ON r.id_usuario  = u.id_usuario
                INNER JOIN paquetes p             ON r.id_paquete  = p.id_paquete
                INNER JOIN pagos pg               ON r.id_reserva  = pg.id_reserva
                INNER JOIN horarios_disponibles h ON r.id_horario  = h.id_horario
                WHERE 1=1";

        if ($estado !== 'all' && $estado !== '') {
            $sql .= " AND COALESCE(NULLIF(pg.estado,''), 'pendiente') = :estado";
        }
        if ($fecha_desde !== '') {
            $sql .= " AND h.fecha >= :fecha_desde";
        }
        if ($fecha_hasta !== '') {
            $sql .= " AND h.fecha <= :fecha_hasta";
        }

        $sql .= " ORDER BY h.fecha ASC, h.hora_inicio ASC";

        $this->query($sql);

        if ($estado !== 'all' && $estado !== '') {
            $this->bind(':estado', $estado);
        }
        if ($fecha_desde !== '') {
            $this->bind(':fecha_desde', $fecha_desde);
        }
        if ($fecha_hasta !== '') {
            $this->bind(':fecha_hasta', $fecha_hasta);
        }

        return $this->resultSet();
    }

    /**
     * Resumen de ingresos agrupados por mes.
     */
    public function getResumenIngresosPorMes() {
        $this->query("SELECT
                        DATE_FORMAT(h.fecha, '%Y-%m') AS mes,
                        DATE_FORMAT(h.fecha, '%M %Y') AS mes_nombre,
                        COUNT(r.id_reserva)            AS total_reservas,
                        SUM(pg.monto)                  AS total_ingresos
                      FROM reservas r
                      INNER JOIN pagos pg               ON r.id_reserva = pg.id_reserva
                      INNER JOIN horarios_disponibles h ON r.id_horario  = h.id_horario
                      WHERE pg.estado = 'confirmada'
                      GROUP BY DATE_FORMAT(h.fecha, '%Y-%m')
                      ORDER BY mes ASC");
        return $this->resultSet();
    }

    /**
     * Paquete más solicitado con conteo, acotado al mismo periodo/estado que
     * el resto del reporte (antes era siempre global, sin importar los
     * filtros elegidos - por eso no coincidía con "el periodo elegido").
     */
    public function getResumenPorPaquete($estado = 'all', $fecha_desde = '', $fecha_hasta = '') {
        $sql = "SELECT
                    p.nombre              AS paquete,
                    COUNT(r.id_reserva)   AS total_reservas,
                    SUM(pg.monto)         AS total_ingresos
                FROM reservas r
                INNER JOIN paquetes p             ON r.id_paquete = p.id_paquete
                INNER JOIN pagos pg                ON r.id_reserva = pg.id_reserva
                INNER JOIN horarios_disponibles h ON r.id_horario  = h.id_horario
                WHERE 1=1";

        if ($estado !== 'all' && $estado !== '') {
            $sql .= " AND COALESCE(NULLIF(pg.estado,''), 'pendiente') = :estado";
        }
        if ($fecha_desde !== '') {
            $sql .= " AND h.fecha >= :fecha_desde";
        }
        if ($fecha_hasta !== '') {
            $sql .= " AND h.fecha <= :fecha_hasta";
        }
        $sql .= " GROUP BY p.id_paquete, p.nombre ORDER BY total_reservas DESC";

        $this->query($sql);
        if ($estado !== 'all' && $estado !== '') {
            $this->bind(':estado', $estado);
        }
        if ($fecha_desde !== '') {
            $this->bind(':fecha_desde', $fecha_desde);
        }
        if ($fecha_hasta !== '') {
            $this->bind(':fecha_hasta', $fecha_hasta);
        }
        return $this->resultSet();
    }

}
