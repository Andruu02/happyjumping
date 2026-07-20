<?php
/*
 * VISTA RESERVA - PASO 1 (VERSIÓN TODO-EN-UNO)
 * ¡Corregido el cálculo de extras (ya no es por persona)!
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

    <?php $paquete_preseleccionado = isset($_GET['paquete']) ? (int)$_GET['paquete'] : 0; ?>

    <a href="<?php echo URL_ROOT; ?>/#cumpleanos" class="btn-back-reserva">
        <i class="bi bi-arrow-left-circle-fill"></i>
    </a>

    <div class="container">
        <div class="step-card">
            <h2>Paso 1: Completa tu Reserva</h2>

            <h5 class="fw-semibold">1. Selecciona tu paquete</h5>
            <div class="accordion mb-4" id="paquetesAccordion">

                <?php foreach($paquetes as $paquete): ?>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button<?php echo ($paquete_preseleccionado == $paquete->id_paquete) ? '' : ' collapsed'; ?>" type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#paquete-<?php echo $paquete->id_paquete; ?>">
                            
                            <input type="radio" name="paquete" class="package-select"
                                   id="paquete_id_<?php echo $paquete->id_paquete; ?>"
                                   value="<?php echo $paquete->precio_semana; ?>" 
                                   data-precio-finde="<?php echo $paquete->precio_fin_semana; ?>" 
                                   data-nombre="<?php echo $paquete->nombre; ?>" 
                                   data-duracion="<?php echo $paquete->duracion; ?>">
                            
                            <span class="ms-2"> 
                                <?php echo $paquete->nombre; ?> — 
                                S/<?php echo number_format($paquete->precio_semana, 2); ?> (L-V) | 
                                S/<?php echo number_format($paquete->precio_fin_semana, 2); ?> (S-D)
                            </span>
                        </button>
                    </h2>
                    <div id="paquete-<?php echo $paquete->id_paquete; ?>" class="accordion-collapse collapse<?php echo ($paquete_preseleccionado == $paquete->id_paquete) ? ' show' : ''; ?>" data-bs-parent="#paquetesAccordion">
                        <div class="accordion-body">
                            <?php if ($paquete->id_paquete == 1): ?>
                                <ul>
                                    <li>2 horas de uso del local.</li>
                                    <li>Pulsera de 1 hora en camas saltarinas.</li>
                                    <li>Dinámicas con premios a cargo de nuestras anfitrionas.</li>
                                </ul>
                            <?php elseif ($paquete->id_paquete == 2): ?>
                                <ul>
                                    <li>2 horas y media de diversión total.</li>
                                    <li>Pulsera de 1 hora en trampolines.</li>
                                    <li>Glitter Bar y tatuajes neón durante 1 hora.</li>
                                    <li>Combo Happy: Popcorn + agua mineral.</li>
                                </ul>
                            <?php elseif ($paquete->id_paquete == 3): ?>
                                <ul>
                                    <li>3 horas de uso completo del local.</li>
                                    <li>1 hora y media de trampolines, pared de escalar y tirolesa.</li>
                                    <li>Maquillaje neón y Glitter.</li>
                                    <li>Combo Happy: Popcorn + bebida (frugos, agua o gaseosa).</li>
                                </ul>
                            <?php elseif ($paquete->id_paquete == 4): ?>
                                <ul>
                                    <li>5 horas de uso exclusivo del local.</li>
                                    <li>Acceso a trampolines, tirolesa y pared de escalar.</li>
                                    <li>Dinámicas con premios y muñecos inflables.</li>
                                    <li>Combo Happy: Popcorn + bebida + pan con hotdog.</li>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
            </div> <div class="row">
                <div class="col-md-6">
                    <h5 class="fw-semibold">2. Extras (S/15 c/u por reserva)</h5>
                    <div class="mb-3">
                        <label><input type="checkbox" class="experience-select" value="15" id="pintura"> Cuarto de Pintura</label><br>
                        <label><input type="checkbox" class="experience-select" value="15" id="destruccion"> Cuarto de Destrucción</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <h5 class="fw-semibold">3. Cantidad de personas</h5>
                    <div class="mb-4">
                        <label for="cantidad" class="form-label visually-hidden">Cantidad de personas:</label>
                        <input type="number" id="cantidad" class="form-control" min="10" max="30" value="10" style="max-width:150px;">
                    </div>
                </div>
            </div>

            <hr class="my-4">
            <h5 class="mb-3 fw-semibold">4. Selecciona Fecha y Hora de Inicio</h5>
            <div class="row g-3">
                <div class="col-lg-7">
                    <div class="calendar-wrapper">
                        <div class="calendar-header">
                            <button id="prev-month" class="calendar-nav"><i class="bi bi-chevron-left"></i></button>
                            <div class="month-year" id="month-year"></div>
                            <button id="next-month" class="calendar-nav"><i class="bi bi-chevron-right"></i></button>
                        </div>
                        <div class="calendar-grid"></div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="time-selector-wrapper">
                        <h5>Hora de Inicio</h5>
                        <p class="text-muted" style="font-size: 0.9rem;">El evento durará <strong id="duracion-paquete">X</strong> horas. (Horario: 3:00 PM - 11:00 PM)</p>
                        
                        <select class="form-select" id="hora-inicio-select" disabled>
                            <option value="">Selecciona un horario</option>
                            <option value="15:00:00">3:00 PM</option>
                            <option value="16:00:00">4:00 PM</option>
                            <option value="17:00:00">5:00 PM</option>
                            <option value="18:00:00">6:00 PM</option>
                            <option value="19:00:00">7:00 PM</option>
                            <option value="20:00:00">8:00 PM</option>
                            <option value="21:00:00">9:00 PM</option>
                            <option value="22:00:00">10:00 PM</option>
                        </select>
                        <small id="hora-fin-calculada" class="text-primary fw-bold mt-2 d-block"></small>
                    </div>
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="total-box" id="total">Total: S/0.00</div>
                <button class="btn-next" id="btnContinuar">Continuar al Paso 2</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // --- Elementos del DOM ---
        const paquetes = document.querySelectorAll('input[name="paquete"]');
        const experiencias = document.querySelectorAll('.experience-select');
        const cantidadInput = document.getElementById('cantidad');
        const totalEl = document.getElementById('total');
        const btnContinuar = document.getElementById('btnContinuar');
        const calendarGrid = document.querySelector('.calendar-grid');
        const monthYearEl = document.getElementById('month-year');
        const prevMonthBtn = document.getElementById('prev-month');
        const nextMonthBtn = document.getElementById('next-month');
        const horaInicioSelect = document.getElementById('hora-inicio-select');
        const duracionPaqueteEl = document.getElementById('duracion-paquete');
        const horaFinCalculadaEl = document.getElementById('hora-fin-calculada');
        
        let total = 0;
        let currentDate = new Date();
        currentDate.setDate(1); 
        let selectedDate = null;
        let fechasOcupadas = []; 
        const today = new Date();
        today.setHours(0, 0, 0, 0); 
        
        // --- 1. LÓGICA DEL CALENDARIO (Sin cambios) ---
        // Filtra horas ya pasadas si el dia seleccionado es hoy
        function filtrarHorasPorFecha(dateStr) {
            const hoy = new Date();
            const seleccionada = new Date(dateStr + 'T00:00:00');
            const esHoy = (
                seleccionada.getFullYear() === hoy.getFullYear() &&
                seleccionada.getMonth()    === hoy.getMonth()    &&
                seleccionada.getDate()     === hoy.getDate()
            );

            const horaActualMinutos = hoy.getHours() * 60 + hoy.getMinutes();

            for (let i = 1; i < horaInicioSelect.options.length; i++) {
                const opt = horaInicioSelect.options[i];
                const [h, m] = opt.value.split(':').map(Number);
                const optMinutos = h * 60 + m;

                if (esHoy && optMinutos <= horaActualMinutos) {
                    opt.disabled = true;
                    opt.style.display = 'none';
                } else {
                    // No tocar disabled aqui, lo maneja filtrarHorasPorDuracion
                    opt.style.display = '';
                }
            }
        }

        async function renderCalendar() {
            const month = currentDate.getMonth() + 1; 
            const year = currentDate.getFullYear();
            
            monthYearEl.textContent = new Date(year, month - 1).toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });
            calendarGrid.innerHTML = `<div class="weekday">L</div><div class="weekday">M</div><div class="weekday">X</div><div class="weekday">J</div><div class="weekday">V</div><div class="weekday">S</div><div class="weekday">D</div>`;
            
            await fetchFechasOcupadas(year, month); // Carga los días rojos

            const firstDayOfMonth = new Date(year, month - 1, 1).getDay();
            const daysInMonth = new Date(year, month, 0).getDate();
            let startingDay = (firstDayOfMonth === 0) ? 6 : firstDayOfMonth - 1;

            for (let i = 0; i < startingDay; i++) {
                calendarGrid.innerHTML += `<div class="day empty"></div>`;
            }
            for (let day = 1; day <= daysInMonth; day++) {
                const dayDate = new Date(year, month - 1, day);
                const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                
                let classes = 'day';
                if (dayDate < today) {
                    classes += ' empty'; 
                } else if (fechasOcupadas.includes(dateStr)) {
                    classes += ' occupied'; // Día rojo
                }
                
                if (selectedDate && dayDate.getTime() === selectedDate.getTime()) {
                    classes += ' selected';
                }
                
                calendarGrid.innerHTML += `<div class="${classes}" data-date="${dateStr}">${day}</div>`;
            }
        }
        prevMonthBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });
        nextMonthBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });
        calendarGrid.addEventListener('click', (e) => {
            if (e.target.classList.contains('day') && !e.target.classList.contains('empty') && !e.target.classList.contains('occupied')) {
                const dateStr = e.target.dataset.date;
                selectedDate = new Date(dateStr + 'T00:00:00'); 
                
                document.querySelectorAll('.day.selected').forEach(d => d.classList.remove('selected'));
                e.target.classList.add('selected');
                
                horaInicioSelect.disabled = false;
                horaInicioSelect.value = "";
                horaFinCalculadaEl.textContent = "";
                calcularTotal(); 
            }
        });
        
        // --- 2. LÓGICA DE HORARIOS (AJAX) (Sin cambios) ---
        async function fetchFechasOcupadas(ano, mes) {
            try {
                const response = await fetch(`<?php echo URL_ROOT; ?>/reservas/getFechasOcupadas/${ano}/${mes}`);
                fechasOcupadas = await response.json();
            } catch (error) {
                console.error('Error fetching fechas ocupadas:', error);
                fechasOcupadas = [];
            }
        }

        // --- 3. LÓGICA DE CÁLCULO (¡MODIFICADA!) ---
        function esFinDeSemana(fecha) {
            const dia = fecha.getUTCDay(); 
            return dia === 0 || dia === 6;
        }
        
        function calcularTotal() {
            let totalBase = 0;
            let extras = 0;
            const cantidad = parseInt(cantidadInput.value) || 0;
            const paqueteSeleccionado = document.querySelector('input[name="paquete"]:checked');
            
            if (paqueteSeleccionado) {
                let precio = parseFloat(paqueteSeleccionado.value);
                if (selectedDate && esFinDeSemana(selectedDate)) {
                    precio = parseFloat(paqueteSeleccionado.dataset.precioFinde);
                }
                totalBase = precio * cantidad;
            }

            /*
             * ======================================================
             * ¡AQUÍ ESTÁ EL CAMBIO!
             * Ya no multiplicamos por 'cantidad'
             * ======================================================
             */
            experiencias.forEach(e => {
                if (e.checked) {
                    extras += parseFloat(e.value); // Solo suma 15 (o el valor)
                }
            });
            // ======================================================

            total = totalBase + extras;
            totalEl.textContent = `Total: S/${total.toFixed(2)}`;
        }
        
        // --- 4. LÓGICA DE EVENTOS (¡MODIFICADA!) ---
        
        // (La función filtrarHorasPorDuracion y HORA_CIERRE_MINUTOS permanecen igual)
        const HORA_CIERRE_MINUTOS = 23 * 60; // 11:00 PM = 1380 minutos

        function filtrarHorasPorDuracion(duracionMinutosStr) {
            const duracion = parseInt(duracionMinutosStr);
            if (isNaN(duracion)) return; 

            let duracionHoras = (duracion / 60).toFixed(1);
            if (duracionHoras.endsWith('.0')) {
                duracionHoras = duracionHoras.substring(0, duracionHoras.length - 2);
            }
            duracionPaqueteEl.textContent = `${duracionHoras} horas`;

            for (let i = 1; i < horaInicioSelect.options.length; i++) {
                const option = horaInicioSelect.options[i];
                const [horas, minutos] = option.value.split(':').map(Number);
                const horaInicioMinutos = (horas * 60) + minutos;
                const horaFinMinutos = horaInicioMinutos + duracion;

                if (horaFinMinutos > HORA_CIERRE_MINUTOS) {
                    option.style.display = 'none'; 
                    option.disabled = true;
                } else {
                    option.style.display = 'block'; 
                    option.disabled = false;
                }
            }
            
            if (horaInicioSelect.options[horaInicioSelect.selectedIndex]?.disabled) {
                 horaInicioSelect.value = "";
                 horaFinCalculadaEl.textContent = "";
            }
        }
        
        // --- LISTENER DE EVENTOS ---
        
        paquetes.forEach(p => {
            p.addEventListener('change', (e) => {
                calcularTotal(); 
                filtrarHorasPorDuracion(e.target.dataset.duracion);
            });
            
            p.closest('.accordion-header').querySelector('button').addEventListener('click', (e) => {
                p.checked = true;
                calcularTotal(); 
                filtrarHorasPorDuracion(p.dataset.duracion);
            });
        });
        
        // ¡CAMBIO! Ahora 'cantidad' también recalcula el total (por si acaso)
        experiencias.forEach(e => e.addEventListener('change', calcularTotal));
        cantidadInput.addEventListener('input', () => {
            if (cantidadInput.value < 10) cantidadInput.value = 10;
            calcularTotal(); // Recalcula el total cuando cambia la cantidad
        });

        // Evento para el selector de hora (calcula la hora final)
        horaInicioSelect.addEventListener('change', () => {
            const paqueteSeleccionado = document.querySelector('input[name="paquete"]:checked');
            if (!selectedDate || !horaInicioSelect.value || !paqueteSeleccionado) {
                horaFinCalculadaEl.textContent = "";
                return;
            }
            
            const duracionMinutos = parseInt(paqueteSeleccionado.dataset.duracion);
            const [horas, minutos] = horaInicioSelect.value.split(':').map(Number);
            
            const fechaInicio = new Date(selectedDate);
            fechaInicio.setHours(horas, minutos);
            
            const fechaFin = new Date(fechaInicio.getTime() + duracionMinutos * 60000);
            
            const horaFinStr = fechaFin.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: true });
            
            if (fechaFin.getHours() >= 23 && fechaFin.getMinutes() > 0 || fechaFin.getHours() < horas) {
                 horaFinCalculadaEl.textContent = `Termina ${horaFinStr} (¡Pasa las 11 PM!)`;
                 horaFinCalculadaEl.style.color = 'red';
            } else {
                 horaFinCalculadaEl.textContent = `Tu evento terminará aprox. a las ${horaFinStr}`;
                 horaFinCalculadaEl.style.color = 'var(--morado)';
            }
        });
        
        // --- 5. LÓGICA DE CONTINUAR (Sin cambios) ---
        btnContinuar.addEventListener('click', () => {
            const paquete = document.querySelector('input[name="paquete"]:checked');
            const horaInicio = horaInicioSelect.value;

            if (!paquete) {
                alert("Por favor, selecciona un paquete."); return;
            }
            if (!selectedDate) {
                alert("Selecciona la fecha de tu evento."); return;
            }
            if (!horaInicio) {
                alert("Selecciona una hora de inicio."); return;
            }
            if (cantidadInput.value < 10 || cantidadInput.value > 30) {
                 alert("La cantidad debe ser entre 10 y 30 personas.");
                 if (cantidadInput.value < 10) cantidadInput.value = 10;
                 if (cantidadInput.value > 30) cantidadInput.value = 30;
                 calcularTotal();
                 return;
            }
            
            const seleccion = {
                id_paquete: paquete.id.replace('paquete_id_', ''), 
                paquete_nombre: paquete.dataset.nombre,
                cantidad: parseInt(cantidadInput.value),
                extra_pintura: document.getElementById('pintura').checked,
                extra_destruccion: document.getElementById('destruccion').checked,
                total_calculado: total,
                fecha: selectedDate.toISOString().split('T')[0],
                hora_inicio: horaInicio,
                duracion_minutos: parseInt(paquete.dataset.duracion)
            };
            
            sessionStorage.setItem('reservaPaso1Completa', JSON.stringify(seleccion));
            window.location.href = '<?php echo URL_ROOT; ?>/reservas/paso2';
        });

        // Inicializar
        const paquetePreseleccionado = <?php echo $paquete_preseleccionado; ?>;

        paquetes.forEach(p => {
            const idPaquete = parseInt(p.id.replace('paquete_id_', ''));
            if (paquetePreseleccionado > 0 && idPaquete === paquetePreseleccionado) {
                p.checked = true;
                filtrarHorasPorDuracion(p.dataset.duracion);
                calcularTotal();
            } else if (paquetePreseleccionado === 0) {
                p.checked = false;
            }
        });

        renderCalendar();
    </script>
</body>
</html>