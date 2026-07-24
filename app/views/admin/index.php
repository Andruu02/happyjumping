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
    <h1 class="titulo-admin"><i class="bi bi-speedometer2"></i> Panel de Control</h1>
    <p class="subtitulo-admin">Bienvenido de nuevo, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>.</p>

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
        <button class="btn-admin-primario" data-bs-toggle="modal" data-bs-target="#modalReporte">
            <i class="bi bi-file-earmark-bar-graph-fill"></i> Generar Reporte
        </button>
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
                                <span class="nombre"><?php echo htmlspecialchars($reserva->nombre_cumpleanero); ?></span>
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
                borderColor: '#7b2ff7',
                backgroundColor: 'rgba(123,47,247,0.08)',
                borderWidth: 2,
                pointBackgroundColor: '#7b2ff7',
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

<!-- ══ CHATBOT ADMIN — HappyBot ══ -->
<script>
window.HJ_URL_ROOT = "<?= URL_ROOT ?>";
</script>
<script src="<?= URL_ROOT ?>/js/chatbot_admin.js"></script>

</body>
</html>
