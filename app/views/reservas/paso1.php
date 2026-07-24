<?php
/*
 * VISTA RESERVA - FLUJO COMPLETO EN UNA SOLA PÁGINA
 * (Paso 1: Paquete/Fecha, Paso 2: Detalles, Paso 3: Pago)
 *
 * Los 3 pasos viven en el mismo documento, uno debajo del otro, y NO están
 * bloqueados entre sí: el usuario puede rellenarlos en el orden que quiera
 * mientras baja normalmente por la página. Todos los campos son
 * obligatorios (excepto "observaciones") y se validan recién al presionar
 * "Finalizar Reserva" al final.
 *
 * Los números grandes (1-2-3) que conectan las 3 tarjetas quedan listos
 * para, más adelante, animarse con GSAP MotionPath + ScrollTrigger.
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
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Fredoka:wght@500;600;700&family=Baloo+2:wght@600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/reserva.css?v=<?php echo filemtime(PUBLIC_ROOT . '/css/reserva.css'); ?>">
</head>
<body>

    <?php $paquete_preseleccionado = isset($_GET['paquete']) ? (int)$_GET['paquete'] : 0; ?>

    <a href="javascript:void(0)" id="btnBack" class="btn-back-reserva">
        <i class="bi bi-arrow-left"></i>
    </a>

    <div class="reserva-fondo-wrapper">
        <div class="capa-color-fondo" aria-hidden="true"></div>

        <header class="reserva-hero">
            <h1 class="fuente_bouncy">Arma tu Reserva</h1>
            <p>Completa los datos de tu fiesta: puedes rellenarlos en el orden que prefieras mientras sigues bajando.</p>
        </header>

        <div class="wrap reserva-pasos">

            <!-- ============== PASO 1: PAQUETE, EXTRAS, FECHA Y HORA ============== -->
            <div class="paso-fila">
                <div class="paso-numero-col">
                    <span class="paso-numero" data-paso-num="1">1</span>
                </div>
                <div class="step-card" id="card-paso1" data-paso="1">
                    <h2>Paquete, Extras y Fecha</h2>

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

                    </div>

                    <div class="row">
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
                    <div class="total-box" id="total-paso1">Total: S/0.00</div>
                </div>
            </div>

            <!-- ============== PASO 2: DETALLES DEL CUMPLEAÑERO ============== -->
            <div class="paso-fila">
                <div class="paso-numero-col">
                    <span class="paso-numero" data-paso-num="2">2</span>
                </div>
                <div class="step-card" id="card-paso2" data-paso="2">
                    <h2>Detalles del Cumpleañero</h2>

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
                                <p><strong>Paquete:</strong> <span id="resumen_paquete">—</span></p>
                                <p><strong>Cantidad:</strong> <span id="resumen_cantidad">—</span></p>
                                <p><strong>Extras:</strong> <span id="resumen_extras">—</span></p>
                                <p><strong>Fecha:</strong> <span id="resumen_fecha">—</span></p>
                                <p><strong>Hora:</strong> <span id="resumen_hora">—</span></p>

                                <hr>
                                <div class="total-box" id="total-resumen">Total: S/0.00</div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ============== PASO 3: PAGO Y SUBIDA DE COMPROBANTE ============== -->
            <div class="paso-fila">
                <div class="paso-numero-col">
                    <span class="paso-numero" data-paso-num="3">3</span>
                </div>
                <div class="step-card" id="card-paso3" data-paso="3">
                    <h2>Realiza tu Pago</h2>

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
                                    <label for="captura_pago" class="form-label">Adjunta tu captura (JPG, PNG, PDF)</label>
                                    <input class="form-control" type="file" id="captura_pago" name="captura_pago" accept="image/png, image/jpeg, application/pdf">
                                </div>

                                <input type="hidden" name="reserva_data" id="reserva_data_input">

                                <hr class="my-4">

                                <p class="text-muted small">Al hacer clic en "Finalizar", tu reserva quedará en estado "Pendiente" hasta que un administrador verifique tu pago. Todos los campos de los 3 pasos son obligatorios (excepto observaciones).</p>

                                <button type="submit" class="btn-next w-100" id="btnFinalizar">
                                    Finalizar Reserva
                                </button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // --- BOTÓN DE RETROCEDER: vuelve a la pestaña/página anterior ---
        document.getElementById('btnBack').addEventListener('click', function (e) {
            e.preventDefault();
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '<?php echo URL_ROOT; ?>/';
            }
        });

        // --- Resalta el número del paso que está actualmente en pantalla ---
        // (Punto de partida para, más adelante, animar estos números con
        // GSAP MotionPath + ScrollTrigger a medida que se hace scroll.)
        const pasoCards = document.querySelectorAll('.step-card[data-paso]');
        const numeroObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const numero = document.querySelector(`.paso-numero[data-paso-num="${entry.target.dataset.paso}"]`);
                if (numero) numero.classList.toggle('numero-activo', entry.isIntersecting);
            });
        }, { rootMargin: '-35% 0px -35% 0px', threshold: 0 });
        pasoCards.forEach(card => numeroObserver.observe(card));

        /* ================= PASO 1: Paquete, extras, fecha y hora ================= */

        const paquetes = document.querySelectorAll('input[name="paquete"]');
        const experiencias = document.querySelectorAll('.experience-select');
        const cantidadInput = document.getElementById('cantidad');
        const totalEl1 = document.getElementById('total-paso1');
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

        async function renderCalendar() {
            const month = currentDate.getMonth() + 1;
            const year = currentDate.getFullYear();

            monthYearEl.textContent = new Date(year, month - 1).toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });
            calendarGrid.innerHTML = `<div class="weekday">L</div><div class="weekday">M</div><div class="weekday">X</div><div class="weekday">J</div><div class="weekday">V</div><div class="weekday">S</div><div class="weekday">D</div>`;

            await fetchFechasOcupadas(year, month);

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
                    classes += ' occupied';
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
                actualizarResumenGlobal();
            }
        });

        async function fetchFechasOcupadas(ano, mes) {
            try {
                const response = await fetch(`<?php echo URL_ROOT; ?>/reservas/getFechasOcupadas/${ano}/${mes}`);
                fechasOcupadas = await response.json();
            } catch (error) {
                console.error('Error fetching fechas ocupadas:', error);
                fechasOcupadas = [];
            }
        }

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

            experiencias.forEach(e => {
                if (e.checked) {
                    extras += parseFloat(e.value);
                }
            });

            total = totalBase + extras;
            totalEl1.textContent = `Total: S/${total.toFixed(2)}`;
        }

        // Refresca en vivo el total del Paso 1, el resumen del Paso 2 y el
        // monto a pagar del Paso 3, sin importar en qué orden se llenen.
        function actualizarResumenGlobal() {
            calcularTotal();

            const paqueteSeleccionado = document.querySelector('input[name="paquete"]:checked');
            document.getElementById('resumen_paquete').textContent = paqueteSeleccionado ? paqueteSeleccionado.dataset.nombre : '—';
            document.getElementById('resumen_cantidad').textContent = cantidadInput.value ? cantidadInput.value + ' personas' : '—';

            if (selectedDate) {
                document.getElementById('resumen_fecha').textContent = selectedDate.toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' });
            } else {
                document.getElementById('resumen_fecha').textContent = '—';
            }

            if (horaInicioSelect.value) {
                const [h, m] = horaInicioSelect.value.split(':');
                const horaObj = new Date(0, 0, 0, h, m);
                document.getElementById('resumen_hora').textContent = horaObj.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: true });
            } else {
                document.getElementById('resumen_hora').textContent = '—';
            }

            const pintura = document.getElementById('pintura').checked;
            const destruccion = document.getElementById('destruccion').checked;
            let extrasTexto = 'Ninguno';
            if (pintura && destruccion) extrasTexto = 'Pintura y Destrucción';
            else if (pintura) extrasTexto = 'Cuarto de Pintura';
            else if (destruccion) extrasTexto = 'Cuarto de Destrucción';
            document.getElementById('resumen_extras').textContent = extrasTexto;

            document.getElementById('total-resumen').textContent = `Total: S/${total.toFixed(2)}`;
            document.getElementById('monto_pagar').textContent = `S/${total.toFixed(2)}`;
        }

        const HORA_CIERRE_MINUTOS = 23 * 60; // 11:00 PM

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

        paquetes.forEach(p => {
            p.addEventListener('change', (e) => {
                actualizarResumenGlobal();
                filtrarHorasPorDuracion(e.target.dataset.duracion);
            });

            p.closest('.accordion-header').querySelector('button').addEventListener('click', (e) => {
                p.checked = true;
                actualizarResumenGlobal();
                filtrarHorasPorDuracion(p.dataset.duracion);
            });
        });

        experiencias.forEach(e => e.addEventListener('change', actualizarResumenGlobal));
        cantidadInput.addEventListener('input', () => {
            if (cantidadInput.value < 10) cantidadInput.value = 10;
            actualizarResumenGlobal();
        });

        horaInicioSelect.addEventListener('change', () => {
            actualizarResumenGlobal();

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

        // Preselección de paquete (cuando se viene con ?paquete=ID desde el inicio)
        const paquetePreseleccionado = <?php echo $paquete_preseleccionado; ?>;

        paquetes.forEach(p => {
            const idPaquete = parseInt(p.id.replace('paquete_id_', ''));
            if (paquetePreseleccionado > 0 && idPaquete === paquetePreseleccionado) {
                p.checked = true;
                filtrarHorasPorDuracion(p.dataset.duracion);
            }
        });

        renderCalendar();
        actualizarResumenGlobal();

        /* ================= PASO 2: Detalles del cumpleañero ================= */

        const nombreInput = document.getElementById('nombre_cumpleanero');
        const edadInput = document.getElementById('edad_cumpleanero');
        const observacionesInput = document.getElementById('observaciones');

        /* ================= PASO 3 + VALIDACIÓN FINAL ================= */

        const form = document.getElementById('form-finalizar');
        const hiddenInput = document.getElementById('reserva_data_input');
        const fileInput = document.getElementById('captura_pago');

        function limpiarErrores() {
            document.querySelectorAll('.campo-invalido').forEach(el => el.classList.remove('campo-invalido'));
            document.querySelectorAll('.numero-error').forEach(el => el.classList.remove('numero-error'));
        }

        function validarTodo() {
            const errores = [];
            const paqueteSeleccionado = document.querySelector('input[name="paquete"]:checked');

            if (!paqueteSeleccionado) {
                errores.push({ paso: 1, mensaje: 'Selecciona un paquete.', el: document.getElementById('paquetesAccordion') });
            }
            if (!selectedDate) {
                errores.push({ paso: 1, mensaje: 'Selecciona la fecha de tu evento.', el: document.querySelector('.calendar-wrapper') });
            }
            if (!horaInicioSelect.value) {
                errores.push({ paso: 1, mensaje: 'Selecciona una hora de inicio.', el: horaInicioSelect });
            }
            if (cantidadInput.value < 10 || cantidadInput.value > 30) {
                errores.push({ paso: 1, mensaje: 'La cantidad debe ser entre 10 y 30 personas.', el: cantidadInput });
            }
            if (nombreInput.value.trim() === '') {
                errores.push({ paso: 2, mensaje: 'Ingresa el nombre del cumpleañero.', el: nombreInput });
            }
            if (edadInput.value === '' || parseInt(edadInput.value) < 1) {
                errores.push({ paso: 2, mensaje: 'Ingresa una edad válida.', el: edadInput });
            }
            if (fileInput.files.length === 0) {
                errores.push({ paso: 3, mensaje: 'Adjunta la captura de tu pago.', el: fileInput });
            }

            return errores;
        }

        function mostrarErrores(errores) {
            const pasosConError = new Set();
            errores.forEach(err => {
                pasosConError.add(err.paso);
                if (err.el) err.el.classList.add('campo-invalido');
            });
            pasosConError.forEach(p => {
                const numero = document.querySelector(`.paso-numero[data-paso-num="${p}"]`);
                if (numero) numero.classList.add('numero-error');
            });

            let banner = document.getElementById('reserva-errores-banner');
            if (!banner) {
                banner = document.createElement('div');
                banner.id = 'reserva-errores-banner';
                banner.className = 'reserva-errores-banner';
                document.body.appendChild(banner);
            }
            banner.innerHTML = '<strong><i class="bi bi-exclamation-triangle-fill"></i> Falta completar</strong><ul>' +
                errores.map(e => `<li>${e.mensaje}</li>`).join('') + '</ul>';
            banner.classList.add('visible');
            clearTimeout(window._reservaBannerTimeout);
            window._reservaBannerTimeout = setTimeout(() => banner.classList.remove('visible'), 6000);

            if (errores[0].el) {
                errores[0].el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            limpiarErrores();

            const errores = validarTodo();
            if (errores.length > 0) {
                mostrarErrores(errores);
                return;
            }

            const paqueteSeleccionado = document.querySelector('input[name="paquete"]:checked');
            const reservaData = {
                id_paquete: paqueteSeleccionado.id.replace('paquete_id_', ''),
                cantidad: parseInt(cantidadInput.value),
                extra_pintura: document.getElementById('pintura').checked,
                extra_destruccion: document.getElementById('destruccion').checked,
                total_calculado: total,
                fecha: selectedDate.toISOString().split('T')[0],
                hora_inicio: horaInicioSelect.value,
                duracion_minutos: parseInt(paqueteSeleccionado.dataset.duracion),
                nombre_cumpleanero: nombreInput.value.trim(),
                edad_cumpleanero: parseInt(edadInput.value),
                observaciones: observacionesInput.value.trim()
            };

            hiddenInput.value = JSON.stringify(reservaData);

            // Envío nativo: no dispara este mismo listener de nuevo (evita bucles).
            HTMLFormElement.prototype.submit.call(form);
        });
    </script>
</body>
</html>
