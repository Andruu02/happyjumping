/**
 * chatbot_admin.js — Widget "HappyBot" para el panel admin de Happy Jumping Peru
 * Incluir antes del </body> en app/views/admin/index.php
 *
 * Rediseño visual — funcionamiento idéntico a la versión anterior.
 */
(function () {
    'use strict';

    // ── Historial de la sesión (no persiste en BD directamente, eso lo hace el PHP) ──
    let historial = [];

    // ── Crear el HTML del widget ──────────────────────────────────────────────
    const widget = document.createElement('div');
    widget.id = 'hj-chat-widget';
    widget.innerHTML = `
        <button id="hj-chat-btn" title="HappyBot — Asistente IA">
            <span id="hj-chat-btn-halo"></span>
            <span id="hj-chat-btn-avatar">
                <span class="hj-bot-face">
                    <span class="hj-bot-eye"></span>
                    <span class="hj-bot-eye"></span>
                    <span class="hj-bot-mouth"></span>
                </span>
            </span>
        </button>

        <div id="hj-chat-panel" class="hj-oculto">
            <div id="hj-chat-header">
                <div id="hj-chat-header-info">
                    <span id="hj-chat-avatar-mini">
                        <span class="hj-bot-face hj-bot-face-sm">
                            <span class="hj-bot-eye"></span>
                            <span class="hj-bot-eye"></span>
                            <span class="hj-bot-mouth"></span>
                        </span>
                    </span>
                    <span id="hj-chat-header-text">
                        <span id="hj-chat-titulo">HappyBot</span>
                        <span id="hj-chat-estado"><i class="hj-dot-online"></i>En línea</span>
                    </span>
                </div>
                <button id="hj-chat-cerrar" title="Cerrar"><i class="bi bi-x-lg"></i></button>
            </div>

            <div id="hj-chat-mensajes">
                <div class="hj-msg-row hj-msg-row-bot">
                    <span class="hj-avatar-msg">
                        <span class="hj-bot-face hj-bot-face-xs">
                            <span class="hj-bot-eye"></span>
                            <span class="hj-bot-eye"></span>
                            <span class="hj-bot-mouth"></span>
                        </span>
                    </span>
                    <div class="hj-msg hj-msg-bot">
                        ¡Hola! Soy <strong>HappyBot</strong> 🎈 Puedo ayudarte con reservas, pagos,
                        clientes, paquetes y más. ¿En qué te ayudo hoy?
                    </div>
                </div>
            </div>

            <div id="hj-chat-sugerencias">
                <button class="hj-sug" data-texto="¿Cuántas reservas tenemos hoy?">
                    <i class="bi bi-calendar-event"></i> Reservas hoy
                </button>
                <button class="hj-sug" data-texto="¿Qué pagos están pendientes?">
                    <i class="bi bi-hourglass-split"></i> Pagos pendientes
                </button>
                <button class="hj-sug" data-texto="¿Cuál es el paquete más vendido?">
                    <i class="bi bi-trophy"></i> Paquete top
                </button>
                <button class="hj-sug" data-texto="¿Cuánto hemos ingresado este mes?">
                    <i class="bi bi-graph-up-arrow"></i> Ingresos del mes
                </button>
            </div>

            <div id="hj-chat-input-wrap">
                <input id="hj-chat-input" type="text" placeholder="Escríbele a HappyBot..." autocomplete="off" />
                <button id="hj-chat-enviar" title="Enviar"><i class="bi bi-send-fill"></i></button>
            </div>
        </div>
    `;
    document.body.appendChild(widget);

    // ── Estilos ───────────────────────────────────────────────────────────────
    const style = document.createElement('style');
    style.textContent = `
        #hj-chat-widget {
            --hj-morado: #6C00FF;
            --hj-morado-oscuro: #4B0FCC;
            --hj-morado-suave: #F3EEFF;
            --hj-fondo-panel: #FAF8FF;
            --hj-texto: #241B3D;
            --hj-texto-suave: #6E668A;
            --hj-acento: #FFB020;
            --hj-borde: #E7DFFF;

            position: fixed;
            bottom: 28px;
            right: 28px;
            z-index: 9999;
            font-family: 'Nunito', Arial, sans-serif;
        }

        #hj-chat-widget .hj-titulo-font {
            font-family: 'Baloo 2', 'Nunito', Arial, sans-serif;
        }

        /* ── Botón flotante con halo ─────────────────────────────────────── */
        #hj-chat-btn {
            position: relative;
            width: 62px; height: 62px;
            border-radius: 50%;
            background: linear-gradient(145deg, var(--hj-morado), var(--hj-morado-oscuro));
            border: none;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(108,0,255,.4);
            display: flex; align-items: center; justify-content: center;
            transition: transform .18s ease;
        }
        #hj-chat-btn:hover { transform: scale(1.08); }
        #hj-chat-btn:active { transform: scale(0.96); }

        #hj-chat-btn-halo {
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: 2px solid rgba(108,0,255,.35);
            animation: hj-pulso 2.4s ease-out infinite;
        }
        @keyframes hj-pulso {
            0%   { transform: scale(0.85); opacity: .8; }
            70%  { transform: scale(1.25); opacity: 0; }
            100% { transform: scale(1.25); opacity: 0; }
        }

        #hj-chat-btn-avatar { position: relative; z-index: 1; }

        /* ── Carita del bot (avatar reutilizable en 3 tamaños) ───────────── */
        .hj-bot-face {
            position: relative;
            display: block;
            width: 30px; height: 30px;
        }
        .hj-bot-face-sm { width: 22px; height: 22px; }
        .hj-bot-face-xs { width: 18px; height: 18px; }
        .hj-bot-eye {
            position: absolute;
            top: 35%;
            width: 16%; height: 16%;
            background: #fff;
            border-radius: 50%;
            animation: hj-parpadeo 4.5s ease-in-out infinite;
        }
        .hj-bot-eye:first-child { left: 22%; }
        .hj-bot-eye:last-child  { right: 22%; }
        .hj-bot-mouth {
            position: absolute;
            bottom: 24%; left: 50%;
            width: 42%; height: 16%;
            border-bottom: 2.5px solid #fff;
            border-radius: 0 0 50% 50%;
            transform: translateX(-50%);
        }
        @keyframes hj-parpadeo {
            0%, 90%, 100% { transform: scaleY(1); }
            95%           { transform: scaleY(0.15); }
        }

        /* ── Panel ────────────────────────────────────────────────────────── */
        #hj-chat-panel {
            position: absolute;
            bottom: 76px; right: 0;
            width: 368px;
            background: var(--hj-fondo-panel);
            border-radius: 20px;
            box-shadow: 0 12px 48px rgba(76,0,175,.22);
            display: flex; flex-direction: column;
            overflow: hidden;
            border: 1px solid var(--hj-borde);
            transform-origin: bottom right;
            transition: opacity .22s ease, transform .22s ease;
        }
        #hj-chat-panel.hj-oculto {
            opacity: 0; pointer-events: none;
            transform: scale(.92) translateY(10px);
        }

        /* ── Header ───────────────────────────────────────────────────────── */
        #hj-chat-header {
            background: linear-gradient(120deg, var(--hj-morado), var(--hj-morado-oscuro));
            color: #fff;
            padding: 14px 16px;
            display: flex; justify-content: space-between; align-items: center;
        }
        #hj-chat-header-info { display: flex; align-items: center; gap: 10px; }
        #hj-chat-avatar-mini {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: rgba(255,255,255,.16);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        #hj-chat-header-text { display: flex; flex-direction: column; line-height: 1.25; }
        #hj-chat-titulo {
            font-family: 'Baloo 2', 'Nunito', Arial, sans-serif;
            font-weight: 700;
            font-size: 1.02rem;
            letter-spacing: .2px;
        }
        #hj-chat-estado {
            font-size: .72rem;
            color: rgba(255,255,255,.85);
            display: flex; align-items: center; gap: 5px;
        }
        .hj-dot-online {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #3CE87A;
            box-shadow: 0 0 0 rgba(60,232,122,.5);
            animation: hj-latido 1.8s ease-in-out infinite;
        }
        @keyframes hj-latido {
            0%, 100% { box-shadow: 0 0 0 0 rgba(60,232,122,.55); }
            50%      { box-shadow: 0 0 0 4px rgba(60,232,122,0); }
        }
        #hj-chat-cerrar {
            background: rgba(255,255,255,.14);
            border: none; color: #fff;
            width: 30px; height: 30px;
            border-radius: 50%;
            font-size: .82rem; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background .15s;
        }
        #hj-chat-cerrar:hover { background: rgba(255,255,255,.28); }

        /* ── Mensajes ─────────────────────────────────────────────────────── */
        #hj-chat-mensajes {
            padding: 16px;
            height: 320px;
            overflow-y: auto;
            display: flex; flex-direction: column; gap: 12px;
        }
        #hj-chat-mensajes::-webkit-scrollbar { width: 6px; }
        #hj-chat-mensajes::-webkit-scrollbar-thumb {
            background: var(--hj-borde);
            border-radius: 10px;
        }

        .hj-msg-row { display: flex; align-items: flex-end; gap: 8px; }
        .hj-msg-row-user { flex-direction: row-reverse; }

        .hj-avatar-msg {
            width: 26px; height: 26px;
            border-radius: 50%;
            background: linear-gradient(145deg, var(--hj-morado), var(--hj-morado-oscuro));
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .hj-msg {
            max-width: 78%;
            padding: 10px 14px;
            border-radius: 16px;
            font-size: .88rem;
            line-height: 1.5;
            word-break: break-word;
            white-space: pre-wrap;
        }
        .hj-msg-bot {
            background: #fff;
            color: var(--hj-texto);
            border: 1px solid var(--hj-borde);
            border-bottom-left-radius: 5px;
        }
        .hj-msg-user {
            background: linear-gradient(135deg, var(--hj-morado), var(--hj-morado-oscuro));
            color: #fff;
            border-bottom-right-radius: 5px;
        }

        /* Indicador de "escribiendo" */
        .hj-typing {
            display: flex; align-items: center; gap: 4px;
            padding: 12px 14px;
        }
        .hj-typing span {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--hj-morado);
            opacity: .5;
            animation: hj-typing-dot 1.2s infinite ease-in-out;
        }
        .hj-typing span:nth-child(2) { animation-delay: .15s; }
        .hj-typing span:nth-child(3) { animation-delay: .3s; }
        @keyframes hj-typing-dot {
            0%, 60%, 100% { transform: translateY(0); opacity: .4; }
            30%           { transform: translateY(-4px); opacity: 1; }
        }

        /* ── Sugerencias ──────────────────────────────────────────────────── */
        #hj-chat-sugerencias {
            display: flex; flex-wrap: wrap; gap: 6px;
            padding: 4px 16px 12px;
        }
        .hj-sug {
            display: flex; align-items: center; gap: 5px;
            background: var(--hj-morado-suave);
            color: var(--hj-morado-oscuro);
            border: 1px solid var(--hj-borde);
            border-radius: 999px;
            padding: 6px 13px;
            font-size: .75rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Nunito', Arial, sans-serif;
            transition: background .15s, transform .1s;
        }
        .hj-sug:hover { background: #e7d9ff; transform: translateY(-1px); }
        .hj-sug i { font-size: .8rem; }

        /* ── Input ────────────────────────────────────────────────────────── */
        #hj-chat-input-wrap {
            display: flex; gap: 8px;
            padding: 12px 14px;
            border-top: 1px solid var(--hj-borde);
            background: #fff;
        }
        #hj-chat-input {
            flex: 1;
            border: 1.5px solid var(--hj-borde);
            border-radius: 999px;
            padding: 9px 16px;
            font-size: .88rem;
            outline: none;
            font-family: 'Nunito', Arial, sans-serif;
            color: var(--hj-texto);
            transition: border-color .15s;
        }
        #hj-chat-input::placeholder { color: var(--hj-texto-suave); }
        #hj-chat-input:focus { border-color: var(--hj-morado); }
        #hj-chat-enviar {
            background: linear-gradient(145deg, var(--hj-morado), var(--hj-morado-oscuro));
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 1rem;
            transition: transform .12s, background .15s;
            flex-shrink: 0;
        }
        #hj-chat-enviar:hover:not(:disabled) { transform: scale(1.06); }
        #hj-chat-enviar:disabled { background: #d8d0ee; cursor: wait; }

        /* ── Responsive ───────────────────────────────────────────────────── */
        @media (max-width: 420px) {
            #hj-chat-widget { right: 14px; bottom: 14px; }
            #hj-chat-panel { width: calc(100vw - 28px); }
        }
    `;
    document.head.appendChild(style);

    // ── Referencias DOM ───────────────────────────────────────────────────────
    const btnAbrir   = document.getElementById('hj-chat-btn');
    const panel      = document.getElementById('hj-chat-panel');
    const btnCerrar  = document.getElementById('hj-chat-cerrar');
    const mensajes   = document.getElementById('hj-chat-mensajes');
    const input      = document.getElementById('hj-chat-input');
    const btnEnviar  = document.getElementById('hj-chat-enviar');
    const sugerencias = document.querySelectorAll('.hj-sug');

    // ── Abrir / cerrar ────────────────────────────────────────────────────────
    btnAbrir.addEventListener('click', () => {
        panel.classList.toggle('hj-oculto');
        if (!panel.classList.contains('hj-oculto')) input.focus();
    });
    btnCerrar.addEventListener('click', () => panel.classList.add('hj-oculto'));

    // ── Sugerencias ───────────────────────────────────────────────────────────
    sugerencias.forEach(btn => {
        btn.addEventListener('click', () => {
            input.value = btn.dataset.texto;
            enviar();
        });
    });

    // ── Enviar con Enter ──────────────────────────────────────────────────────
    input.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); enviar(); }
    });
    btnEnviar.addEventListener('click', enviar);

    // ── Función principal de envío ────────────────────────────────────────────
    async function enviar() {
        const texto = input.value.trim();
        if (!texto) return;

        agregarMensaje(texto, 'user');
        input.value = '';
        btnEnviar.disabled = true;

        const cargando = agregarTyping();

        // Ajusta la URL si tu URL_ROOT no es '/'
        const urlBase = window.HJ_URL_ROOT || '';

        try {
            const res = await fetch(urlBase + '/chatbot/enviar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ pregunta: texto, historial }),
            });

            const data = await res.json();
            cargando.remove();

            if (data.error) {
                agregarMensaje('Error: ' + data.error, 'bot');
            } else {
                agregarMensaje(data.respuesta, 'bot');
                // Guardar ronda en historial de sesión
                historial.push({ role: 'user',      content: texto });
                historial.push({ role: 'assistant', content: data.respuesta });
                // Máximo 12 mensajes en memoria
                if (historial.length > 12) historial = historial.slice(-12);
            }
        } catch (err) {
            cargando.remove();
            agregarMensaje('No se pudo conectar. Intenta de nuevo.', 'bot');
        }

        btnEnviar.disabled = false;
        input.focus();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function agregarMensaje(texto, tipo) {
        const row = document.createElement('div');
        row.className = 'hj-msg-row hj-msg-row-' + tipo;

        if (tipo === 'bot') {
            row.innerHTML = `
                <span class="hj-avatar-msg">
                    <span class="hj-bot-face hj-bot-face-xs">
                        <span class="hj-bot-eye"></span>
                        <span class="hj-bot-eye"></span>
                        <span class="hj-bot-mouth"></span>
                    </span>
                </span>
                <div class="hj-msg hj-msg-bot"></div>
            `;
            row.querySelector('.hj-msg').textContent = texto;
        } else {
            row.innerHTML = `<div class="hj-msg hj-msg-user"></div>`;
            row.querySelector('.hj-msg').textContent = texto;
        }

        mensajes.appendChild(row);
        mensajes.scrollTop = mensajes.scrollHeight;
        return row;
    }

    function agregarTyping() {
        const row = document.createElement('div');
        row.className = 'hj-msg-row hj-msg-row-bot';
        row.innerHTML = `
            <span class="hj-avatar-msg">
                <span class="hj-bot-face hj-bot-face-xs">
                    <span class="hj-bot-eye"></span>
                    <span class="hj-bot-eye"></span>
                    <span class="hj-bot-mouth"></span>
                </span>
            </span>
            <div class="hj-msg hj-msg-bot hj-typing">
                <span></span><span></span><span></span>
            </div>
        `;
        mensajes.appendChild(row);
        mensajes.scrollTop = mensajes.scrollHeight;
        return row;
    }
})();