<?php

// VISTA DE INICIO (PÁGINA PRINCIPAL)
// Incluye, en una sola página con scroll, el contenido de Entradas,
// Cumpleaños y Conócenos (antes rutas separadas).
require_once APP_ROOT . '/views/includes/header.php';
?>

<section class="hero">
    <div class="hero-content">
        <h1 class="fuente_bouncy">¡Salta hacia la diversión!</h1>
        <p>Descubre la emoción sin límites en Happy&amp;Jumping, el lugar donde la alegría nunca se detiene.</p>
        <a href="<?php echo URL_ROOT; ?>/#cumpleanos" class="hero-cta">🎉 ¡Reserva tu diversión!</a>
    </div>
</section>

<!-- Envoltura de toda la página de inicio: UNA sola capa de color de fondo
     continua (detrás de todas las secciones, que van transparentes), para
     que se vea variada pero sin cortes ni monotonía. -->
<div class="inicio-fondo-wrapper">
    <div class="capa-color-fondo" aria-hidden="true"></div>

<!-- MARQUEE -->
<div class="marquee">
    <div class="marquee-track">
        <?php
        // Se repite 4 veces (no 2) para que en pantallas anchas siempre haya
        // contenido de sobra cubriendo el recorrido del scroll; con solo 2
        // copias, en monitores grandes se alcanzaba a ver el final del
        // recorrido en blanco antes de reiniciar el loop.
        $itemsMarquee = ['Trampolines', 'Anfitrionas', 'Helados y granizados', 'Brazos giratorios', 'Glitter y maquillaje neón', 'Zona de fiestas'];
        for ($vuelta = 0; $vuelta < 4; $vuelta++):
            foreach ($itemsMarquee as $item):
        ?>
        <span><?php echo $item; ?></span>
        <?php
            endforeach;
        endfor;
        ?>
    </div>
</div>

<!-- ============================================================
     AMENIDADES ("zonas" de la experiencia)
     ============================================================ -->
<section class="section">
    <div class="wrap">
        <div class="section-head">
            <h2 class="section-title fuente_bouncy titulo-arcoiris">¿Qué encontrarás en Happy&amp;Jumping?</h2>
            <p class="section-lead">Todo lo que necesitas para pasarla en grande, en un solo lugar. Toca cualquier tarjeta para ver fotos.</p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php
            // Cada amenidad tiene una foto real (public/img/<archivo>.webp),
            // que se muestra tanto de miniatura en la tarjeta como de fondo
            // en la tarjeta expandida.
            $amenidades = [
                ['archivo' => 'anfitrionas',  'icono' => '🤸‍♀️', 'texto' => 'Anfitrionas',              'desc' => 'Dinámicas y juegos para toda la fiesta.'],
                ['archivo' => 'trampolines',  'icono' => '🤾', 'texto' => 'Trampolines',                'desc' => 'Diversión y salto sin límites.'],
                ['archivo' => 'helados',      'icono' => '🍧', 'texto' => 'Helados y granizados',       'desc' => 'Para refrescarte entre salto y salto.'],
                ['archivo' => 'brazo',        'icono' => '🌀', 'texto' => 'Brazos giratorios',          'desc' => 'Adrenalina en cada vuelta.'],
                ['archivo' => 'glitter',      'icono' => '✨', 'texto' => 'Glitter y maquillaje neón',   'desc' => 'Brilla en cada celebración.'],
                ['archivo' => 'fiestas',      'icono' => '🎉', 'texto' => 'Zona de fiestas',            'desc' => 'Espacios decorados para tu evento.'],
            ];
            foreach ($amenidades as $i => $a):
                $foto = URL_ROOT . '/img/' . $a['archivo'] . '.webp';
            ?>
            <div class="col-6 col-md-4">
                <div class="amenidad-card" tabindex="0" role="button"
                     data-titulo="<?php echo htmlspecialchars($a['texto']); ?>"
                     data-desc="<?php echo htmlspecialchars($a['desc']); ?>"
                     data-imgs="<?php echo htmlspecialchars(json_encode([$foto]), ENT_QUOTES); ?>">
                    <div class="amenidad-foto" style="background-image:url('<?php echo $foto; ?>');">
                        <span class="icono color-<?php echo ($i % 6) + 1; ?>"><?php echo $a['icono']; ?></span>
                    </div>
                    <p class="amenidad-titulo"><?php echo $a['texto']; ?></p>
                    <p class="amenidad-desc"><?php echo $a['desc']; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Overlay reutilizable para la tarjeta "expandida" (ver amenidad-expandir.js) -->
<div class="amenidad-backdrop" id="amenidad-backdrop">
    <div class="amenidad-expandida" id="amenidad-expandida">
        <button type="button" class="amenidad-cerrar" id="amenidad-cerrar" aria-label="Cerrar">
            <i class="bi bi-x-lg"></i>
        </button>
        <div class="amenidad-expandida-img">
            <div class="amenidad-slider-track" id="amenidad-slider-track"></div>
        </div>
        <div class="amenidad-expandida-body">
            <p class="amenidad-titulo" id="amenidad-expandida-titulo"></p>
            <p class="amenidad-desc" id="amenidad-expandida-desc"></p>
        </div>
    </div>
</div>

<div class="divisor-ondas c1" aria-hidden="true"></div>

<!-- ============================================================
     ENTRADAS Y PROMOCIONES + DISPONIBILIDAD (calendario informativo:
     para quien quiera venir a saltar de forma normal y ver si un día/hora
     está ocupado por un cumpleaños. Las entradas normales se venden de
     forma presencial, esto es solo informativo, no vende ni reserva nada)
     ============================================================ -->
<section id="entradas" class="section" style="padding-top:0;">
    <div class="wrap">
        <div class="section-head">
            <h2 class="section-title fuente_bouncy titulo-arcoiris">Entradas y Disponibilidad</h2>
            <p class="section-lead">Precios y promociones vigentes, además elige el mejor momento para venir a divertirte.</p>
            <p class="horario-atencion"><i class="bi bi-clock-fill"></i> Lunes a domingo, de 3:00pm a 11:00pm</p>
        </div>
        <div class="row g-4 align-items-start justify-content-center">
            <div class="col-lg-6">
                <div class="flier">
                    <img src="<?php echo URL_ROOT; ?>/img/flier.webp" alt="Precios y promociones">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="calendario-disponibilidad">
                    <div class="calendar-header">
                        <button type="button" id="cal-prev" class="calendar-nav" aria-label="Mes anterior"><i class="bi bi-chevron-left"></i></button>
                        <div class="month-year" id="cal-month-year"></div>
                        <button type="button" id="cal-next" class="calendar-nav" aria-label="Mes siguiente"><i class="bi bi-chevron-right"></i></button>
                    </div>
                    <div class="calendar-grid" id="cal-grid"></div>
                    <div class="calendario-leyenda">
                        <span><span class="punto punto-disponible"></span> Sin cumpleaños</span>
                        <span><span class="punto punto-ocupado"></span> Con cumpleaños</span>
                    </div>
                    <div class="calendario-cta" id="cal-cta">
                        <p id="cal-info-fecha"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="divisor-ondas c2" aria-hidden="true"></div>

<!-- ============================================================
     PAQUETES DE CUMPLEAÑOS (antes /paquetes/cumpleanos)
     ============================================================ -->
<section id="cumpleanos" class="section" style="padding-top:0;">
    <div class="wrap">
    <div class="section-head">
        <h2 class="section-title fuente_bouncy titulo-arcoiris">Paquetes de Cumpleaños</h2>
        <p class="section-lead">Elige el paquete perfecto y deja que nosotros nos encarguemos del resto.</p>
    </div>

    <?php
    $imagenesPaquetes = [
        1 => 'trampolin.webp',
        2 => 'trampolin2.webp',
        3 => 'pared_escalar.webp',
        4 => 'HJ1.webp',
    ];

    // Contenido de cada paquete: icono + lista de beneficios.
    $contenidoPaquetes = [
        1 => [
            'bi-clock-fill'          => '2 horas de uso del local.',
            'bi-lightning-charge-fill' => 'Pulsera de 1 hora en camas saltarinas.',
            'bi-gift-fill'           => 'Dinámicas con premios a cargo de nuestras anfitrionas.',
        ],
        2 => [
            'bi-clock-fill'          => '2 horas y media de diversión total.',
            'bi-lightning-charge-fill' => 'Pulsera de 1 hora en trampolines.',
            'bi-gift-fill'           => 'Dinámicas con premios a cargo de nuestras anfitrionas.',
            'bi-stars'               => 'Glitter Bar y tatuajes neón durante 1 hora.',
            'bi-cup-straw'           => 'Combo Happy: Popcorn + agua mineral.',
        ],
        3 => [
            'bi-clock-fill'          => '3 horas de uso completo del local.',
            'bi-lightning-charge-fill' => '1 hora y media de trampolines, pared de escalar y tirolesa.',
            'bi-gift-fill'           => 'Dinámicas con premios por anfitrionas.',
            'bi-stars'               => 'Maquillaje neón y Glitter.',
            'bi-cup-straw'           => 'Combo Happy: Popcorn + bebida (frugos, agua o gaseosa).',
        ],
        4 => [
            'bi-clock-fill'          => '5 horas de uso exclusivo del local.',
            'bi-lightning-charge-fill' => 'Acceso a trampolines, tirolesa y pared de escalar.',
            'bi-gift-fill'           => 'Dinámicas con premios y muñecos inflables.',
            'bi-stars'               => 'Maquillaje neón y Glitter.',
            'bi-cup-straw'           => 'Combo Happy: Popcorn + bebida (gaseosa, frugos o agua) + pan con hotdog.',
        ],
    ];

    $alternar = false;

    foreach ($paquetes as $paquete):
        $imagen    = $imagenesPaquetes[$paquete->id_paquete] ?? 'default.jpg';
        $row_class = $alternar ? 'flex-md-row-reverse' : '';
    ?>

    <div class="package-card color-<?php echo (($paquete->id_paquete - 1) % 4) + 1; ?>">
        <div class="row g-0 align-items-center <?php echo $row_class; ?>">

            <div class="col-md-5 package-img" style="background-image: url('<?php echo URL_ROOT; ?>/img/<?php echo $imagen; ?>');"></div>

            <div class="col-md-7 package-body">
                <h3><?php echo $paquete->nombre; ?></h3>

                <?php if (isset($contenidoPaquetes[$paquete->id_paquete])): ?>
                <ul class="package-features">
                    <?php foreach ($contenidoPaquetes[$paquete->id_paquete] as $icono => $texto): ?>
                    <li><i class="bi <?php echo $icono; ?>"></i><span><?php echo $texto; ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <div class="package-price-tag">
                    <span>S/<?php echo number_format($paquete->precio_semana, 2); ?> Lunes a Viernes</span>
                    <span class="separador">•</span>
                    <span>S/<?php echo number_format($paquete->precio_fin_semana, 2); ?> Sábado y Domingo</span>
                </div>
                <div class="text-muted small mb-2">Precio por persona</div>

                <a href="<?php echo URL_ROOT; ?>/reservas/paso1?paquete=<?php echo $paquete->id_paquete; ?>">
                    <button class="btn-contratar mt-2">Reservar Paquete</button>
                </a>
            </div>
        </div>
    </div>

    <?php
        $alternar = !$alternar;
    endforeach;
    ?>

    <div class="extra-card text-center p-4 my-5 mx-auto">
        <h4 class="fw-bold mb-2">Cuartos de Experiencia</h4>
        <p class="mb-0">
            Si deseas que tu paquete de cumpleaños incluya nuestros
            <strong>cuartos de experiencia (cuarto de pintura o cuarto de destrucción)</strong>,
            solo agregarías <strong>S/. 15 soles MÁS</strong> por cada cuarto al precio del paquete que escojas.
        </p>
    </div>
    </div>
</section>

<div class="divisor-ondas c3" aria-hidden="true"></div>

<!-- ============================================================
     CÓMO FUNCIONA (nuestro flujo real de reserva)
     ============================================================ -->
<section class="section" style="padding-top:0;">
    <div class="wrap">
        <div class="section-head">
            <h2 class="section-title fuente_bouncy titulo-arcoiris">Cómo funciona tu reserva</h2>
            <p class="section-lead">Cuatro pasos entre que eliges tu paquete y celebras tu fiesta.</p>
        </div>
        <div class="steps">
            <div class="step">
                <span class="num">01</span>
                <h3>Elige tu paquete</h3>
                <p>Compara los paquetes de cumpleaños y escoge el que más se ajuste a tu celebración.</p>
            </div>
            <div class="step">
                <span class="num">02</span>
                <h3>Elige fecha y hora</h3>
                <p>Selecciona el día y el horario disponible en nuestro calendario en línea.</p>
            </div>
            <div class="step">
                <span class="num">03</span>
                <h3>Confirma tu pago</h3>
                <p>Paga tu reserva de forma sencilla y recibe la confirmación al instante.</p>
            </div>
            <div class="step">
                <span class="num">04</span>
                <h3>¡A saltar!</h3>
                <p>Llega el día de tu evento y disfruta de tu fiesta con nosotros.</p>
            </div>
        </div>
    </div>
</section>

<div class="divisor-ondas c4" aria-hidden="true"></div>

<!-- ============================================================
     CONÓCENOS (antes /inicio/conocenos)
     ============================================================ -->
<section id="conocenos" class="section" style="padding-top:0;">
    <div class="wrap">
    <div class="section-head">
        <h2 class="section-title fuente_bouncy titulo-arcoiris">Conócenos</h2>
        <p class="section-lead">La esencia detrás de cada salto, cada risa y cada celebración.</p>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="mision-card">
                <div class="icono">🎯</div>
                <h2 class="fuente_bouncy">Misión</h2>
                <p>Brindar una experiencia recreativa única, segura y divertida que promueva el bienestar físico y
                   emocional a través del juego y la actividad física en trampolines. Nos comprometemos a ofrecer
                   un entorno inclusivo y emocionante para todas las edades, donde las personas puedan mejorar su
                   salud, liberar su energía y crear recuerdos inolvidables con amigos y familiares, siempre bajo
                   los más altos estándares de seguridad y calidad.</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mision-card">
                <div class="icono">🚀</div>
                <h2 class="fuente_bouncy">Visión</h2>
                <p>Un centro recreativo de trampolines, con una atmósfera energizante y un diseño innovador,
                   destinado a fomentar el ejercicio físico, la creatividad y el entretenimiento a través
                   del salto. El parque será un espacio inclusivo para todas las edades, ofreciendo áreas
                   diferenciadas por niveles de habilidad, así como zonas temáticas para maximizar la diversión.</p>
            </div>
        </div>
    </div>

    <div class="map-container">
        <h2 class="section-title fuente_bouncy titulo-arcoiris">¡Ubícanos!</h2>
        <div id="mapa-ubicanos" role="img" aria-label="Mapa de ubicación de Happy Jumping Perú"></div>
    </div>
    </div>
</section>

<div class="divisor-ondas c5" aria-hidden="true"></div>

<!-- ============================================================
     COMENTARIOS (sección tipo blog: cualquiera puede ver los
     comentarios; solo un usuario con sesión iniciada puede comentar)
     ============================================================ -->
<section id="comentarios" class="section" style="padding-top:0;">
    <div class="wrap">
        <div class="section-head">
            <h2 class="section-title fuente_bouncy titulo-arcoiris">Comentarios</h2>
            <p class="section-lead">Lo que dicen quienes ya vinieron a saltar con nosotros.</p>
        </div>

        <div class="comentarios-formulario">
            <?php if (isset($_SESSION['id_usuario'])): ?>
            <form action="<?php echo URL_ROOT; ?>/comentarios/agregar" method="POST">
                <textarea name="comentario" maxlength="500" rows="3" placeholder="Cuéntanos tu experiencia en Happy&amp;Jumping..." required></textarea>
                <button type="submit" class="btn-contratar mt-2">Publicar comentario</button>
            </form>
            <?php else: ?>
            <p class="comentarios-login-prompt">
                <a href="<?php echo URL_ROOT; ?>/usuarios/login?redirect=<?php echo urlencode('/#comentarios'); ?>">Inicia sesión</a> para dejar tu comentario.
            </p>
            <?php endif; ?>
        </div>

        <div class="comentarios-lista">
            <?php if (empty($comentarios)): ?>
            <p class="comentarios-vacio">Todavía no hay comentarios. ¡Sé el primero en contar tu experiencia!</p>
            <?php else: ?>
                <?php foreach ($comentarios as $i => $c): ?>
                <div class="comentario-card">
                    <div class="comentario-avatar icono color-<?php echo ($i % 6) + 1; ?>"><?php echo htmlspecialchars(mb_strtoupper(mb_substr($c->nombre, 0, 1))); ?></div>
                    <div class="comentario-cuerpo">
                        <div class="comentario-cabecera">
                            <span class="comentario-nombre"><?php echo htmlspecialchars($c->nombre); ?></span>
                            <span class="comentario-fecha"><?php echo date('d/m/Y', strtotime($c->fecha_creacion)); ?></span>
                        </div>
                        <p class="comentario-texto"><?php echo nl2br(htmlspecialchars($c->comentario)); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

</div><!-- /.inicio-fondo-wrapper -->

<?php
// Carga el footer
require_once APP_ROOT . '/views/includes/footer.php';
?>

<script src="https://cdn.jsdelivr.net/npm/gsap@3.15/dist/gsap.min.js"></script>
<script src="<?php echo URL_ROOT; ?>/js/amenidad-expandir.js"></script>
<script src="<?php echo URL_ROOT; ?>/js/calendario-disponibilidad.js"></script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?php echo URL_ROOT; ?>/js/mapa.js"></script>
