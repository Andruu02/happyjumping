/**
 * ======================================================
 * HAPPY JUMPING CHATBOT V2
 * Versión 2.0
 * ======================================================
 */

class HappyJumpingChat {

    constructor() {

        this.config = {

            url: window.HJ_URL_ROOT || "",

            storage: "hj_chat_v2",

            titulo: "Happy Jumping IA"

        };

        this.historial = [];

        this.escribiendo = false;

        this.inicializar();

    }

    inicializar() {

        // Evitar crear el widget dos veces

        if (document.getElementById("hj-chat-widget")) {
            return;
        }

        this.crearHTML();

        this.crearCSS();

        this.obtenerElementos();

        this.registrarEventos();

    }

    crearHTML() {

        const widget = document.createElement("div");

        widget.id = "hj-chat-widget";

        widget.innerHTML = `

<div id="hj-chat-boton">

🤖

</div>

<div id="hj-chat-panel" class="hj-hidden">

<div class="hj-header">

<div class="hj-title">

🤖 ${this.config.titulo}

</div>

<button id="hj-chat-close">

✕

</button>

</div>

<div id="hj-chat-body">

<div class="hj-message bot">

Hola 👋

Soy el asistente de Happy Jumping.

</div>

</div>

<div class="hj-footer">

<input

id="hj-input"

type="text"

placeholder="Escribe un mensaje..."

>

<button id="hj-send">

➜

</button>

</div>

</div>

`;

        document.body.appendChild(widget);

    }

    crearCSS() {

        const style = document.createElement("style");

        style.innerHTML = `

#hj-chat-widget{

position:fixed;

right:25px;

bottom:25px;

z-index:999999;

font-family:Arial,sans-serif;

}

#hj-chat-boton{

width:65px;

height:65px;

border-radius:50%;

background:#ff4d00;

color:white;

display:flex;

align-items:center;

justify-content:center;

font-size:30px;

cursor:pointer;

box-shadow:0 8px 20px rgba(0,0,0,.25);

transition:.25s;

}

#hj-chat-boton:hover{

transform:scale(1.08);

}

#hj-chat-panel{

width:370px;

height:520px;

background:white;

border-radius:15px;

box-shadow:0 10px 35px rgba(0,0,0,.25);

overflow:hidden;

display:flex;

flex-direction:column;

position:absolute;

bottom:80px;

right:0;

}

.hj-hidden{

display:none!important;

}

.hj-header{

height:60px;

background:#ff4d00;

color:white;

display:flex;

justify-content:space-between;

align-items:center;

padding:0 18px;

font-weight:bold;

}

#hj-chat-close{

background:none;

border:none;

color:white;

font-size:22px;

cursor:pointer;

}

#hj-chat-body{

flex:1;

overflow:auto;

padding:18px;

background:#f6f6f6;

}

.hj-message{

padding:12px;

border-radius:12px;

margin-bottom:12px;

max-width:80%;

}

.bot{

background:white;

}

.user{

background:#ff4d00;

color:white;

margin-left:auto;

}

.hj-footer{

display:flex;

padding:12px;

gap:10px;

border-top:1px solid #ddd;

}

#hj-input{

flex:1;

padding:12px;

border-radius:10px;

border:1px solid #ddd;

outline:none;

}

#hj-send{

width:55px;

border:none;

background:#ff4d00;

color:white;

border-radius:10px;

cursor:pointer;

font-size:18px;

}

`;

        document.head.appendChild(style);

    }

    agregarMensaje(texto,tipo){

        const mensaje = document.createElement("div");

        mensaje.className = "hj-message " + tipo;

        mensaje.innerHTML = texto;

        this.body.appendChild(mensaje);

        this.scrollFinal();

    }

    scrollFinal(){

        this.body.scrollTop = this.body.scrollHeight;

    }

    limpiarInput(){

        this.input.value="";

        this.input.focus();

    }

    obtenerPregunta(){

        return this.input.value.trim();

    }

    enviar(){

        const pregunta=this.obtenerPregunta();

        if(!pregunta){

            return;

        }

        this.historial.push({

            role:"user",

            content:pregunta

        });

        this.agregarMensaje(

            pregunta,

            "user"

        );

        this.limpiarInput();

    }



    obtenerElementos(){

        this.boton=document.getElementById("hj-chat-boton");

        this.panel=document.getElementById("hj-chat-panel");

        this.cerrar=document.getElementById("hj-chat-close");

        this.body=document.getElementById("hj-chat-body");

        this.input=document.getElementById("hj-input");

        this.send=document.getElementById("hj-send");

    }

    registrarEventos(){

        this.boton.addEventListener("click",()=>{

            this.toggle();

        });

        this.cerrar.addEventListener("click",()=>{

            this.cerrarPanel();

        });

        this.send.addEventListener("click",()=>{

            this.enviar();

        });

        this.input.addEventListener("keydown",(e)=>{

            if(e.key==="Enter"){

                e.preventDefault();

                this.enviar();

            }

        });

    }

    abrir(){

        this.panel.classList.remove("hj-hidden");

    }

    cerrarPanel(){

        this.panel.classList.add("hj-hidden");

    }

    toggle(){

        this.panel.classList.toggle("hj-hidden");

    }

}

document.addEventListener("DOMContentLoaded",()=>{

    new HappyJumpingChat();

});