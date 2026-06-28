<?php
require_once '../app/core/Model.php';

class ChatbotService extends Model {
    
    public function procesarPregunta($pregunta) {
        $respuesta = "Lo siento, no entendí tu consulta. Puedes preguntarme cosas como 'reservas hoy' o 'pagos pendientes'.";
        $contexto_sql = null;

        // 1. LÓGICA DE PALABRAS CLAVE
        
        // Sugerencia: "¿Cuántas reservas tenemos hoy?"
        if (strpos($pregunta, 'reservas') !== false && strpos($pregunta, 'hoy') !== false) {
            $sql = "SELECT COUNT(*) as total FROM reservas WHERE DATE(fecha_reserva) = CURDATE()";
            $stmt = $this->db->query($sql);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $respuesta = "Hoy se han registrado " . $resultado['total'] . " reservas en total.";
            $contexto_sql = json_encode(["query" => $sql, "action" => "reservas_hoy"]);
        }
        
        // Sugerencia: "¿Qué pagos están pendientes?"
        elseif (strpos($pregunta, 'pagos') !== false && strpos($pregunta, 'pendientes') !== false) {
            $sql = "SELECT COUNT(*) as total, SUM(monto) as monto_total FROM pagos WHERE estado = 'pendiente'";
            $stmt = $this->db->query($sql);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $monto = $resultado['monto_total'] ? $resultado['monto_total'] : '0.00';
            $respuesta = "Actualmente hay " . $resultado['total'] . " pagos pendientes, que suman un total de S/ " . $monto . ".";
            $contexto_sql = json_encode(["query" => $sql, "action" => "pagos_pendientes"]);
        }

        // Sugerencia: "¿Cuál es el paquete más vendido?" (Paquete top)
        elseif (strpos($pregunta, 'paquete') !== false && (strpos($pregunta, 'top') !== false || strpos($pregunta, 'vendido') !== false)) {
            $sql = "SELECT p.nombre, COUNT(r.id_reserva) as total_reservas 
                    FROM reservas r 
                    JOIN paquetes p ON r.id_paquete = p.id_paquete 
                    GROUP BY r.id_paquete 
                    ORDER BY total_reservas DESC LIMIT 1";
            $stmt = $this->db->query($sql);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($resultado) {
                $respuesta = "El paquete más vendido es '" . $resultado['nombre'] . "' con " . $resultado['total_reservas'] . " reservas.";
                $contexto_sql = json_encode(["query" => $sql, "action" => "paquete_top"]);
            }
        }

        // Sugerencia: "¿Cuánto hemos ingresado este mes?"
        elseif (strpos($pregunta, 'ingresos') !== false || strpos($pregunta, 'mes') !== false) {
            $sql = "SELECT SUM(monto) as total_ingresos FROM pagos WHERE estado = 'confirmada' AND MONTH(fecha_pago) = MONTH(CURDATE()) AND YEAR(fecha_pago) = YEAR(CURDATE())";
            $stmt = $this->db->query($sql);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $ingresos = $resultado['total_ingresos'] ? $resultado['total_ingresos'] : '0.00';
            $respuesta = "Los ingresos confirmados de este mes suman S/ " . $ingresos . ".";
            $contexto_sql = json_encode(["query" => $sql, "action" => "ingresos_mes"]);
        }

        // 2. GUARDAR EN LA BASE DE DATOS
        $this->guardarHistorial($pregunta, $respuesta, $contexto_sql);

        // 3. RETORNAR LA RESPUESTA AL CONTROLADOR
        return ["respuesta" => $respuesta];
    }

    private function guardarHistorial($pregunta, $respuesta, $contexto_sql) {
        $sql = "INSERT INTO chatbot_historial (pregunta, respuesta, contexto_sql) VALUES (:pregunta, :respuesta, :contexto)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':pregunta' => $pregunta,
            ':respuesta' => $respuesta,
            ':contexto' => $contexto_sql
        ]);
    }
}