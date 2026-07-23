// Calendario de solo lectura para ver fechas disponibles/ocupadas en el
// inicio. Usa el mismo endpoint que el calendario real de reservas
// (ReservasController::getFechasOcupadas), pero sin selección de paquete
// ni de hora: solo muestra el estado del día y, si está libre, un botón
// para ir a reservar.
document.addEventListener('DOMContentLoaded', function () {
    const grid = document.getElementById('cal-grid');
    if (!grid) return;

    const monthYearEl = document.getElementById('cal-month-year');
    const prevBtn = document.getElementById('cal-prev');
    const nextBtn = document.getElementById('cal-next');
    const cta = document.getElementById('cal-cta');
    const fechaElegidaEl = document.getElementById('cal-fecha-elegida');

    let currentDate = new Date();
    currentDate.setDate(1);
    let selectedDate = null;
    let fechasOcupadas = [];
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    async function fetchFechasOcupadas(ano, mes) {
        try {
            const res = await fetch(window.HJ_URL_ROOT + '/reservas/getFechasOcupadas/' + ano + '/' + mes);
            const datos = await res.json();
            fechasOcupadas = Array.isArray(datos) ? datos : [];
        } catch (e) {
            fechasOcupadas = [];
        }
    }

    async function renderCalendar() {
        const month = currentDate.getMonth() + 1;
        const year = currentDate.getFullYear();

        monthYearEl.textContent = new Date(year, month - 1).toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });
        grid.innerHTML = '<div class="weekday">L</div><div class="weekday">M</div><div class="weekday">X</div><div class="weekday">J</div><div class="weekday">V</div><div class="weekday">S</div><div class="weekday">D</div>';

        await fetchFechasOcupadas(year, month);

        const firstDayOfMonth = new Date(year, month - 1, 1).getDay();
        const daysInMonth = new Date(year, month, 0).getDate();
        const startingDay = (firstDayOfMonth === 0) ? 6 : firstDayOfMonth - 1;

        for (let i = 0; i < startingDay; i++) {
            grid.innerHTML += '<div class="day empty"></div>';
        }
        for (let day = 1; day <= daysInMonth; day++) {
            const dayDate = new Date(year, month - 1, day);
            const dateStr = year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');

            let clases = 'day';
            if (dayDate < today) {
                clases += ' empty';
            } else if (fechasOcupadas.includes(dateStr)) {
                clases += ' occupied';
            }
            if (selectedDate && dayDate.getTime() === selectedDate.getTime()) {
                clases += ' selected';
            }

            grid.innerHTML += '<div class="' + clases + '" data-date="' + dateStr + '">' + day + '</div>';
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
        if (!dia || dia.classList.contains('empty') || dia.classList.contains('occupied')) return;

        const dateStr = dia.dataset.date;
        selectedDate = new Date(dateStr + 'T00:00:00');

        grid.querySelectorAll('.day.selected').forEach(function (d) { d.classList.remove('selected'); });
        dia.classList.add('selected');

        fechaElegidaEl.textContent = selectedDate.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
        cta.classList.add('activo');
    });

    renderCalendar();
});
