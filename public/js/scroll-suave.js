// Scroll suave para los enlaces del header que apuntan a una sección de
// la misma página (ej. "#entradas"). Si el link apunta a otra página
// (aunque sea con hash), se deja la navegación normal del navegador.
// También cierra el menú colapsado del navbar (móvil) ANTES de scrollear,
// para que no se vea el salto hacia arriba mientras el menú se cierra.

document.addEventListener('DOMContentLoaded', function () {
    const nav = document.querySelector('.navbar');
    const enlaces = document.querySelectorAll('a.nav-link[href*="#"]');

    enlaces.forEach(function (enlace) {
        const url = new URL(enlace.href, window.location.href);
        const mismaPagina = url.pathname === window.location.pathname;
        const destino = url.hash ? document.querySelector(url.hash) : null;

        if (!mismaPagina || !destino) return; // navegación normal

        enlace.addEventListener('click', function (e) {
            e.preventDefault();

            const colapso = document.getElementById('navbarNav');
            const abierto = colapso && colapso.classList.contains('show');

            function irAlDestino() {
                const alturaNav = nav ? nav.offsetHeight : 0;
                const top = destino.getBoundingClientRect().top + window.pageYOffset - alturaNav - 10;
                window.scrollTo({ top, behavior: 'smooth' });
                history.pushState(null, '', url.hash);
            }

            if (abierto && window.bootstrap) {
                colapso.addEventListener('hidden.bs.collapse', irAlDestino, { once: true });
                bootstrap.Collapse.getOrCreateInstance(colapso).hide();
            } else {
                irAlDestino();
            }
        });
    });
});
