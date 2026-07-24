# Happy Jumping — Contexto del proyecto para Claude Code

> Este archivo se carga automáticamente por Claude Code al abrir este directorio.
> Si por algún motivo no se carga solo (por ejemplo, si Claude Code se abrió en
> una carpeta padre), pide explícitamente: **"lee CLAUDE.md y ponte al día"**.

## Qué es esto

Sitio web completo para **Happy Jumping**, un parque de trampolines/salón de
fiestas infantiles en Perú. Permite a los usuarios reservar paquetes de
fiesta, subir su comprobante de pago (Yape/QR), y a los administradores
gestionar reservas, paquetes, promociones y reportes. Producción en
Hostinger (`happyjumpingperu.com`), con soporte para desplegar también en
Cloud Run.

## Stack y arquitectura

- **Backend**: PHP puro con un **MVC casero** (no framework). Sin Composer
  autoload real más allá de lo que haya en `vendor/` para librerías puntuales.
- **Frontend**: Vistas PHP con HTML embebido, Bootstrap 5 (CDN), Bootstrap
  Icons (CDN), CSS propio por página/sección (no hay bundler ni build step).
- **Base de datos**: MySQL (Hostinger en producción). Config en
  `app/config/database.php` (con overrides por variable de entorno para
  Cloud Run/Cloud SQL).
- **Punto de entrada único**: `public/index.php` — define `APP_ROOT`,
  `PUBLIC_ROOT` y `URL_ROOT` (detectado dinámicamente por host/protocolo, o
  forzado por env var), inicia sesión, carga configs y arranca `App`.

### Núcleo MVC (`app/core/`)

- **`App.php`**: router. Parsea la URL como `/controlador/metodo/parametros`,
  usa `ucwords()` para resolver el nombre de la clase controlador y convierte
  kebab-case a camelCase para el nombre del método.
- **`Controller.php`**: clase base. `model($nombre)` devuelve una instancia
  nueva del modelo. `view($vista, $datos=[])` hace `extract($datos)` y luego
  `require_once` la vista — **importante**: como `extract()` no destruye el
  array original, dentro de la vista se puede usar tanto la variable suelta
  (`$titulo`) como `$datos['titulo']` indistintamente.
- **`Model.php`**: clase base con `query()`, `bind()`, `execute()` (con
  reconexión automática si MySQL da "gone away"), `single()`, `resultSet()`.
  **No** tiene helper de `lastInsertId()` — hay que llamar
  `$this->dbh->lastInsertId()` directo si se necesita.

### Estructura de carpetas relevante

```
app/
  controllers/   InicioController, ReservasController, PerfilController,
                 UsuariosController, AdminController, ComentariosController,
                 PromocionController, ReporteController, ChatbotController,
                 ApiController
  models/        Uno por controlador aprox. (PaqueteModel, HorarioModel,
                 ReservaModel, PerfilModel, AdminModel, UsuarioModel, etc.)
  views/
    inicio/      Página principal (paquetes, promociones, sección cumpleaños)
    reservas/    Flujo de reserva (ver detalle abajo)
    perfil/      Perfil del usuario logueado (sus reservas)
    admin/       Panel de administración
    usuarios/    Login/registro
    includes/    header.php / footer.php compartidos
public/
  css/           Una hoja de estilos por sección: style.css (global/:root
                 con variables de marca), inicio.css, perfil.css, reserva.css,
                 login.css, admin_login.css
  index.php      Entry point
db/              (si aplica) dumps/migraciones sueltas
```

### Convención de CSS — ¡importante, causó un bug real!

Cada página carga **su propia hoja de estilos** además de (o en vez de)
`header.php`. Por ejemplo `perfil/index.php` incluye `header.php` (que carga
`style.css` global) y **además** su propio `<link>` a `perfil.css`. Pero
`reservas/paso1.php` **no** incluye `header.php` en absoluto — es una página
standalone con su propio `<head>` completo y solo carga `reserva.css`.

Consecuencia: una clase definida en `inicio.css` (como `.btn-contratar`) **no
existe** en la página de perfil ni en la de reservas, aunque visualmente se
parezca. Ya nos pasó: un botón se veía como link azul subrayado sin estilo
porque se le puso una clase de `inicio.css` que nunca se cargaba ahí. Antes
de reusar una clase visual entre páginas, verificar qué hoja de estilos
carga esa vista.

Variables de marca en `:root` (definidas en `style.css`, disponibles en
cualquier página que cargue `header.php`): `--rosa`, `--celeste`,
`--amarillo`, `--naranja`, `--verde`, `--morado`. `reserva.css` define su
propio `:root` con nombres iguales para no depender de `style.css` (ya que
esa página no carga `header.php`).

### Cache-busting de CSS

Cuando se edita un CSS y el navegador del usuario podría tener una copia
vieja en caché, usar `filemtime()` en el `<link>`:
```php
<link rel="stylesheet" href="<?php echo URL_ROOT; ?>/css/archivo.css?v=<?php echo filemtime(PUBLIC_ROOT . '/css/archivo.css'); ?>">
```
Ya aplicado en `perfil/index.php`. Si se reportan cambios de CSS que "no se
ven", sospechar caché antes que del código.

## Trabajo reciente (este es el estado actual)

### 1. Rediseño de "Mis Reservas" (`app/views/perfil/index.php`)

- Se pasó de tarjetas apiladas a una **lista** (`.reserva-fila`): franja con
  fecha a la izquierda, info al centro, badge de estado a la derecha.
- Se agregó **paginación** (10 por página) y **filtros** (estado, paquete,
  rango de fechas), siguiendo el mismo patrón que ya existía en
  `AdminModel`/`app/views/admin/reservas.php`:
  - `PerfilModel` tiene un método privado
    `condicionesReservasUsuario($id_usuario, $filtros)` que arma el `WHERE` +
    array de `params`, reusado por `getReservasPorUsuario()` (con
    `LIMIT`/`OFFSET` opcional) y `contarReservasPorUsuario()` (para el total
    de páginas).
  - `PerfilController::index()` calcula `$filtros` desde `$_GET`, pagina, y
    pasa todo a la vista.
- La cabecera de "Bienvenido" se reemplazó por un **header estilo
  LinkedIn**: banner con degradado + avatar circular superpuesto (ícono de
  animal aleatorio + color de gradiente aleatorio, elegido con
  `array_rand()`/`random_int()` en cada carga) + saludo + botón "Hacer una
  reserva".
- Todo (header + filtros + lista + paginación) se unificó en **una sola
  tarjeta** (`.perfil-unificado` / `.perfil-unificado-contenido`), no 3 cajas
  separadas.
- El botón "Hacer una reserva" tenía un bug de estilos (ver sección CSS de
  arriba) — corregido dándole estilos propios y autocontenidos en
  `perfil.css` en vez de depender de `.btn-contratar` de `inicio.css`.

### 2. Flujo de reserva unificado en una sola página (`app/views/reservas/paso1.php`)

Antes había 3 páginas separadas (`/reservas/paso1`, `/paso2`, `/paso3`) que
se comunicaban entre sí guardando datos en `sessionStorage` y navegando con
`window.location.href`. Ahora:

- **Todo vive en un solo documento** (`paso1.php`, servido por
  `ReservasController::paso1()`): 3 `<div class="step-card">` apiladas
  verticalmente (`#card-paso1`, `#card-paso2`, `#card-paso3`).
- Los pasos 2 y 3 arrancan con la clase `.step-bloqueado` (atenuados,
  `pointer-events: none`, badge "🔒 Bloqueado"). Al completar un paso, JS
  quita esa clase del siguiente y hace `scrollIntoView({behavior:'smooth'})`
  hacia él.
- Barra fija arriba (`.reserva-topbar`) con un **stepper** visual (1-2-3, se
  van marcando con ✓ en verde a medida que avanzas).
- Todo el estado de la reserva vive en un objeto JS en memoria
  (`reservaData = {}`), ya **no se usa `sessionStorage`** ni recarga de
  página entre pasos.
- El **botón de retroceder** (esquina superior izquierda) ya no navega
  dentro del flujo — ahora hace `window.history.back()` (con fallback a
  `URL_ROOT` si no hay historial), o sea que te devuelve a la página/pestaña
  desde la que entraste a reservar (inicio, perfil, etc.), tal como se pidió.
- `ReservasController::paso2()` y `paso3()` quedaron como simples redirects a
  `/reservas/paso1` (por si algún link viejo/favorito apunta ahí), no
  renderizan vistas propias.
- El submit final (subida de comprobante) sigue siendo un POST normal de
  formulario a `/reservas/finalizar` — eso es correcto porque termina en una
  página de resultado distinta (`/reservas/exito`), no es "un paso más".

Enlaces externos que apuntan a `/reservas/paso1` (siguen funcionando igual,
no se tocaron): `inicio/index.php` (botón hero y tarjetas de paquete con
`?paquete=ID`), `includes/header.php` (dropdown "Nueva Reserva"),
`perfil/index.php` (botón "Hacer una reserva").

## Cómo verificar cambios en este proyecto (sin entorno de desarrollo real)

No hay conexión a la base de datos real desde un entorno de pruebas aislado
(apunta a Hostinger en prod). El flujo que se ha usado para verificar cambios
en vistas PHP sin tocar la BD:

1. `php -l archivo.php` para sintaxis.
2. Un script scratch que define constantes mínimas (`URL_ROOT`, `PUBLIC_ROOT`
   si aplica) y variables que la vista espera (`$titulo`, `$paquetes`,
   objetos `stdClass` simulando filas de BD, etc.), y hace `require` directo
   de la vista — sin pasar por `App`/`Controller`/DB.
3. Revisar el HTML resultante con `grep` buscando las clases/IDs esperados y
   confirmar que no haya `PHP Warning`/`PHP Fatal error` en stderr.
4. Borrar los archivos scratch al terminar.

(Nota: en Windows/Git Bash, PHP necesita rutas estilo `D:/HJ/...`, las rutas
`/d/HJ/...` de Git Bash no las resuelve directamente en `require`.)

## Preferencias de trabajo con el usuario

- El usuario escribe en español y da libertad creativa de diseño cuando dice
  frases tipo "mira tú cómo lo hacemos, que se vea bonito" — se espera que
  se proponga una solución visual pulida, no solo lo mínimo funcional.
- Suele mandar capturas de pantalla como referencia de estilo (p. ej. mandó
  una captura de un perfil de LinkedIn como referencia para el header de
  reservas).
- Cuando algo "sigue sin verse bien" después de un fix ya verificado en
  código, sospechar caché de navegador antes que asumir que el fix está mal.
