<?php
/*
 * VISTA DE RECUPERAR CONTRASEÑA
 * Esta vista es independiente. No usa el header.php ni footer.php estándar.
 * (Estructura basada en login.php)
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $datos['titulo']; ?></title>
    
    <link rel="icon" type="image/png" href="<?php echo URL_ROOT; ?>/img/logo_escupitajo-removebg-preview.webp">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/login.css">
</head>

<body>

    <a href="<?php echo URL_ROOT; ?>" class="btn-back">
        <i class="bi bi-arrow-left-circle-fill"></i>
    </a>

    <div class="container d-flex justify-content-center align-items-center vh-100">
        
        <div class="recover-card">
            
            <img src="<?php echo URL_ROOT; ?>/img/logo_happy_contorno.webp" class="top-logo" alt="Happy&Jumping Logo">
            <p class="title">Recuperar contraseña</p>

            <form action="<?php echo URL_ROOT; ?>/usuarios/recover" method="POST">
                
                <div class="mb-3">
                    <label for="correo">Correo electrónico</label>
                    <input type="email" id="correo" name="correo" class="form-control" placeholder="Ingresa tu correo registrado" required>
                </div>

                <button type="submit" class="btn-purple mt-3">Enviar instrucciones</button>
            </form>

            <p class="links mt-4">
                ¿Recordaste tu contraseña? <a href="<?php echo URL_ROOT; ?>/usuarios/login">Inicia sesión</a><br>
                ¿No tienes cuenta? <a href="<?php echo URL_ROOT; ?>/usuarios/register">Crear cuenta</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>