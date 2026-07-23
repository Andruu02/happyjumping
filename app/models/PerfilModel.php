<?php
/*
|--------------------------------------------------------------------------
| Modelo de Perfil
|--------------------------------------------------------------------------
|
| Este modelo obtiene los datos del perfil del usuario,
| principalmente sus reservas.
|
*/

class PerfilModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Arma el WHERE + los binds comunes a getReservasPorUsuario() y
     * contarReservasPorUsuario(), para no duplicar la lógica de filtros.
     */
    private function condicionesReservasUsuario($id_usuario, $filtros) {
        $estado      = $filtros['estado']      ?? 'all';
        $id_paquete  = $filtros['id_paquete']   ?? '';
        $fecha_desde = $filtros['fecha_desde']  ?? '';
        $fecha_hasta = $filtros['fecha_hasta']  ?? '';

        $where  = ['r.id_usuario = :id_usuario'];
        $params = [':id_usuario' => $id_usuario];

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

        return [
            'sql'    => ' WHERE ' . implode(' AND ', $where),
            'params' => $params,
        ];
    }

    /**
     * Reservas de un usuario, con filtros opcionales y paginación opcional
     * (porPagina = 0 devuelve todas, sin límite).
     */
    public function getReservasPorUsuario($id_usuario, $filtros = [], $porPagina = 0, $pagina = 1) {
        $cond = $this->condicionesReservasUsuario($id_usuario, $filtros);

        $sql = "SELECT
                    r.id_reserva,
                    COALESCE(NULLIF(pg.estado, ''), 'pendiente') AS estado,
                    r.cantidad_personas,
                    r.nombre_cumpleanero,
                    r.edad_cumpleanero,
                    p.nombre as paquete_nombre,
                    h.fecha,
                    h.hora_inicio
                FROM reservas as r
                JOIN paquetes as p ON r.id_paquete = p.id_paquete
                JOIN horarios_disponibles as h ON r.id_horario = h.id_horario
                JOIN pagos as pg ON r.id_reserva = pg.id_reserva"
                . $cond['sql'] .
                " ORDER BY h.fecha DESC";

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

    /** Total de reservas del usuario que cumplen los mismos filtros (para la paginación) */
    public function contarReservasPorUsuario($id_usuario, $filtros = []) {
        $cond = $this->condicionesReservasUsuario($id_usuario, $filtros);

        $sql = "SELECT COUNT(*) AS total
                FROM reservas as r
                JOIN paquetes as p ON r.id_paquete = p.id_paquete
                JOIN horarios_disponibles as h ON r.id_horario = h.id_horario
                JOIN pagos as pg ON r.id_reserva = pg.id_reserva"
                . $cond['sql'];

        $this->query($sql);
        foreach ($cond['params'] as $param => $valor) {
            $this->bind($param, $valor);
        }
        return (int) $this->single()->total;
    }
}
?>
