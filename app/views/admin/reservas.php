<?php /* VISTA: app/views/admin/reservas.php */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $titulo; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { font-family: 'Poppins', Arial, sans-serif; background: #f4f8ff; margin: 0; }
    .sidebar {
        width: 240px; height: 100vh; background: #7F00FF;
        position: fixed; top: 0; left: 0; padding-top: 25px;
        color: white; display: flex; flex-direction: column; align-items: center;
    }
    .sidebar img { width: 120px; margin-bottom: 25px; }
    .sidebar a {
        width: 100%; text-decoration: none; padding: 14px 30px;
        color: white; font-weight: bold; font-size: 17px; transition: 0.3s;
    }
    .sidebar a i { margin-right: 10px; }
    .sidebar a:hover, .sidebar a.active { background: #6200c4; }
    .sidebar .btn-logout {
        background: #00d8ff; color: black; font-weight: bold;
        border-radius: 8px; width: 80%; margin-top: auto;
        margin-bottom: 20px; text-align: center; padding: 10px;
    }
    .sidebar .btn-logout:hover { background: #fff; }
    .content { margin-left: 240px; padding: 30px; }
    .title { font-size: 32px; font-weight: bold; color: #7F00FF; }
    .table-reservas th { background-color: #7F00FF; color: white; }
    .status-badge { padding: 5px 12px; border-radius: 10px; font-weight: bold; font-size: 13px; display: inline-block; }
    .status-pendiente  { background: #fff3cd; color: #856404; }
    .status-confirmada { background: #d1e7dd; color: #0a3622; }
    .status-cancelada  { background: #f8d7da; color: #58151c; }
    .sel-pendiente  { background-color: #fff3cd !important; color: #856404 !important; border-color: #ffc107 !important; font-weight: bold; }
    .sel-confirmada { background-color: #d1e7dd !important; color: #0a3622 !important; border-color: #198754 !important; font-weight: bold; }
    .sel-cancelada  { background-color: #f8d7da !important; color: #58151c !important; border-color: #dc3545 !important; font-weight: bold; }
    .filtros-card { background: white; border-radius: 14px; padding: 20px; box-shadow: 0 0 14px rgba(0,0,0,0.06); margin-bottom: 20px; }
    .pagina-info { font-size: .9rem; color: #666; }
</style>
</head>
<body>

<div class="sidebar">
    <img src="<?php echo URL_ROOT; ?>/img/logo_happy_contorno.webp" alt="Logo">
    <a href="<?php echo URL_ROOT; ?>/admin"><i class="bi bi-house-door-fill"></i> Dashboard</a>
    <a href="<?php echo URL_ROOT; ?>/admin/reservas" class="active"><i class="bi bi-calendar-fill"></i> Reservas</a>
    <a href="<?php echo URL_ROOT; ?>/admin/codigos"><i class="bi bi-ticket-perforated-fill"></i> Códigos</a>
    <a href="<?php echo URL_ROOT; ?>/admin/notificaciones"><i class="bi bi-bell-fill"></i> Notificaciones</a>
    <a href="<?php echo URL_ROOT; ?>/admin/correos"><i class="bi bi-envelope-fill"></i> Correos</a>

    <a href="<?php echo URL_ROOT; ?>/usuarios/logout" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Cerrar sesion</a>
</div>

<div class="content">
    <p class="title">Gestion de Reservas</p>
    <p class="text-muted fs-5">Controla las solicitudes y pagos de los clientes.</p>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $mensaje['tipo']; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensaje['texto']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="mb-3 d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Reservas encontradas: <strong><?php echo $totalReservas; ?></strong></h4>
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

    <div class="card p-4 shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-reservas">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Cliente / Paquete</th>
                        <th>Monto</th>
                        <th>Estado actual</th>
                        <th>Cambiar estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($reservas)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No hay reservas con este filtro.</td></tr>
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
        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
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
                            <a class="page-link" href="?<?php echo http_build_query($qs); ?>" <?php echo $activa ? 'style="background:#7F00FF;border-color:#7F00FF;"' : ''; ?>><?php echo $p; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal detalle -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle Reserva #<span id="d_id"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="d_body"></div>
            <div class="modal-footer">
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
            '<p><strong>Observaciones:</strong> ' + (r.observaciones || 'Ninguna') + '</p>' +
            '<p><strong>Estado:</strong> <span class="status-badge status-' + r.estado_pago + '">' + r.estado_pago.toUpperCase() + '</span></p>' +
            '<p><strong>🎵 Playlist sugerida:</strong></p>' +
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
window.HJ_URL_ROOT = "<?php echo URL_ROOT; ?>";
</script>

<script>

window.HJ_URL_ROOT="<?= URL_ROOT ?>";

</script>

<script src="<?= URL_ROOT ?>/js/chatbot_admin.js"></script>
</body>
</html>