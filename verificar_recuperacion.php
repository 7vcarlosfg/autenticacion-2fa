<?php
session_start();
require 'conexion.php';

$email = $_SESSION['email_recuperar'] ?? '';
if (!$email) {
    header('Location: recuperar.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['intentos_rec'] = ($_SESSION['intentos_rec'] ?? 0) + 1;

    if ($_SESSION['intentos_rec'] > 5) {
        $error = 'Demasiados intentos. Solicita un código nuevo.';
    } else {
        $codigo = trim($_POST['codigo'] ?? '');

        $stmt = $pdo->prepare('SELECT codigo_verificacion, codigo_expiracion > NOW() AS vigente
                               FROM usuarios WHERE email = ? AND cuenta_activa = 1');
        $stmt->execute([$email]);
        $u = $stmt->fetch();

        if ($u && $u['codigo_verificacion'] && $u['vigente']
            && hash_equals($u['codigo_verificacion'], hash('sha256', $codigo))) {

            // El código solo sirve una vez
            $pdo->prepare('UPDATE usuarios SET codigo_verificacion = NULL, codigo_expiracion = NULL WHERE email = ?')
                ->execute([$email]);

            $_SESSION['reset_email'] = $email;
            unset($_SESSION['email_recuperar'], $_SESSION['intentos_rec']);
            header('Location: nueva_password.php');
            exit;
        }
        $error = 'Código incorrecto o expirado.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verificar código</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>
<div class="container">
    <h2>Verificación de Seguridad</h2>
    <p>Ingresa el código de 6 dígitos enviado a <strong><?php echo htmlspecialchars($email); ?></strong></p>
    <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
    <form method="POST">
        <input class="codigo" type="text" name="codigo" maxlength="6" required placeholder="------" autocomplete="off">
        <button type="submit">Verificar</button>
    </form>
</div>
</body>
</html>