<?php
/*
 * VISTA DEL DASHBOARD DE ADMIN
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
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Fredoka:wght@500;600;700&family=Baloo+2:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/admin.css?v=<?php echo filemtime(PUBLIC_ROOT . '/css/admin.css'); ?>">
</head>
<body>

<?php require APP_ROOT . '/views/admin/includes/sidebar.php'; ?>

<!-- ══ CONTENIDO ══ -->
<div class="content">
    <div class="topline-admin">
        <div>
            <h1 class="titulo-admin"><i class="bi bi-speedometer2"></i> Panel de Control</h1>
            <p class="subtitulo-admin">Esto es lo que necesita tu atención hoy, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>.</p>
        </div>
        <span class="fecha-chip-admin"><i class="bi bi-calendar3"></i> <?php echo htmlspecialchars($fechaHoyTexto); ?></span>
    </div>

    <?php if (($_GET['spotify'] ?? '') === 'ok'): ?>
        <div class="alert alert-success" style="border-radius:12px;"><i class="bi bi-check-circle-fill me-1"></i> Cuenta de Spotify conectada. Las playlists de las próximas reservas ya se crean solas.</div>
    <?php elseif (($_GET['spotify'] ?? '') === 'error'): ?>
        <div class="alert alert-danger" style="border-radius:12px;"><i class="bi bi-exclamation-triangle-fill me-1"></i> No se pudo conectar la cuenta de Spotify. Intenta de nuevo.</div>
    <?php endif; ?>

    <!-- Conexión con Spotify (playlists automáticas) - solo se muestra mientras falta conectar -->
    <?php if (!$spotifyConectado): ?>
        <div class="alert-admin-spotify">
            <i class="bi bi-spotify"></i>
            <div>
                <strong>Playlists de Spotify no conectadas</strong>
                <p class="mb-0">Conecta la cuenta Premium del negocio una sola vez para que las playlists de las reservas se creen automáticamente en Spotify.</p>
            </div>
            <a href="<?php echo URL_ROOT; ?>/admin/spotify-conectar" class="btn-admin-primario">Conectar Spotify</a>
        </div>
    <?php endif; ?>

    <?php if ($spotifyConectado && ($_GET['spotify_debug'] ?? '') === '1'): ?>
        <div class="alert-admin-spotify conectado">
            <i class="bi bi-spotify"></i>
            <div>
                <strong>Spotify conectado (modo diagnóstico)</strong>
                <p class="mb-0">Herramientas de prueba — entra a esta página con <code>?spotify_debug=1</code> cuando necesites revisar la conexión.</p>
            </div>
            <div class="d-flex flex-column align-items-stretch gap-2">
                <button type="button" id="btn-probar-spotify" class="btn-admin-primario">Probar creación de playlist</button>
                <a href="<?php echo URL_ROOT; ?>/admin/spotify-desconectar" class="link-spotify-desconectar" onclick="return confirm('¿Desconectar Spotify? Vas a tener que volver a loguearte y autorizar permisos.');">Desconectar y reconectar</a>
            </div>
        </div>
        <div id="spotify-prueba-resultado" class="alert-spotify-prueba hidden"></div>
    <?php endif; ?>

    <!-- ============ KPIs ============ -->
    <div class="kpi-grid">

        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-icon ico-verde"><i class="bi bi-cash-coin"></i></div>
                <?php if ($deltaIngresos !== null): ?>
                    <span class="kpi-delta <?php echo $deltaIngresos >= 0 ? 'up' : 'down'; ?>">
                        <i class="bi bi-caret-<?php echo $deltaIngresos >= 0 ? 'up' : 'down'; ?>-fill"></i>
                        <?php echo abs($deltaIngresos); ?>%
                    </span>
                <?php endif; ?>
            </div>
            <div>
                <div class="kpi-label">Ingresos del mes</div>
                <div class="kpi-value">S/ <?php echo number_format($ingresosMesActual, 2); ?></div>
            </div>
            <?php if (!empty($grafica['linea'])): ?>
                <svg class="spark" viewBox="0 0 600 190" preserveAspectRatio="none">
                    <polyline points="<?php echo str_replace(['M', ' L'], ['', ' '], $grafica['linea']); ?>" fill="none" stroke="#00a884" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            <?php endif; ?>
        </div>

        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-icon ico-naranja"><i class="bi bi-clock-history"></i></div>
                <?php if (!empty($pendientesUrgentes)): ?>
                    <span class="kpi-delta warn">requieren acción</span>
                <?php endif; ?>
            </div>
            <div>
                <div class="kpi-label">Reservas pendientes</div>
                <div class="kpi-value"><?php echo $reservasPendientes; ?></div>
            </div>
            <div class="kpi-foot">
                <?php if (!empty($pendientesUrgentes)): ?>
                    <?php echo count($pendientesUrgentes); ?> con evento en menos de 5 días
                <?php else: ?>
                    Ninguna con evento próximo urgente
                <?php endif; ?>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-top">
                <div class="kpi-icon ico-celeste"><i class="bi bi-calendar-week-fill"></i></div>
            </div>
            <div>
                <div class="kpi-label">Eventos esta semana</div>
                <div class="kpi-value"><?php echo $eventosSemana; ?></div>
            </div>
            <div class="kpi-foot"><?php echo $proximoEventoTexto ? htmlspecialchars($proximoEventoTexto) : 'No hay eventos confirmados próximos'; ?></div>
        </div>

    </div>

    <!-- ============ BENTO ============ -->
    <div class="bento-admin">

        <!-- LEFT -->
        <div class="stack-admin">

            <div class="panel-admin">
                <div class="panel-admin-head">
                    <div>
                        <h2>Ingresos</h2>
                        <div class="panel-admin-sub">Últimos 30 días, reservas confirmadas</div>
                    </div>
                </div>

                <?php if (!empty($grafica['linea'])): ?>
                    <div class="chart-wrap-admin">
                        <svg viewBox="0 0 600 190" width="100%" height="190" preserveAspectRatio="none" role="img" aria-label="Gráfica de ingresos de los últimos 30 días">
                            <defs>
                                <linearGradient id="fillGradAdmin" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="var(--morado)" stop-opacity="0.28"/>
                                    <stop offset="100%" stop-color="var(--morado)" stop-opacity="0"/>
                                </linearGradient>
                            </defs>
                            <g stroke="#eee5fc" stroke-width="1">
                                <line x1="0" y1="16" x2="600" y2="16"/>
                                <line x1="0" y1="60" x2="600" y2="60"/>
                                <line x1="0" y1="104" x2="600" y2="104"/>
                                <line x1="0" y1="148" x2="600" y2="148"/>
                            </g>
                            <path d="<?php echo $grafica['area']; ?>" fill="url(#fillGradAdmin)"/>
                            <path d="<?php echo $grafica['linea']; ?>" fill="none" stroke="var(--morado)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="<?php echo $grafica['ultimoX']; ?>" cy="<?php echo $grafica['ultimoY']; ?>" r="5" fill="var(--morado)" stroke="#fff" stroke-width="2.5"/>
                        </svg>
                        <span class="chart-end-label-admin">S/ <?php echo number_format($ingresosMesActual, 0); ?></span>
                    </div>
                <?php else: ?>
                    <p class="text-muted mt-3">Todavía no hay suficientes datos para graficar.</p>
                <?php endif; ?>
            </div>

            <div class="panel-admin">
                <div class="panel-admin-head">
                    <div>
                        <h2>Próximos eventos</h2>
                        <div class="panel-admin-sub">Lo que el equipo va a montar los próximos 7 días</div>
                    </div>
                    <a class="panel-admin-link" href="<?php echo URL_ROOT; ?>/admin/reservas">Ver todas<i class="bi bi-chevron-right"></i></a>
                </div>

                <?php if (empty($eventosAgrupados)): ?>
                    <p class="text-muted mt-3">No hay eventos programados en los próximos 7 días.</p>
                <?php else: ?>
                    <?php $metodoLabels = ['yape_mp' => 'Yape · MP', 'plin' => 'Plin', 'yape' => 'Yape']; ?>
                    <?php foreach ($eventosAgrupados as $grupo): ?>
                        <div class="day-group-admin">
                            <div class="day-label-admin"><?php echo htmlspecialchars($grupo['label']); ?></div>
                            <?php foreach ($grupo['eventos'] as $ev): ?>
                                <div class="event-row-admin">
                                    <div class="event-time-admin"><?php echo strtolower(date('g:ia', strtotime($ev->hora_inicio))); ?></div>
                                    <div class="event-main-admin">
                                        <div class="event-title-admin">
                                            Cumpleaños de <?php echo htmlspecialchars($ev->nombre_cumpleanero); ?>
                                            <span class="age-admin">· <?php echo (int) $ev->edad_cumpleanero; ?> años</span>
                                        </div>
                                        <div class="event-meta-admin"><?php echo htmlspecialchars($ev->nombre_paquete); ?> · <?php echo (int) $ev->cantidad_personas; ?> niños</div>
                                        <div class="chips-admin">
                                            <?php if ($ev->extra_pintura): ?><span class="chip-admin extra">🎨 Pintura</span><?php endif; ?>
                                            <?php if ($ev->extra_destruccion): ?><span class="chip-admin extra">💥 Destrucción</span><?php endif; ?>
                                            <?php if ($ev->estado_pago === 'confirmada'): ?>
                                                <span class="chip-admin pay-ok">Pagado</span>
                                            <?php else: ?>
                                                <span class="chip-admin pay-cash"><?php echo $metodoLabels[$ev->metodo_pago] ?? ucfirst($ev->metodo_pago); ?> por confirmar</span>
                                            <?php endif; ?>
                                            <?php if (!empty($ev->spotify_playlist_url)): ?>
                                                <a href="<?php echo htmlspecialchars($ev->spotify_playlist_url); ?>" target="_blank" rel="noopener" class="chip-admin playlist"><i class="bi bi-music-note-beamed"></i> Playlist lista</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>

        <!-- RIGHT -->
        <div class="stack-admin">

            <div class="panel-admin">
                <div class="panel-admin-head">
                    <h2>Pendientes por revisar</h2>
                    <a class="panel-admin-link" href="<?php echo URL_ROOT; ?>/admin/reservas?estado=pendiente">Ver todas<i class="bi bi-chevron-right"></i></a>
                </div>
                <?php if (empty($pendientesRecientes)): ?>
                    <p class="text-muted mt-3">No hay reservas pendientes. 🎉</p>
                <?php else: ?>
                    <?php foreach ($pendientesRecientes as $reserva): ?>
                        <a class="pending-row-admin" href="<?php echo URL_ROOT; ?>/admin/reservas?estado=pendiente">
                            <div class="pending-avatar-admin"><?php echo strtoupper(mb_substr($reserva->nombre_cumpleanero, 0, 1)); ?></div>
                            <div style="flex:1;min-width:0;">
                                <div class="pending-name-admin"><?php echo htmlspecialchars($reserva->nombre_cumpleanero); ?></div>
                                <div class="pending-sub-admin">Evento: <?php echo date('d/m/Y', strtotime($reserva->fecha)); ?></div>
                            </div>
                            <div class="pending-amt-admin">S/ <?php echo number_format($reserva->monto, 2); ?></div>
                            <i class="bi bi-chevron-right pending-chevron-admin"></i>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="panel-admin">
                <div class="panel-admin-head"><h2>Alertas</h2></div>
                <?php if (empty($pendientesUrgentes)): ?>
                    <p class="text-muted mt-3">Sin alertas urgentes por ahora. 🎉</p>
                <?php else: ?>
                    <?php foreach ($pendientesUrgentes as $u): ?>
                        <?php $diasFaltan = (strtotime($u->fecha) - strtotime(date('Y-m-d'))) / 86400; ?>
                        <div class="alert-item-admin <?php echo $diasFaltan <= 2 ? 'critical' : 'warning'; ?>">
                            <i class="bi bi-exclamation-triangle-fill alert-ico-admin"></i>
                            <div class="alert-text-admin">
                                <?php echo htmlspecialchars($u->nombre_cumpleanero); ?> — pago pendiente
                                <span>Evento el <?php echo date('d/m/Y', strtotime($u->fecha)); ?> · S/ <?php echo number_format($u->monto, 2); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="panel-admin">
                <div class="panel-admin-head"><h2>Acciones rápidas</h2></div>
                <div class="qa-grid-admin">
                    <button type="button" class="qa-btn-admin" data-bs-toggle="modal" data-bs-target="#modalReporte">
                        <i class="bi bi-file-earmark-bar-graph-fill"></i><b>Generar reporte</b>
                    </button>
                    <a href="<?php echo URL_ROOT; ?>/admin/codigos" class="qa-btn-admin">
                        <i class="bi bi-ticket-perforated-fill"></i><b>Códigos promo</b>
                    </a>
                    <a href="<?php echo URL_ROOT; ?>/admin/notificaciones" class="qa-btn-admin">
                        <i class="bi bi-bell-fill"></i><b>Enviar aviso</b>
                    </a>
                    <a href="<?php echo URL_ROOT; ?>/admin/reservas?estado=pendiente" class="qa-btn-admin">
                        <i class="bi bi-hourglass-split"></i><b>Ver pendientes</b>
                    </a>
                </div>
            </div>

        </div>
    </div>

    <!-- ============ FILA INFERIOR ============ -->
    <div class="bottom-row-admin">

        <div class="panel-admin">
            <div class="panel-admin-head"><h2>Paquetes más vendidos</h2></div>
            <?php if (empty($paquetesMasVendidos)): ?>
                <p class="text-muted mt-3">Todavía no hay reservas para comparar.</p>
            <?php else: ?>
                <?php foreach ($paquetesMasVendidos as $pq): ?>
                    <div class="bar-row-admin">
                        <span class="bar-label-admin"><?php echo htmlspecialchars($pq->paquete); ?></span>
                        <div class="bar-track-admin"><div class="bar-fill-admin" style="width:<?php echo $pq->porcentaje; ?>%"></div></div>
                        <span class="bar-pct-admin"><?php echo $pq->total; ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="panel-admin">
            <div class="panel-admin-head"><h2>Métodos de pago</h2></div>
            <?php if (empty($metodosPago)): ?>
                <p class="text-muted mt-3">Todavía no hay pagos registrados.</p>
            <?php else: ?>
                <?php
                    $metodoLabelsLargo = ['yape_mp' => 'Yape (Mercado Pago)', 'plin' => 'Plin', 'yape' => 'Yape (manual)'];
                    $coloresMetodo = ['yape_mp' => 'morado', 'plin' => 'celeste', 'yape' => 'faint'];
                ?>
                <div class="pm-track-admin">
                    <?php foreach ($metodosPago as $m): ?>
                        <div class="pm-seg-admin pm-<?php echo $coloresMetodo[$m->metodo] ?? 'faint'; ?>" style="width:<?php echo $m->porcentaje; ?>%"></div>
                    <?php endforeach; ?>
                </div>
                <div class="pm-legend-admin">
                    <?php foreach ($metodosPago as $m): ?>
                        <div class="pm-legend-row-admin">
                            <span class="pm-dot-admin pm-<?php echo $coloresMetodo[$m->metodo] ?? 'faint'; ?>"></span>
                            <?php echo $metodoLabelsLargo[$m->metodo] ?? ucfirst($m->metodo); ?>
                            <b><?php echo $m->porcentaje; ?>%</b>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="panel-admin">
            <div class="panel-admin-head">
                <h2>Comentarios recientes</h2>
                <a class="panel-admin-link" href="<?php echo URL_ROOT; ?>">Ver inicio<i class="bi bi-chevron-right"></i></a>
            </div>
            <?php if (empty($comentariosRecientes)): ?>
                <p class="text-muted mt-3">Todavía no hay comentarios de clientes.</p>
            <?php else: ?>
                <?php foreach ($comentariosRecientes as $c): ?>
                    <div class="review-admin">
                        <div class="review-avatar-admin"><?php echo strtoupper(mb_substr($c->nombre, 0, 1)); ?></div>
                        <div>
                            <div class="review-name-admin"><?php echo htmlspecialchars($c->nombre); ?></div>
                            <div class="review-text-admin"><?php echo htmlspecialchars($c->comentario); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- ══ MODAL REPORTE ══ -->
    <div class="modal fade" id="modalReporte" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
            <div class="modal-content" style="border-radius:18px;overflow:hidden;border:none;box-shadow:0 8px 40px rgba(123,47,247,.18);">

                <div class="modal-header border-0 modal-header-admin">
                    <i class="bi bi-file-earmark-bar-graph-fill text-white fs-5 me-2"></i>
                    <h5 class="modal-title fw-bold text-white mb-0">Generar Reporte</h5>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4" style="background:#fafafa;">

                    <!-- Estado -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;">Estado de pago</label>
                        <select id="r-estado" class="form-select">
                            <option value="all">Todos los estados</option>
                            <option value="confirmada">✔ Confirmadas</option>
                            <option value="pendiente">⏳ Pendientes</option>
                            <option value="cancelada">✘ Canceladas</option>
                        </select>
                    </div>

                    <!-- Selector de periodo -->
                    <label class="form-label fw-semibold text-secondary mb-2 d-block" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;">
                        Periodo del reporte
                        <span class="badge ms-1" style="background:var(--morado);font-size:10px;">mín 1 mes · máx 2 meses</span>
                    </label>

                    <div style="background:#fff;border-radius:14px;border:1.5px solid var(--morado-claro);padding:16px;">
                        <!-- Navegador de año -->
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <button type="button" onclick="mpCambiarAnio(-1)"
                                style="background:var(--morado-claro);color:var(--morado);border:none;border-radius:8px;width:32px;height:32px;font-size:18px;line-height:1;cursor:pointer;">‹</button>
                            <span id="mp-anio" class="fw-bold" style="color:#2D2D2D;font-size:15px;"></span>
                            <button type="button" onclick="mpCambiarAnio(1)"
                                style="background:var(--morado-claro);color:var(--morado);border:none;border-radius:8px;width:32px;height:32px;font-size:18px;line-height:1;cursor:pointer;">›</button>
                        </div>
                        <!-- Grid 4×3 -->
                        <div id="mp-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:7px;"></div>
                        <!-- Resumen -->
                        <div id="mp-resumen" class="mt-3 text-center" style="font-size:13px;min-height:18px;color:#555;"></div>
                    </div>

                    <!-- Alertas -->
                    <div id="r-alerta" class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" style="font-size:13px;border-radius:10px;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i><span id="r-alerta-txt"></span>
                    </div>
                    <div id="r-sin-datos" class="alert alert-warning py-2 px-3 mt-3 mb-0 d-none" style="font-size:13px;border-radius:10px;">
                        <i class="bi bi-database-x me-1"></i>No hay reservas para el periodo y filtro seleccionados. Ajusta los filtros.
                    </div>

                    <!-- Botones -->
                    <div class="d-grid gap-2 mt-3">
                        <button id="btn-excel" onclick="descargarReporte('excel')" class="btn btn-success btn-lg fw-bold" style="border-radius:12px;">
                            <i class="bi bi-file-earmark-excel-fill me-2"></i>Descargar Excel
                        </button>
                        <button id="btn-pdf" onclick="descargarReporte('pdf')" class="btn btn-danger btn-lg fw-bold" style="border-radius:12px;">
                            <i class="bi bi-file-earmark-pdf-fill me-2"></i>Descargar PDF
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div><!-- /content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- ══ SELECTOR DE MESES + VALIDACIONES ══ -->
<script>
(function () {
    const MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    const hoy   = new Date();
    let anio     = hoy.getFullYear();
    let selStart = null;  // {y, m}
    let selEnd   = null;

    function key(y, m) { return y * 100 + m; }

    function render() {
        document.getElementById('mp-anio').textContent = anio;
        const grid     = document.getElementById('mp-grid');
        grid.innerHTML = '';
        const limKey   = key(hoy.getFullYear(), hoy.getMonth());

        for (let m = 0; m < 12; m++) {
            const k   = key(anio, m);
            const btn = document.createElement('button');
            btn.type        = 'button';
            btn.className   = 'mes-btn';
            btn.textContent = MESES[m];

            if (k > limKey) {
                btn.classList.add('mes-disabled');
            } else {
                btn.onclick = () => clickMes(anio, m);
                if (selStart && selEnd) {
                    const ks = key(selStart.y, selStart.m);
                    const ke = key(selEnd.y,   selEnd.m);
                    if      (k === ks && k === ke) btn.classList.add('mes-single');
                    else if (k === ks)              btn.classList.add('mes-start');
                    else if (k === ke)              btn.classList.add('mes-end');
                    else if (k > ks && k < ke)      btn.classList.add('mes-range');
                } else if (selStart && !selEnd) {
                    if (k === key(selStart.y, selStart.m)) btn.classList.add('mes-single');
                }
            }
            grid.appendChild(btn);
        }
        actualizarResumen();
    }

    function clickMes(y, m) {
        if (!selStart || (selStart && selEnd)) {
            selStart = { y, m }; selEnd = null;
        } else {
            const ks = key(selStart.y, selStart.m);
            const ke = key(y, m);
            if (ke < ks) { selStart = { y, m }; selEnd = null; }
            else          { selEnd = { y, m }; }
        }
        render();
    }

    window.mpCambiarAnio = function (d) {
        anio += d;
        if (anio > hoy.getFullYear()) anio = hoy.getFullYear();
        render();
    };

    function actualizarResumen() {
        const resEl = document.getElementById('mp-resumen');
        const errEl = document.getElementById('r-alerta');
        const errTx = document.getElementById('r-alerta-txt');
        errEl.classList.add('d-none');

        if (!selStart) { resEl.textContent = 'Selecciona el mes de inicio'; return; }
        if (!selEnd)   { resEl.textContent = 'Ahora selecciona el mes de fin'; return; }

        const diff = (selEnd.y - selStart.y) * 12 + (selEnd.m - selStart.m) + 1;

        if (diff < 1) {
            errTx.textContent = 'Selecciona al menos 1 mes.';
            errEl.classList.remove('d-none');
            resEl.textContent = '';
            return;
        }
        if (diff > 2) {
            errTx.textContent = 'El rango máximo es de 2 meses (' + diff + ' seleccionados). Ajusta la selección.';
            errEl.classList.remove('d-none');
            resEl.innerHTML = '<span style="color:#B71C1C;">Rango demasiado amplio</span>';
            return;
        }

        const ni = MESES[selStart.m] + ' ' + selStart.y;
        const nf = MESES[selEnd.m]   + ' ' + selEnd.y;
        resEl.innerHTML =
            '<span style="color:var(--morado);font-weight:600;">📅 ' +
            (diff === 1 ? ni : ni + ' → ' + nf) +
            ' &nbsp;·&nbsp; ' + diff + ' mes' + (diff > 1 ? 'es' : '') +
            '</span>';
    }

    function getFechas() {
        // Si solo hay inicio, usar ese mes como fin también
        const fin   = selEnd || selStart;
        if (!selStart || !fin) return null;
        const diff  = (fin.y - selStart.y) * 12 + (fin.m - selStart.m) + 1;
        if (diff < 1 || diff > 2) return null;
        const pad   = n => String(n).padStart(2, '0');
        const desde = selStart.y + '-' + pad(selStart.m + 1) + '-01';
        const last  = new Date(fin.y, fin.m + 1, 0).getDate();
        const hasta = fin.y + '-' + pad(fin.m + 1) + '-' + pad(last);
        return { desde, hasta };
    }

    window.descargarReporte = function (tipo) {
        const errEl = document.getElementById('r-alerta');
        const errTx = document.getElementById('r-alerta-txt');
        const sdEl  = document.getElementById('r-sin-datos');
        errEl.classList.add('d-none');
        sdEl.classList.add('d-none');

        if (!selStart) {
            errTx.textContent = 'Selecciona al menos el mes de inicio.';
            errEl.classList.remove('d-none');
            return;
        }
        // Un solo clic = 1 mes
        if (!selEnd) selEnd = { ...selStart };

        const diff = (selEnd.y - selStart.y) * 12 + (selEnd.m - selStart.m) + 1;
        if (diff > 2) {
            errTx.textContent = 'El rango máximo es 2 meses. Ajusta la selección.';
            errEl.classList.remove('d-none');
            return;
        }

        const fechas = getFechas();
        if (!fechas) return;

        const estado = document.getElementById('r-estado').value;
        const params = new URLSearchParams({ estado, fecha_desde: fechas.desde, fecha_hasta: fechas.hasta });

        // Deshabilitar botones + spinner mientras verifica
        const btnE = document.getElementById('btn-excel');
        const btnP = document.getElementById('btn-pdf');
        btnE.disabled = btnP.disabled = true;
        btnE.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verificando...';
        btnP.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verificando...';

        fetch('<?php echo URL_ROOT; ?>/reporte/verificar?' + params.toString())
            .then(r => r.json())
            .then(data => {
                btnE.disabled = btnP.disabled = false;
                btnE.innerHTML = '<i class="bi bi-file-earmark-excel-fill me-2"></i>Descargar Excel';
                btnP.innerHTML = '<i class="bi bi-file-earmark-pdf-fill me-2"></i>Descargar PDF';
                if (data.total === 0) { sdEl.classList.remove('d-none'); return; }
                window.location.href = '<?php echo URL_ROOT; ?>/reporte/' + tipo + '?' + params.toString();
            })
            .catch(() => {
                btnE.disabled = btnP.disabled = false;
                btnE.innerHTML = '<i class="bi bi-file-earmark-excel-fill me-2"></i>Descargar Excel';
                btnP.innerHTML = '<i class="bi bi-file-earmark-pdf-fill me-2"></i>Descargar PDF';
                // Si falla el verificar, descargar de todas formas
                window.location.href = '<?php echo URL_ROOT; ?>/reporte/' + tipo + '?' + params.toString();
            });
    };

    // Reset al abrir modal
    document.getElementById('modalReporte').addEventListener('show.bs.modal', function () {
        selStart = null; selEnd = null;
        anio = hoy.getFullYear();
        render();
        document.getElementById('r-alerta').classList.add('d-none');
        document.getElementById('r-sin-datos').classList.add('d-none');
    });

    render();
})();
</script>

<?php if ($spotifyConectado && ($_GET['spotify_debug'] ?? '') === '1'): ?>
<!-- ══ PROBAR CREACIÓN DE PLAYLIST (diagnóstico) ══ -->
<script>
document.getElementById('btn-probar-spotify').addEventListener('click', async function () {
    const btn = this;
    const caja = document.getElementById('spotify-prueba-resultado');
    const textoOriginal = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Probando...';
    caja.className = 'alert-spotify-prueba hidden';

    try {
        const resp = await fetch('<?php echo URL_ROOT; ?>/admin/spotify-probar-playlist', { method: 'POST' });
        const data = await resp.json();

        caja.classList.remove('hidden');
        if (data.ok) {
            caja.classList.add('exito');
            caja.innerHTML = '✅ Funciona. Se creó una playlist de prueba: <a href="' + data.resultado + '" target="_blank" rel="noopener">abrir en Spotify</a> (bórrala cuando quieras, era solo para probar).';
        } else {
            caja.classList.add('error');
            caja.textContent = '❌ ' + data.resultado;
        }
    } catch (err) {
        caja.classList.remove('hidden');
        caja.classList.add('error');
        caja.textContent = '❌ No se pudo hacer la prueba: ' + err.message;
    }

    btn.disabled = false;
    btn.textContent = textoOriginal;
});
</script>
<?php endif; ?>

<!-- ══ CHATBOT ADMIN — HappyBot ══ -->
<script>
window.HJ_URL_ROOT = "<?= URL_ROOT ?>";
</script>
<script src="<?= URL_ROOT ?>/js/chatbot_admin.js"></script>

</body>
</html>
