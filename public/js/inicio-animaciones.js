// Animaciones de entrada (GSAP + ScrollTrigger) para las tarjetas de la
// página de inicio: cada tarjeta aparece deslizándose y alternando el
// lado desde el que entra a medida que se hace scroll.

document.addEventListener('DOMContentLoaded', function () {
    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

    gsap.registerPlugin(ScrollTrigger);

    const tarjetas = document.querySelectorAll('.card-gsap');

    tarjetas.forEach(function (tarjeta, index) {
        gsap.from(tarjeta, {
            y: 40,
            x: index % 2 === 0 ? 40 : -40,
            opacity: 0,
            duration: 1,
            ease: 'power2.out',
            scrollTrigger: {
                trigger: tarjeta,
                start: 'top 85%',
                end: 'top 55%',
                scrub: 1,
            },
        });
    });
});
