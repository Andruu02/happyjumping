<?php
require_once APP_ROOT . '/../vendor/phpmailer/src/PHPMailer.php';
require_once APP_ROOT . '/../app/config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;

class Mailer {

    private static function instancia() {
        $m = new PHPMailer();
        $m->isSMTP();
        $m->Host       = MAIL_HOST;
        $m->Port       = MAIL_PORT;
        $m->SMTPAuth   = true;
        $m->SMTPSecure = defined('MAIL_ENCRYPTION') ? MAIL_ENCRYPTION : 'tls';
        $m->Username   = MAIL_USERNAME;
        $m->Password   = MAIL_PASSWORD;
        $m->From       = MAIL_FROM;
        $m->FromName   = MAIL_NAME;
        $m->CharSet    = 'UTF-8';
        $m->isHTML(true);
        return $m;
    }

    // ── Código de verificación de cuenta nueva ────────────────────────────────
    public static function enviarCodigoVerificacion($correo, $nombre, $codigo) {
        $m = self::instancia();
        $m->addAddress($correo, $nombre);
        $m->Subject = 'Tu código de verificación — Happy Jumping Peru';
        $m->Body = '<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"></head>
<body style="font-family:Poppins,Arial,sans-serif;background:#f4f8ff;margin:0;padding:20px;">
  <div style="max-width:520px;margin:0 auto;background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.10);">
    <div style="background:linear-gradient(90deg,#ff3c8d,#7F00FF);padding:32px;text-align:center;">
      <h1 style="color:#fff;margin:0;font-size:1.7rem;">Happy Jumping Peru 🎈</h1>
      <p style="color:rgba(255,255,255,.85);margin:6px 0 0;">Verificación de cuenta</p>
    </div>
    <div style="padding:32px;">
      <p style="font-size:1.05rem;color:#333;">Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</p>
      <p style="color:#555;line-height:1.6;">Usa el siguiente código para verificar tu correo. Válido por <strong>15 minutos</strong>.</p>
      <div style="background:#f3e5ff;border-radius:14px;padding:28px;text-align:center;margin:24px 0;">
        <p style="margin:0 0 8px;color:#7F00FF;font-weight:700;font-size:.85rem;letter-spacing:2px;text-transform:uppercase;">Código de verificación</p>
        <div style="font-size:3rem;font-weight:900;letter-spacing:10px;color:#7F00FF;font-family:monospace;">' . $codigo . '</div>
      </div>
      <p style="color:#888;font-size:.85rem;">Si no creaste esta cuenta, ignora este correo.</p>
    </div>
    <div style="background:#f4f8ff;padding:16px;text-align:center;border-top:1px solid #eee;">
      <p style="margin:0;color:#aaa;font-size:.8rem;">Happy Jumping Peru — happyjumpingperu.com</p>
    </div>
  </div>
</body></html>';
        return $m->send();
    }

    // ── Código de recuperación de contraseña ──────────────────────────────────
    public static function enviarCodigoRecuperacion($correo, $nombre, $codigo) {
        $m = self::instancia();
        $m->addAddress($correo, $nombre);
        $m->Subject = 'Recupera tu contraseña — Happy Jumping Peru';
        $m->Body = '<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"></head>
<body style="font-family:Poppins,Arial,sans-serif;background:#f4f8ff;margin:0;padding:20px;">
  <div style="max-width:520px;margin:0 auto;background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.10);">
    <div style="background:linear-gradient(90deg,#7F00FF,#ff3c8d);padding:32px;text-align:center;">
      <h1 style="color:#fff;margin:0;font-size:1.7rem;">Happy Jumping Peru 🔑</h1>
      <p style="color:rgba(255,255,255,.85);margin:6px 0 0;">Recuperación de contraseña</p>
    </div>
    <div style="padding:32px;">
      <p style="font-size:1.05rem;color:#333;">Hola' . ($nombre ? ' <strong>' . htmlspecialchars($nombre) . '</strong>' : '') . ',</p>
      <p style="color:#555;line-height:1.6;">Recibimos una solicitud para restablecer tu contraseña. Usa el siguiente código para continuar. Válido por <strong>15 minutos</strong>.</p>
      <div style="background:#f3e5ff;border-radius:14px;padding:28px;text-align:center;margin:24px 0;">
        <p style="margin:0 0 8px;color:#7F00FF;font-weight:700;font-size:.85rem;letter-spacing:2px;text-transform:uppercase;">Código de recuperación</p>
        <div style="font-size:3rem;font-weight:900;letter-spacing:10px;color:#7F00FF;font-family:monospace;">' . $codigo . '</div>
      </div>
      <p style="color:#888;font-size:.85rem;">Si no pediste este cambio, ignora este correo — tu contraseña actual sigue funcionando normal.</p>
    </div>
    <div style="background:#f4f8ff;padding:16px;text-align:center;border-top:1px solid #eee;">
      <p style="margin:0;color:#aaa;font-size:.8rem;">Happy Jumping Peru — happyjumpingperu.com</p>
    </div>
  </div>
</body></html>';
        return $m->send();
    }

    // ── Confirmación de reserva ───────────────────────────────────────────────
    public static function enviarConfirmacion($reserva, $correo_cliente, $nombre_cliente) {
        $m = self::instancia();
        $m->addAddress($correo_cliente, $nombre_cliente);
        $m->Subject = '¡Tu reserva en Happy Jumping Peru fue confirmada! 🎉';
        $fecha = date('d/m/Y', strtotime($reserva->fecha));
        $hora  = substr($reserva->hora_inicio, 0, 5);
        $m->Body = '<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"></head>
<body style="font-family:Poppins,Arial,sans-serif;background:#f4f8ff;margin:0;padding:20px;">
  <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1);">
    <div style="background:linear-gradient(90deg,#ff3c8d,#00c6ff);padding:30px;text-align:center;">
      <h1 style="color:white;margin:0;font-size:1.8rem;">¡Reserva Confirmada! 🎉</h1>
      <p style="color:white;margin:8px 0 0;opacity:.9;">Happy Jumping Peru</p>
    </div>
    <div style="padding:30px;">
      <p style="font-size:1.1rem;color:#333;">Hola <strong>' . htmlspecialchars($nombre_cliente) . '</strong>,</p>
      <p style="color:#555;">Tu reserva ha sido <strong style="color:#00a854;">CONFIRMADA</strong>.</p>
      <div style="background:#f9f0ff;border-left:4px solid #7b2ff7;border-radius:8px;padding:20px;margin:20px 0;">
        <table style="width:100%;border-collapse:collapse;">
          <tr><td style="padding:8px 0;color:#888;width:40%;">🎂 Cumpleañero</td><td style="padding:8px 0;font-weight:600;color:#333;">' . htmlspecialchars($reserva->nombre_cumpleanero) . '</td></tr>
          <tr><td style="padding:8px 0;color:#888;">📦 Paquete</td><td style="padding:8px 0;font-weight:600;color:#333;">' . htmlspecialchars($reserva->nombre_paquete) . '</td></tr>
          <tr><td style="padding:8px 0;color:#888;">📅 Fecha</td><td style="padding:8px 0;font-weight:600;color:#333;">' . $fecha . '</td></tr>
          <tr><td style="padding:8px 0;color:#888;">🕐 Hora</td><td style="padding:8px 0;font-weight:600;color:#333;">' . $hora . '</td></tr>
          <tr><td style="padding:8px 0;color:#888;">👥 Personas</td><td style="padding:8px 0;font-weight:600;color:#333;">' . $reserva->cantidad_personas . '</td></tr>
          <tr><td style="padding:8px 0;color:#888;">💰 Monto</td><td style="padding:8px 0;font-weight:600;color:#7b2ff7;">S/ ' . number_format($reserva->monto, 2) . '</td></tr>
        </table>
      </div>
      <div style="background:#fff3cd;border-radius:8px;padding:15px;margin:20px 0;">
        <p style="margin:0;color:#856404;font-size:.9rem;">⚠️ <strong>Recuerda:</strong> Llega 15 minutos antes. No traer bebidas embotelladas.</p>
      </div>
      <p style="color:#555;">¿Dudas? <a href="mailto:happyjumpingperu01@gmail.com" style="color:#7b2ff7;">happyjumpingperu01@gmail.com</a></p>
      <p style="color:#555;">¡Nos vemos pronto! 🎈</p>
    </div>
    <div style="background:#f4f8ff;padding:20px;text-align:center;border-top:1px solid #eee;">
      <p style="margin:0;color:#aaa;font-size:.85rem;">Happy Jumping Peru — happyjumpingperu.com</p>
    </div>
  </div>
</body></html>';
        return $m->send();
    }

    // ── Plantilla 1: Recordatorio de reserva próxima ─────────────────────────
    public static function enviarRecordatorioReserva($correo, $nombre, $datos_reserva) {
        $m = self::instancia();
        $m->addAddress($correo, $nombre);
        $m->Subject = '🎂 Tu fiesta en Happy Jumping es muy pronto — ¡Prepárate!';
        $fecha = date('d/m/Y', strtotime($datos_reserva->fecha));
        $hora  = substr($datos_reserva->hora_inicio, 0, 5);
        $m->Body = '<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"></head>
<body style="font-family:Poppins,Arial,sans-serif;background:#f4f8ff;margin:0;padding:20px;">
  <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1);">
    <div style="background:linear-gradient(90deg,#FF6B6B,#FF8E53);padding:30px;text-align:center;">
      <div style="font-size:3rem;">🎂</div>
      <h1 style="color:white;margin:8px 0 0;font-size:1.7rem;">¡Tu fiesta se acerca!</h1>
      <p style="color:rgba(255,255,255,.9);margin:6px 0 0;">Happy Jumping Peru</p>
    </div>
    <div style="padding:30px;">
      <p style="font-size:1.05rem;color:#333;">Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</p>
      <p style="color:#555;line-height:1.6;">Te recordamos que tienes una fiesta reservada con nosotros. ¡Estamos listos para que sea un día increíble! 🎉</p>
      <div style="background:#fff3e0;border-left:4px solid #FF8E53;border-radius:0 12px 12px 0;padding:20px;margin:20px 0;">
        <table style="width:100%;border-collapse:collapse;">
          <tr><td style="padding:7px 0;color:#888;width:45%;">🎂 Cumpleañero</td><td style="padding:7px 0;font-weight:600;color:#333;">' . htmlspecialchars($datos_reserva->nombre_cumpleanero) . '</td></tr>
          <tr><td style="padding:7px 0;color:#888;">📦 Paquete</td><td style="padding:7px 0;font-weight:600;color:#333;">' . htmlspecialchars($datos_reserva->nombre_paquete) . '</td></tr>
          <tr><td style="padding:7px 0;color:#888;">📅 Fecha</td><td style="padding:7px 0;font-weight:700;color:#FF6B6B;font-size:1.1rem;">' . $fecha . '</td></tr>
          <tr><td style="padding:7px 0;color:#888;">🕐 Hora</td><td style="padding:7px 0;font-weight:700;color:#FF6B6B;font-size:1.1rem;">' . $hora . '</td></tr>
        </table>
      </div>
      <div style="background:#e8f5e9;border-radius:12px;padding:16px;margin:20px 0;">
        <p style="margin:0 0 8px;font-weight:600;color:#2e7d32;">✅ Recomendaciones antes de llegar:</p>
        <ul style="margin:0;padding-left:20px;color:#555;font-size:.9rem;line-height:1.8;">
          <li>Llega <strong>15 minutos antes</strong> de tu horario.</li>
          <li>No ingresar bebidas embotelladas.</li>
          <li>Trae a los invitados con ropa cómoda.</li>
        </ul>
      </div>
      <p style="color:#555;">¿Necesitas algo? <a href="mailto:happyjumpingperu01@gmail.com" style="color:#FF6B6B;font-weight:600;">happyjumpingperu01@gmail.com</a></p>
      <p style="color:#555;">¡Te esperamos! 🎈</p>
    </div>
    <div style="background:#f4f8ff;padding:16px;text-align:center;border-top:1px solid #eee;">
      <p style="margin:0;color:#aaa;font-size:.8rem;">Happy Jumping Peru — happyjumpingperu.com</p>
    </div>
  </div>
</body></html>';
        return $m->send();
    }

    // ── Plantilla 2: Promoción especial ──────────────────────────────────────
    public static function enviarPromoEspecial($correo, $nombre, $detalle_promo) {
        $m = self::instancia();
        $m->addAddress($correo, $nombre);
        $m->Subject = '🎉 ¡Oferta exclusiva para ti en Happy Jumping Peru!';
        $m->Body = '<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"></head>
<body style="font-family:Poppins,Arial,sans-serif;background:#f4f8ff;margin:0;padding:20px;">
  <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1);">
    <div style="background:linear-gradient(90deg,#7F00FF,#E100FF);padding:30px;text-align:center;">
      <div style="font-size:3rem;">🎉</div>
      <h1 style="color:white;margin:8px 0 0;font-size:1.7rem;">¡Oferta especial!</h1>
      <p style="color:rgba(255,255,255,.9);margin:6px 0 0;">Solo por tiempo limitado</p>
    </div>
    <div style="padding:30px;">
      <p style="font-size:1.05rem;color:#333;">Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</p>
      <p style="color:#555;line-height:1.6;">Tenemos una oferta especial preparada para ti. ¡No dejes pasar esta oportunidad de celebrar con los mejores! 🥳</p>
      <div style="background:linear-gradient(135deg,#f3e5ff,#e8f0ff);border-radius:16px;padding:28px;text-align:center;margin:24px 0;border:2px dashed #7F00FF;">
        <p style="margin:0 0 8px;color:#7F00FF;font-weight:700;font-size:.85rem;letter-spacing:2px;text-transform:uppercase;">Promoción</p>
        <p style="font-size:1.2rem;font-weight:700;color:#333;margin:0;line-height:1.6;">' . nl2br(htmlspecialchars($detalle_promo)) . '</p>
      </div>
      <div style="text-align:center;margin:24px 0;">
        <a href="https://happyjumpingperu.com" style="background:#7F00FF;color:white;text-decoration:none;padding:14px 36px;border-radius:30px;font-weight:700;font-size:1rem;display:inline-block;">¡Reserva ahora! 🎈</a>
      </div>
      <p style="color:#888;font-size:.85rem;text-align:center;">*Sujeto a disponibilidad. No acumulable con otras promociones.</p>
    </div>
    <div style="background:#f4f8ff;padding:16px;text-align:center;border-top:1px solid #eee;">
      <p style="margin:0;color:#aaa;font-size:.8rem;">Happy Jumping Peru — happyjumpingperu.com</p>
    </div>
  </div>
</body></html>';
        return $m->send();
    }

    // ── Plantilla 4: Puntos acumulados listos para canjear ───────────────────
    /**
     * Umbral mínimo de puntos para poder canjear. Debajo de esto el correo
     * anima a seguir jugando; en o por encima, invita a canjear en la app.
     */
    const PUNTOS_MINIMOS_CANJE = 200;

    public static function enviarRecordatorioPuntos($correo, $nombre, $puntos) {
        $puntos = intval($puntos);
        $m = self::instancia();
        $m->addAddress($correo, $nombre);

        if ($puntos < self::PUNTOS_MINIMOS_CANJE) {
            $faltan = self::PUNTOS_MINIMOS_CANJE - $puntos;
            $m->Subject = '🎮 ¡Te faltan solo ' . $faltan . ' puntos para poder canjear!';
            $m->Body = '<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"></head>
<body style="font-family:Poppins,Arial,sans-serif;background:#f4f8ff;margin:0;padding:20px;">
  <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1);">
    <div style="background:linear-gradient(90deg,#7b2ff7,#ff3c8d);padding:30px;text-align:center;">
      <div style="font-size:3rem;">🎮</div>
      <h1 style="color:white;margin:8px 0 0;font-size:1.7rem;">¡Ya casi llegas a tus primeros premios!</h1>
      <p style="color:rgba(255,255,255,.9);margin:6px 0 0;">Happy Jumping Peru</p>
    </div>
    <div style="padding:30px;">
      <p style="font-size:1.05rem;color:#333;">Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</p>
      <p style="color:#555;line-height:1.6;">Vas acumulando puntos jugando en la <strong>app de Happy Jumping</strong>, pero todavía no llegas al mínimo para canjearlos por descuentos.</p>
      <div style="background:linear-gradient(135deg,#f4ecff,#fde8f2);border-radius:16px;padding:28px;text-align:center;margin:24px 0;border:2px solid #e9d5ff;">
        <p style="margin:0 0 8px;color:#7b2ff7;font-weight:700;font-size:.85rem;letter-spacing:2px;text-transform:uppercase;">Tus puntos acumulados</p>
        <div style="font-size:4rem;font-weight:900;color:#7b2ff7;line-height:1;">' . $puntos . '</div>
        <div style="font-size:1rem;color:#888;margin-top:4px;">de ' . self::PUNTOS_MINIMOS_CANJE . ' puntos necesarios</div>
      </div>
      <p style="color:#555;">Sigue jugando en la app para sumar los <strong>' . $faltan . ' puntos</strong> que te faltan y desbloquear tus descuentos. ¡Vamos que ya casi! 🚀</p>
      <div style="text-align:center;margin:24px 0;">
        <a href="https://happyjumpingperu.com" style="background:#7b2ff7;color:white;text-decoration:none;padding:14px 36px;border-radius:30px;font-weight:700;font-size:1rem;display:inline-block;">¡Seguir jugando! 🎮</a>
      </div>
    </div>
    <div style="background:#f4f8ff;padding:16px;text-align:center;border-top:1px solid #eee;">
      <p style="margin:0;color:#aaa;font-size:.8rem;">Happy Jumping Peru — happyjumpingperu.com</p>
    </div>
  </div>
</body></html>';
        } else {
            $m->Subject = '🏆 ¡Tienes ' . $puntos . ' puntos listos para canjear en Happy Jumping Peru!';
            $m->Body = '<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"></head>
<body style="font-family:Poppins,Arial,sans-serif;background:#f4f8ff;margin:0;padding:20px;">
  <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1);">
    <div style="background:linear-gradient(90deg,#f7971e,#FFD200);padding:30px;text-align:center;">
      <div style="font-size:3rem;">🏆</div>
      <h1 style="color:white;margin:8px 0 0;font-size:1.7rem;">¡Tus puntos te esperan!</h1>
      <p style="color:rgba(255,255,255,.9);margin:6px 0 0;">Happy Jumping Peru</p>
    </div>
    <div style="padding:30px;">
      <p style="font-size:1.05rem;color:#333;">Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</p>
      <p style="color:#555;line-height:1.6;">Gracias por confiar en nosotros. Acumulaste puntos jugando en la app y ya puedes canjearlos por descuentos increíbles.</p>
      <div style="background:linear-gradient(135deg,#fff8e1,#fff3cd);border-radius:16px;padding:28px;text-align:center;margin:24px 0;border:2px solid #FFD200;">
        <p style="margin:0 0 8px;color:#f7971e;font-weight:700;font-size:.85rem;letter-spacing:2px;text-transform:uppercase;">Tus puntos acumulados</p>
        <div style="font-size:4rem;font-weight:900;color:#f7971e;line-height:1;">' . $puntos . '</div>
        <div style="font-size:1rem;color:#888;margin-top:4px;">puntos disponibles</div>
      </div>
      <p style="color:#555;">Ingresa a la <strong>app de Happy Jumping</strong> para canjear tus puntos y obtener descuentos en tu próxima reserva. 🎉</p>
      <div style="text-align:center;margin:24px 0;">
        <a href="https://happyjumpingperu.com" style="background:#f7971e;color:white;text-decoration:none;padding:14px 36px;border-radius:30px;font-weight:700;font-size:1rem;display:inline-block;">¡Canjear mis puntos! ⭐</a>
      </div>
    </div>
    <div style="background:#f4f8ff;padding:16px;text-align:center;border-top:1px solid #eee;">
      <p style="margin:0;color:#aaa;font-size:.8rem;">Happy Jumping Peru — happyjumpingperu.com</p>
    </div>
  </div>
</body></html>';
        }

        return $m->send();
    }

    // ── Plantilla 5: Mensaje personalizado del admin ──────────────────────────
    public static function enviarMensajePersonalizado($correo, $nombre, $asunto, $cuerpo_mensaje) {
        $m = self::instancia();
        $m->addAddress($correo, $nombre);
        $m->Subject = $asunto;
        $m->Body = '<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"></head>
<body style="font-family:Poppins,Arial,sans-serif;background:#f4f8ff;margin:0;padding:20px;">
  <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1);">
    <div style="background:linear-gradient(90deg,#ff3c8d,#7F00FF);padding:30px;text-align:center;">
      <h1 style="color:white;margin:0;font-size:1.7rem;">Happy Jumping Peru 🎈</h1>
      <p style="color:rgba(255,255,255,.9);margin:8px 0 0;font-size:1rem;">' . htmlspecialchars($asunto) . '</p>
    </div>
    <div style="padding:30px;">
      <p style="font-size:1.05rem;color:#333;">Hola <strong>' . htmlspecialchars($nombre) . '</strong>,</p>
      <div style="color:#444;line-height:1.8;font-size:1rem;margin:16px 0;">' . nl2br(htmlspecialchars($cuerpo_mensaje)) . '</div>
      <hr style="border:none;border-top:1px solid #eee;margin:24px 0;">
      <p style="color:#555;font-size:.9rem;">¿Tienes alguna consulta? Escríbenos a <a href="mailto:happyjumpingperu01@gmail.com" style="color:#7F00FF;font-weight:600;">happyjumpingperu01@gmail.com</a></p>
      <p style="color:#555;font-size:.9rem;">¡Gracias por ser parte de la familia Happy Jumping! 🎈</p>
    </div>
    <div style="background:#f4f8ff;padding:16px;text-align:center;border-top:1px solid #eee;">
      <p style="margin:0;color:#aaa;font-size:.8rem;">Happy Jumping Peru — happyjumpingperu.com</p>
    </div>
  </div>
</body></html>';
        return $m->send();
    }
}