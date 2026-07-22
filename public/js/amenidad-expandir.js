// Al hacer click en una tarjeta de "amenidades", la tarjeta se agranda
// (técnica FLIP con GSAP: parte del tamaño/posición exactos de la tarjeta
// y crece hasta un tamaño grande centrado) mostrando una imagen y el texto
// completo. Sin librerías de lightbox: se aprovecha GSAP, que ya se carga
// en esta página para las animaciones de scroll.

document.addEventListener('DOMContentLoaded', function () {
    if (typeof gsap === 'undefined') return;

    const backdrop   = document.getElementById('amenidad-backdrop');
    const expandida  = document.getElementById('amenidad-expandida');
    const btnCerrar  = document.getElementById('amenidad-cerrar');
    const imgEl      = document.getElementById('amenidad-expandida-img-el');
    const tituloEl   = document.getElementById('amenidad-expandida-titulo');
    const descEl     = document.getElementById('amenidad-expandida-desc');
    const tarjetas   = document.querySelectorAll('.amenidad-card');

    if (!backdrop || !expandida || tarjetas.length === 0) return;

    let origen = null; // rect de la tarjeta que abrió el modal

    function abrir(tarjeta) {
        origen = tarjeta.getBoundingClientRect();

        tituloEl.textContent = tarjeta.dataset.titulo || '';
        descEl.textContent   = tarjeta.dataset.desc || '';
        imgEl.src = tarjeta.dataset.img || '';
        imgEl.alt = tarjeta.dataset.titulo || '';

        // Punto de partida: exactamente el tamaño/posición de la tarjeta.
        gsap.set(expandida, {
            position: 'fixed',
            top: origen.top,
            left: origen.left,
            width: origen.width,
            height: origen.height,
            borderRadius: '20px',
        });
        expandida.classList.add('mostrando-contenido-parcial');

        backdrop.classList.add('activo');
        gsap.to(backdrop, { opacity: 1, duration: 0.25, ease: 'power1.out' });

        const anchoObjetivo  = Math.min(window.innerWidth * 0.92, 480);
        const altoObjetivo   = Math.min(window.innerHeight * 0.85, 620);

        gsap.to(expandida, {
            top: (window.innerHeight - altoObjetivo) / 2,
            left: (window.innerWidth - anchoObjetivo) / 2,
            width: anchoObjetivo,
            height: altoObjetivo,
            borderRadius: '24px',
            duration: 0.45,
            ease: 'power3.out',
            onComplete: function () {
                expandida.classList.add('expandida');
            },
        });
    }

    function cerrar() {
        if (!origen) return;
        expandida.classList.remove('expandida');

        gsap.to(expandida, {
            top: origen.top,
            left: origen.left,
            width: origen.width,
            height: origen.height,
            borderRadius: '20px',
            duration: 0.35,
            ease: 'power2.in',
            onComplete: function () {
                expandida.classList.remove('mostrando-contenido-parcial');
                backdrop.classList.remove('activo');
                gsap.set(expandida, { clearProps: 'all' });
            },
        });
        gsap.to(backdrop, { opacity: 0, duration: 0.3 });
    }

    tarjetas.forEach(function (tarjeta) {
        tarjeta.addEventListener('click', function () { abrir(tarjeta); });
        tarjeta.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); abrir(tarjeta); }
        });
    });

    btnCerrar.addEventListener('click', cerrar);
    backdrop.addEventListener('click', function (e) {
        if (e.target === backdrop) cerrar();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && backdrop.classList.contains('activo')) cerrar();
    });
});
