<?php /* VISTA: app/views/admin/codigos.php */ ?>
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
    .table-codigos th { background-color: #7F00FF; color: white; }
    .status-badge { padding: 5px 12px; border-radius: 10px; font-weight: bold; font-size: 13px; display: inline-block; }
    .status-disponible { background: #d1e7dd; color: #0a3622; }
    .status-usado      { background: #e2e3e5; color: #41464b; }
    .sel-disponible { background-color: #d1e7dd !important; color: #0a3622 !important; border-color: #198754 !important; font-weight: bold; }
    .sel-usado      { background-color: #e2e3e5 !important; color: #41464b !important; border-color: #6c757d !important; font-weight: bold; }
    .codigo-text { font-family: monospace; font-size: 15px; font-weight: bold; letter-spacing: 2px; color: #7F00FF; }
</style>
</head>
<body>

<div class="sidebar">
    <img src="<?php echo URL_ROOT; ?>/img/logo_happy_contorno.png" alt="Logo">
    <a href="<?php echo URL_ROOT; ?>/admin"><i class="bi bi-house-door-fill"></i> Dashboard</a>
    <a href="<?php echo URL_ROOT; ?>/admin/reservas"><i class="bi bi-calendar-fill"></i> Reservas</a>
    <a href="<?php echo URL_ROOT; ?>/admin/codigos" class="active"><i class="bi bi-ticket-perforated-fill"></i> Códigos</a>
    <a href="<?php echo URL_ROOT; ?>/admin/notificaciones"><i class="bi bi-bell-fill"></i> Notificaciones</a>
    <a href="<?php echo URL_ROOT; ?>/admin/correos"><i class="bi bi-envelope-fill"></i> Correos</a>

    <a href="<?php echo URL_ROOT; ?>/usuarios/logout" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Cerrar sesion</a>
</div>

<div class="content">
    <p class="title">Códigos Canjeados</p>
    <p class="text-muted fs-5">Gestiona los códigos de promoción generados por los usuarios.</p>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $mensaje['tipo']; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensaje['texto']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h4 class="mb-0">Códigos encontrados: <strong><?php echo count($codigos); ?></strong></h4>
        <form method="GET" action="<?php echo URL_ROOT; ?>/admin/codigos" class="d-flex align-items-center gap-2 flex-wrap">
            <label class="mb-0 fw-bold">Estado:</label>
            <select name="estado" class="form-select w-auto">
                <option value="all"        <?php echo $estado_filtro === 'all'        ? 'selected' : ''; ?>>Todos</option>
                <option value="disponible" <?php echo $estado_filtro === 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                <option value="usado"      <?php echo $estado_filtro === 'usado'      ? 'selected' : ''; ?>>Usado</option>
            </select>
            <label class="mb-0 fw-bold ms-2">Código:</label>
            <input type="text" name="codigo" value="<?php echo htmlspecialchars($codigo_filtro); ?>"
                   class="form-control w-auto" placeholder="Ej: EF4E36FE"
                   style="min-width:140px; font-family:monospace; letter-spacing:1px; text-transform:uppercase"
                   oninput="this.value=this.value.toUpperCase()">
            <label class="mb-0 fw-bold ms-2">Usuario:</label>
            <input type="text" name="buscar" value="<?php echo htmlspecialchars($buscar); ?>"
                   class="form-control w-auto" placeholder="Nombre o correo..."
                   style="min-width:180px">
            <button type="submit" class="btn" style="background:#7F00FF;color:white;">
                <i class="bi bi-search"></i> Buscar
            </button>
            <?php if ($estado_filtro !== 'all' || $buscar !== '' || $codigo_filtro !== ''): ?>
                <a href="<?php echo URL_ROOT; ?>/admin/codigos" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card p-4 shadow-sm">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-codigos">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Promoción</th>
                        <th>Usuario</th>
                        <th>Fecha generación</th>
                        <th>Fecha uso</th>
                        <th>Estado actual</th>
                        <th>Cambiar estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($codigos)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No hay códigos con este filtro.</td></tr>
                <?php else: ?>
                    <?php foreach ($codigos as $cod): ?>
                    <tr>
                        <td><strong>#<?php echo $cod->id_codigo; ?></strong></td>
                        <td><span class="codigo-text"><?php echo htmlspecialchars($cod->codigo); ?></span></td>
                        <td>
                            <?php echo htmlspecialchars($cod->nombre_promocion); ?><br>
                            <small class="text-muted"><i class="bi bi-star-fill text-warning"></i> <?php echo $cod->puntos_necesarios; ?> pts</small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($cod->nombre_usuario); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($cod->correo_usuario); ?></small>
                        </td>
                        <td>
                            <?php echo date('d/m/Y', strtotime($cod->fecha_generacion)); ?><br>
                            <small class="text-muted"><?php echo date('H:i', strtotime($cod->fecha_generacion)); ?></small>
                        </td>
                        <td>
                            <?php if ($cod->fecha_uso): ?>
                                <?php echo date('d/m/Y', strtotime($cod->fecha_uso)); ?><br>
                                <small class="text-muted"><?php echo date('H:i', strtotime($cod->fecha_uso)); ?></small>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $cod->estado; ?>">
                                <?php echo strtoupper($cod->estado); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="<?php echo URL_ROOT; ?>/admin/codigos"
                                  style="display:flex; gap:6px; align-items:center;"
                                  onsubmit="return confirmarCambio(this)">
                                <input type="hidden" name="id_codigo" value="<?php echo $cod->id_codigo; ?>">
                                <select name="estado"
                                        class="form-select form-select-sm sel-<?php echo $cod->estado; ?>"
                                        style="width:130px"
                                        onchange="this.className='form-select form-select-sm sel-'+this.value">
                                    <option value="disponible" <?php echo $cod->estado === 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                                    <option value="usado"      <?php echo $cod->estado === 'usado'      ? 'selected' : ''; ?>>Usado</option>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-floppy-fill"></i> Guardar
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmarCambio(form) {
    const select   = form.querySelector('select[name="estado"]');
    const estadoNuevo = select.options[select.selectedIndex].text;
    const id       = form.querySelector('input[name="id_codigo"]').value;
    return confirm('¿Marcar el código #' + id + ' como "' + estadoNuevo + '"?');
}
</script>
<script src="<?php echo URL_ROOT; ?>/js/chatbot_admin.js"></script>
</body>
</html>