// Al ir al login, el círculo del overlay nace en el botón que se clickeó y
// se transforma (MorphSVG) en un rectángulo que cubre toda la pantalla,
// como una "cortina" de marca; recién entonces se navega a la página.
document.addEventListener('DOMContentLoaded', function () {
    if (typeof gsap === 'undefined' || typeof MorphSVGPlugin === 'undefined') return;
    gsap.registerPlugin(MorphSVGPlugin);

    const overlay = document.getElementById('transicion-login');
    const circulo = document.getElementById('forma-transicion');
    if (!overlay || !circulo) return;

    const enlacesLogin = document.querySelectorAll('a[href$="/usuarios/login"]');

    enlacesLogin.forEach(function (enlace) {
        enlace.addEventListener('click', function (e) {
            e.preventDefault();
            const destino = enlace.href;
            const rect = enlace.getBoundingClientRect();
            const cx = rect.left + rect.width / 2;
            const cy = rect.top + rect.height / 2;
            const ancho = window.innerWidth;
            const alto = window.innerHeight;

            overlay.classList.add('activo');
            // Radio inicial pequeño pero distinto de 0: un círculo de radio 0
            // es una forma "degenerada" (sin área) y MorphSVGPlugin no logra
            // interpolarla correctamente, dejando la animación invisible.
            gsap.set(circulo, { attr: { cx: cx, cy: cy, r: 1 } });

            // Si por lo que sea la animación no llega a completar, igual
            // navegamos: nunca debe quedar al usuario "atascado" sin poder
            // entrar a iniciar sesión.
            let yaNavego = false;
            function irAlLogin() {
                if (yaNavego) return;
                yaNavego = true;
                window.location.href = destino;
            }
            setTimeout(irAlLogin, 1200);

            gsap.to(circulo, {
                duration: 0.7,
                ease: 'power2.inOut',
                morphSVG: 'M0,0 H' + ancho + ' V' + alto + ' H0 Z',
                onComplete: irAlLogin,
            });
        });
    });
});
