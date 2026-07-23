<?php
/*
 * =========================================
 * HEADER GLOBAL (¡SIMPLIFICADO Y BASADO EN ID!)
 * =========================================
 */
require_once APP_ROOT . '/config/vapid.php';
$esInicio = isset($active_page) && $active_page === 'inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?></title> 
    
    <link rel="icon" type="image/webp" href="<?php echo URL_ROOT; ?>/img/logo_escupitajo-removebg-preview.webp">
    <link rel="manifest" href="<?php echo URL_ROOT; ?>/manifest.json">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Fredoka:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/style.css">
    <?php if ($esInicio): ?>
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/inicio.css">
    <?php endif; ?>
</head>
<body <?php echo $esInicio ? 'class="body-index"' : ''; ?>>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo URL_ROOT; ?>">
            <img src="<?php echo URL_ROOT; ?>/img/logo_happy_contorno.webp" alt="Logo Happy Jumping">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" id="nav-inicio" href="<?php echo URL_ROOT; ?>/">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo URL_ROOT; ?>/#entradas">Entradas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo URL_ROOT; ?>/#cumpleanos">Cumpleaños</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo URL_ROOT; ?>/#conocenos">Conócenos</a>
                </li>

                <?php if(isset($_SESSION['id_usuario'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-4"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            
                            <?php if(isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/admin"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/perfil"><i class="bi bi-person-fill"></i> Mi Perfil</a></li>
                                <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/reservas/paso1"><i class="bi bi-calendar-plus-fill"></i> Nueva Reserva</a></li>
                            <?php endif; ?>
                            
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo URL_ROOT; ?>/usuarios/logout"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="<?php echo URL_ROOT; ?>/usuarios/login" class="btn btn-outline-light ms-2 fw-bold px-4">Iniciar Sesion</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Overlay para la animación de transición (MorphSVG) al ir al login -->
<div class="transicion-login" id="transicion-login" aria-hidden="true">
    <svg width="100%" height="100%">
        <circle id="forma-transicion" cx="0" cy="0" r="0" fill="url(#grad-transicion)"></circle>
        <defs>
            <linearGradient id="grad-transicion" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#ff3c8d"></stop>
                <stop offset="50%" stop-color="#7b2ff7"></stop>
                <stop offset="100%" stop-color="#00c6ff"></stop>
            </linearGradient>
        </defs>
    </svg>
</div>

<script>
    window.HJ_URL_ROOT = "<?php echo URL_ROOT; ?>";
    window.HJ_VAPID_PUBLIC_KEY = "<?php echo VAPID_PUBLIC_KEY; ?>";
</script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.15/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.15/dist/MorphSVGPlugin.min.js"></script>
<script src="<?php echo URL_ROOT; ?>/js/push.js"></script>
<script src="<?php echo URL_ROOT; ?>/js/scroll-suave.js"></script>
<script src="<?php echo URL_ROOT; ?>/js/transicion-login.js"></script>

<main class="main-container">