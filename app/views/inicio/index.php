<?php

// VISTA DE INICIO (PÁGINA PRINCIPAL)
// Incluye, en una sola página con scroll, el contenido de Entradas,
// Cumpleaños y Conócenos (antes rutas separadas).
require_once APP_ROOT . '/views/includes/header.php';
?>
<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/inicio.css">

<section class="hero">
    <div class="hero-content">
        <h1 class="fuente_bouncy">¡Salta hacia la diversión!</h1>
        <p>Descubre la emoción sin límites en Happy&Jumping, el lugar donde la alegría nunca se detiene.</p>
        <a href="<?php echo URL_ROOT; ?>/reservas/paso1" class="hero-cta">🎉 ¡Reserva tu diversión!</a>
    </div>
</section>

<section class="info-section container position-relative">
    <div class="info-card card-gsap flex-row-reverse">
        <img src="<?php echo URL_ROOT; ?>/img/CUMPLEHJ.webp" alt="Cumpleaños Happy Jumping">
        <div class="text">
            <h3 class="fuente_bouncy">¡Celebra tu día con estilo!</h3>
            <p>Vive un cumpleaños inolvidable con nuestro <strong>Paquete Jumping Party</strong>. Incluye decoración temática, zona exclusiva, animación, bebidas y pastel personalizado. Un ambiente lleno de color, risas y saltos que harán brillar tu día especial.</p>
        </div>
    </div>
</section>

<section class="info-section container position-relative" style="background-color:#fdf9ff;">
    <div class="info-card card-gsap">
        <img src="<?php echo URL_ROOT; ?>/img/HAPPY INICIO 1.webp" alt="Entradas Happy Jumping">
        <div class="text">
            <h3 class="fuente_bouncy">¡Ven a vivir la experiencia Happy&Jumping!</h3>
            <p>Disfruta de un día lleno de energía, saltos y diversión. Observa los detalles de tu entrada y siente la emoción de saltar en un ambiente seguro, lleno de color y buena vibra. ¡Perfecto para grandes y pequeños!</p>
        </div>
    </div>
</section>

<!-- ============================================================
     ENTRADAS Y PROMOCIONES (antes /paquetes/entradas)
     ============================================================ -->
<section id="entradas" class="container position-relative py-5">
    <h2 class="section-title">Entradas y Promociones</h2>
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
</section>

<!-- ============================================================
     PAQUETES DE CUMPLEAÑOS (antes /paquetes/cumpleanos)
     ============================================================ -->
<section id="cumpleanos" class="container position-relative py-5">
    <h2 class="section-title">Paquetes de Cumpleaños</h2>

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

    <div class="package-card card-gsap">
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
</section>

<!-- ============================================================
     CONÓCENOS (antes /inicio/conocenos)
     ============================================================ -->
<section id="conocenos" class="container position-relative py-5">
    <h2 class="section-title">Conócenos</h2>

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
</section>

<?php
// Carga el footer
require_once APP_ROOT . '/views/includes/footer.php';
?>

<script src="https://cdn.jsdelivr.net/npm/gsap@3.15/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.15/dist/ScrollTrigger.min.js"></script>
<script src="<?php echo URL_ROOT; ?>/js/inicio-animaciones.js"></script>
