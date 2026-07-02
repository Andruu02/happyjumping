<?php /* VISTA: Notificaciones Push - Admin */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $titulo; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    body { font-family: 'Poppins', Arial, sans-serif; background: #f4f8ff; margin: 0; overflow-x: hidden; }
    .sidebar {
        width: 240px; height: 100vh; background: #7F00FF;
        position: fixed; top: 0; left: 0; padding-top: 25px;
        color: white; display: flex; flex-direction: column; align-items: center;
    }
    .sidebar img { width: 120px; margin-bottom: 25px; }
    .sidebar a {
        width: 100%; text-decoration: none; padding: 14px 20px 14px 30px;
        color: white; font-weight: bold; font-size: 17px; transition: 0.3s;
    }
    .sidebar a i { margin-right: 10px; }
    .sidebar a:hover, .sidebar a.active { background: #6200c4; }
    .sidebar .btn-logout {
        background: #00d8ff; color: black; font-weight: bold; border-radius: 8px;
        width: 80%; margin-top: auto; margin-bottom: 20px; text-align: center; padding-left: 14px;
    }
    .sidebar .btn-logout:hover { background: #fff; }
    .content { margin-left: 240px; padding: 30px; }
    .title { font-size: 32px; font-weight: bold; color: #7F00FF; }
    .notif-card { background: white; border-radius: 16px; padding: 30px; box-shadow: 0 0 14px rgba(0,0,0,0.08); }
    .plantilla-btn {
        cursor: pointer; border: 2px solid #e0d4ff; border-radius: 12px;
        padding: 12px 16px; transition: 0.2s; background: white; text-align: left; width: 100%;
    }
    .plantilla-btn:hover { border-color: #7F00FF; background: #f8f0ff; }
    .plantilla-btn .emoji { font-size: 1.6rem; }
    .plantilla-btn .texto { font-size: 0.85rem; color: #555; margin-top: 2px; }
    .historial-item {
        border-left: 4px solid #7F00FF; padding: 10px 15px;
        background: #f8f0ff; border-radius: 0 10px 10px 0; margin-bottom: 10px;
    }
    .historial-item .hora { font-size: 0.75rem; color: #999; }
    #preview-box {
        background: linear-gradient(135deg, #FF6B6B, #FF8E53);
        border-radius: 20px; padding: 20px; color: white;
        max-width: 260px; text-align: center; box-shadow: 0 4px 14px rgba(255,107,0,0.3);
    }
    #preview-box .emoji-preview { font-size: 2.5rem; }
    #preview-box .titulo-preview { font-weight: bold; font-size: 1rem; margin-top: 6px; }
    #preview-box .msg-preview { font-size: 0.88rem; margin-top: 8px; opacity: 0.95; line-height: 1.4; }
    #preview-box .btn-preview {
        background: white; color: #FF6B6B; font-weight: bold;
        border: none; border-radius: 30px; padding: 8px 24px; margin-top: 14px; font-size: 0.9rem;
    }
</style>
</head>
<body>

<div class="sidebar">
    <img src="<?php echo URL_ROOT; ?>/img/logo_happy_contorno.png" alt="Logo">
    <a href="<?php echo URL_ROOT; ?>/admin"><i class="bi bi-house-door-fill"></i> Dashboard</a>
    <a href="<?php echo URL_ROOT; ?>/admin/reservas"><i class="bi bi-calendar-fill"></i> Reservas</a>
    <a href="<?php echo URL_ROOT; ?>/admin/codigos"><i class="bi bi-ticket-perforated-fill"></i> Códigos</a>
    <a href="<?php echo URL_ROOT; ?>/admin/notificaciones" class="active"><i class="bi bi-bell-fill"></i> Notificaciones</a>
    <a href="<?php echo URL_ROOT; ?>/admin/correos"><i class="bi bi-envelope-fill"></i> Correos</a>
    <a href="<?php echo URL_ROOT; ?>/usuarios/logout" class="btn btn-logout">Cerrar sesión</a>
</div>

<div class="content">
    <p class="title"><i class="bi bi-bell-fill me-2"></i>Notificaciones</p>
    <p class="text-muted fs-5">Envía alertas y promos directamente a la app móvil.</p>

    <?php if (isset($resultado)): ?>
        <div class="alert alert-<?php echo $resultado['tipo']; ?> alert-dismissible fade show">
            <i class="bi bi-<?php echo $resultado['tipo'] === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?> me-2"></i>
            <?php echo $resultado['texto']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4 mt-1">

        <!-- Formulario -->
        <div class="col-lg-7">
            <div class="notif-card">
                <h5 class="fw-bold mb-1" style="color:#7F00FF">
                    <i class="bi bi-send-fill me-2"></i>Nueva notificación
                </h5>
                <p class="text-muted small mb-4">El mensaje llegará a todos los usuarios con la app abierta.</p>

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
                    <button type="submit" class="btn btn-lg w-100 fw-bold text-white"
                        style="background:#7F00FF; border-radius:12px;">
                        <i class="bi bi-send-fill me-2"></i>Enviar a todos los usuarios
                    </button>
                </form>
            </div>
        </div>

        <!-- Preview + Historial -->
        <div class="col-lg-5">
            <div class="notif-card mb-4 text-center">
                <p class="fw-semibold mb-3">📱 Así se verá en el celular</p>
                <div id="preview-box" class="mx-auto">
                    <div class="emoji-preview">🎉</div>
                    <div class="titulo-preview">¡Oferta Especial!</div>
                    <div class="msg-preview" id="preview-texto">Tu mensaje aparecerá aquí...</div>
                    <button class="btn-preview">¡Entendido!</button>
                </div>
            </div>

            <div class="notif-card">
                <p class="fw-semibold mb-3">
                    <i class="bi bi-clock-history me-2" style="color:#7F00FF"></i>Últimas enviadas
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

<script>

window.HJ_URL_ROOT="<?= URL_ROOT ?>";

</script>

<script src="<?= URL_ROOT ?>/js/chatbot_admin_v2.js"></script>
</body>
</html>