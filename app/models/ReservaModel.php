<?php
/*
|--------------------------------------------------------------------------
| Modelo de Reservas (¡SINTAXIS CORREGIDA!)
|--------------------------------------------------------------------------
|
| Se corrigió $this.bind a $this->bind (flecha).
|
*/

class ReservaModel extends Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Crea la reserva completa usando una transacción
     */
    public function crearReservaCompleta($datos) {
        
        try {
            // 1. Iniciar Transacción
            $this->dbh->beginTransaction();

            // 2. Calcular Hora Fin
            $horaInicio = $datos['hora_inicio'];
            $duracionMin = $datos['duracion_minutos'];
            $horaFin = date('H:i:s', strtotime("+$duracionMin minutes", strtotime($horaInicio)));

            // 3. Insertar en 'horarios_disponibles' (para marcarlo como ocupado)
            $this->query("INSERT INTO horarios_disponibles (fecha, hora_inicio, hora_fin, disponible) 
                          VALUES (:fecha, :hora_inicio, :hora_fin, 0)"); // 0 = Ocupado
            $this->bind(':fecha', $datos['fecha']);
            $this->bind(':hora_inicio', $datos['hora_inicio']);
            $this->bind(':hora_fin', $horaFin);
            $this->execute();
            
            $id_horario_nuevo = $this->dbh->lastInsertId();

            // 5. Insertar en 'reservas'
            // estado va también acá (no solo en 'pagos'): el chatbot y los
            // reportes de ingresos filtran por reservas.estado = 'confirmada',
            // así que tiene que quedar en sync desde la creación.
            $this->query("INSERT INTO reservas (id_usuario, id_paquete, id_horario, cantidad_personas,
                                            extra_pintura, extra_destruccion, nombre_cumpleanero, edad_cumpleanero, observaciones, estado)
                          VALUES (:id_usuario, :id_paquete, :id_horario, :cantidad,
                                  :extra_pintura, :extra_destruccion, :nombre_cumple, :edad_cumple, :observaciones, :estado)");

            /*
             * ======================================================
             * ¡AQUÍ ESTABA EL ERROR! (Corregido a ->)
             * ======================================================
             */
            $this->bind(':id_usuario', $datos['id_usuario']);
            $this->bind(':id_paquete', $datos['id_paquete']);
            $this->bind(':id_horario', $id_horario_nuevo);
            $this->bind(':cantidad', $datos['cantidad']);
            $this->bind(':extra_pintura', $datos['extra_pintura'] ? 1 : 0);
            $this->bind(':extra_destruccion', $datos['extra_destruccion'] ? 1 : 0);
            $this->bind(':nombre_cumple', $datos['nombre_cumpleanero']);
            $this->bind(':edad_cumple', $datos['edad_cumpleanero']);
            $this->bind(':observaciones', $datos['observaciones']);
            $this->bind(':estado', $datos['estado_pago'] ?? 'pendiente');

            $this->execute();

            $id_reserva_nueva = $this->dbh->lastInsertId();

            // 7. Insertar en 'pagos'
            // estado_pago llega ya resuelto desde el controlador: 'confirmada'
            // si Mercado Pago aprobó el Yape al toque, 'pendiente' en cualquier
            // otro caso (Plin manual, o Yape en revisión/rechazado).
            $this->query("INSERT INTO pagos (id_reserva, monto, estado, ruta_captura, metodo_pago, mp_payment_id)
                          VALUES (:id_reserva, :monto, :estado, :ruta_captura, :metodo_pago, :mp_payment_id)");

            $this->bind(':id_reserva', $id_reserva_nueva);
            $this->bind(':monto', $datos['total_calculado']);
            $this->bind(':estado', $datos['estado_pago'] ?? 'pendiente');
            $this->bind(':ruta_captura', $datos['ruta_captura']);
            $this->bind(':metodo_pago', $datos['metodo_pago'] ?? 'yape');
            $this->bind(':mp_payment_id', $datos['mp_payment_id'] ?? null);
            $this->execute();

            // 7.5. Insertar las canciones sugeridas para la playlist de la
            // fiesta (buscadas en Deezer durante el Paso 2), si mandó alguna.
            if (!empty($datos['canciones']) && is_array($datos['canciones'])) {
                foreach ($datos['canciones'] as $cancion) {
                    $this->query("INSERT INTO reserva_canciones (id_reserva, nombre, artista, enlace)
                                  VALUES (:id_reserva, :nombre, :artista, :enlace)");
                    $this->bind(':id_reserva', $id_reserva_nueva);
                    $this->bind(':nombre', $cancion['nombre'] ?? '');
                    $this->bind(':artista', $cancion['artista'] ?? '');
                    $this->bind(':enlace', $cancion['enlace'] ?? '');
                    $this->execute();
                }
            }

            // 8. ¡Éxito! Confirmar la transacción y devolver el ID nuevo
            // (lo necesita el controlador para poder armar el link de
            // "Agregar a Google Calendar" en la página de éxito).
            if (!$this->dbh->commit()) {
                return false;
            }
            return $id_reserva_nueva;

        } catch (Exception $e) {
            // 9. ¡Error! Revertir todo
            $this->dbh->rollBack();

            // Muéstrame el error real de la BD
            die('Error de Base de Datos: ' . $e->getMessage());
        }
    }

    /**
     * Datos de una reserva puntual (paquete, fecha/hora, cumpleañero) para
     * armar el link de "Agregar a Google Calendar" en la página de éxito.
     * Se filtra también por id_usuario para que nadie pueda ver el detalle
     * de una reserva ajena solo cambiando el ID en la URL.
     */
    public function obtenerDetalleParaCalendario($id_reserva, $id_usuario) {
        $this->query("SELECT p.nombre AS paquete_nombre, r.cantidad_personas, r.nombre_cumpleanero,
                             h.fecha, h.hora_inicio, h.hora_fin
                      FROM reservas r
                      JOIN paquetes p ON r.id_paquete = p.id_paquete
                      JOIN horarios_disponibles h ON r.id_horario = h.id_horario
                      WHERE r.id_reserva = :id_reserva AND r.id_usuario = :id_usuario
                      LIMIT 1");
        $this->bind(':id_reserva', $id_reserva);
        $this->bind(':id_usuario', $id_usuario);
        return $this->single();
    }
}
?>