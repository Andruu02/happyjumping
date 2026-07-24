<?php
/*
 * VISTA DE LOGIN DE ADMIN
 * (No usa header ni footer)
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Fredoka:wght@500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/admin_login.css?v=<?php echo filemtime(PUBLIC_ROOT . '/css/admin_login.css'); ?>">
</head>
<body>

    <div class="capa-color-fondo" aria-hidden="true"></div>

    <div class="login-card">
        <img src="<?php echo URL_ROOT; ?>/img/logo_happy_contorno.webp" alt="Logo" class="logo">
        <h2 class="fuente_bouncy">Acceso Administrador</h2>
        
        <form action="<?php echo URL_ROOT; ?>/admin/procesarLogin" method="POST">
            
            <div class="mb-3 text-start">
                <label for="correo" class="form-label fw-semibold">Correo Electrónico</label>
                <input type="email" class="form-control" id="correo" name="correo" value="<?php echo $correo; ?>" required>
            </div>
            
            <div class="mb-3 text-start">
                <label for="password" class="form-label fw-semibold">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger p-2">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <button type="submit" class="btn btn-login w-100 mt-3">Ingresar</button>
        </form>
    </div>

</body>
</html>