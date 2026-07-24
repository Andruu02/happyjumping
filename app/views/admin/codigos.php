<?php /* VISTA: app/views/admin/codigos.php */ ?>
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
    <h1 class="titulo-admin"><i class="bi bi-ticket-perforated-fill"></i> Códigos Canjeados</h1>
    <p class="subtitulo-admin">Gestiona los códigos de promoción generados por los usuarios.</p>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $mensaje['tipo']; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensaje['texto']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="filtros-card d-flex justify-content-between align-items-center flex-wrap gap-3">
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
            <button type="submit" class="btn-admin-primario">
                <i class="bi bi-search"></i> Buscar
            </button>
            <?php if ($estado_filtro !== 'all' || $buscar !== '' || $codigo_filtro !== ''): ?>
                <a href="<?php echo URL_ROOT; ?>/admin/codigos" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="admin-card">
        <div class="table-responsive">
            <table class="table table-hover admin-table">
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
<script>
window.HJ_URL_ROOT = "<?php echo URL_ROOT; ?>";
</script>

<script src="<?= URL_ROOT ?>/js/chatbot_admin.js"></script>
</body>
</html>
