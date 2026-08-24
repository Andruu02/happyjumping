// Calendario informativo de disponibilidad del local: para alguien que
// quiera venir a saltar de forma normal (las entradas se venden de forma
// presencial, esto no vende ni reserva nada) y quiera saber si un día
// va a estar ocupado por un cumpleaños, y a qué horas exactas.
// Al acercar el mouse a un día sale un globo de texto con la info; al
// hacer click (útil en celular, donde no hay "hover") queda igual escrita
// debajo del calendario.
document.addEventListener('DOMContentLoaded', function () {
    const grid = document.getElementById('cal-grid');
    if (!grid) return;

    const monthYearEl = document.getElementById('cal-month-year');
    const prevBtn = document.getElementById('cal-prev');
    const nextBtn = document.getElementById('cal-next');
    const cta = document.getElementById('cal-cta');
    const infoFechaEl = document.getElementById('cal-info-fecha');

    let currentDate = new Date();
    currentDate.setDate(1);
    let selectedDate = null;
    let fechasOcupadas = [];
    const horariosCache = {}; // dateStr -> [{hora_inicio, hora_fin}, ...]
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    function formatearHora(horaStr) {
        const [h, m] = horaStr.split(':').map(Number);
        const fecha = new Date(2000, 0, 1, h, m);
        return fecha.toLocaleTimeString('es-PE', { hour: 'numeric', minute: '2-digit', hour12: true });
    }

    function escaparAtributo(texto) {
        return texto.replace(/"/g, '&quot;');
    }

    async function fetchFechasOcupadas(ano, mes) {
        try {
            const res = await fetch(window.HJ_URL_ROOT + '/reservas/getFechasOcupadasConfirmadas/' + ano + '/' + mes);
            const datos = await res.json();
            fechasOcupadas = Array.isArray(datos) ? datos : [];
        } catch (e) {
            fechasOcupadas = [];
        }
    }

    // Trae de una vez el horario de cada día ocupado del mes (los que
    // todavía no estén en caché), para que el globo de texto aparezca al
    // instante al pasar el mouse, sin tener que esperar una petición.
    async function precargarHorarios(fechas) {
        const pendientes = fechas.filter(function (f) { return !(f in horariosCache); });
        await Promise.all(pendientes.map(async function (fecha) {
            try {
                const res = await fetch(window.HJ_URL_ROOT + '/reservas/getHorariosOcupadosConfirmados/' + fecha);
                const datos = await res.json();
                horariosCache[fecha] = Array.isArray(datos) ? datos : [];
            } catch (e) {
                horariosCache[fecha] = [];
            }
        }));
    }

    function textoTooltip(dateStr, ocupado) {
        if (!ocupado) return 'Sin cumpleaños, puedes venir a saltar con normalidad';

        const horarios = horariosCache[dateStr] || [];
        if (horarios.length === 0) return 'Sin cumpleaños, puedes venir a saltar con normalidad';

        const rangos = horarios.map(function (h) {
            return formatearHora(h.hora_inicio) + ' a ' + formatearHora(h.hora_fin);
        }).join(', ');
        return 'Cumpleaños de ' + rangos;
    }

    function mostrarInfoFecha(dateStr, ocupado) {
        cta.classList.add('activo');
        const fechaLegible = (new Date(dateStr + 'T00:00:00')).toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });

        if (!ocupado) {
            infoFechaEl.innerHTML = '<strong>' + fechaLegible + '</strong>: no hay ningún cumpleaños programado. Puedes venir a saltar cuando quieras dentro de nuestro horario de atención.';
            return;
        }

        const horarios = horariosCache[dateStr] || [];
        if (horarios.length === 0) {
            infoFechaEl.innerHTML = '<strong>' + fechaLegible + '</strong>: no hay ningún cumpleaños programado. Puedes venir a saltar cuando quieras dentro de nuestro horario de atención.';
            return;
        }

        const rangos = horarios.map(function (h) {
            return formatearHora(h.hora_inicio) + ' a ' + formatearHora(h.hora_fin);
        }).join(', ');
        infoFechaEl.innerHTML = '<strong>' + fechaLegible + '</strong>: hay un cumpleaños de ' + rangos + '. Fuera de ese horario puedes venir a saltar con normalidad.';
    }

    async function renderCalendar() {
        const month = currentDate.getMonth() + 1;
        const year = currentDate.getFullYear();

        monthYearEl.textContent = new Date(year, month - 1).toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });
        grid.innerHTML = '<div class="weekday">L</div><div class="weekday">M</div><div class="weekday">X</div><div class="weekday">J</div><div class="weekday">V</div><div class="weekday">S</div><div class="weekday">D</div>';

        await fetchFechasOcupadas(year, month);
        await precargarHorarios(fechasOcupadas);

        const firstDayOfMonth = new Date(year, month - 1, 1).getDay();
        const daysInMonth = new Date(year, month, 0).getDate();
        const startingDay = (firstDayOfMonth === 0) ? 6 : firstDayOfMonth - 1;

        for (let i = 0; i < startingDay; i++) {
            grid.innerHTML += '<div class="day empty"></div>';
        }
        for (let day = 1; day <= daysInMonth; day++) {
            const dayDate = new Date(year, month - 1, day);
            const dateStr = year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');

            const esPasado = dayDate < today;
            const ocupado = fechasOcupadas.includes(dateStr);

            let clases = 'day';
            if (esPasado) clases += ' empty';
            else if (ocupado) clases += ' occupied';
            if (selectedDate && dayDate.getTime() === selectedDate.getTime()) clases += ' selected';

            const tooltip = esPasado ? '' : ' data-tooltip="' + escaparAtributo(textoTooltip(dateStr, ocupado)) + '"';

            grid.innerHTML += '<div class="' + clases + '" data-date="' + dateStr + '"' + tooltip + '>' + day + '</div>';
        }
    }

    prevBtn.addEventListener('click', function () {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });
    nextBtn.addEventListener('click', function () {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    grid.addEventListener('click', function (e) {
        const dia = e.target.closest('.day');
        if (!dia || dia.classList.contains('empty')) return;

        const dateStr = dia.dataset.date;
        selectedDate = new Date(dateStr + 'T00:00:00');

        grid.querySelectorAll('.day.selected').forEach(function (d) { d.classList.remove('selected'); });
        dia.classList.add('selected');

        mostrarInfoFecha(dateStr, dia.classList.contains('occupied'));
    });

    renderCalendar();
});
