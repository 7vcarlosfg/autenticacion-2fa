<?php
session_start();
require 'conexion.php';
require 'vendor/autoload.php';
$config = require 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Escribe un correo válido.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND cuenta_activa = 1');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            $codigo      = random_int(100000, 999999);
            $codigo_hash = hash('sha256', (string)$codigo);

            $pdo->prepare('UPDATE usuarios
                           SET codigo_verificacion = ?, codigo_expiracion = DATE_ADD(NOW(), INTERVAL 10 MINUTE)
                           WHERE id = ?')
                ->execute([$codigo_hash, $usuario['id']]);

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = $config['smtp_user'];
                $mail->Password   = $config['smtp_pass'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom($config['smtp_user'], 'Sistema 2FA Seguridad');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Recuperación de contraseña';
                $mail->Body    = "Tu código para restablecer la contraseña es: <b>$codigo</b>.<br>"
                               . "Expira en 10 minutos. Si no lo solicitaste, ignora este mensaje.";
                $mail->AltBody = "Tu código para restablecer la contraseña es: $codigo. Expira en 10 minutos.";
                $mail->send();
            } catch (Exception $e) {
                $error = 'No se pudo enviar el correo: ' . $mail->ErrorInfo;
            }
        }

        // Mismo resultado exista o no el correo, para no revelar qué cuentas existen
        if (!$error) {
            $_SESSION['email_recuperar'] = $email;
            $_SESSION['intentos_rec'] = 0;
            echo "<script>
                    alert('Si el correo está registrado, recibirás un código.');
                    window.location.href='verificar_recuperacion.php';
                  </script>";
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar contraseña</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>
<div class="container">
    <h2>Recuperar contraseña</h2>
    <p>Escribe tu correo y te enviaremos un código.</p>
    <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Correo electrónico" required>
        <button type="submit">Enviar código</button>
    </form>
    <p><a href="index.php">Volver al login</a></p>
</div>
</body>
</html>