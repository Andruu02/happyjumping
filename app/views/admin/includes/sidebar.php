<?php
/*
 * SIDEBAR COMPARTIDO DEL PANEL DE ADMIN
 * Se incluye desde cada vista de admin/*.php. Detecta el link activo
 * comparando la URL actual, así ninguna vista/controlador necesita pasar
 * una variable extra solo para esto.
 */
$rutaActualAdmin = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<div class="sidebar">
    <img src="<?php echo URL_ROOT; ?>/img/logo_happy_contorno.webp" alt="Logo">
    <nav>
        <a href="<?php echo URL_ROOT; ?>/admin" class="<?php echo $rutaActualAdmin === '/admin' ? 'active' : ''; ?>">
            <i class="bi bi-house-door-fill"></i> <span>Dashboard</span>
        </a>
        <a href="<?php echo URL_ROOT; ?>/admin/reservas" class="<?php echo $rutaActualAdmin === '/admin/reservas' ? 'active' : ''; ?>">
            <i class="bi bi-calendar-fill"></i> <span>Reservas</span>
        </a>
        <a href="<?php echo URL_ROOT; ?>/admin/codigos" class="<?php echo $rutaActualAdmin === '/admin/codigos' ? 'active' : ''; ?>">
            <i class="bi bi-ticket-perforated-fill"></i> <span>Códigos</span>
        </a>
        <a href="<?php echo URL_ROOT; ?>/admin/notificaciones" class="<?php echo $rutaActualAdmin === '/admin/notificaciones' ? 'active' : ''; ?>">
            <i class="bi bi-bell-fill"></i> <span>Notificaciones</span>
        </a>
        <a href="<?php echo URL_ROOT; ?>/admin/correos" class="<?php echo $rutaActualAdmin === '/admin/correos' ? 'active' : ''; ?>">
            <i class="bi bi-envelope-fill"></i> <span>Correos</span>
        </a>
    </nav>
    <a href="<?php echo URL_ROOT; ?>/usuarios/logout" class="btn-logout">
        <i class="bi bi-box-arrow-right"></i> <span>Cerrar sesión</span>
    </a>
</div>
