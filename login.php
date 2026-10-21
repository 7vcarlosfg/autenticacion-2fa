<?php
session_start();
require 'conexion.php';
require 'vendor/autoload.php';
$config = require 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare('SELECT id, email, password_hash, cuenta_activa FROM usuarios WHERE email = ?');
$stmt->execute([$email]);
$usuario = $stmt->fetch();

// Mismo mensaje si el correo no existe o la contraseña está mal
if (!$usuario || !$usuario['password_hash'] || !password_verify($password, $usuario['password_hash'])) {
    echo "<script>alert('Correo o contraseña incorrectos.'); window.location.href='index.php';</script>";
    exit;
}

if (!$usuario['cuenta_activa']) {
    echo "<script>alert('Tu cuenta aún no está verificada. Revisa tu correo.'); window.location.href='index.php';</script>";
    exit;
}

// ---- Segundo paso: la contraseña fue correcta, ahora se manda un código al correo ----
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
    $mail->addAddress($usuario['email']);
    $mail->isHTML(true);
    $mail->Subject = 'Código de acceso';
    $mail->Body    = "Tu código para iniciar sesión es: <b>$codigo</b>.<br>"
                   . "Expira en 10 minutos. Si no intentaste entrar, cambia tu contraseña.";
    $mail->AltBody = "Tu código para iniciar sesión es: $codigo. Expira en 10 minutos.";
    $mail->send();
} catch (Exception $e) {
    echo "<script>alert('No se pudo enviar el código. Intenta de nuevo.'); window.location.href='index.php';</script>";
    exit;
}

// Todavía NO hay sesión iniciada: solo se recuerda quién pasó el primer paso
session_regenerate_id(true);
$_SESSION['email_login']    = $usuario['email'];
$_SESSION['intentos_login'] = 0;

echo "<script>
        alert('Contraseña correcta. Te enviamos un código a tu correo para completar el acceso.');
        window.location.href='verificar_login.php';
      </script>";
exit;
