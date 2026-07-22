<?php

// VISTA DE INICIO (PÁGINA PRINCIPAL)
// Incluye, en una sola página con scroll, el contenido de Entradas,
// Cumpleaños y Conócenos (antes rutas separadas).
require_once APP_ROOT . '/views/includes/header.php';
?>
<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/inicio.css">

<!-- Envoltura de toda la página de inicio: contiene UNA sola capa de color
     de fondo (detrás de todas las secciones, que ahora van transparentes),
     para que el color sea continuo de arriba a abajo sin cortes. -->
<div class="inicio-fondo-wrapper">
    <div class="capa-color-fondo" aria-hidden="true"></div>

<section class="hero">
    <div class="hero-content">
        <h1 class="fuente_bouncy">¡Salta hacia la diversión!</h1>
        <p>Descubre la emoción sin límites en Happy&Jumping, el lugar donde la alegría nunca se detiene.</p>
        <a href="<?php echo URL_ROOT; ?>/reservas/paso1" class="hero-cta">🎉 ¡Reserva tu diversión!</a>
    </div>
</section>

<section class="amenidades-section position-relative py-5">
    <div class="container position-relative">
        <h2 class="section-title">¿Qué encontrarás en Happy&amp;Jumping?</h2>
        <p class="section-lead">Todo lo que necesitas para pasarla en grande, en un solo lugar.</p>
        <div class="row g-4 justify-content-center">
            <?php
            // Cada amenidad tiene 3 fotos (amenidades/<clave>-1.webp, -2.webp, -3.webp)
            // que se muestran en un slider infinito dentro de la tarjeta expandida.
            $amenidades = [
                ['clave' => 'anfitrionas',       'icono' => '🤸‍♀️', 'texto' => 'Anfitrionas',              'desc' => 'Dinámicas y juegos para toda la fiesta.'],
                ['clave' => 'trampolines',       'icono' => '🤾', 'texto' => 'Trampolines',                'desc' => 'Diversión y salto sin límites.'],
                ['clave' => 'helados',           'icono' => '🍧', 'texto' => 'Helados y granizados',       'desc' => 'Para refrescarte entre salto y salto.'],
                ['clave' => 'brazos-giratorios', 'icono' => '🌀', 'texto' => 'Brazos giratorios',          'desc' => 'Adrenalina en cada vuelta.'],
                ['clave' => 'glitter-neon',      'icono' => '✨', 'texto' => 'Glitter y maquillaje neón',   'desc' => 'Brilla en cada celebración.'],
                ['clave' => 'zona-fiestas',      'icono' => '🎉', 'texto' => 'Zona de fiestas',            'desc' => 'Espacios decorados para tu evento.'],
            ];
            foreach ($amenidades as $i => $a):
                $imagenes = [
                    URL_ROOT . '/img/amenidades/' . $a['clave'] . '-1.webp',
                    URL_ROOT . '/img/amenidades/' . $a['clave'] . '-2.webp',
                    URL_ROOT . '/img/amenidades/' . $a['clave'] . '-3.webp',
                ];
            ?>
            <div class="col-6 col-md-4">
                <div class="amenidad-card card-gsap" tabindex="0" role="button"
                     data-titulo="<?php echo htmlspecialchars($a['texto']); ?>"
                     data-desc="<?php echo htmlspecialchars($a['desc']); ?>"
                     data-imgs="<?php echo htmlspecialchars(json_encode($imagenes), ENT_QUOTES); ?>">
                    <div class="icono color-<?php echo ($i % 6) + 1; ?>"><span><?php echo $a['icono']; ?></span></div>
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

<!-- ============================================================
     ENTRADAS Y PROMOCIONES (antes /paquetes/entradas)
     ============================================================ -->
<section id="entradas" class="entradas-section position-relative py-5">
    <div class="container position-relative">
        <h2 class="section-title">Entradas y Promociones</h2>
        <p class="section-lead">Precios claros y promociones vigentes, siempre a la mano.</p>
        <div class="row g-4 justify-content-center">
            <div class="col-md-6">
                <div class="card-grande card-gsap">
                    <img src="<?php echo URL_ROOT; ?>/img/precios.webp" alt="Imagen Precios">
                </div>
            </div>
            <div class="col-md-6">
                <div class="card-grande card-gsap">
                    <img src="<?php echo URL_ROOT; ?>/img/promos.webp" alt="Imagen Promos">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     PAQUETES DE CUMPLEAÑOS (antes /paquetes/cumpleanos)
     ============================================================ -->
<section id="cumpleanos" class="cumpleanos-section position-relative py-5">
    <div class="container position-relative">
    <h2 class="section-title">Paquetes de Cumpleaños</h2>
    <p class="section-lead">Elige el paquete perfecto y deja que nosotros nos encarguemos del resto.</p>

    <?php
    $imagenesPaquetes = [
        1 => 'trampolin.webp',
        2 => 'trampolin2.webp',
        3 => 'pared_escalar.webp',
        4 => 'HJ1.webp',
    ];

    // Contenido de cada paquete: icono + lista de beneficios (más "chulo"
    // que la lista de viñetas plana que tenía antes).
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

    <div class="package-card card-gsap color-<?php echo (($paquete->id_paquete - 1) % 4) + 1; ?>">
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

    <div class="extra-card card-gsap text-center text-white p-4 my-5 mx-auto">
        <h4 class="fw-bold mb-2">Cuartos de Experiencia</h4>
        <p class="mb-0">
            Si deseas que tu paquete de cumpleaños incluya nuestros
            <strong>cuartos de experiencia (cuarto de pintura o cuarto de destrucción)</strong>,
            solo agregarías <strong>S/. 15 soles MÁS</strong> por cada cuarto al precio del paquete que escojas.
        </p>
    </div>

    <div class="mt-4 mb-5">
        <p class="text-muted disclaimers">
            * Al adquirir cualquiera de nuestros paquetes queda totalmente PROHIBIDO traer bebidas embotelladas.<br>
            * Los horarios para cumpleaños privados se confirman según disponibilidad del local.<br>
            * Cualquiera de los paquetes puede ser modificado según lo que desee el cliente.
        </p>
    </div>
    </div>
</section>

<!-- ============================================================
     CONÓCENOS (antes /inicio/conocenos)
     ============================================================ -->
<section id="conocenos" class="conocenos-section position-relative py-5">
    <div class="container position-relative">
    <h2 class="section-title">Conócenos</h2>
    <p class="section-lead">La esencia detrás de cada salto, cada risa y cada celebración.</p>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="mision-card card-gsap">
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
            <div class="mision-card card-gsap">
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
        <h2 class="fuente_bouncy">¡Ubícanos!</h2>
        <iframe
          src="https://www.google.com/maps?q=-6.5058575638378375, -76.35724119120785&z=15&output=embed">
        </iframe>
    </div>
    </div>
</section>

</div><!-- /.inicio-fondo-wrapper -->

<?php
// Carga el footer
require_once APP_ROOT . '/views/includes/footer.php';
?>

<script src="https://cdn.jsdelivr.net/npm/gsap@3.15/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.15/dist/ScrollTrigger.min.js"></script>
<script src="<?php echo URL_ROOT; ?>/js/inicio-animaciones.js"></script>
<script src="<?php echo URL_ROOT; ?>/js/amenidad-expandir.js"></script>
