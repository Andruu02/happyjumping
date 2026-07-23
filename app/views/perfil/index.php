<?php
/*
 * VISTA DE PERFIL (Carga su propio CSS)
 */

// 1. Cargar el header estándar (que ahora carga style.css)
require APP_ROOT . '/views/includes/header.php';
?>

<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/perfil.css">

<div class="container mt-5 mb-5">

    <?php
        // Ícono de animal "saltarín" al azar para el avatar, así la cabecera
        // no es siempre igual y combina con el tema del parque.
        $iconosAnimales = ['🦘', '🐒', '🐰', '🐸', '🦋', '🦩', '🐬', '🦁', '🐯', '🐨'];
        $avatarIcono = $iconosAnimales[array_rand($iconosAnimales)];
        $avatarColor = random_int(1, 6);
    ?>
    <div class="perfil-unificado">
        <div class="perfil-banner"></div>
        <div class="perfil-portada-body">
            <div class="perfil-avatar-grande icono color-<?php echo $avatarColor; ?>"><?php echo $avatarIcono; ?></div>
            <div class="perfil-portada-info">
                <h2 class="fuente_bouncy">¡Hola, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>!</h2>
                <p>Aquí puedes ver el estado de todas tus reservas.</p>
            </div>
            <a href="<?php echo URL_ROOT; ?>/reservas/paso1" class="perfil-btn-reservar">
                <i class="bi bi-calendar-plus-fill"></i> Hacer una reserva
            </a>
        </div>

        <div class="perfil-unificado-contenido">
            <h3 class="fuente_bouncy text-center">Tus Reservas</h3>

            <div class="perfil-filtros">
                <form method="GET" action="<?php echo URL_ROOT; ?>/perfil" class="row g-2 align-items-end">
                    <div class="col-6 col-md-3">
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
                    <div class="col-6 col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel-fill"></i> Filtrar</button>
                    </div>
                    <?php $hayFiltros = $filtros['estado'] !== 'all' || $filtros['id_paquete'] !== '' || $filtros['fecha_desde'] !== '' || $filtros['fecha_hasta'] !== ''; ?>
                    <?php if ($hayFiltros): ?>
                    <div class="col-12">
                        <a href="<?php echo URL_ROOT; ?>/perfil" class="small text-muted"><i class="bi bi-x-circle"></i> Limpiar filtros</a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (empty($reservas)): ?>

                <div class="alert alert-info fs-5" role="alert">
                    <?php if ($hayFiltros): ?>
                        <i class="bi bi-info-circle-fill"></i> No hay reservas que coincidan con estos filtros.
                    <?php else: ?>
                        <i class="bi bi-info-circle-fill"></i> Aún no tienes ninguna reserva registrada.
                        <a href="<?php echo URL_ROOT; ?>/reservas/paso1" class="alert-link">¡Haz tu primera reserva aquí!</a>
                    <?php endif; ?>
                </div>

            <?php else: ?>

                <div class="reservas-lista">
                <?php foreach($reservas as $reserva): ?>

                    <?php
                        $status_class = '';
                        $status_text = '';
                        switch ($reserva->estado) {
                            case 'pendiente':
                                $status_class = 'status-pendiente';
                                $status_text = 'Pendiente';
                                break;
                            case 'confirmada':
                                $status_class = 'status-confirmada';
                                $status_text = 'Confirmada';
                                break;
                            case 'cancelada':
                                $status_class = 'status-cancelada';
                                $status_text = 'Cancelada';
                                break;
                            default:
                                $status_class = 'status-pendiente';
                                $status_text = 'Pendiente';
                        }
                        $fechaObj = new DateTime($reserva->fecha . ' ' . $reserva->hora_inicio);
                    ?>

                    <div class="reserva-fila">
                        <div class="reserva-fila-fecha">
                            <span class="dia"><?php echo $fechaObj->format('d'); ?></span>
                            <span class="mes"><?php echo $fechaObj->format('M'); ?></span>
                        </div>
                        <div class="reserva-fila-info">
                            <h5><?php echo htmlspecialchars($reserva->paquete_nombre); ?></h5>
                            <p class="reserva-fila-detalle">
                                <span><i class="bi bi-clock-fill"></i> <?php echo $fechaObj->format('h:i A'); ?></span>
                                <span><i class="bi bi-cake2-fill"></i> <?php echo htmlspecialchars($reserva->nombre_cumpleanero); ?> (cumple <?php echo (int) $reserva->edad_cumpleanero; ?>)</span>
                                <span><i class="bi bi-people-fill"></i> <?php echo (int) $reserva->cantidad_personas; ?> personas</span>
                            </p>
                        </div>
                        <div class="reserva-fila-estado">
                            <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>

                <?php if ($totalPaginas > 1): ?>
                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                    <span class="pagina-info">Página <?php echo $pagina; ?> de <?php echo $totalPaginas; ?> · <?php echo $totalReservas; ?> reservas en total</span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php
                            $qs = $_GET;
                            for ($p = 1; $p <= $totalPaginas; $p++):
                                $qs['pagina'] = $p;
                                $activa = $p === $pagina;
                            ?>
                                <li class="page-item <?php echo $activa ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query($qs); ?>" <?php echo $activa ? 'style="background:var(--morado);border-color:var(--morado);"' : ''; ?>><?php echo $p; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>

    <div class="text-center mt-5">
        <a href="<?php echo URL_ROOT; ?>/usuarios/logout" class="btn-logout">
            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
        </a>
    </div>

</div>

<?php
// Cargar el footer estándar
require APP_ROOT . '/views/includes/footer.php';
?>