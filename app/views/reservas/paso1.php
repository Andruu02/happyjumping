<?php
/*
 * VISTA RESERVA - FLUJO COMPLETO EN UNA SOLA PÁGINA
 * (Paso 1: Extras/Fecha, Paso 2: Detalles, Paso 3: Pago)
 *
 * El paquete YA viene elegido desde la sección "Cumpleaños" del inicio
 * (?paquete=ID, resuelto en ReservasController::paso1()) - aquí solo se
 * muestra como información ("Paquete elegido"), no se puede cambiar sin
 * volver a esa sección.
 *
 * Los 3 pasos viven en el mismo documento, uno debajo del otro, y NO están
 * bloqueados entre sí: el usuario puede rellenarlos en el orden que quiera
 * mientras baja normalmente por la página. Todos los campos son
 * obligatorios (excepto "observaciones") y se validan recién al presionar
 * "Finalizar Reserva" al final.
 *
 * Un indicador numérico (1-2-3) viaja con GSAP MotionPath + ScrollTrigger
 * a lo largo de una ruta que conecta los 3 pasos, a medida que se hace
 * scroll por la página.
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

    <a href="javascript:void(0)" id="btnBack" class="btn-back-reserva">
        <i class="bi bi-arrow-left"></i>
    </a>

    <div class="reserva-fondo-wrapper">
        <div class="capa-color-fondo" aria-hidden="true"></div>

        <header class="reserva-hero">
            <h1 class="fuente_bouncy">Arma tu Reserva</h1>
            <p>Completa los datos de tu fiesta.</p>
        </header>

        <div class="wrap reserva-pasos">

            <!-- Ruta (SVG) que conecta los 3 pasos + indicador numérico que viaja con GSAP -->
            <svg id="reserva-path-svg" class="reserva-path-svg" aria-hidden="true">
                <defs>
                    <linearGradient id="reserva-path-gradient" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%"   stop-color="#7b2ff7"/>
                        <stop offset="50%"  stop-color="#ff3c8d"/>
                        <stop offset="100%" stop-color="#ff8c00"/>
                    </linearGradient>
                </defs>
                <path id="reserva-path" fill="none" stroke="url(#reserva-path-gradient)" stroke-width="3" stroke-dasharray="2 14" stroke-linecap="round"/>
            </svg>
            <div id="paso-indicador" class="paso-indicador">
                <span class="paso-indicador-label" id="paso-indicador-label">Personaliza tu Fiesta</span>
                <span id="paso-indicador-numero">1</span>
            </div>

            <!-- ============== PASO 1: EXTRAS, CANTIDAD, FECHA Y HORA ============== -->
            <div class="paso-fila">
                <div class="paso-numero-col">
                    <span class="paso-punto" data-paso-num="1"></span>
                </div>
                <div class="step-card" id="card-paso1" data-paso="1" data-titulo="Personaliza tu Fiesta">

                    <?php
                        $contenidoPaquetes = [
                            1 => ['2 horas de uso del local.', 'Pulsera de 1 hora en camas saltarinas.', 'Dinámicas con premios a cargo de nuestras anfitrionas.'],
                            2 => ['2 horas y media de diversión total.', 'Pulsera de 1 hora en trampolines.', 'Glitter Bar y tatuajes neón durante 1 hora.', 'Combo Happy: Popcorn + agua mineral.'],
                            3 => ['3 horas de uso completo del local.', '1 hora y media de trampolines, pared de escalar y tirolesa.', 'Maquillaje neón y Glitter.', 'Combo Happy: Popcorn + bebida (frugos, agua o gaseosa).'],
                            4 => ['5 horas de uso exclusivo del local.', 'Acceso a trampolines, tirolesa y pared de escalar.', 'Dinámicas con premios y muñecos inflables.', 'Combo Happy: Popcorn + bebida + pan con hotdog.'],
                        ];
                        $imagenesPaquetes = [
                            1 => 'trampolin.webp',
                            2 => 'trampolin2.webp',
                            3 => 'pared_escalar.webp',
                            4 => 'HJ1.webp',
                        ];
                        $imagenPaquete = $imagenesPaquetes[$paquete->id_paquete] ?? 'trampolin.webp';
                    ?>
                    <div class="paquete-elegido-box">
                        <div class="paquete-elegido-img" style="background-image:url('<?php echo URL_ROOT; ?>/img/<?php echo $imagenPaquete; ?>');"></div>
                        <div class="paquete-elegido-info">
                            <span class="paquete-elegido-label">Paquete elegido</span>
                            <h4><?php echo htmlspecialchars($paquete->nombre); ?></h4>
                            <div class="paquete-elegido-precios">
                                <span>S/<?php echo number_format($paquete->precio_semana, 2); ?> (L-V)</span>
                                <span>S/<?php echo number_format($paquete->precio_fin_semana, 2); ?> (S-D)</span>
                            </div>
                            <?php if (isset($contenidoPaquetes[$paquete->id_paquete])): ?>
                            <ul class="paquete-elegido-lista">
                                <?php foreach ($contenidoPaquetes[$paquete->id_paquete] as $item): ?>
                                    <li><i class="bi bi-check-circle-fill"></i> <?php echo $item; ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                            <a href="<?php echo URL_ROOT; ?>/#cumpleanos" class="paquete-elegido-cambiar"><i class="bi bi-arrow-repeat"></i> ¿Cambiar de paquete?</a>
                        </div>
                    </div>

                    <div class="paso-subtitulo">
                        <span class="paso-subtitulo-num">1</span>
                        <span class="paso-subtitulo-texto">Extras <small>S/15 c/u por reserva</small></span>
                    </div>
                    <div class="row g-3 mb-2">
                        <div class="col-sm-6">
                            <label class="extra-chip">
                                <input type="checkbox" class="experience-select" value="15" id="pintura">
                                <span class="extra-chip-icono"><i class="bi bi-palette-fill"></i></span>
                                <span class="extra-chip-texto">Cuarto de Pintura</span>
                                <span class="extra-chip-check"><i class="bi bi-check-circle-fill"></i></span>
                            </label>
                        </div>
                        <div class="col-sm-6">
                            <label class="extra-chip">
                                <input type="checkbox" class="experience-select" value="15" id="destruccion">
                                <span class="extra-chip-icono"><i class="bi bi-hammer"></i></span>
                                <span class="extra-chip-texto">Cuarto de Destrucción</span>
                                <span class="extra-chip-check"><i class="bi bi-check-circle-fill"></i></span>
                            </label>
                        </div>
                    </div>

                    <div class="paso-subtitulo">
                        <span class="paso-subtitulo-num">2</span>
                        <span class="paso-subtitulo-texto">Cantidad de personas</span>
                    </div>
                    <div class="cantidad-stepper">
                        <button type="button" class="cantidad-btn" id="cantidad-menos" aria-label="Restar persona"><i class="bi bi-dash-lg"></i></button>
                        <label for="cantidad" class="visually-hidden">Cantidad de personas</label>
                        <input type="number" id="cantidad" class="cantidad-input" min="10" max="30" value="10">
                        <button type="button" class="cantidad-btn" id="cantidad-mas" aria-label="Sumar persona"><i class="bi bi-plus-lg"></i></button>
                        <span class="cantidad-hint">personas (mín. 10, máx. 30)</span>
                    </div>

                    <div class="paso-subtitulo">
                        <span class="paso-subtitulo-num">3</span>
                        <span class="paso-subtitulo-texto">Selecciona Fecha y Hora de Inicio</span>
                    </div>
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
                </div>
            </div>

            <!-- ============== PASO 2: DETALLES DEL CUMPLEAÑERO ============== -->
            <div class="paso-fila">
                <div class="paso-numero-col">
                    <span class="paso-punto" data-paso-num="2"></span>
                </div>
                <div class="step-card" id="card-paso2" data-paso="2" data-titulo="Detalles del Cumpleañero">

                    <div class="row g-4">

                        <div class="col-lg-7">
                            <h3>Completa los datos</h3>

                            <div class="mb-3">
                                <label for="nombre_cumpleanero" class="form-label"><i class="bi bi-person-hearts"></i> Nombre del Cumpleañero</label>
                                <input type="text" class="form-control" id="nombre_cumpleanero" placeholder="Ingresa el nombre">
                            </div>

                            <div class="mb-3">
                                <label for="edad_cumpleanero" class="form-label"><i class="bi bi-balloon-heart-fill"></i> Edad que cumple</label>
                                <input type="number" class="form-control" id="edad_cumpleanero" placeholder="Ej: 7" min="1">
                            </div>

                            <div class="mb-3">
                                <label for="observaciones" class="form-label"><i class="bi bi-chat-square-text-fill"></i> Observaciones (Opcional)</label>
                                <textarea class="form-control" id="observaciones" rows="3" placeholder="Ej: Alergias, temática de fútbol, etc."></textarea>
                            </div>

                        </div>

                        <div class="col-lg-5">
                            <div class="musica-box">
                                <h5><i class="bi bi-music-note-beamed"></i> Arma la playlist de tu fiesta</h5>
                                <p class="musica-hint">Busca canciones y agrégalas - se las pasamos a nuestra anfitriona el día del evento. (Opcional)</p>

                                <div class="musica-buscador">
                                    <label for="musica-buscar-input" class="visually-hidden">Buscar canción</label>
                                    <input type="text" id="musica-buscar-input" class="form-control" placeholder="Busca una canción o artista...">
                                    <button type="button" id="musica-buscar-btn" class="musica-buscar-btn" aria-label="Buscar"><i class="bi bi-search"></i></button>
                                </div>

                                <div id="musica-resultados" class="musica-resultados"></div>

                                <div class="musica-seleccion">
                                    <h6>Tu playlist <span id="musica-contador">(0)</span></h6>
                                    <p id="musica-vacio" class="text-muted small mb-0">Aún no has agregado canciones.</p>
                                    <ul id="musica-lista-elegidas" class="musica-lista-elegidas"></ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ============== PASO 3: PAGO Y SUBIDA DE COMPROBANTE ============== -->
            <div class="paso-fila">
                <div class="paso-numero-col">
                    <span class="paso-punto" data-paso-num="3"></span>
                </div>
                <div class="step-card" id="card-paso3" data-paso="3" data-titulo="Realiza tu Pago">

                    <div class="row g-4">

                        <div class="col-lg-7">

                            <!-- Panel Yape (vía Mercado Pago: cobro automático) -->
                            <div class="metodo-panel" id="panel-yape" data-metodo="yape_mp">
                                <div class="pago-seguro-header">
                                    <i class="bi bi-shield-lock-fill"></i>
                                    <div>
                                        <h3>Pago seguro con Yape</h3>
                                        <p class="mb-0">Completa tus datos para cobrar al toque</p>
                                    </div>
                                </div>

                                <div class="yape-mp-body">
                                    <div class="yape-phone-mockup" aria-hidden="true">
                                        <div class="yape-phone-notch"></div>
                                        <div class="yape-phone-screen">
                                            <div class="yape-phone-icon"><i class="bi bi-phone-vibrate-fill"></i></div>
                                            <span class="yape-phone-label">Total a pagar</span>
                                            <strong class="yape-phone-total" id="monto_pagar_phone">S/0.00</strong>
                                            <div class="yape-phone-hint">Abre Yape, genera el OTP e ingresa tus datos al costado</div>
                                        </div>
                                    </div>

                                    <div class="yape-form-fields">
                                        <div class="mb-3">
                                            <label for="yape_celular" class="form-label"><i class="bi bi-telephone-fill"></i> Celular Yape</label>
                                            <input type="tel" class="form-control" id="yape_celular" placeholder="987654321" maxlength="9" inputmode="numeric">
                                        </div>
                                        <div class="mb-2">
                                            <label for="yape_otp" class="form-label"><i class="bi bi-shield-lock-fill"></i> Código OTP</label>
                                            <input type="text" class="form-control" id="yape_otp" placeholder="123456" maxlength="6" inputmode="numeric">
                                        </div>
                                        <p class="form-text mb-0">Abre tu app de Yape, genera el código y escríbelo acá.</p>

                                        <div id="yape-pago-error" class="pago-error hidden"></div>

                                        <div class="test-monto-wrap">
                                            <label for="test_monto" class="form-label mb-1">Monto a cobrar (S/)</label>
                                            <input type="number" class="form-control" id="test_monto" value="1.50" min="1.50" step="0.10">
                                        </div>
                                        <button type="button" class="btn-test-yape" id="btn-test-yape">
                                            <i class="bi bi-shield-check"></i> Cobrar
                                        </button>
                                        <p class="form-text mb-0">Cobra el monto de arriba vía Mercado Pago con tu celular Yape real y muestra el resultado acá abajo, sin crear ninguna reserva.</p>
                                        <div id="test-yape-resultado" class="test-yape-resultado hidden"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Panel Plin (manual: QR + captura obligatoria) -->
                            <div class="metodo-panel hidden" id="panel-plin" data-metodo="plin">
                                <div class="qr-code-wrapper qr-code-compacto">
                                    <img src="<?php echo URL_ROOT; ?>/img/plin_qr.webp" alt="Código QR de Plin">
                                    <p class="mb-0">Escanea con tu app Plin y paga el monto exacto.<br><strong>A nombre de Ana Escalona.</strong></p>
                                </div>
                            </div>

                            <div class="resumen-compacto">
                                <div class="resumen-chip"><i class="bi bi-box-seam-fill"></i> <span id="resumen_paquete"><?php echo htmlspecialchars($paquete->nombre); ?></span></div>
                                <div class="resumen-chip"><i class="bi bi-people-fill"></i> <span id="resumen_cantidad">—</span></div>
                                <div class="resumen-chip"><i class="bi bi-stars"></i> <span id="resumen_extras">—</span></div>
                                <div class="resumen-chip"><i class="bi bi-calendar-event-fill"></i> <span id="resumen_fecha">—</span></div>
                                <div class="resumen-chip"><i class="bi bi-clock-fill"></i> <span id="resumen_hora">—</span></div>
                            </div>
                        </div>

                        <div class="col-lg-5">

                            <div class="pago-total-box">
                                <span>Total a pagar</span>
                                <strong id="monto_pagar">S/0.00</strong>
                            </div>

                            <div class="metodo-pago-tabs">
                                <p class="metodo-pago-tabs-label">Método de pago</p>
                                <button type="button" class="metodo-pago-tab activo" data-metodo="yape_mp"><i class="bi bi-phone-fill"></i> Yape</button>
                                <button type="button" class="metodo-pago-tab" data-metodo="plin"><i class="bi bi-qr-code"></i> Plin</button>
                            </div>

                            <form action="<?php echo URL_ROOT; ?>/reservas/finalizar" method="POST" enctype="multipart/form-data" id="form-finalizar">

                                <div class="upload-box">
                                    <label for="captura_pago" class="form-label" id="label-captura-pago">Adjunta tu captura (opcional)</label>
                                    <input class="form-control" type="file" id="captura_pago" name="captura_pago" accept="image/png, image/jpeg, application/pdf">
                                </div>

                                <input type="hidden" name="reserva_data" id="reserva_data_input">

                                <p class="text-muted small mt-3 mb-3" id="texto-ayuda-finalizar">Con Yape el cobro es automático. Con Plin tu reserva queda "Pendiente" hasta que verifiquemos el pago.</p>

                                <button type="submit" class="btn-next w-100" id="btnFinalizar">
                                    Pagar con Yape y Finalizar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="total-flotante">
        <span class="total-flotante-label">Total</span>
        <span class="total-flotante-valor" id="total-flotante-valor">S/0.00</span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/MotionPathPlugin.min.js"></script>
    <script src="https://sdk.mercadopago.com/js/v2"></script>

    <script>
        // El paquete ya viene elegido desde el inicio (?paquete=ID); acá solo
        // se usa como dato fijo para calcular precios y duración.
        const paqueteElegido = {
            id: <?php echo (int) $paquete->id_paquete; ?>,
            nombre: <?php echo json_encode($paquete->nombre); ?>,
            precioSemana: <?php echo (float) $paquete->precio_semana; ?>,
            precioFinde: <?php echo (float) $paquete->precio_fin_semana; ?>,
            duracion: <?php echo (int) $paquete->duracion; ?>
        };

        // Public Key de Mercado Pago (segura de exponer en el navegador,
        // se usa para tokenizar el celular+OTP de Yape antes de mandarlo
        // a nuestro backend, que es el único que ve el Access Token secreto).
        const MP_PUBLIC_KEY = <?php echo json_encode($mpPublicKey); ?>;
        // El SDK de Mercado Pago (cargado arriba) es quien sabe hablar con su
        // endpoint de tokenización de Yape: una llamada fetch directa desde
        // acá choca con su política de CORS, por eso se usa el SDK y no fetch.
        const mp = new MercadoPago(MP_PUBLIC_KEY);

        // --- BOTÓN DE RETROCEDER: vuelve a la pestaña/página anterior ---
        document.getElementById('btnBack').addEventListener('click', function (e) {
            e.preventDefault();
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '<?php echo URL_ROOT; ?>/';
            }
        });

        /* ================= PASO 1: Extras, cantidad, fecha y hora ================= */

        const experiencias = document.querySelectorAll('.experience-select');
        const cantidadInput = document.getElementById('cantidad');
        const totalFlotanteValor = document.getElementById('total-flotante-valor');
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
            // (La reconstrucción de la ruta del GSAP tras esta carga
            // asíncrona la dispara solo el ResizeObserver de más abajo,
            // que detecta el cambio de alto de la tarjeta.)
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
            let extras = 0;
            const cantidad = parseInt(cantidadInput.value) || 0;

            let precio = paqueteElegido.precioSemana;
            if (selectedDate && esFinDeSemana(selectedDate)) {
                precio = paqueteElegido.precioFinde;
            }
            const totalBase = precio * cantidad;

            experiencias.forEach(e => {
                if (e.checked) extras += parseFloat(e.value);
            });

            total = totalBase + extras;
            totalFlotanteValor.textContent = `S/${total.toFixed(2)}`;
        }

        // Refresca en vivo la tarjetita flotante de total, el resumen del
        // Paso 3 y el monto a pagar, sin importar en qué orden se llenen
        // los pasos.
        function actualizarResumenGlobal() {
            calcularTotal();

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

            document.getElementById('monto_pagar').textContent = `S/${total.toFixed(2)}`;
            document.getElementById('monto_pagar_phone').textContent = `S/${total.toFixed(2)}`;
        }

        const HORA_CIERRE_MINUTOS = 23 * 60; // 11:00 PM

        function filtrarHorasPorDuracion(duracionMinutos) {
            let duracionHoras = (duracionMinutos / 60).toFixed(1);
            if (duracionHoras.endsWith('.0')) {
                duracionHoras = duracionHoras.substring(0, duracionHoras.length - 2);
            }
            duracionPaqueteEl.textContent = `${duracionHoras} horas`;

            for (let i = 1; i < horaInicioSelect.options.length; i++) {
                const option = horaInicioSelect.options[i];
                const [horas, minutos] = option.value.split(':').map(Number);
                const horaInicioMinutos = (horas * 60) + minutos;
                const horaFinMinutos = horaInicioMinutos + duracionMinutos;

                if (horaFinMinutos > HORA_CIERRE_MINUTOS) {
                    option.style.display = 'none';
                    option.disabled = true;
                } else {
                    option.style.display = 'block';
                    option.disabled = false;
                }
            }
        }

        experiencias.forEach(e => e.addEventListener('change', actualizarResumenGlobal));
        cantidadInput.addEventListener('input', () => {
            if (cantidadInput.value < 10) cantidadInput.value = 10;
            actualizarResumenGlobal();
        });

        document.getElementById('cantidad-menos').addEventListener('click', () => {
            cantidadInput.value = Math.max(10, (parseInt(cantidadInput.value) || 10) - 1);
            actualizarResumenGlobal();
        });
        document.getElementById('cantidad-mas').addEventListener('click', () => {
            cantidadInput.value = Math.min(30, (parseInt(cantidadInput.value) || 10) + 1);
            actualizarResumenGlobal();
        });

        horaInicioSelect.addEventListener('change', () => {
            actualizarResumenGlobal();

            if (!selectedDate || !horaInicioSelect.value) {
                horaFinCalculadaEl.textContent = "";
                return;
            }

            const [horas, minutos] = horaInicioSelect.value.split(':').map(Number);

            const fechaInicio = new Date(selectedDate);
            fechaInicio.setHours(horas, minutos);

            const fechaFin = new Date(fechaInicio.getTime() + paqueteElegido.duracion * 60000);

            const horaFinStr = fechaFin.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: true });

            if (fechaFin.getHours() >= 23 && fechaFin.getMinutes() > 0 || fechaFin.getHours() < horas) {
                 horaFinCalculadaEl.textContent = `Termina ${horaFinStr} (¡Pasa las 11 PM!)`;
                 horaFinCalculadaEl.style.color = 'red';
            } else {
                 horaFinCalculadaEl.textContent = `Tu evento terminará aprox. a las ${horaFinStr}`;
                 horaFinCalculadaEl.style.color = 'var(--morado)';
            }
        });

        filtrarHorasPorDuracion(paqueteElegido.duracion);
        renderCalendar();
        actualizarResumenGlobal();

        /* ================= PASO 2: Detalles del cumpleañero ================= */

        const nombreInput = document.getElementById('nombre_cumpleanero');
        const edadInput = document.getElementById('edad_cumpleanero');
        const observacionesInput = document.getElementById('observaciones');

        /* ================= PASO 2: Playlist con Deezer (opcional) ================= */

        let cancionesSeleccionadas = [];
        const MAX_CANCIONES = 15;

        const musicaBuscarInput = document.getElementById('musica-buscar-input');
        const musicaBuscarBtn = document.getElementById('musica-buscar-btn');
        const musicaResultados = document.getElementById('musica-resultados');
        const musicaListaElegidas = document.getElementById('musica-lista-elegidas');
        const musicaContador = document.getElementById('musica-contador');
        const musicaVacio = document.getElementById('musica-vacio');

        async function buscarCancionesMusica() {
            const texto = musicaBuscarInput.value.trim();
            if (texto === '') return;

            musicaResultados.innerHTML = '<p class="text-muted small mb-0">Buscando...</p>';

            try {
                const respuesta = await fetch(`<?php echo URL_ROOT; ?>/reservas/buscarCanciones?q=${encodeURIComponent(texto)}`);
                const canciones = await respuesta.json();
                renderResultadosMusica(canciones);
            } catch (error) {
                musicaResultados.innerHTML = '<p class="text-danger small mb-0">No se pudo buscar. Intenta de nuevo.</p>';
            }
        }

        function renderResultadosMusica(canciones) {
            if (!Array.isArray(canciones) || canciones.length === 0) {
                musicaResultados.innerHTML = '<p class="text-muted small mb-0">Sin resultados. Prueba con otro nombre.</p>';
                return;
            }

            musicaResultados.innerHTML = '';
            canciones.forEach((cancion) => {
                const item = document.createElement('div');
                item.className = 'musica-resultado-item';
                item.innerHTML = `
                    <img src="${cancion.imagen}" alt="">
                    <div class="musica-resultado-info">
                        <strong>${cancion.nombre}</strong>
                        <span>${cancion.artista}</span>
                    </div>
                    <button type="button" class="musica-agregar-btn" aria-label="Agregar a la playlist"><i class="bi bi-plus-lg"></i></button>
                `;
                item.querySelector('.musica-agregar-btn').addEventListener('click', () => agregarCancion(cancion));
                musicaResultados.appendChild(item);
            });
        }

        function agregarCancion(cancion) {
            const yaExiste = cancionesSeleccionadas.some(c => c.enlace === cancion.enlace);
            if (yaExiste) return;

            if (cancionesSeleccionadas.length >= MAX_CANCIONES) {
                alert(`Puedes agregar hasta ${MAX_CANCIONES} canciones.`);
                return;
            }

            cancionesSeleccionadas.push(cancion);
            renderListaElegidas();
        }

        function quitarCancion(indice) {
            cancionesSeleccionadas.splice(indice, 1);
            renderListaElegidas();
        }

        function renderListaElegidas() {
            musicaContador.textContent = `(${cancionesSeleccionadas.length})`;
            musicaVacio.style.display = cancionesSeleccionadas.length ? 'none' : 'block';

            musicaListaElegidas.innerHTML = '';
            cancionesSeleccionadas.forEach((cancion, indice) => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <span>${cancion.nombre} <small>${cancion.artista}</small></span>
                    <button type="button" aria-label="Quitar canción"><i class="bi bi-x-lg"></i></button>
                `;
                li.querySelector('button').addEventListener('click', () => quitarCancion(indice));
                musicaListaElegidas.appendChild(li);
            });
        }

        musicaBuscarBtn.addEventListener('click', buscarCancionesMusica);
        musicaBuscarInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarCancionesMusica();
            }
        });

        /* ================= PASO 3 + VALIDACIÓN FINAL ================= */

        const form = document.getElementById('form-finalizar');
        const hiddenInput = document.getElementById('reserva_data_input');
        const fileInput = document.getElementById('captura_pago');

        /* ---------- Selector de método de pago (Yape vía MP / Plin) ---------- */
        const metodoPagoTabs = document.querySelectorAll('.metodo-pago-tab');
        const panelYape = document.getElementById('panel-yape');
        const panelPlin = document.getElementById('panel-plin');
        const metodoPagoHidden = { value: 'yape_mp' }; // no es un <input>, solo guarda el método elegido
        const yapeCelularInput = document.getElementById('yape_celular');
        const yapeOtpInput = document.getElementById('yape_otp');
        const yapePagoError = document.getElementById('yape-pago-error');
        const textoAyudaFinalizar = document.getElementById('texto-ayuda-finalizar');
        const btnFinalizar = document.getElementById('btnFinalizar');
        const labelCapturaPago = document.getElementById('label-captura-pago');

        // Celulares reales de Yape empiezan con 9 (9 dígitos).
        const CELULAR_YAPE_REGEX = /^9\d{8}$/;

        function mostrarErrorPago(mensaje) {
            yapePagoError.textContent = mensaje;
            yapePagoError.classList.remove('hidden');
            yapePagoError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        function ocultarErrorPago() {
            yapePagoError.classList.add('hidden');
            yapePagoError.textContent = '';
        }

        function seleccionarMetodoPago(metodo) {
            metodoPagoHidden.value = metodo;
            metodoPagoTabs.forEach(btn => btn.classList.toggle('activo', btn.dataset.metodo === metodo));
            panelYape.classList.toggle('hidden', metodo !== 'yape_mp');
            panelPlin.classList.toggle('hidden', metodo !== 'plin');
            ocultarErrorPago();

            if (metodo === 'yape_mp') {
                btnFinalizar.textContent = 'Pagar con Yape y Finalizar';
                textoAyudaFinalizar.textContent = 'Con Yape el cobro es automático: tu reserva queda "Confirmada" al toque si el pago se aprueba.';
                labelCapturaPago.textContent = 'Adjunta tu captura (opcional)';
            } else {
                btnFinalizar.textContent = 'Finalizar Reserva';
                textoAyudaFinalizar.textContent = 'Escanea el QR de Plin y paga el monto exacto. Tu reserva queda "Pendiente" hasta que verifiquemos el pago.';
                labelCapturaPago.textContent = 'Adjunta tu captura de pago (obligatorio)';
            }
        }
        metodoPagoTabs.forEach(btn => btn.addEventListener('click', () => seleccionarMetodoPago(btn.dataset.metodo)));

        /* ---------- Cobro de Yape vía Mercado Pago ---------- */
        async function generarTokenYape(celular, otp) {
            // Tiene que ser el SDK (mp.yape().create()), no un fetch directo:
            // el endpoint de tokenización de Yape bloquea llamadas CORS desde
            // cualquier origen que no sea el propio SDK de Mercado Pago.
            try {
                const yape = mp.yape({ otp, phoneNumber: celular });
                const yapeToken = await yape.create();
                if (!yapeToken || !yapeToken.id) {
                    throw new Error('Respuesta inesperada al validar tu Yape.');
                }
                return yapeToken.id;
            } catch (err) {
                throw new Error(err.message || 'No se pudo validar tu Yape. Revisa el celular y el código OTP.');
            }
        }

        /* ---------- Botón de verificación: cobra el monto de arriba y muestra el resultado, sin reservar nada ---------- */
        const btnTestYape = document.getElementById('btn-test-yape');
        const testYapeResultado = document.getElementById('test-yape-resultado');

        btnTestYape.addEventListener('click', async function () {
            const celular = yapeCelularInput.value.trim();
            const otp = yapeOtpInput.value.trim();

            testYapeResultado.classList.add('hidden');
            testYapeResultado.textContent = '';

            if (!CELULAR_YAPE_REGEX.test(celular)) {
                yapeCelularInput.classList.add('campo-invalido');
                return mostrarResultadoTestYape('Ingresa un celular Yape válido (9 dígitos).', false);
            }
            if (!/^\d{6}$/.test(otp)) {
                yapeOtpInput.classList.add('campo-invalido');
                return mostrarResultadoTestYape('Ingresa el código OTP de 6 dígitos de tu app Yape.', false);
            }

            const textoOriginal = btnTestYape.innerHTML;
            btnTestYape.disabled = true;
            btnTestYape.innerHTML = '<i class="bi bi-hourglass-split"></i> Probando...';

            try {
                const tokenId = await generarTokenYape(celular, otp);
                const montoPrueba = parseFloat(document.getElementById('test_monto').value) || 1;

                const resp = await fetch(`<?php echo URL_ROOT; ?>/reservas/pagar-yape`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ token: tokenId, monto: montoPrueba })
                });
                const data = await resp.json();

                if (!data.ok) {
                    let msg = 'Error de Mercado Pago: ' + (data.error || 'desconocido');
                    if (data.debug) msg += '\n\n' + data.debug;
                    mostrarResultadoTestYape(msg, false);
                } else {
                    mostrarResultadoTestYape(
                        `Resultado: ${data.status}${data.status_detail ? ' (' + data.status_detail + ')' : ''} — id de pago: ${data.mp_payment_id}`,
                        data.status === 'approved'
                    );
                }
            } catch (err) {
                mostrarResultadoTestYape(err.message || 'No se pudo probar el cobro.', false);
            }

            btnTestYape.disabled = false;
            btnTestYape.innerHTML = textoOriginal;
        });

        function mostrarResultadoTestYape(mensaje, esExito) {
            testYapeResultado.textContent = mensaje;
            testYapeResultado.classList.remove('hidden', 'exito', 'error');
            testYapeResultado.classList.add(esExito ? 'exito' : 'error');
        }

        function limpiarErrores() {
            document.querySelectorAll('.campo-invalido').forEach(el => el.classList.remove('campo-invalido'));
            document.querySelectorAll('.numero-error').forEach(el => el.classList.remove('numero-error'));
        }

        function validarTodo() {
            const errores = [];

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
            if (metodoPagoHidden.value === 'yape_mp') {
                if (!CELULAR_YAPE_REGEX.test(yapeCelularInput.value.trim())) {
                    errores.push({ paso: 3, mensaje: 'Ingresa un celular Yape válido (9 dígitos).', el: yapeCelularInput });
                }
                if (!/^\d{6}$/.test(yapeOtpInput.value.trim())) {
                    errores.push({ paso: 3, mensaje: 'Ingresa el código OTP de 6 dígitos de tu app Yape.', el: yapeOtpInput });
                }
            } else if (metodoPagoHidden.value === 'plin' && fileInput.files.length === 0) {
                errores.push({ paso: 3, mensaje: 'Adjunta la captura de tu pago por Plin.', el: fileInput });
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
                const punto = document.querySelector(`.paso-punto[data-paso-num="${p}"]`);
                if (punto) punto.classList.add('numero-error');
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

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            limpiarErrores();
            ocultarErrorPago();

            const errores = validarTodo();
            if (errores.length > 0) {
                mostrarErrores(errores);
                return;
            }

            const reservaData = {
                id_paquete: paqueteElegido.id,
                cantidad: parseInt(cantidadInput.value),
                extra_pintura: document.getElementById('pintura').checked,
                extra_destruccion: document.getElementById('destruccion').checked,
                total_calculado: total,
                fecha: selectedDate.toISOString().split('T')[0],
                hora_inicio: horaInicioSelect.value,
                duracion_minutos: paqueteElegido.duracion,
                nombre_cumpleanero: nombreInput.value.trim(),
                edad_cumpleanero: parseInt(edadInput.value),
                observaciones: observacionesInput.value.trim(),
                canciones: cancionesSeleccionadas,
                metodo_pago: metodoPagoHidden.value
            };

            if (metodoPagoHidden.value === 'yape_mp') {
                const textoOriginalBtn = btnFinalizar.textContent;
                btnFinalizar.disabled = true;
                btnFinalizar.textContent = 'Procesando pago...';

                try {
                    const tokenId = await generarTokenYape(yapeCelularInput.value.trim(), yapeOtpInput.value.trim());

                    const pagoResp = await fetch(`<?php echo URL_ROOT; ?>/reservas/pagar-yape`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ token: tokenId, monto: total })
                    });
                    const pagoData = await pagoResp.json();

                    if (!pagoData.ok) {
                        throw new Error(pagoData.error || 'No se pudo procesar el pago con Yape.');
                    }
                    if (pagoData.status === 'rejected') {
                        throw new Error('Tu pago con Yape fue rechazado. Verifica tu saldo/límite de Yape, o paga con Plin.');
                    }

                    reservaData.mp_payment_id = pagoData.mp_payment_id;

                } catch (err) {
                    mostrarErrorPago(err.message || 'No se pudo procesar el pago con Yape.');
                    btnFinalizar.disabled = false;
                    btnFinalizar.textContent = textoOriginalBtn;
                    return;
                }
            }

            hiddenInput.value = JSON.stringify(reservaData);

            // Envío nativo: no dispara este mismo listener de nuevo (evita bucles).
            HTMLFormElement.prototype.submit.call(form);
        });

        /* ================= GSAP: indicador viajando por el MotionPath ================= */

        gsap.registerPlugin(ScrollTrigger, MotionPathPlugin);

        const contenedorPasos = document.querySelector('.reserva-pasos');
        const svgRuta = document.getElementById('reserva-path-svg');
        const pathRuta = document.getElementById('reserva-path');
        const indicador = document.getElementById('paso-indicador');
        const indicadorNumero = document.getElementById('paso-indicador-numero');
        const indicadorLabel = document.getElementById('paso-indicador-label');
        let progresoPaso2 = 0.5;
        let puntoInicioY = 0;
        let puntoFinY = 0;

        // Título de cada paso (viene de data-titulo en cada tarjeta; las
        // tarjetas ya no tienen su propio <h2> porque el título viaja junto
        // al número del indicador).
        const titulosPaso = {};
        document.querySelectorAll('.step-card[data-paso]').forEach(card => {
            titulosPaso[card.dataset.paso] = card.dataset.titulo || '';
        });

        function construirRutaPasos() {
            const rectContenedor = contenedorPasos.getBoundingClientRect();
            const puntos = document.querySelectorAll('.paso-punto');
            if (puntos.length < 3) return false;

            const coords = Array.from(puntos).map(p => {
                const r = p.getBoundingClientRect();
                return {
                    x: r.left + r.width / 2 - rectContenedor.left,
                    y: r.top + r.height / 2 - rectContenedor.top
                };
            });
            const [p1, p2, p3] = coords;

            // OJO: no usar contenedorPasos.scrollHeight acá. El propio SVG y
            // el indicador son "position:absolute" dentro de .reserva-pasos,
            // así que su alto influye en el scrollHeight del contenedor -
            // si se usara ese valor, cada reconstrucción de la ruta podría
            // inflar el alto un poco más que la anterior (más espacio en
            // blanco debajo de la tarjeta 3, y la ruta mal calculada hace
            // que el indicador se atrase). Por eso medimos el alto real del
            // contenido a partir de la última fila (que sí está en flujo
            // normal, no es absoluta).
            const filas = document.querySelectorAll('.paso-fila');
            const ultimaFila = filas[filas.length - 1];
            const rectUltimaFila = ultimaFila.getBoundingClientRect();
            const alturaTotal = rectUltimaFila.bottom - rectContenedor.top;

            svgRuta.setAttribute('viewBox', `0 0 ${rectContenedor.width} ${alturaTotal}`);
            svgRuta.setAttribute('width', rectContenedor.width);
            svgRuta.setAttribute('height', alturaTotal);

            // Curvas suaves (tipo "S") entre cada par de puntos, en vez de una
            // línea recta, para que se vea como un camino ondulado.
            const c1x = p1.x + 26;
            const c2x = p2.x - 26;
            const d = `M ${p1.x} ${p1.y} Q ${c1x} ${(p1.y + p2.y) / 2} ${p2.x} ${p2.y} Q ${c2x} ${(p2.y + p3.y) / 2} ${p3.x} ${p3.y}`;
            pathRuta.setAttribute('d', d);

            const dist = (a, b) => Math.hypot(b.x - a.x, b.y - a.y);
            const d1 = dist(p1, p2);
            const d2 = dist(p2, p3);
            progresoPaso2 = (d1 + d2) > 0 ? d1 / (d1 + d2) : 0.5;

            // El ScrollTrigger tiene que arrancar/terminar EXACTAMENTE en la
            // posición de p1/p3 (no en el borde de todo el contenedor). Si
            // se usa el contenedor completo como referencia de scroll (que
            // incluye el padding de arriba y todo el contenido que sigue
            // después del punto 3, como el QR y el formulario de pago), el
            // progreso 0-1 del scroll ya no coincide con el progreso 0-1 a
            // lo largo de la ruta -el punto 3 queda "muy arriba" dentro de
            // ese rango y el indicador se atrasa, sin llegar a tiempo.
            puntoInicioY = p1.y;
            puntoFinY = p3.y;

            return true;
        }

        function actualizarNumeroYEtiqueta(numero) {
            if (indicadorNumero.textContent === numero) return;
            indicadorNumero.textContent = numero;
            indicadorLabel.textContent = titulosPaso[numero] || '';
            gsap.fromTo(indicador, { scale: 0.65 }, { scale: 1, duration: 0.35, ease: 'back.out(3)' });
            gsap.fromTo(indicadorLabel, { opacity: 0, x: 8 }, { opacity: 1, x: 0, duration: 0.3, ease: 'power2.out' });
        }

        function inicializarGSAP() {
            if (!construirRutaPasos()) return;

            // Se destruye y se vuelve a crear por completo (en vez de solo
            // "refrescar") cada vez que el layout puede haber cambiado
            // (calendario recién cargado, resize, fuentes listas), para que
            // la ruta y el ScrollTrigger queden calculados con las medidas
            // reales y el indicador no se quede atrasado.
            ScrollTrigger.getAll().forEach(st => st.kill());

            gsap.set(indicador, {
                motionPath: {
                    path: '#reserva-path',
                    align: '#reserva-path',
                    alignOrigin: [0.5, 0.5],
                    start: 0
                }
            });
            indicadorNumero.textContent = '1';
            indicadorLabel.textContent = titulosPaso['1'] || '';

            gsap.to(indicador, {
                motionPath: {
                    path: '#reserva-path',
                    align: '#reserva-path',
                    alignOrigin: [0.5, 0.5],
                    start: 0,
                    end: 1
                },
                ease: 'none',
                scrollTrigger: {
                    trigger: contenedorPasos,
                    // "top+=Npx center": el punto que está N px debajo del
                    // borde superior del contenedor. Usamos la posición real
                    // de los waypoints 1 y 3 (no el contenedor completo), así
                    // el progreso 0-1 del scroll coincide con el progreso
                    // 0-1 a lo largo de la ruta.
                    start: () => `top+=${puntoInicioY} center`,
                    end: () => `top+=${puntoFinY} center`,
                    scrub: true,
                    onUpdate: (self) => {
                        let numero = '1';
                        if (self.progress >= 0.999) numero = '3';
                        else if (self.progress >= progresoPaso2) numero = '2';
                        actualizarNumeroYEtiqueta(numero);
                    }
                }
            });
        }

        window.addEventListener('load', () => {
            inicializarGSAP();
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(inicializarGSAP);
            }
        });

        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(inicializarGSAP, 250);
        });

        // Cualquier tarjeta de paso puede cambiar de alto después de cargada
        // (resultados de búsqueda de canciones, canciones agregadas/quitadas,
        // el calendario, el texto de "termina a las..."). En vez de acordarse
        // de reconstruir la ruta manualmente en cada uno de esos lugares, se
        // observa el alto real de las 3 tarjetas y se reconstruye sola cada
        // vez que cambia - así el indicador nunca se queda corto.
        if (window.ResizeObserver) {
            let resizeObserverTimeout;
            const alturaPasosObserver = new ResizeObserver(() => {
                clearTimeout(resizeObserverTimeout);
                resizeObserverTimeout = setTimeout(inicializarGSAP, 80);
            });
            document.querySelectorAll('.step-card').forEach(card => alturaPasosObserver.observe(card));
        }
    </script>
</body>
</html>
