<?php
/*
 * VISTA DE PERFIL (Carga su propio CSS)
 */

// 1. Cargar el header estándar (que ahora carga style.css)
require APP_ROOT . '/views/includes/header.php';
?>

<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/perfil.css">

<div class="container mt-5 mb-5">

    <?php
        // Iconos de animales "saltarines" al azar, para que la cabecera del
        // perfil no sea siempre igual y combine con el tema del parque.
        $iconosAnimales = ['🦘', '🐒', '🐰', '🐸', '🦋', '🦩', '🐬', '🦁', '🐯', '🐨'];
        shuffle($iconosAnimales);
        $iconosElegidos = array_slice($iconosAnimales, 0, 5);
    ?>
    <div class="perfil-saludo">
        <div class="perfil-saludo-iconos" aria-hidden="true">
            <?php foreach ($iconosElegidos as $i => $icono): ?>
            <span class="perfil-icono color-<?php echo ($i % 6) + 1; ?>"><?php echo $icono; ?></span>
            <?php endforeach; ?>
        </div>
        <h2 class="fuente_bouncy">¡Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>! 🎉</h2>
        <p class="perfil-saludo-lead">Aquí puedes ver el estado de todas tus reservas.</p>
    </div>

    <h3 class="fuente_bouncy text-center">Tus Reservas</h3>
    <hr>

    <?php if (empty($reservas)): ?>
        
        <div class="alert alert-info fs-5" role="alert">
            <i class="bi bi-info-circle-fill"></i> Aún no tienes ninguna reserva registrada.
            <a href="<?php echo URL_ROOT; ?>/reservas/paso1" class="alert-link">¡Haz tu primera reserva aquí!</a>
        </div>

    <?php else: ?>

        <div class="reservas-lista">
        <?php foreach($reservas as $reserva): ?>

            <?php
                $status_class = '';
                $status_text = '';
                switch ($reserva->estado) {
                    case 'pendiente':
                        $status_class = 'status-pendiente';
                        $status_text = 'Pendiente';
                        break;
                    case 'confirmada':
                        $status_class = 'status-confirmada';
                        $status_text = 'Confirmada';
                        break;
                    case 'cancelada':
                        $status_class = 'status-cancelada';
                        $status_text = 'Cancelada';
                        break;
                    default:
                        $status_class = 'status-pendiente';
                        $status_text = 'Pendiente';
                }
                $fechaObj = new DateTime($reserva->fecha . ' ' . $reserva->hora_inicio);
            ?>

            <div class="reserva-fila">
                <div class="reserva-fila-fecha">
                    <span class="dia"><?php echo $fechaObj->format('d'); ?></span>
                    <span class="mes"><?php echo $fechaObj->format('M'); ?></span>
                </div>
                <div class="reserva-fila-info">
                    <h5><?php echo htmlspecialchars($reserva->paquete_nombre); ?></h5>
                    <p class="reserva-fila-detalle">
                        <span><i class="bi bi-clock-fill"></i> <?php echo $fechaObj->format('h:i A'); ?></span>
                        <span><i class="bi bi-cake2-fill"></i> <?php echo htmlspecialchars($reserva->nombre_cumpleanero); ?> (cumple <?php echo (int) $reserva->edad_cumpleanero; ?>)</span>
                        <span><i class="bi bi-people-fill"></i> <?php echo (int) $reserva->cantidad_personas; ?> personas</span>
                    </p>
                </div>
                <div class="reserva-fila-estado">
                    <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>


    <div class="text-center mt-5">
        <a href="<?php echo URL_ROOT; ?>/usuarios/logout" class="btn-logout">
            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
        </a>
    </div>

</div>

<?php
// Cargar el footer estándar
require APP_ROOT . '/views/includes/footer.php';
?>