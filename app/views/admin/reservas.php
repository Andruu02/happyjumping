<?php /* VISTA: app/views/admin/reservas.php */ ?>
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

<div class="content">
    <h1 class="titulo-admin"><i class="bi bi-calendar-fill"></i> Gestión de Reservas</h1>
    <p class="subtitulo-admin">Controla las solicitudes y pagos de los clientes.</p>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $mensaje['tipo']; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensaje['texto']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="mb-3 d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Reservas encontradas: <strong><?php echo $totalReservas; ?></strong></h4>
        <button type="button" id="btn-refrescar-reservas" class="btn btn-outline-primary btn-sm" title="Refrescar">
            <i class="bi bi-arrow-clockwise"></i> Refrescar
        </button>
    </div>

    <div class="filtros-card">
        <form method="GET" action="<?php echo URL_ROOT; ?>/admin/reservas" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label fw-bold mb-1">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="all"        <?php echo $filtros['estado'] === 'all'        ? 'selected' : ''; ?>>Todas</option>
                    <option value="pendiente"  <?php echo $filtros['estado'] === 'pendiente'  ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="confirmada" <?php echo $filtros['estado'] === 'confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                    <option value="cancelada"  <?php echo $filtros['estado'] === 'cancelada'  ? 'selected' : ''; ?>>Cancelada</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label fw-bold mb-1">Paquete</label>
                <select name="id_paquete" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($paquetes as $paquete): ?>
                        <option value="<?php echo $paquete->id_paquete; ?>" <?php echo (string) $filtros['id_paquete'] === (string) $paquete->id_paquete ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($paquete->nombre); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-bold mb-1">Desde</label>
                <input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filtros['fecha_desde']); ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label fw-bold mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filtros['fecha_hasta']); ?>">
            </div>
            <div class="col-8 col-md-2">
                <label class="form-label fw-bold mb-1">Cliente</label>
                <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Nombre o correo" value="<?php echo htmlspecialchars($filtros['buscar']); ?>">
            </div>
            <div class="col-4 col-md-1 d-grid">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel-fill"></i> Filtrar</button>
            </div>
            <?php if ($filtros['estado'] !== 'all' || $filtros['id_paquete'] !== '' || $filtros['fecha_desde'] !== '' || $filtros['fecha_hasta'] !== '' || $filtros['buscar'] !== ''): ?>
            <div class="col-12">
                <a href="<?php echo URL_ROOT; ?>/admin/reservas" class="small text-muted"><i class="bi bi-x-circle"></i> Limpiar filtros</a>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <div class="admin-card">
        <div class="table-responsive">
            <table class="table table-hover admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Cliente / Paquete</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Estado actual</th>
                        <th>Cambiar estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($reservas)): ?>
                    <tr><td colspan="8" class="text-center text-muted">No hay reservas con este filtro.</td></tr>
                <?php else: ?>
                    <?php foreach ($reservas as $reserva): ?>
                    <tr>
                        <td><strong>#<?php echo $reserva->id_reserva; ?></strong></td>
                        <td>
                            <?php echo date('d/m/Y', strtotime($reserva->fecha)); ?><br>
                            <small class="text-muted"><?php echo substr($reserva->hora_inicio, 0, 5); ?></small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($reserva->nombre_cliente); ?></strong><br>
                            <small class="text-info"><?php echo htmlspecialchars($reserva->nombre_paquete); ?></small>
                        </td>
                        <td>S/ <?php echo number_format($reserva->monto, 2); ?></td>
                        <td>
                            <?php
                                $metodoLabels = ['yape_mp' => 'Yape · MP', 'plin' => 'Plin', 'yape' => 'Yape (manual)'];
                                $metodoTexto = $metodoLabels[$reserva->metodo_pago] ?? ucfirst($reserva->metodo_pago);
                            ?>
                            <span class="badge-metodo badge-metodo-<?php echo htmlspecialchars($reserva->metodo_pago); ?>"><?php echo htmlspecialchars($metodoTexto); ?></span>
                            <?php if (!empty($reserva->mp_payment_id)): ?>
                                <br><small class="text-muted">#<?php echo htmlspecialchars($reserva->mp_payment_id); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $reserva->estado_pago; ?>">
                                <?php echo strtoupper($reserva->estado_pago); ?>
                            </span>
                        </td>
                        <td>
                            <?php /* FORM individual por fila - POST a /admin/reservas */ ?>
                            <form method="POST" action="<?php echo URL_ROOT; ?>/admin/reservas"
                                  style="display:flex; gap:6px; align-items:center;"
                                  onsubmit="return confirmarCambio(this)">
                                <input type="hidden" name="id_reserva" value="<?php echo $reserva->id_reserva; ?>">
                                <select name="estado"
                                        class="form-select form-select-sm sel-<?php echo $reserva->estado_pago; ?>"
                                        style="width:130px"
                                        onchange="this.className='form-select form-select-sm sel-'+this.value">
                                    <option value="pendiente"  <?php echo $reserva->estado_pago === 'pendiente'  ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="confirmada" <?php echo $reserva->estado_pago === 'confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                                    <option value="cancelada"  <?php echo $reserva->estado_pago === 'cancelada'  ? 'selected' : ''; ?>>Cancelada</option>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-floppy-fill"></i> Guardar
                                </button>
                            </form>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info text-white"
                                    data-bs-toggle="modal" data-bs-target="#modalDetalle"
                                    data-reserva='<?php echo htmlspecialchars(json_encode($reserva), ENT_QUOTES); ?>'>
                                <i class="bi bi-eye"></i> Ver
                            </button>
                            <?php if ($reserva->ruta_captura): ?>
                                <a href="<?php echo URL_ROOT; ?>/uploads/capturas/<?php echo $reserva->ruta_captura; ?>"
                                   target="_blank" class="btn btn-sm btn-warning mt-1">
                                    <i class="bi bi-image"></i> Pago
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPaginas > 1): ?>
        <div class="paginacion-admin mt-3">
            <span class="pagina-info">Página <?php echo $pagina; ?> de <?php echo $totalPaginas; ?></span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php
                    $qs = $_GET;
                    for ($p = 1; $p <= $totalPaginas; $p++):
                        $qs['pagina'] = $p;
                        $activa = $p === $pagina;
                    ?>
                        <li class="page-item <?php echo $activa ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo http_build_query($qs); ?>"><?php echo $p; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <span class="pagina-spacer"></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal detalle -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px;overflow:hidden;border:none;">
            <div class="modal-header modal-header-admin border-0">
                <h5 class="modal-title fw-bold text-white mb-0">Detalle Reserva #<span id="d_id"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="d_body"></div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('[data-reserva]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var r = JSON.parse(this.dataset.reserva);
        document.getElementById('d_id').textContent = r.id_reserva;
        document.getElementById('d_body').innerHTML =
            '<p><strong>Cliente:</strong> ' + r.nombre_cliente + ' (' + r.correo_cliente + ')</p>' +
            '<p><strong>Cumpleanero:</strong> ' + r.nombre_cumpleanero + ', ' + r.edad_cumpleanero + ' anos</p>' +
            '<p><strong>Paquete:</strong> ' + r.nombre_paquete + '</p>' +
            '<p><strong>Fecha:</strong> ' + r.fecha + ' a las ' + r.hora_inicio.substring(0,5) + '</p>' +
            '<p><strong>Monto:</strong> S/ ' + parseFloat(r.monto).toFixed(2) + '</p>' +
            '<p><strong>Método de pago:</strong> ' + ({yape_mp:'Yape (Mercado Pago)', plin:'Plin', yape:'Yape (manual)'}[r.metodo_pago] || r.metodo_pago) +
                (r.mp_payment_id ? ' — id de pago: ' + r.mp_payment_id : '') + '</p>' +
            '<p><strong>Observaciones:</strong> ' + (r.observaciones || 'Ninguna') + '</p>' +
            '<p><strong>Estado:</strong> <span class="status-badge status-' + r.estado_pago + '">' + r.estado_pago.toUpperCase() + '</span></p>' +
            (r.spotify_playlist_url
                ? '<p><strong>🎵 Playlist en Spotify:</strong> <a href="' + r.spotify_playlist_url + '" target="_blank" rel="noopener">Abrir en Spotify</a></p>'
                : '') +
            '<p><strong>Canciones pedidas:</strong></p>' +
            '<div id="d_playlist"><span class="text-muted">Cargando...</span></div>';

        fetch(window.HJ_URL_ROOT + '/admin/cancionesReserva/' + r.id_reserva)
            .then(function(resp) { return resp.json(); })
            .then(function(canciones) {
                var contenedor = document.getElementById('d_playlist');
                if (!contenedor) return; // el usuario ya cerró el modal / abrió otro

                if (!Array.isArray(canciones) || canciones.length === 0) {
                    contenedor.innerHTML = '<span class="text-muted">El cliente no sugirió canciones.</span>';
                    return;
                }

                var lista = document.createElement('ul');
                lista.className = 'mb-0 ps-3';
                canciones.forEach(function(c) {
                    var li = document.createElement('li');
                    if (c.enlace) {
                        var a = document.createElement('a');
                        a.href = c.enlace;
                        a.target = '_blank';
                        a.rel = 'noopener';
                        a.textContent = c.nombre + ' — ' + c.artista;
                        li.appendChild(a);
                    } else {
                        li.textContent = c.nombre + ' — ' + c.artista;
                    }
                    lista.appendChild(li);
                });
                contenedor.innerHTML = '';
                contenedor.appendChild(lista);
            })
            .catch(function() {
                var contenedor = document.getElementById('d_playlist');
                if (contenedor) contenedor.innerHTML = '<span class="text-danger">No se pudo cargar la playlist.</span>';
            });
    });
});
</script>
<script>
function confirmarCambio(form) {
    const select = form.querySelector('select[name="estado"]');
    const estadoNuevo = select.options[select.selectedIndex].text;
    const id = form.querySelector('input[name="id_reserva"]').value;
    return confirm('¿Confirmar cambio de estado de la Reserva #' + id + ' a "' + estadoNuevo + '"?');
}
</script>
<script>
// Recarga la tabla manteniendo los filtros ya aplicados (van en la URL vía
// GET) - no hace falta AJAX, la página entera es server-rendered.
document.getElementById('btn-refrescar-reservas').addEventListener('click', function () {
    this.disabled = true;
    this.querySelector('i').classList.add('girando');
    window.location.reload();
});
</script>
<script>
window.HJ_URL_ROOT = "<?php echo URL_ROOT; ?>";
</script>

<script src="<?= URL_ROOT ?>/js/chatbot_admin.js"></script>
</body>
</html>
