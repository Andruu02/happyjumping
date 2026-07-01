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
</style>
</head>
<body>

<div class="sidebar">
    <img src="<?php echo URL_ROOT; ?>/img/logo_happy_contorno.png" alt="Logo">
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

    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Reservas encontradas: <strong><?php echo count($reservas); ?></strong></h4>
        <form method="GET" action="<?php echo URL_ROOT; ?>/admin/reservas" class="d-flex align-items-center gap-2">
            <label class="mb-0 fw-bold">Filtrar:</label>
            <select name="estado" class="form-select w-auto" onchange="this.form.submit()">
                <option value="all"        <?php echo $estado_filtro === 'all'        ? 'selected' : ''; ?>>Todas</option>
                <option value="pendiente"  <?php echo $estado_filtro === 'pendiente'  ? 'selected' : ''; ?>>Pendiente</option>
                <option value="confirmada" <?php echo $estado_filtro === 'confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                <option value="cancelada"  <?php echo $estado_filtro === 'cancelada'  ? 'selected' : ''; ?>>Cancelada</option>
            </select>
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
            '<p><strong>Estado:</strong> <span class="status-badge status-' + r.estado_pago + '">' + r.estado_pago.toUpperCase() + '</span></p>';
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
<script src="<?php echo URL_ROOT; ?>/js/chatbot_admin.js"></script>
</body>
</html>