<?php
/*
 * VISTA RESERVA - ÉXITO
 * Mismo lenguaje visual que el resto del flujo de reserva (fondo con
 * destellos, tarjetas blancas, Fredoka, botones tipo píldora).
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?></title>
    <link rel="icon" type="image/png" href="<?php echo URL_ROOT; ?>/img/logo_escupitajo-removebg-preview.webp">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Fredoka:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/reserva.css?v=<?php echo filemtime(PUBLIC_ROOT . '/css/reserva.css'); ?>">
</head>
<body>

    <div class="reserva-fondo-wrapper">
        <div class="capa-color-fondo" aria-hidden="true"></div>

        <div class="exito-wrap">
            <div class="exito-card">
                <div class="exito-icono"><i class="bi bi-check-lg"></i></div>

                <h1 class="fuente_bouncy">¡Reserva Pendiente!</h1>

                <p class="exito-texto">
                    ¡Gracias por tu preferencia, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>!
                    Hemos recibido tu solicitud y la captura de pago.
                </p>
                <p class="exito-subtexto">Revisa tu perfil en las próximas horas para ver si tu pago ha sido aceptado.</p>

                <?php if (!empty($googleCalendarUrl)): ?>
                <a href="<?php echo $googleCalendarUrl; ?>" target="_blank" rel="noopener" class="btn-calendario">
                    <i class="bi bi-calendar-plus-fill"></i> Agregar a Google Calendar
                </a>
                <?php endif; ?>

                <div class="exito-acciones">
                    <a href="<?php echo URL_ROOT; ?>" class="btn-next">Volver al Inicio</a>
                    <a href="<?php echo URL_ROOT; ?>/perfil" class="btn-secundario">Ir a Mi Perfil</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
