<?php

/*
 * VISTA DE LOGIN (CORREGIDA con atributos 'name' y manejo de errores)
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $datos['titulo']; ?></title>

    <link rel="icon" type="image/png" href="<?php echo URL_ROOT; ?>/img/logo_escupitajo-removebg-preview.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/login.css">
    
    <style>
        .invalid-feedback {
            display: block; /* Forzamos a que se muestre */
            color: #ff3c8d; /* Color rosa */
            font-weight: 600;
        }
        .is-invalid {
            border-color: #ff3c8d !important;
        }
    </style>
</head>

<body>

    <a href="<?php echo URL_ROOT; ?>" class="btn-back">
        <i class="bi bi-arrow-left-circle-fill"></i>
    </a>

    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="login-card">
            
            <img src="<?php echo URL_ROOT; ?>/img/logo_happy_contorno.png" class="top-logo" alt="Happy&Jumping Logo">
            <p class="title">Iniciar sesión</p>

            <form action="<?php echo URL_ROOT; ?>/usuarios/login" method="POST">
                
                <div class="mb-3">
                    <label for="correo">Correo electrónico</label>
                    <input type="email" id="correo" 
                           name="correo" class="form-control <?php echo (!empty($datos['correo_error'])) ? 'is-invalid' : ''; ?>" 
                           placeholder="Ingresa tu correo" 
                           value="<?php echo $datos['correo']; ?>" required>
                    <span class="invalid-feedback"><?php echo $datos['correo_error']; ?></span>
                </div>

                <div class="mb-3">
                    <label for="clave">Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="clave" 
                               name="password" class="form-control input-password <?php echo (!empty($datos['password_error'])) ? 'is-invalid' : ''; ?>" 
                               placeholder="Ingresa tu contraseña" 
                               value="<?php echo $datos['password']; ?>" required>
                        <button type="button" class="password-toggle-btn" id="togglePassword">
                            <i class="bi bi-eye-slash" id="toggleIcon"></i>
                        </button>
                    </div>
                    <span class="invalid-feedback"><?php echo $datos['password_error']; ?></span>
                </div>
                
                <button type="submit" class="btn-purple mt-3">Ingresar</button>
            </form>

            <p class="links mt-4">
                
                ¿No tienes cuenta? <a href="<?php echo URL_ROOT; ?>/usuarios/register">Crear cuenta</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('clave');
        const icon = document.getElementById('toggleIcon');

        if(togglePassword) {
            togglePassword.addEventListener('click', function () {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            });
        }
    </script>
</body>
</html>