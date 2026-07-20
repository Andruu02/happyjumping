<?php
/*
 * VISTA RESERVA - ÉXITO
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/reserva.css">
    <style>
        .success-card {
            text-align: center;
            padding: 50px 30px;
        }
        .success-icon {
            font-size: 6rem;
            color: var(--verde); /* (Variable de tu CSS original) */
            line-height: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="step-card success-card">
            <div class="success-icon mb-3">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h2 class="text-success">¡Reserva Pendiente!</h2>
            <p class="fs-5" style="color: #555;">
                ¡Gracias por su preferencia, <?php echo $_SESSION['usuario_nombre']; ?>!
                <br>
                Hemos recibido tu solicitud y la captura de pago.
            </p>
            <p class="lead">
                Revisa tu perfil en las próximas horas para ver si tu pago ha sido aceptado.
            </p>
            <hr class="my-4">
            <a href="<?php echo URL_ROOT; ?>" class="btn-next">Volver al Inicio</a>
            <a href="<?php echo URL_ROOT; ?>/perfil" class="btn btn-outline-primary btn-lg mt-3">Ir a Mi Perfil</a>
        </div>
    </div>
</body>
</html>