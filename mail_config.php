<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/lib/phpmailer/PHPMailer.php';
require_once __DIR__ . '/lib/phpmailer/SMTP.php';
require_once __DIR__ . '/lib/phpmailer/Exception.php';

function enviarCorreoConfirmacion($destinatario, $nombre, $token) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tu-correo@gmail.com';
        $mail->Password   = 'tu-contrasena-app';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('tu-correo@gmail.com', 'Taekwondo Poomsae');
        $mail->addAddress($destinatario, $nombre);

        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Confirma tu registro - Dojang ' . $nombre;

        $enlace = 'http://' . $_SERVER['HTTP_HOST'] . '/taek/confirmar_dojang.php?token=' . urlencode($token);

        $mail->isHTML(true);
        $mail->Body = "
        <div style='font-family:Arial,sans-serif;max-width:560px;margin:0 auto;padding:24px;border-radius:16px;background:#f5f5dc;'>
            <div style='text-align:center;padding:20px 0;'>
                <div style='font-size:40px;'>🥋</div>
                <h2 style='color:#2e7d32;margin:8px 0 4px;'>Taekwondo Poomsae</h3>
            </div>
            <div style='background:#fff;border-radius:14px;padding:28px 24px;'>
                <h3 style='margin:0 0 12px;color:#333;'>Hola <strong>$nombre</strong></h3>
                <p style='color:#555;line-height:1.6;margin:0 0 16px;'>
                    Gracias por registrar tu Dojang. Confirma tu cuenta haciendo clic en el botón:
                </p>
                <div style='text-align:center;margin:24px 0;'>
                    <a href='$enlace' style='display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#2e7d32,#4caf50);color:#fff;text-decoration:none;border-radius:12px;font-size:16px;font-weight:700;letter-spacing:0.5px;'>
                        Confirmar mi cuenta
                    </a>
                </div>
                <p style='color:#999;font-size:12px;margin:16px 0 0;'>
                    Si no puedes hacer clic, copia este enlace en tu navegador:<br>
                    <span style='color:#4caf50;word-break:break-all;'>$enlace</span>
                </p>
                <p style='color:#999;font-size:12px;margin:16px 0 0;'>
                    Este enlace expirará en 24 horas.
                </p>
            </div>
            <div style='text-align:center;padding:16px 0;color:#999;font-size:11px;'>
                &copy; " . date('Y') . " Taekwondo Poomsae Evaluaci&oacute;n
            </div>
        </div>";

        $mail->AltBody = "Hola $nombre, confirma tu registro copiando este enlace en tu navegador: $enlace";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
