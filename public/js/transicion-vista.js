// Hace que el círculo de la transición (ver @view-transition en style.css)
// nazca justo en el punto donde se hizo click, en vez de siempre en la
// misma esquina. No usa preventDefault: la navegación sigue siendo normal,
// esto solo deja "la miguita" de dónde debe empezar el círculo antes de
// que el navegador arranque la transición nativa.
document.addEventListener('click', function (e) {
    const enlace = e.target.closest('a[href]');
    if (!enlace) return;

    const rect = enlace.getBoundingClientRect();
    const x = rect.left + rect.width / 2;
    const y = rect.top + rect.height / 2;
    document.documentElement.style.setProperty('--vt-x', x + 'px');
    document.documentElement.style.setProperty('--vt-y', y + 'px');
});
