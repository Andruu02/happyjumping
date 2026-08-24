<?php
/*
 * VISTA: Recuperar contraseña - Paso 2 (código + nueva contraseña)
 * Mismo patrón visual que usuarios/verificar.php.
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
    <style>
        .digit-boxes {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 20px 0;
        }
        .digit-box {
            width: 52px;
            height: 64px;
            border: 2px solid #d0b3ff;
            border-radius: 12px;
            font-size: 1.8rem;
            font-weight: 700;
            color: #7F00FF;
            text-align: center;
            background: #faf5ff;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        .digit-box:focus {
            border-color: #7F00FF;
            box-shadow: 0 0 0 3px rgba(127,0,255,.15);
            background: #fff;
        }
        .digit-box.filled { border-color: #7F00FF; background: #f3e5ff; }
        .mail-icon {
            width: 72px; height: 72px;
            background: linear-gradient(135deg,#7F00FF,#ff3c8d);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
        }
        .mail-icon i { font-size: 2rem; color: #fff; }
        #contador { color: #7F00FF; font-weight: 700; }
        #codigo-hidden { position: absolute; opacity: 0; pointer-events: none; }
    </style>
</head>
<body>
    <a href="<?php echo URL_ROOT; ?>/usuarios/login" class="btn-back">
        <i class="bi bi-arrow-left-circle-fill"></i>
    </a>

    <div class="container d-flex justify-content-center align-items-center min-vh-100 py-4">
        <div class="register-card text-center" style="max-width:440px;">

            <div class="mail-icon">
                <i class="bi bi-key-fill"></i>
            </div>

            <p class="title" style="font-size:1.6rem;">Recupera tu contraseña</p>
            <p class="text-muted mb-1">
                Si <strong><?php echo htmlspecialchars($datos['correo']); ?></strong> está registrado, te enviamos un código de 6 dígitos.
            </p>

            <?php if (!empty($datos['exito'])): ?>
                <div class="alert alert-success py-2 mb-3 mt-3" style="border-radius:10px;font-size:.9rem;">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    <?php echo htmlspecialchars($datos['exito']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($datos['error'])): ?>
                <div class="alert alert-danger py-2 mb-3 mt-3" style="border-radius:10px;font-size:.9rem;">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <?php echo htmlspecialchars($datos['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Formulario principal: código + nueva contraseña -->
            <form action="<?php echo URL_ROOT; ?>/usuarios/recover-codigo" method="POST" id="form-reset" class="text-start">
                <div class="digit-boxes">
                    <input class="digit-box" type="text" inputmode="numeric" maxlength="1" data-idx="0">
                    <input class="digit-box" type="text" inputmode="numeric" maxlength="1" data-idx="1">
                    <input class="digit-box" type="text" inputmode="numeric" maxlength="1" data-idx="2">
                    <input class="digit-box" type="text" inputmode="numeric" maxlength="1" data-idx="3">
                    <input class="digit-box" type="text" inputmode="numeric" maxlength="1" data-idx="4">
                    <input class="digit-box" type="text" inputmode="numeric" maxlength="1" data-idx="5">
                </div>
                <input type="text" id="codigo-hidden" name="codigo" maxlength="6">

                <div class="mb-3">
                    <label for="password">Nueva contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Mínimo 8 caracteres" minlength="8" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password">Confirma la nueva contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repite la contraseña" minlength="8" required>
                </div>

                <button type="submit" class="btn-purple mt-2 w-100">
                    <i class="bi bi-shield-check me-2"></i>Cambiar contraseña
                </button>
            </form>

            <hr class="my-4" style="border-color:#eee;">

            <p class="text-muted mb-2" style="font-size:.9rem;">
                ¿No llegó el correo? Revisa tu carpeta de <strong>Spam</strong>.
            </p>
            <p class="text-muted mb-3" style="font-size:.9rem;">
                Podrás reenviar en <span id="contador">60</span>s
            </p>

            <form action="<?php echo URL_ROOT; ?>/usuarios/recover-codigo" method="POST" id="form-reenviar">
                <input type="hidden" name="reenviar" value="1">
                <button type="submit" class="btn btn-outline-secondary w-100"
                        id="btn-reenviar" disabled
                        style="border-radius:12px;font-weight:600;">
                    <i class="bi bi-arrow-clockwise me-2"></i>Reenviar código
                </button>
            </form>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function () {
        const boxes    = document.querySelectorAll('.digit-box');
        const hidden   = document.getElementById('codigo-hidden');
        const btnReenv = document.getElementById('btn-reenviar');
        const counter  = document.getElementById('contador');

        boxes.forEach((box, i) => {
            box.addEventListener('input', e => {
                const val = e.target.value.replace(/\D/, '');
                box.value = val;
                box.classList.toggle('filled', val !== '');
                if (val && i < 5) boxes[i + 1].focus();
                actualizarHidden();
            });
            box.addEventListener('keydown', e => {
                if (e.key === 'Backspace' && !box.value && i > 0) {
                    boxes[i - 1].value = '';
                    boxes[i - 1].classList.remove('filled');
                    boxes[i - 1].focus();
                    actualizarHidden();
                }
            });
            box.addEventListener('paste', e => {
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'');
                [...text.slice(0,6)].forEach((ch, j) => {
                    if (boxes[j]) {
                        boxes[j].value = ch;
                        boxes[j].classList.add('filled');
                    }
                });
                actualizarHidden();
                if (boxes[Math.min(text.length, 5)]) boxes[Math.min(text.length, 5)].focus();
            });
        });

        function actualizarHidden() {
            hidden.value = [...boxes].map(b => b.value).join('');
        }

        let seg = 60;
        const timer = setInterval(() => {
            seg--;
            counter.textContent = seg;
            if (seg <= 0) {
                clearInterval(timer);
                counter.parentElement.innerHTML = 'Ya puedes solicitar un nuevo código.';
                btnReenv.disabled = false;
            }
        }, 1000);

        boxes[0].focus();
    })();
    </script>
</body>
</html>
