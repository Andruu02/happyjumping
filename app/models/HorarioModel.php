<?php
/*
|--------------------------------------------------------------------------
| Modelo de Horarios (MODIFICADO)
|--------------------------------------------------------------------------
*/

class HorarioModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Obtiene una lista de TODAS las fechas ocupadas (un cumpleaños por día)
     * para un mes y año específicos.
     */
    public function getFechasOcupadas($ano, $mes) {
        
        // Buscamos cualquier fecha en este mes que tenga una entrada.
        // La lógica es "un cumpleaños por día", así que cualquier entrada la marca como ocupada.
        $this->query("SELECT DISTINCT fecha FROM horarios_disponibles 
                      WHERE YEAR(fecha) = :ano AND MONTH(fecha) = :mes");
        
        $this->bind(':ano', $ano);
        $this->bind(':mes', $mes);
        
        $resultados = $this->resultSet();
        
        // Devolvemos un array simple de fechas [ "2025-11-15", "2025-11-20" ]
        $fechas = [];
        foreach($resultados as $row) {
            $fechas[] = $row->fecha;
        }
        return $fechas;
    }

    /**
     * Obtiene los rangos de hora ocupados por cumpleaños en una fecha
     * específica (para que un visitante que quiera entrar de forma normal
     * vea a qué horas el local va a estar ocupado por una fiesta).
     */
    public function getHorariosPorFecha($fecha) {
        $this->query("SELECT hora_inicio, hora_fin FROM horarios_disponibles
                      WHERE fecha = :fecha ORDER BY hora_inicio ASC");

        $this->bind(':fecha', $fecha);

        return $this->resultSet();
    }

    /**
     * Igual que getFechasOcupadas() pero solo cuenta las reservas con pago
     * CONFIRMADO. La usa el calendario informativo del inicio ("Entradas y
     * Disponibilidad") - ahí no tiene sentido mostrar en rojo un día que
     * todavía tiene el pago pendiente y podría caerse.
     *
     * OJO: el flujo real de reservas (Paso 1) sigue usando
     * getFechasOcupadas() sin filtrar, porque ahí sí hay que bloquear
     * también los días con una reserva pendiente - si no, dos clientes
     * podrían terminar reservando el mismo horario físico.
     */
    public function getFechasOcupadasConfirmadas($ano, $mes) {
        $this->query("SELECT DISTINCT h.fecha
                      FROM horarios_disponibles h
                      INNER JOIN reservas r ON r.id_horario = h.id_horario
                      INNER JOIN pagos pg    ON pg.id_reserva = r.id_reserva
                      WHERE YEAR(h.fecha) = :ano AND MONTH(h.fecha) = :mes
                        AND pg.estado = 'confirmada'");

        $this->bind(':ano', $ano);
        $this->bind(':mes', $mes);

        $resultados = $this->resultSet();

        $fechas = [];
        foreach ($resultados as $row) {
            $fechas[] = $row->fecha;
        }
        return $fechas;
    }

    /** Igual que getHorariosPorFecha() pero solo con pago confirmado (ver nota arriba). */
    public function getHorariosConfirmadosPorFecha($fecha) {
        $this->query("SELECT h.hora_inicio, h.hora_fin
                      FROM horarios_disponibles h
                      INNER JOIN reservas r ON r.id_horario = h.id_horario
                      INNER JOIN pagos pg    ON pg.id_reserva = r.id_reserva
                      WHERE h.fecha = :fecha AND pg.estado = 'confirmada'
                      ORDER BY h.hora_inicio ASC");

        $this->bind(':fecha', $fecha);

        return $this->resultSet();
    }
}
?>