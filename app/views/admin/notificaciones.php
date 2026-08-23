<?php /* VISTA: Notificaciones Push - Admin */ ?>
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
    <h1 class="titulo-admin"><i class="bi bi-bell-fill"></i> Notificaciones</h1>
    <p class="subtitulo-admin">
        Envía notificaciones push reales al celular de tus clientes (aparecen en la bandeja del sistema, aunque el sitio esté cerrado).
        <span class="badge rounded-pill" style="background:var(--morado-claro);color:var(--morado);" title="<?php echo $totalWebPush; ?> navegador(es) + <?php echo $totalFcm; ?> app(s) móvil(es)"><i class="bi bi-phone-fill me-1"></i><?php echo $totalSuscritos; ?> dispositivo(s) suscrito(s)</span>
    </p>

    <?php
        $iconosResultado = ['success' => 'check-circle-fill', 'danger' => 'x-circle-fill', 'warning' => 'exclamation-triangle-fill'];
    ?>
    <?php if (isset($resultado)): ?>
        <div class="alert alert-<?php echo $resultado['tipo']; ?> alert-dismissible fade show">
            <i class="bi bi-<?php echo $iconosResultado[$resultado['tipo']] ?? 'exclamation-triangle-fill'; ?> me-2"></i>
            <?php echo $resultado['texto']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4 mt-1">

        <!-- Formulario -->
        <div class="col-lg-7">
            <div class="admin-card">
                <h5 class="fw-bold mb-1" style="color:var(--morado)">
                    <i class="bi bi-send-fill me-2"></i>Nueva notificación
                </h5>
                <p class="text-muted small mb-4">El mensaje llegará como notificación del sistema a todos los dispositivos suscritos, tengan o no el sitio abierto.</p>

                <p class="fw-semibold mb-2">⚡ Plantillas rápidas</p>
                <div class="row g-2 mb-4">
                    <?php foreach ($plantillas as $p): ?>
                    <div class="col-6">
                        <button class="plantilla-btn" onclick="usarPlantilla('<?php echo htmlspecialchars($p['mensaje'], ENT_QUOTES); ?>')">
                            <div class="emoji"><?php echo $p['emoji']; ?></div>
                            <div class="fw-semibold"><?php echo $p['titulo']; ?></div>
                            <div class="texto"><?php echo $p['mensaje']; ?></div>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <p class="fw-semibold mb-2">✏️ O escribe tu propio mensaje</p>
                <form method="POST" action="<?php echo URL_ROOT; ?>/admin/notificaciones">
                    <div class="mb-3">
                        <textarea
                            name="mensaje"
                            id="mensaje-input"
                            class="form-control"
                            rows="3"
                            maxlength="200"
                            placeholder="Ej: ¡Hoy 2x1 en entradas de 6pm a 8pm! 🎉"
                            required
                            oninput="actualizarPreview(this.value)"
                        ><?php echo isset($mensajeAnterior) ? htmlspecialchars($mensajeAnterior) : ''; ?></textarea>
                        <div class="form-text text-end" id="contador">0 / 200 caracteres</div>
                    </div>
                    <button type="submit" class="btn-admin-primario w-100 justify-content-center py-3" style="font-size:1.05rem;">
                        <i class="bi bi-send-fill"></i> Enviar a todos los usuarios
                    </button>
                </form>
            </div>
        </div>

        <!-- Preview + Historial -->
        <div class="col-lg-5">
            <div class="admin-card mb-4">
                <p class="fw-semibold mb-3 text-center">📱 Así se verá en la bandeja de notificaciones</p>
                <div id="preview-box" class="mx-auto">
                    <div class="preview-cabecera">
                        <img src="<?php echo URL_ROOT; ?>/img/logo_happy_contorno.webp" alt="Happy Jumping">
                        <span>Happy Jumping Peru</span>
                        <span class="preview-ahora">ahora</span>
                    </div>
                    <div class="titulo-preview">Happy Jumping Peru 🎈</div>
                    <div class="msg-preview" id="preview-texto">Tu mensaje aparecerá aquí...</div>
                </div>
            </div>

            <div class="admin-card">
                <p class="fw-semibold mb-3">
                    <i class="bi bi-clock-history me-2" style="color:var(--morado)"></i>Últimas enviadas
                </p>
                <?php if (empty($historial)): ?>
                    <p class="text-muted small">Aún no se han enviado notificaciones.</p>
                <?php else: ?>
                    <?php foreach ($historial as $h): ?>
                        <div class="historial-item">
                            <div class="fw-semibold"><?php echo htmlspecialchars($h->mensaje); ?></div>
                            <div class="hora">
                                <i class="bi bi-person-fill me-1"></i><?php echo $h->admin_nombre; ?>
                                &nbsp;·&nbsp;
                                <i class="bi bi-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($h->created_at)); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function usarPlantilla(msg) {
        document.getElementById('mensaje-input').value = msg;
        actualizarPreview(msg);
    }
    function actualizarPreview(texto) {
        document.getElementById('preview-texto').textContent = texto || 'Tu mensaje aparecerá aquí...';
        document.getElementById('contador').textContent = texto.length + ' / 200 caracteres';
    }
    const v = document.getElementById('mensaje-input').value;
    if (v) actualizarPreview(v);
</script>
<script>
window.HJ_URL_ROOT = "<?php echo URL_ROOT; ?>";
</script>

<script src="<?= URL_ROOT ?>/js/chatbot_admin.js"></script>
</body>
</html>
