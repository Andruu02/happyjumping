<?php
/*
 * VISTA RESERVA - PASO 3 (Pago y Subida)
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
</head>
<body>

    <a href="<?php echo URL_ROOT; ?>/reservas/paso2" class="btn-back-reserva">
        <i class="bi bi-arrow-left-circle-fill"></i>
    </a>

    <div class="container">
        <div class="step-card">
            <h2>Paso 3: Realiza tu Pago</h2>

            <div class="row g-4">
                
                <div class="col-lg-6">
                    <div class="qr-code-wrapper">
                        <h4>Monto a Pagar: <span id="monto_pagar">S/0.00</span></h4>
                        <img src="<?php echo URL_ROOT; ?>/img/yape_qr.webp" alt="Código QR de Yape">
                        <p class="mt-3">
                            Escanea el código para pagar.
                            <br>
                            <strong>¡Importante!</strong> Guarda la captura de tu pago.
                        </p>
                    </div>
                </div>

                <div class="col-lg-6">
                    <h3>Sube tu Captura de Pago</h3>
                    
                    <form action="<?php echo URL_ROOT; ?>/reservas/finalizar" method="POST" enctype="multipart/form-data" id="form-finalizar">
                        
                        <div class="upload-box">
                            <label for="captura_pago" class="form-label">1. Adjunta tu captura (JPG, PNG, PDF)</label>
                            <input class="form-control" type="file" id="captura_pago" name="captura_pago" accept="image/png, image/jpeg, application/pdf" required>
                        </div>
                        
                        <input type="hidden" name="reserva_data" id="reserva_data_input">
                        
                        <hr class="my-4">
                        
                        <p class="text-muted small">Al hacer clic en "Finalizar", tu reserva quedará en estado "Pendiente" hasta que un administrador verifique tu pago.</p>
                        
                        <button type="submit" class="btn-next w-100" id="btnFinalizar">
                            Finalizar Reserva
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // --- 1. LEER DATOS DEL PASO 2 ---
        const reservaFinal = JSON.parse(sessionStorage.getItem('reservaFinal'));
        
        if (!reservaFinal) {
            alert('Ocurrió un error. Tus datos de reserva se perdieron.');
            window.location.href = '<?php echo URL_ROOT; ?>/reservas/paso1';
        }

        // --- 2. MOSTRAR MONTO A PAGAR ---
        document.getElementById('monto_pagar').textContent = `S/${reservaFinal.total_calculado.toFixed(2)}`;

        // --- 3. PREPARAR EL FORMULARIO PARA ENVÍO ---
        const form = document.getElementById('form-finalizar');
        const hiddenInput = document.getElementById('reserva_data_input');
        const fileInput = document.getElementById('captura_pago');

        form.addEventListener('submit', function(e) {
            // Validación de archivo
            if (fileInput.files.length === 0) {
                e.preventDefault(); // Detener el envío
                alert('Por favor, adjunta la captura de pantalla de tu pago.');
                return;
            }
            
            // ¡Clave! Inyectamos los datos de la sesión en el input oculto
            hiddenInput.value = JSON.stringify(reservaFinal);
            
            // Limpiamos la sesión para que no se pueda reservar 2 veces
            sessionStorage.removeItem('reservaFinal');
            sessionStorage.removeItem('reservaPaso1Completa');
            
            // El formulario se envía...
        });
        
    </script>
</body>
</html>