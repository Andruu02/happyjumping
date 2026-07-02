/**
 * chatbot_admin.js
 * Happy Jumping Perú
 * Versión mejorada con persistencia de sesión
 */

(function () {
    'use strict';

    // Evitar crear el widget dos veces
    if (document.getElementById('hj-chat-widget')) {
        return;
    }

    //==========================
    // CONFIGURACIÓN
    //==========================

    const STORAGE_KEY = 'hj_admin_chat';

    const URL_BASE = window.HJ_URL_ROOT || '';

    let historial = [];

    //==========================
    // CREAR HTML
    //==========================

    const widget = document.createElement('div');

    widget.id = 'hj-chat-widget';

    widget.innerHTML = `

        <button id="hj-chat-btn" title="Asistente IA">
            <i class="bi bi-robot"></i>
        </button>

        <div id="hj-chat-panel" class="hj-oculto">

            <div id="hj-chat-header">

                <span>

                    <i class="bi bi-robot me-2"></i>

                    Asistente Happy Jumping

                </span>

                <button id="hj-chat-cerrar">

                    <i class="bi bi-x-lg"></i>

                </button>

            </div>

            <div id="hj-chat-mensajes">

                <div class="hj-msg hj-msg-bot">

                    Hola 👋 Soy tu asistente.

                    Puedo ayudarte con reservas,

                    pagos, clientes,

                    paquetes y estadísticas.

                </div>

            </div>

            <div id="hj-chat-sugerencias">

                <button class="hj-sug"
                        data-texto="¿Cuántas reservas tenemos hoy?">

                    Reservas hoy

                </button>

                <button class="hj-sug"
                        data-texto="¿Qué pagos están pendientes?">

                    Pagos pendientes

                </button>

                <button class="hj-sug"
                        data-texto="¿Cuál es el paquete más vendido?">

                    Paquete top

                </button>

                <button class="hj-sug"
                        data-texto="¿Cuánto hemos ingresado este mes?">

                    Ingresos del mes

                </button>

            </div>

            <div id="hj-chat-input-wrap">

                <input

                    id="hj-chat-input"

                    type="text"

                    autocomplete="off"

                    placeholder="Escribe tu pregunta..."

                >

                <button id="hj-chat-enviar">

                    <i class="bi bi-send-fill"></i>

                </button>

            </div>

        </div>

    `;

    document.body.appendChild(widget);

    //==========================
    // FUNCIONES DE PERSISTENCIA
    //==========================

    function guardarEstado() {

        const datos = {

            abierto: !panel.classList.contains('hj-oculto'),

            historial: historial,

            html: mensajes.innerHTML,

            texto: input.value,

            scroll: mensajes.scrollTop

        };

        sessionStorage.setItem(

            STORAGE_KEY,

            JSON.stringify(datos)

        );

    }

    function restaurarEstado() {

        const guardado = sessionStorage.getItem(STORAGE_KEY);

        if (!guardado) return;

        try {

            const datos = JSON.parse(guardado);

            historial = datos.historial || [];

            if (datos.html) {

                mensajes.innerHTML = datos.html;

            }

            if (datos.texto) {

                input.value = datos.texto;

            }

            if (datos.abierto) {

                panel.classList.remove('hj-oculto');

            }

            requestAnimationFrame(() => {

                mensajes.scrollTop =

                    datos.scroll ||

                    mensajes.scrollHeight;

            });

        }

        catch (e) {

            console.error(e);

        }

    }
