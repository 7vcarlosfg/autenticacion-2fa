<?php
session_start();
require 'conexion.php';
require 'vendor/autoload.php';
$config = require 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        echo "<script>alert('Escribe un correo válido y una contraseña de al menos 8 caracteres.'); window.history.back();</script>";
        exit;
    }

    // 1. Contraseña con bcrypt
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // 2. Código de 6 dígitos seguro; en la BD solo se guarda su hash
    $codigo      = random_int(100000, 999999);
    $codigo_hash = hash('sha256', (string)$codigo);

    try {
        $stmt = $pdo->prepare("SELECT id, cuenta_activa FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && $usuario['cuenta_activa']) {
            echo "<script>alert('Este correo ya está registrado.'); window.history.back();</script>";
            exit;
        }

        // 3. Expiración calculada con el reloj de MySQL (10 minutos)
        if ($usuario) {
            // Cuenta creada pero nunca verificada: se renueva contraseña y código
            $sql = "UPDATE usuarios
                    SET password_hash = ?, codigo_verificacion = ?,
                        codigo_expiracion = DATE_ADD(NOW(), INTERVAL 10 MINUTE)
                    WHERE email = ?";
            $params = [$password_hash, $codigo_hash, $email];
        } else {
            $sql = "INSERT INTO usuarios (password_hash, codigo_verificacion, codigo_expiracion, cuenta_activa, email)
                    VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE), 0, ?)";
            $params = [$password_hash, $codigo_hash, $email];
        }
        $pdo->prepare($sql)->execute($params);

        // 4. Envío SMTP con PHPMailer
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
            $mail->Subject = 'Código de verificación';
            $mail->Body    = "Hola, tu código de verificación es: <b>$codigo</b>.<br>"
                           . "Este código expirará en 10 minutos. Si no lo solicitaste, ignora este mensaje.";
            $mail->AltBody = "Tu código de verificación es: $codigo. Expira en 10 minutos.";

            $mail->send();

            // El correo pendiente va en la sesión, no en la URL
            $_SESSION['email_pendiente'] = $email;
            $_SESSION['intentos'] = 0;

            echo "<script>
                    alert('Registro exitoso. Revisa tu correo electrónico para obtener el código.');
                    window.location.href='verificar.php';
                  </script>";
        } catch (Exception $e) {
            echo "Error al enviar el correo: " . htmlspecialchars($mail->ErrorInfo);
        }
    } catch (PDOException $e) {
        echo "Error en la base de datos: " . htmlspecialchars($e->getMessage());
    }
}
?>