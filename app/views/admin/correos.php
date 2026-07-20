<?php /* VISTA: Correos Masivos - Admin */ ?>
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
    .card-seccion {
        background: white; border-radius: 16px; padding: 28px;
        box-shadow: 0 0 14px rgba(0,0,0,0.07); margin-bottom: 24px;
    }
    /* Plantillas */
    .plantilla-card {
        border: 2px solid #e0d4ff; border-radius: 14px; padding: 16px;
        cursor: pointer; transition: .2s; background: white; text-align: left;
        width: 100%; position: relative;
    }
    .plantilla-card:hover  { border-color: #7F00FF; background: #f9f0ff; }
    .plantilla-card.activa { border-color: #7F00FF; background: #f3e5ff; box-shadow: 0 0 0 3px rgba(127,0,255,.15); }
    .plantilla-card .emoji { font-size: 1.8rem; }
    .plantilla-card .badge-activa {
        position: absolute; top: 10px; right: 10px;
        background: #7F00FF; color: white; font-size: .7rem;
        padding: 2px 8px; border-radius: 20px; display: none;
    }
    .plantilla-card.activa .badge-activa { display: inline; }
    /* Extras dinámicos */
    .extras-panel { display: none; }
    .extras-panel.visible { display: block; }
    /* Tabla de clientes */
    .cliente-row td { vertical-align: middle; font-size: .9rem; }
    .badge-reservas { background: #f3e5ff; color: #7F00FF; border-radius: 20px; padding: 2px 10px; font-size: .8rem; }
    /* Historial */
    .hist-item {
        border-left: 4px solid #7F00FF; padding: 10px 15px;
        background: #f8f0ff; border-radius: 0 10px 10px 0; margin-bottom: 10px;
    }
    .hist-item .meta { font-size: .75rem; color: #999; }
    /* Contadores */
    #contador-sel { font-weight: 700; color: #7F00FF; }
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <img src="<?php echo URL_ROOT; ?>/img/logo_happy_contorno.webp" alt="Logo">
    <a href="<?php echo URL_ROOT; ?>/admin"><i class="bi bi-house-door-fill"></i> Dashboard</a>
    <a href="<?php echo URL_ROOT; ?>/admin/reservas"><i class="bi bi-calendar-fill"></i> Reservas</a>
    <a href="<?php echo URL_ROOT; ?>/admin/codigos"><i class="bi bi-ticket-perforated-fill"></i> Códigos</a>
    <a href="<?php echo URL_ROOT; ?>/admin/notificaciones"><i class="bi bi-bell-fill"></i> Notificaciones</a>
    <a href="<?php echo URL_ROOT; ?>/admin/correos" class="active"><i class="bi bi-envelope-fill"></i> Correos</a>
    <a href="<?php echo URL_ROOT; ?>/usuarios/logout" class="btn btn-logout">Cerrar sesión</a>
</div>

<!-- CONTENIDO -->
<div class="content">
    <p class="title"><i class="bi bi-envelope-fill me-2"></i>Correos Masivos</p>
    <p class="text-muted fs-5">Envía correos personalizados a tus clientes desde el panel.</p>

    <?php if (!empty($resultado)): ?>
        <div class="alert alert-<?php echo $resultado['tipo']; ?> alert-dismissible fade show">
            <i class="bi bi-<?php echo $resultado['tipo'] === 'success' ? 'check-circle-fill' : ($resultado['tipo'] === 'warning' ? 'exclamation-triangle-fill' : 'x-circle-fill'); ?> me-2"></i>
            <?php echo $resultado['texto']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo URL_ROOT; ?>/admin/correos" id="form-correos">

        <div class="row g-4">

            <!-- COLUMNA IZQUIERDA: Plantillas + Extras -->
            <div class="col-lg-7">

                <!-- 1. Seleccionar plantilla -->
                <div class="card-seccion">
                    <h5 class="fw-bold mb-1" style="color:#7F00FF">
                        <i class="bi bi-layout-text-sidebar-reverse me-2"></i>1. Elige una plantilla
                    </h5>
                    <p class="text-muted small mb-4">Selecciona el tipo de correo que quieres enviar.</p>

                    <input type="hidden" name="plantilla" id="plantilla-input" value="">

                    <div class="row g-3">
                        <?php
                        $plantillas_ui = [
                            ['key'=>'recordatorio', 'emoji'=>'🎂', 'titulo'=>'Recordatorio de reserva', 'desc'=>'Avisa a los clientes que su fiesta se acerca pronto.', 'color'=>'#FF6B6B'],
                            ['key'=>'promo',        'emoji'=>'🎉', 'titulo'=>'Promoción especial',      'desc'=>'Anuncia una oferta, descuento o 2x1.',              'color'=>'#7F00FF'],
                            ['key'=>'puntos',       'emoji'=>'🏆', 'titulo'=>'Puntos acumulados',       'desc'=>'Recuérdales que tienen puntos listos para canjear.','color'=>'#f7971e'],
                            ['key'=>'personalizado','emoji'=>'💬', 'titulo'=>'Mensaje personalizado',   'desc'=>'Escribe un mensaje totalmente libre para tus clientes.','color'=>'#E100FF'],
                        ];
                        foreach ($plantillas_ui as $p): ?>
                        <div class="col-6">
                            <button type="button" class="plantilla-card" data-key="<?php echo $p['key']; ?>"
                                onclick="seleccionarPlantilla('<?php echo $p['key']; ?>', this)">
                                <span class="badge-activa">✓ Seleccionada</span>
                                <div class="emoji"><?php echo $p['emoji']; ?></div>
                                <div class="fw-semibold mt-1"><?php echo $p['titulo']; ?></div>
                                <div style="font-size:.82rem;color:#666;margin-top:3px;"><?php echo $p['desc']; ?></div>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 2. Campos extra por plantilla -->
                <div class="card-seccion" id="extras-wrapper" style="display:none;">
                    <h5 class="fw-bold mb-3" style="color:#7F00FF">
                        <i class="bi bi-sliders me-2"></i>2. Personaliza el contenido
                    </h5>

                    <!-- Extras: PROMO -->
                    <div class="extras-panel" id="extras-promo">
                        <label class="form-label fw-semibold">Detalle de la promoción <span class="text-danger">*</span></label>
                        <textarea name="detalle_promo" class="form-control" rows="3"
                            placeholder="Ej: ¡Esta semana 2x1 en todos los paquetes! Válido del 10 al 15 de junio."
                            maxlength="400"></textarea>
                        <div class="form-text">Hasta 400 caracteres. Este texto aparece dentro del recuadro destacado del correo.</div>
                    </div>

                    <!-- Extras: PERSONALIZADO -->
                    <div class="extras-panel" id="extras-personalizado">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Asunto del correo <span class="text-danger">*</span></label>
                            <input type="text" name="asunto_custom" class="form-control"
                                placeholder="Ej: ¡Novedades de Happy Jumping Peru!" maxlength="120">
                        </div>
                        <div>
                            <label class="form-label fw-semibold">Cuerpo del mensaje <span class="text-danger">*</span></label>
                            <textarea name="cuerpo_custom" class="form-control" rows="5"
                                placeholder="Escribe aquí tu mensaje... Puedes usar saltos de línea."
                                maxlength="1200"></textarea>
                            <div class="form-text">Máximo 1200 caracteres. Los saltos de línea se respetan en el correo.</div>
                        </div>
                    </div>

                    <!-- Las otras plantillas (recordatorio, puntos) no necesitan campos extra -->
                    <div class="extras-panel" id="extras-recordatorio">
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Se enviará solo a clientes que tengan una <strong>reserva confirmada próxima</strong>.
                            Si un cliente seleccionado no tiene reserva futura, se omitirá automáticamente.
                        </div>
                    </div>
                    <div class="extras-panel" id="extras-puntos">
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-star-fill me-2"></i>
                            El correo mostrará los puntos acumulados de cada cliente.
                            Si la tabla <code>usuarios</code> no tiene columna <code>puntos</code> aún, mostrará 0.
                        </div>
                    </div>
                </div>

            </div>

            <!-- COLUMNA DERECHA: Destinatarios + Historial -->
            <div class="col-lg-5">

                <!-- Destinatarios -->
                <div class="card-seccion">
                    <h5 class="fw-bold mb-1" style="color:#7F00FF">
                        <i class="bi bi-people-fill me-2"></i>3. Destinatarios
                    </h5>
                    <p class="text-muted small mb-3">
                        Seleccionados: <span id="contador-sel">0</span> de <?php echo count($clientes); ?> clientes
                    </p>

                    <!-- Selector de todos -->
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="toggle-todos" onchange="toggleTodos(this)">
                            <label class="form-check-label fw-semibold" for="toggle-todos" id="label-todos">Enviar a todos los clientes</label>
                        </div>
                        <input type="hidden" name="todos" id="input-todos" value="0">
                    </div>

                    <div class="alert alert-info py-2 px-3 mb-3" id="aviso-recordatorio" style="display:none;font-size:.85rem;">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        Solo se muestran los clientes con una <strong>reserva confirmada</strong> próxima.
                    </div>

                    <!-- Búsqueda -->
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="buscador-cliente" class="form-control"
                            placeholder="Buscar por nombre o correo..."
                            oninput="filtrarClientes(this.value)">
                    </div>

                    <!-- Lista de clientes -->
                    <div style="max-height:340px;overflow-y:auto;">
                        <table class="table table-sm table-hover mb-0" id="tabla-clientes">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:36px;"></th>
                                    <th>Cliente</th>
                                    <th class="text-center">Reservas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($clientes)): ?>
                                <tr><td colspan="3" class="text-center text-muted py-3">No hay clientes registrados.</td></tr>
                                <?php else: ?>
                                <?php foreach ($clientes as $c): ?>
                                <tr class="cliente-row" data-recordatorio="<?php echo in_array($c->id_usuario, $ids_recordatorio) ? '1' : '0'; ?>">
                                    <td>
                                        <input type="checkbox" name="destinatarios[]"
                                            value="<?php echo $c->id_usuario; ?>"
                                            class="form-check-input chk-cliente"
                                            onchange="actualizarContador()">
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?php echo htmlspecialchars($c->nombre); ?></div>
                                        <div class="text-muted" style="font-size:.78rem;"><?php echo htmlspecialchars($c->correo); ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-reservas"><?php echo $c->total_reservas; ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Historial -->
                <div class="card-seccion">
                    <p class="fw-semibold mb-3">
                        <i class="bi bi-clock-history me-2" style="color:#7F00FF"></i>Últimos envíos
                    </p>
                    <?php if (empty($historial)): ?>
                        <p class="text-muted small">Aún no se han enviado correos desde el panel.</p>
                    <?php else: ?>
                        <?php foreach ($historial as $h): ?>
                        <div class="hist-item">
                            <div class="fw-semibold"><?php echo htmlspecialchars($h->asunto); ?></div>
                            <div class="meta">
                                <i class="bi bi-person-fill me-1"></i><?php echo $h->admin_nombre; ?>
                                &nbsp;·&nbsp;
                                <i class="bi bi-people-fill me-1"></i><?php echo $h->destinatarios; ?> destinatarios
                                &nbsp;·&nbsp;
                                <i class="bi bi-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($h->enviado_at)); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- Botón enviar -->
        <div class="d-grid mt-2">
            <button type="submit" id="btn-enviar" class="btn btn-lg fw-bold text-white py-3" disabled
                style="background:#7F00FF;border-radius:14px;font-size:1.1rem;">
                <i class="bi bi-send-fill me-2"></i>
                <span id="btn-texto">Selecciona una plantilla y destinatarios</span>
            </button>
        </div>

    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let plantillaActual  = '';
    let modoRecordatorio = false;

    function seleccionarPlantilla(key, el) {
        // Quitar clase activa de todos
        document.querySelectorAll('.plantilla-card').forEach(c => c.classList.remove('activa'));
        el.classList.add('activa');
        plantillaActual = key;
        document.getElementById('plantilla-input').value = key;

        // Mostrar wrapper de extras
        document.getElementById('extras-wrapper').style.display = 'block';

        // Ocultar todos los paneles extras
        document.querySelectorAll('.extras-panel').forEach(p => p.classList.remove('visible'));

        // Mostrar el correspondiente
        const panel = document.getElementById('extras-' + key);
        if (panel) panel.classList.add('visible');

        modoRecordatorio = (key === 'recordatorio');
        document.getElementById('aviso-recordatorio').style.display = modoRecordatorio ? 'block' : 'none';
        document.getElementById('label-todos').textContent = modoRecordatorio
            ? 'Enviar a todos los clientes con reserva confirmada'
            : 'Enviar a todos los clientes';

        aplicarFiltroRecordatorio();
        actualizarContador();
    }

    // Oculta y desmarca a los clientes sin reserva confirmada cuando la
    // plantilla activa es "recordatorio".
    function aplicarFiltroRecordatorio() {
        if (modoRecordatorio) {
            document.querySelectorAll('#tabla-clientes tbody tr.cliente-row').forEach(row => {
                if (row.dataset.recordatorio !== '1') {
                    row.querySelector('.chk-cliente').checked = false;
                }
            });
        }
        filtrarClientes(document.getElementById('buscador-cliente').value);
    }

    function toggleTodos(chk) {
        const val = chk.checked ? '1' : '0';
        document.getElementById('input-todos').value = val;
        document.querySelectorAll('.chk-cliente').forEach(c => {
            const elegible = !modoRecordatorio || c.closest('tr').dataset.recordatorio === '1';
            c.checked  = chk.checked && elegible;
            c.disabled = chk.checked && elegible;
        });
        actualizarContador();
    }

    function actualizarContador() {
        const todos = document.getElementById('toggle-todos').checked;
        if (todos) {
            const total = modoRecordatorio
                ? document.querySelectorAll('#tabla-clientes tbody tr.cliente-row[data-recordatorio="1"]').length
                : document.querySelectorAll('.chk-cliente').length;
            document.getElementById('contador-sel').textContent = total + ' (todos)';
        } else {
            const sel = document.querySelectorAll('.chk-cliente:checked').length;
            document.getElementById('contador-sel').textContent = sel;
        }
        actualizarBoton();
    }

    function actualizarBoton() {
        const btn   = document.getElementById('btn-enviar');
        const texto = document.getElementById('btn-texto');
        const todos = document.getElementById('toggle-todos').checked;
        const sel   = document.querySelectorAll('.chk-cliente:checked').length;
        const tieneDestinatarios = todos || sel > 0;
        const tienePlantilla     = plantillaActual !== '';

        if (tienePlantilla && tieneDestinatarios) {
            btn.disabled = false;
            const n = todos ? 'todos los clientes' : sel + ' cliente' + (sel !== 1 ? 's' : '');
            texto.textContent = '✉️ Enviar correos a ' + n;
        } else if (!tienePlantilla) {
            btn.disabled = true;
            texto.textContent = 'Selecciona una plantilla y destinatarios';
        } else {
            btn.disabled = true;
            texto.textContent = 'Selecciona al menos un destinatario';
        }
    }

    function filtrarClientes(q) {
        q = q.toLowerCase();
        document.querySelectorAll('#tabla-clientes tbody tr.cliente-row').forEach(row => {
            if (modoRecordatorio && row.dataset.recordatorio !== '1') {
                row.style.display = 'none';
                return;
            }
            const texto = row.textContent.toLowerCase();
            row.style.display = texto.includes(q) ? '' : 'none';
        });
    }

    // Confirmación antes de enviar
    document.getElementById('form-correos').addEventListener('submit', function(e) {
        const todos = document.getElementById('toggle-todos').checked;
        const sel   = document.querySelectorAll('.chk-cliente:checked').length;
        const n = todos ? 'TODOS los clientes' : sel + ' cliente' + (sel !== 1 ? 's' : '');
        if (!confirm('¿Confirmas el envío de correos a ' + n + '?')) {
            e.preventDefault();
        }
    });
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
