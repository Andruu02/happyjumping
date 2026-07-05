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
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Poppins', Arial, sans-serif; background: #f4f8ff; margin: 0; overflow-x: hidden; }

    /* Sidebar */
    .sidebar { width:240px; height:100vh; background:#7F00FF; position:fixed; top:0; left:0; padding-top:25px; color:white; display:flex; flex-direction:column; align-items:center; }
    .sidebar img { width:120px; margin-bottom:25px; }
    .sidebar a { width:100%; text-decoration:none; padding:14px 20px 14px 30px; color:white; font-weight:bold; text-align:left; transition:0.3s; font-size:17px; }
    .sidebar a i { margin-right:10px; }
    .sidebar a:hover, .sidebar a.active { background:#6200c4; }
    .sidebar .btn-logout { background:#00d8ff; color:black; font-weight:bold; border-radius:8px; width:80%; margin-top:auto; margin-bottom:20px; text-align:center; padding-left:14px; }
    .sidebar .btn-logout:hover { background:#fff; }

    /* Contenido */
    .content { margin-left:240px; padding:30px; }
    .title { font-size:32px; font-weight:bold; color:#7F00FF; }

    /* Cards estadísticas */
    .stat-card { background:#fff; border-radius:14px; padding:20px; box-shadow:0 0 12px rgba(0,0,0,.08); display:flex; align-items:center; }
    .stat-card-icon { font-size:2.5rem; padding:15px; border-radius:10px; margin-right:15px; }
    .stat-card-info h5 { font-size:1rem; color:#888; font-weight:600; margin-bottom:5px; }
    .stat-card-info .stat-number { font-size:2rem; font-weight:700; color:#333; }
    .icon-clientes   { background:#e0f7fa; color:#00838f; }
    .icon-ingresos   { background:#e8f5e9; color:#2e7d32; }
    .icon-pendientes { background:#fff8e1; color:#f9a825; }

    /* Cards sección */
    .card-section { background:#ffffff; border-radius:14px; padding:20px; box-shadow:0 0 12px rgba(0,0,0,.08); border:none; height:350px; }
    .card-section h5 { color:#7F00FF; font-weight:bold; }
    .reservas-list { list-style:none; padding:0; max-height:280px; overflow-y:auto; }
    .reservas-list li { display:flex; justify-content:space-between; padding:12px 5px; border-bottom:1px solid #eee; }
    .reservas-list li .nombre { font-weight:600; color:#333; }
    .reservas-list li .fecha  { font-weight:500; color:#7F00FF; }

    /* Selector de meses */
    .mes-btn { border:1.5px solid #e0d0ff; background:#fff; color:#444; border-radius:10px; padding:8px 4px; font-size:13px; font-weight:500; cursor:pointer; text-align:center; transition:all .15s; user-select:none; width:100%; }
    .mes-btn:hover:not(.mes-disabled)  { background:#f0e6ff; border-color:#7F00FF; color:#7F00FF; }
    .mes-btn.mes-start  { background:#7F00FF!important; color:#fff!important; border-color:#7F00FF!important; border-radius:10px 0 0 10px!important; }
    .mes-btn.mes-end    { background:#7F00FF!important; color:#fff!important; border-color:#7F00FF!important; border-radius:0 10px 10px 0!important; }
    .mes-btn.mes-single { background:#7F00FF!important; color:#fff!important; border-color:#7F00FF!important; border-radius:10px!important; }
    .mes-btn.mes-range  { background:#ede7ff!important; color:#7F00FF!important; border-color:#c9a9ff!important; border-radius:0!important; }
    .mes-btn.mes-disabled { color:#ccc!important; cursor:not-allowed; background:#fafafa!important; pointer-events:none; }
</style>
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<div class="sidebar">
    <img src="<?php echo URL_ROOT; ?>/img/logo_happy_contorno.png" alt="Logo">
    <a href="<?php echo URL_ROOT; ?>/admin"              class="active"><i class="bi bi-house-door-fill"></i> Dashboard</a>
    <a href="<?php echo URL_ROOT; ?>/admin/reservas">             <i class="bi bi-calendar-fill"></i> Reservas</a>
    <a href="<?php echo URL_ROOT; ?>/admin/codigos">              <i class="bi bi-ticket-perforated-fill"></i> Códigos</a>
    <a href="<?php echo URL_ROOT; ?>/admin/notificaciones">       <i class="bi bi-bell-fill"></i> Notificaciones</a>
    <a href="<?php echo URL_ROOT; ?>/admin/correos">               <i class="bi bi-envelope-fill"></i> Correos</a>
    <a href="<?php echo URL_ROOT; ?>/usuarios/logout" class="btn btn-logout">Cerrar sesión</a>
</div>

<!-- ══ CONTENIDO ══ -->
<div class="content">
    <p class="title">Panel de Control</p>
    <p class="text-muted fs-5">Bienvenido de nuevo, <?php echo $_SESSION['usuario_nombre']; ?>.</p>

    <!-- Tarjetas estadísticas -->
    <div class="row g-4 mt-2">
        <div class="col-lg-4 col-md-6">
            <div class="stat-card">
                <div class="stat-card-icon icon-ingresos"><i class="bi bi-cash-coin"></i></div>
                <div class="stat-card-info">
                    <h5>Ingresos Totales</h5>
                    <span class="stat-number">S/ <?php echo number_format($ingresosTotales, 2); ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stat-card">
                <div class="stat-card-icon icon-clientes"><i class="bi bi-people-fill"></i></div>
                <div class="stat-card-info">
                    <h5>Total Clientes</h5>
                    <span class="stat-number"><?php echo $totalClientes; ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stat-card">
                <div class="stat-card-icon icon-pendientes"><i class="bi bi-clock-history"></i></div>
                <div class="stat-card-info">
                    <h5>Reservas Pendientes</h5>
                    <span class="stat-number"><?php echo $reservasPendientes; ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón Generar Reporte -->
    <div class="d-flex justify-content-end mt-3 mb-1">
        <button class="btn fw-bold px-4 py-2"
                style="background:#7F00FF;color:#fff;border-radius:10px;font-size:15px;"
                data-bs-toggle="modal" data-bs-target="#modalReporte">
            <i class="bi bi-file-earmark-bar-graph-fill me-2"></i>Generar Reporte
        </button>
    </div>

    <!-- ══ MODAL REPORTE ══ -->
    <div class="modal fade" id="modalReporte" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
            <div class="modal-content" style="border-radius:18px;overflow:hidden;border:none;box-shadow:0 8px 40px rgba(127,0,255,.18);">

                <div class="modal-header border-0" style="background:#7F00FF;">
                    <i class="bi bi-file-earmark-bar-graph-fill text-white fs-5 me-2"></i>
                    <h5 class="modal-title fw-bold text-white mb-0">Generar Reporte</h5>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4" style="background:#fafafa;">

                    <!-- Estado -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;">Estado de pago</label>
                        <select id="r-estado" class="form-select" style="border-radius:10px;border:1.5px solid #e0d0ff;">
                            <option value="all">Todos los estados</option>
                            <option value="confirmada">✔ Confirmadas</option>
                            <option value="pendiente">⏳ Pendientes</option>
                            <option value="cancelada">✘ Canceladas</option>
                        </select>
                    </div>

                    <!-- Selector de periodo -->
                    <label class="form-label fw-semibold text-secondary mb-2 d-block" style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;">
                        Periodo del reporte
                        <span class="badge ms-1" style="background:#7F00FF;font-size:10px;">mín 1 mes · máx 2 meses</span>
                    </label>

                    <div style="background:#fff;border-radius:14px;border:1.5px solid #e0d0ff;padding:16px;">
                        <!-- Navegador de año -->
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <button type="button" onclick="mpCambiarAnio(-1)"
                                style="background:#f0e6ff;color:#7F00FF;border:none;border-radius:8px;width:32px;height:32px;font-size:18px;line-height:1;cursor:pointer;">‹</button>
                            <span id="mp-anio" class="fw-bold" style="color:#2D2D2D;font-size:15px;"></span>
                            <button type="button" onclick="mpCambiarAnio(1)"
                                style="background:#f0e6ff;color:#7F00FF;border:none;border-radius:8px;width:32px;height:32px;font-size:18px;line-height:1;cursor:pointer;">›</button>
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

    <!-- Gráfica + Próximas reservas -->
    <div class="row g-4 mt-3">
        <div class="col-lg-8">
            <div class="card-section">
                <h5>Ingresos (Últimos 7 días)</h5>
                <canvas id="ingresosChart"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card-section">
                <h5>Próximas Reservas (Confirmadas)</h5>
                <ul class="reservas-list">
                    <?php if (empty($proximasReservas)): ?>
                        <p class="text-muted mt-3">No hay reservas confirmadas próximas.</p>
                    <?php else: ?>
                        <?php foreach($proximasReservas as $reserva): ?>
                            <li>
                                <span class="nombre"><?php echo $reserva->nombre_cumpleanero; ?></span>
                                <span class="fecha"><?php echo date('d/m/Y', strtotime($reserva->fecha)); ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

</div><!-- /content -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- ══ GRÁFICA ══ -->
<script>
    const labels     = <?php echo $chartLabels; ?>;
    const dataValues = <?php echo $chartData; ?>;
    const ctx = document.getElementById('ingresosChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ingresos (S/)',
                data: dataValues,
                borderColor: '#7F00FF',
                backgroundColor: 'rgba(127,0,255,0.08)',
                borderWidth: 2,
                pointBackgroundColor: '#7F00FF',
                pointRadius: 4,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: true } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => 'S/ ' + v }
                }
            }
        }
    });
</script>

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
            '<span style="color:#7F00FF;font-weight:600;">📅 ' +
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
<!-- ══ CHATBOT ADMIN ══ -->
<style>
#hj-chat-widget { position:fixed; bottom:28px; right:28px; z-index:9999; font-family:'Poppins',Arial,sans-serif; }
#hj-chat-btn { width:58px; height:58px; border-radius:50%; background:#7F00FF; color:#fff; border:none; font-size:1.6rem; cursor:pointer; box-shadow:0 4px 18px rgba(127,0,255,.45); display:flex; align-items:center; justify-content:center; transition:transform .15s; }
#hj-chat-btn:hover { transform:scale(1.1); }
#hj-chat-panel { position:absolute; bottom:70px; right:0; width:360px; background:#fff; border-radius:18px; box-shadow:0 8px 40px rgba(0,0,0,.18); display:flex; flex-direction:column; overflow:hidden; transition:opacity .2s, transform .2s; }
#hj-chat-panel.hj-oculto { opacity:0; pointer-events:none; transform:translateY(12px); }
#hj-chat-header { background:#7F00FF; color:#fff; padding:14px 18px; display:flex; justify-content:space-between; align-items:center; font-weight:700; font-size:.95rem; }
#hj-chat-cerrar { background:none; border:none; color:#fff; font-size:1rem; cursor:pointer; }
#hj-chat-cerrar:hover { opacity:.7; }
#hj-chat-mensajes { padding:16px; height:300px; overflow-y:auto; display:flex; flex-direction:column; gap:10px; background:#f8f5ff; }
.hj-msg { max-width:85%; padding:10px 14px; border-radius:14px; font-size:.88rem; line-height:1.5; word-break:break-word; white-space:pre-wrap; }
.hj-msg-bot { background:#fff; color:#333; align-self:flex-start; box-shadow:0 1px 4px rgba(0,0,0,.08); border-bottom-left-radius:4px; }
.hj-msg-user { background:#7F00FF; color:#fff; align-self:flex-end; border-bottom-right-radius:4px; }
.hj-msg-cargando { background:#ede7ff; color:#7F00FF; align-self:flex-start; font-style:italic; }
#hj-chat-sugerencias { display:flex; flex-wrap:wrap; gap:6px; padding:10px 16px 0; }
.hj-sug { background:#f3e5ff; color:#7F00FF; border:1px solid #d4a9ff; border-radius:999px; padding:4px 12px; font-size:.78rem; cursor:pointer; font-family:'Poppins',Arial,sans-serif; transition:background .15s; }
.hj-sug:hover { background:#e0c4ff; }
#hj-chat-input-wrap { display:flex; gap:8px; padding:12px 16px; border-top:1px solid #eee; background:#fff; }
#hj-chat-input { flex:1; border:1.5px solid #ddd; border-radius:999px; padding:8px 16px; font-size:.88rem; outline:none; font-family:'Poppins',Arial,sans-serif; }
#hj-chat-input:focus { border-color:#7F00FF; }
#hj-chat-enviar { background:#7F00FF; color:#fff; border:none; border-radius:50%; width:38px; height:38px; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:1rem; }
#hj-chat-enviar:hover { background:#6200c4; }
#hj-chat-enviar:disabled { background:#ccc; cursor:wait; }
</style>

<div id="hj-chat-widget">
    <button id="hj-chat-btn" title="Asistente IA">
        <i class="bi bi-robot"></i>
    </button>
    <div id="hj-chat-panel" class="hj-oculto">
        <div id="hj-chat-header">
            <span><i class="bi bi-robot me-2"></i>Asistente Happy Jumping</span>
            <button id="hj-chat-cerrar"><i class="bi bi-x-lg"></i></button>
        </div>
        <div id="hj-chat-mensajes">
            <div class="hj-msg hj-msg-bot">Hola 👋 Soy tu asistente. Puedo consultarte reservas, pagos, clientes y más. ¿En qué te ayudo?</div>
        </div>
        <div id="hj-chat-sugerencias">
            <button class="hj-sug" data-texto="¿Cuántas reservas tenemos hoy?">Reservas hoy</button>
            <button class="hj-sug" data-texto="¿Qué pagos están pendientes?">Pagos pendientes</button>
            <button class="hj-sug" data-texto="¿Cuál es el paquete más vendido?">Paquete top</button>
            <button class="hj-sug" data-texto="¿Cuánto hemos ingresado este mes?">Ingresos del mes</button>
        </div>
        <div id="hj-chat-input-wrap">
            <input id="hj-chat-input" type="text" placeholder="Escribe tu pregunta..." autocomplete="off" />
            <button id="hj-chat-enviar"><i class="bi bi-send-fill"></i></button>
        </div>
    </div>
</div>

<script>
(function () {
    var historial = [];
    var urlBase = '<?php echo URL_ROOT; ?>';

    document.getElementById('hj-chat-btn').addEventListener('click', function () {
        var panel = document.getElementById('hj-chat-panel');
        panel.classList.toggle('hj-oculto');
        if (!panel.classList.contains('hj-oculto')) document.getElementById('hj-chat-input').focus();
    });

    document.getElementById('hj-chat-cerrar').addEventListener('click', function () {
        document.getElementById('hj-chat-panel').classList.add('hj-oculto');
    });

    document.querySelectorAll('.hj-sug').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('hj-chat-input').value = btn.dataset.texto;
            enviar();
        });
    });

    document.getElementById('hj-chat-input').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); enviar(); }
    });
    document.getElementById('hj-chat-enviar').addEventListener('click', enviar);

    function enviar() {
        var input = document.getElementById('hj-chat-input');
        var texto = input.value.trim();
        if (!texto) return;
        agregarMensaje(texto, 'user');
        input.value = '';
        document.getElementById('hj-chat-enviar').disabled = true;
        var cargando = agregarMensaje('Consultando datos...', 'cargando');

        fetch(urlBase + '/chatbot/enviar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pregunta: texto, historial: historial })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            cargando.remove();
            var resp = data.error ? 'Error: ' + data.error : data.respuesta;
            agregarMensaje(resp, 'bot');
            if (!data.error) {
                historial.push({ role: 'user', content: texto });
                historial.push({ role: 'assistant', content: data.respuesta });
                if (historial.length > 12) historial = historial.slice(-12);
            }
        })
        .catch(function () {
            cargando.remove();
            agregarMensaje('No se pudo conectar. Intenta de nuevo.', 'bot');
        })
        .finally(function () {
            document.getElementById('hj-chat-enviar').disabled = false;
            document.getElementById('hj-chat-input').focus();
        });
    }

    function agregarMensaje(texto, tipo) {
        var div = document.createElement('div');
        div.className = 'hj-msg hj-msg-' + tipo;
        div.textContent = texto;
        var mensajes = document.getElementById('hj-chat-mensajes');
        mensajes.appendChild(div);
        mensajes.scrollTop = mensajes.scrollHeight;
        return div;
    }
})();
</script>
<script>

window.HJ_URL_ROOT="<?= URL_ROOT ?>";

</script>

<script src="<?= URL_ROOT ?>/js/chatbot_admin.js"></script>
</body>
</html>