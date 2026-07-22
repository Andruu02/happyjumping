// Scroll suave para los enlaces del header que apuntan a una sección de
// la misma página (ej. "#entradas"). Si la sección no existe en la página
// actual (ej. estamos en /admin y el link es a "/#entradas"), se deja la
// navegación normal del navegador para que primero cargue el inicio.
// También cierra el menú colapsado del navbar (móvil) ANTES de scrollear,
// para que no se vea el salto hacia arriba mientras el menú se cierra.

document.addEventListener('DOMContentLoaded', function () {
    const nav = document.querySelector('.navbar');
    const enlaces = document.querySelectorAll('a.nav-link[href*="#"]');

    enlaces.forEach(function (enlace) {
        const href = enlace.getAttribute('href') || '';
        const posHash = href.indexOf('#');
        if (posHash === -1) return;

        const hash = href.slice(posHash); // ej. "#entradas"
        if (hash.length < 2) return;

        const destino = document.querySelector(hash);
        if (!destino) return; // no existe en esta página: navegación normal

        enlace.addEventListener('click', function (e) {
            e.preventDefault();

            const colapso = document.getElementById('navbarNav');
            const abierto = colapso && colapso.classList.contains('show');

            function irAlDestino() {
                const alturaNav = nav ? nav.offsetHeight : 0;
                const top = destino.getBoundingClientRect().top + window.pageYOffset - alturaNav - 10;
                window.scrollTo({ top, behavior: 'smooth' });
                history.pushState(null, '', hash);
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
