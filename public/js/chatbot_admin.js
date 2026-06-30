/**
 * chatbot_admin.js — Widget de chat para el panel admin de Happy Jumping Peru
 * Incluir antes del </body> en app/views/admin/index.php
 */
(function () {
    'use strict';

    // ── Historial de la sesión (no persiste en BD directamente, eso lo hace el PHP) ──
    let historial = [];

    // ── Crear el HTML del widget ──────────────────────────────────────────────
    const widget = document.createElement('div');
    widget.id = 'hj-chat-widget';
    widget.innerHTML = `
        <button id="hj-chat-btn" title="Asistente IA">
            <i class="bi bi-robot"></i>
        </button>

        <div id="hj-chat-panel" class="hj-oculto">
            <div id="hj-chat-header">
                <span><i class="bi bi-robot me-2"></i>Asistente Happy Jumping</span>
                <button id="hj-chat-cerrar" title="Cerrar"><i class="bi bi-x-lg"></i></button>
            </div>

            <div id="hj-chat-mensajes">
                <div class="hj-msg hj-msg-bot">
                    Hola 👋 Soy tu asistente. Puedo consultarte reservas, pagos, clientes,
                    paquetes y más. ¿En qué te ayudo?
                </div>
            </div>

            <div id="hj-chat-sugerencias">
                <button class="hj-sug" data-texto="¿Cuántas reservas tenemos hoy?">Reservas hoy</button>
                <button class="hj-sug" data-texto="¿Qué pagos están pendientes?">Pagos pendientes</button>
                <button class="hj-sug" data-texto="¿Cuál es el paquete más vendido?">Paquete top</button>
                <button class="hj-sug" data-texto="¿Cuánto hemos ingresado este mes?">Ingresos del mes</button>
            </div>

            <div id="hj-chat-input-wrap">
                <input id="hj-chat-input" type="text" placeholder="Escribe tu pregunta..." autocomplete="off" />
                <button id="hj-chat-enviar"><i class="bi bi-send-fill"></i></button>
            </div>
        </div>
    `;
    document.body.appendChild(widget);

    // ── Estilos ───────────────────────────────────────────────────────────────
    const style = document.createElement('style');
    style.textContent = `
        #hj-chat-widget {
            position: fixed;
            bottom: 28px;
            right: 28px;
            z-index: 9999;
            font-family: 'Poppins', Arial, sans-serif;
        }

        /* Botón flotante */
        #hj-chat-btn {
            width: 58px; height: 58px;
            border-radius: 50%;
            background: #7F00FF;
            color: #fff;
            border: none;
            font-size: 1.6rem;
            cursor: pointer;
            box-shadow: 0 4px 18px rgba(127,0,255,.45);
            display: flex; align-items: center; justify-content: center;
            transition: transform .15s;
        }
        #hj-chat-btn:hover { transform: scale(1.1); }

        /* Panel */
        #hj-chat-panel {
            position: absolute;
            bottom: 70px; right: 0;
            width: 360px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 40px rgba(0,0,0,.18);
            display: flex; flex-direction: column;
            overflow: hidden;
            transition: opacity .2s, transform .2s;
        }
        #hj-chat-panel.hj-oculto {
            opacity: 0; pointer-events: none;
            transform: translateY(12px);
        }

        /* Header */
        #hj-chat-header {
            background: #7F00FF;
            color: #fff;
            padding: 14px 18px;
            display: flex; justify-content: space-between; align-items: center;
            font-weight: 700; font-size: .95rem;
        }
        #hj-chat-cerrar {
            background: none; border: none; color: #fff;
            font-size: 1rem; cursor: pointer; line-height: 1;
        }
        #hj-chat-cerrar:hover { opacity: .7; }

        /* Mensajes */
        #hj-chat-mensajes {
            padding: 16px;
            height: 300px;
            overflow-y: auto;
            display: flex; flex-direction: column; gap: 10px;
            background: #f8f5ff;
        }
        .hj-msg {
            max-width: 85%;
            padding: 10px 14px;
            border-radius: 14px;
            font-size: .88rem;
            line-height: 1.5;
            word-break: break-word;
            white-space: pre-wrap;
        }
        .hj-msg-bot {
            background: #fff;
            color: #333;
            align-self: flex-start;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
            border-bottom-left-radius: 4px;
        }
        .hj-msg-user {
            background: #7F00FF;
            color: #fff;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }
        .hj-msg-cargando {
            background: #ede7ff;
            color: #7F00FF;
            align-self: flex-start;
            font-style: italic;
        }

        /* Sugerencias */
        #hj-chat-sugerencias {
            display: flex; flex-wrap: wrap; gap: 6px;
            padding: 10px 16px 0;
        }
        .hj-sug {
            background: #f3e5ff;
            color: #7F00FF;
            border: 1px solid #d4a9ff;
            border-radius: 999px;
            padding: 4px 12px;
            font-size: .78rem;
            cursor: pointer;
            font-family: 'Poppins', Arial, sans-serif;
            transition: background .15s;
        }
        .hj-sug:hover { background: #e0c4ff; }

        /* Input */
        #hj-chat-input-wrap {
            display: flex; gap: 8px;
            padding: 12px 16px;
            border-top: 1px solid #eee;
            background: #fff;
        }
        #hj-chat-input {
            flex: 1;
            border: 1.5px solid #ddd;
            border-radius: 999px;
            padding: 8px 16px;
            font-size: .88rem;
            outline: none;
            font-family: 'Poppins', Arial, sans-serif;
        }
        #hj-chat-input:focus { border-color: #7F00FF; }
        #hj-chat-enviar {
            background: #7F00FF;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 38px; height: 38px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 1rem;
            transition: background .15s;
        }
        #hj-chat-enviar:hover { background: #6200c4; }
        #hj-chat-enviar:disabled { background: #ccc; cursor: wait; }
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

        const cargando = agregarMensaje('Consultando datos...', 'cargando');

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
        const div = document.createElement('div');
        div.className = 'hj-msg hj-msg-' + tipo;
        div.textContent = texto;
        mensajes.appendChild(div);
        mensajes.scrollTop = mensajes.scrollHeight;
        return div;
    }
})();
