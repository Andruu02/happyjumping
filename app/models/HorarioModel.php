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
}
?>