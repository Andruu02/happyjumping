<?php
/*
 * VISTA RESERVA - PASO 2 (Detalles del Cumpleañero)
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

    <a href="<?php echo URL_ROOT; ?>/reservas/paso1" class="btn-back-reserva">
        <i class="bi bi-arrow-left-circle-fill"></i>
    </a>

    <div class="container">
        <div class="step-card">
            <h2>Paso 2: Detalles del Cumpleañero</h2>

            <div class="row g-4">
                
                <div class="col-lg-7">
                    <h3>Completa los datos</h3>
                    
                    <div class="mb-3">
                        <label for="nombre_cumpleanero" class="form-label">Nombre del Cumpleañero</label>
                        <input type="text" class="form-control" id="nombre_cumpleanero" placeholder="Ingresa el nombre">
                    </div>

                    <div class="mb-3">
                        <label for="edad_cumpleanero" class="form-label">Edad que cumple</label>
                        <input type="number" class="form-control" id="edad_cumpleanero" placeholder="Ej: 7" min="1">
                    </div>
                    
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones (Opcional)</label>
                        <textarea class="form-control" id="observaciones" rows="3" placeholder="Ej: Alergias, temática de fútbol, etc."></textarea>
                    </div>

                </div>

                <div class="col-lg-5">
                    <div class="resumen-box">
                        <h5>Resumen de tu Reserva</h5>
                        <p><strong>Paquete:</strong> <span id="resumen_paquete">Cargando...</span></p>
                        <p><strong>Cantidad:</strong> <span id="resumen_cantidad">Cargando...</span></p>
                        <p><strong>Extras:</strong> <span id="resumen_extras">Cargando...</span></p>
                        <p><strong>Fecha:</strong> <span id="resumen_fecha">Cargando...</span></p>
                        <p><strong>Hora:</strong> <span id="resumen_hora">Cargando...</span></p>
                        
                        <hr>
                        <div class="total-box" id="total">Total: S/0.00</div>
                    </div>
                </div>

            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-end align-items-center">
                <button class="btn-next" id="btnContinuar">Continuar al Paso 3 (Pago)</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // --- Elementos del DOM ---
        const totalEl = document.getElementById('total');
        const btnContinuar = document.getElementById('btnContinuar');
        const nombreInput = document.getElementById('nombre_cumpleanero');
        const edadInput = document.getElementById('edad_cumpleanero');
        const observacionesInput = document.getElementById('observaciones');
        
        // --- 1. LEER DATOS DEL PASO 1 ---
        // (Guardados desde el paso1.php)
        const reservaPaso1 = JSON.parse(sessionStorage.getItem('reservaPaso1Completa'));
        
        if (!reservaPaso1) {
            alert('Ocurrió un error. Por favor, selecciona tu paquete y fecha primero.');
            window.location.href = '<?php echo URL_ROOT; ?>/reservas/paso1';
        }

        // --- 2. POBLAR EL RESUMEN ---
        document.getElementById('resumen_paquete').textContent = reservaPaso1.paquete_nombre;
        document.getElementById('resumen_cantidad').textContent = reservaPaso1.cantidad + ' personas';
        
        const fechaObj = new Date(reservaPaso1.fecha + 'T00:00:00');
        document.getElementById('resumen_fecha').textContent = fechaObj.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' });
        
        const [hora, min] = reservaPaso1.hora_inicio.split(':');
        const horaObj = new Date(0, 0, 0, hora, min);
        document.getElementById('resumen_hora').textContent = horaObj.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: true });
        
        let extrasTexto = 'Ninguno';
        if (reservaPaso1.extra_pintura && reservaPaso1.extra_destruccion) {
            extrasTexto = 'Pintura y Destrucción';
        } else if (reservaPaso1.extra_pintura) {
            extrasTexto = 'Cuarto de Pintura';
        } else if (reservaPaso1.extra_destruccion) {
            extrasTexto = 'Cuarto de Destrucción';
        }
        document.getElementById('resumen_extras').textContent = extrasTexto;

        totalEl.textContent = `Total: S/${reservaPaso1.total_calculado.toFixed(2)}`;

        // --- 3. LÓGICA DE CONTINUAR ---
        btnContinuar.addEventListener('click', () => {
            const nombre = nombreInput.value.trim();
            const edad = edadInput.value;

            if (nombre === '') {
                alert("Por favor, ingresa el nombre del cumpleañero.");
                nombreInput.focus();
                return;
            }
            if (edad === '' || parseInt(edad) < 1) {
                alert("Por favor, ingresa una edad válida.");
                edadInput.focus();
                return;
            }

            const reservaCompleta = {
                ...reservaPaso1,
                nombre_cumpleanero: nombre,
                edad_cumpleanero: parseInt(edad),
                observaciones: observacionesInput.value.trim()
            };

            // Guardamos todo para el Paso 3 (Pago)
            sessionStorage.setItem('reservaFinal', JSON.stringify(reservaCompleta));
            sessionStorage.removeItem('reservaPaso1Completa'); // Limpiamos la anterior
            
            window.location.href = '<?php echo URL_ROOT; ?>/reservas/paso3';
        });
        
    </script>
</body>
</html>