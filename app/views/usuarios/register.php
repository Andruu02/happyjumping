<?php
/*
 * VISTA DE REGISTRO
 * (CORREGIDA con validación HTML + Errores PHP)
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
            display: block !important; /* Fuerza a que el mensaje se muestre */
            color: var(--rosa) !important; /* Color de error */
            font-weight: 600;
            text-align: left;
            font-size: 0.9rem;
        }
        .is-invalid {
            border-color: var(--rosa) !important; /* Borde rosa */
            box-shadow: 0 0 10px rgba(255, 60, 141, 0.3) !important;
        }
    </style>
</head>

<body>

    <a href="<?php echo URL_ROOT; ?>" class="btn-back">
        <i class="bi bi-arrow-left-circle-fill"></i>
    </a>

    <div class="container d-flex justify-content-center align-items-center min-vh-100 py-4">
        
        <div class="register-card">
            
            <img src="<?php echo URL_ROOT; ?>/img/logo_happy_contorno.png" class="top-logo" alt="Happy&Jumping Logo">
            <p class="title">Crear cuenta</p>

            <form action="<?php echo URL_ROOT; ?>/usuarios/register" method="POST" novalidate> <div class="mb-3">
                    <label for="dni">DNI</label>
                    <div class="d-flex gap-2">
                        <input type="text" id="dni" name="dni" inputmode="numeric" maxlength="8"
                               class="form-control <?php echo (!empty($datos['dni_error'])) ? 'is-invalid' : ''; ?>"
                               placeholder="Ingresa tu DNI (8 dígitos)"
                               value="<?php echo $datos['dni']; ?>" required>
                        <button type="button" id="btn-verificar-dni" class="btn-purple" style="width:auto;padding:0 18px;white-space:nowrap;">
                            Verificar <i class="bi bi-arrow-right-circle-fill"></i>
                        </button>
                    </div>
                    <span class="invalid-feedback" id="dni-feedback"><?php echo $datos['dni_error']; ?></span>
                    <small id="dni-status" style="display:block;margin-top:4px;"></small>
                </div>

                <div class="mb-3">
                    <label for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" readonly
                           class="form-control <?php echo (!empty($datos['nombre_error'])) ? 'is-invalid' : ''; ?>"
                           style="background-color:#f0f0f0;"
                           placeholder="Se autocompleta al verificar tu DNI"
                           value="<?php echo $datos['nombre']; ?>" required>
                    <span class="invalid-feedback"><?php echo $datos['nombre_error']; ?></span>
                </div>

                <div class="mb-3">
                    <label for="correo">Correo electrónico</label>
                    <input type="email" id="correo" name="correo" 
                           class="form-control <?php echo (!empty($datos['correo_error'])) ? 'is-invalid' : ''; ?>" 
                           placeholder="Ingresa tu correo" 
                           value="<?php echo $datos['correo']; ?>" required>
                    <span class="invalid-feedback"><?php echo $datos['correo_error']; ?></span>
                </div>

                <div class="mb-3">
                    <label for="clave">Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="clave" name="password" 
                               class="form-control input-password <?php echo (!empty($datos['password_error'])) ? 'is-invalid' : ''; ?>" 
                               placeholder="Mínimo 8 caracteres" 
                               value="<?php echo $datos['password']; ?>" required minlength="8">
                        <button type="button" class="password-toggle-btn" id="toggleClave">
                            <i class="bi bi-eye-slash" id="toggleIconClave"></i>
                        </button>
                    </div>
                    <span class="invalid-feedback"><?php echo $datos['password_error']; ?></span>
                </div>

                <div class="mb-3">
                    <label for="confirmar">Confirmar contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirmar" name="confirm_password" 
                               class="form-control input-password <?php echo (!empty($datos['confirm_password_error'])) ? 'is-invalid' : ''; ?>" 
                               placeholder="Repite tu contraseña" 
                               value="<?php echo $datos['confirm_password']; ?>" required minlength="8">
                        <button type="button" class="password-toggle-btn" id="toggleConfirmar">
                            <i class="bi bi-eye-slash" id="toggleIconConfirmar"></i>
                        </button>
                    </div>
                    <span class="invalid-feedback"><?php echo $datos['confirm_password_error']; ?></span>
                </div>

                <button type="submit" class="btn-purple mt-3">Registrarse</button>
            </form>

            <p class="links mt-4">
                ¿Ya tienes una cuenta? <a href="<?php echo URL_ROOT; ?>/usuarios/login">Inicia sesión aquí</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función genérica para manejar un toggle
        function setupPasswordToggle(toggleId, passwordId, iconId) {
            const toggleButton = document.getElementById(toggleId);
            const passwordInput = document.getElementById(passwordId);
            const icon = document.getElementById(iconId);

            if (toggleButton) {
                toggleButton.addEventListener('click', function () {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    icon.classList.toggle('bi-eye');
                    icon.classList.toggle('bi-eye-slash');
                });
            }
        }
        setupPasswordToggle('toggleClave', 'clave', 'toggleIconClave');
        setupPasswordToggle('toggleConfirmar', 'confirmar', 'toggleIconConfirmar');

        // Autocompletar nombre a partir del DNI (RENIEC vía APIsPERU)
        const dniInput      = document.getElementById('dni');
        const nombreInput   = document.getElementById('nombre');
        const dniStatus     = document.getElementById('dni-status');
        const dniFeedback   = document.getElementById('dni-feedback');
        const btnVerificar  = document.getElementById('btn-verificar-dni');

        dniInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 8);
            this.classList.remove('is-invalid');
            dniFeedback.textContent = '';
            dniStatus.textContent = '';
            nombreInput.value = '';
        });

        // Permite verificar con Enter sin enviar el formulario
        dniInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                verificarDni();
            }
        });

        btnVerificar.addEventListener('click', verificarDni);

        document.querySelector('form').addEventListener('submit', function(e) {
            if (!nombreInput.value.trim()) {
                e.preventDefault();
                dniStatus.style.color = '#c0392b';
                dniStatus.textContent = 'Verifica tu DNI antes de registrarte.';
                dniInput.focus();
            }
        });

        function verificarDni() {
            const dni = dniInput.value.trim();

            if (dni.length !== 8) {
                dniStatus.style.color = '#c0392b';
                dniStatus.textContent = 'El DNI debe tener 8 dígitos.';
                return;
            }

            dniStatus.style.color = '#888';
            dniStatus.textContent = 'Buscando...';
            btnVerificar.disabled = true;
            nombreInput.value = '';

            fetch(`<?php echo URL_ROOT; ?>/api/dni/${dni}`)
                .then(res => res.json())
                .then(json => {
                    if (json.success && json.data) {
                        const nombreCompleto = [
                            json.data.nombres,
                            json.data.apellidoPaterno,
                            json.data.apellidoMaterno
                        ].filter(Boolean).join(' ');
                        nombreInput.value = nombreCompleto;
                        dniStatus.style.color = 'green';
                        dniStatus.textContent = 'DNI verificado.';
                    } else {
                        dniStatus.style.color = '#c0392b';
                        dniStatus.textContent = 'No se pudo verificar el DNI. Revisa el número e inténtalo de nuevo.';
                    }
                })
                .catch(() => {
                    dniStatus.style.color = '#c0392b';
                    dniStatus.textContent = 'No se pudo verificar el DNI. Revisa el número e inténtalo de nuevo.';
                })
                .finally(() => {
                    btnVerificar.disabled = false;
                });
        }
    </script>
</body>
</html>