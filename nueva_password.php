<?php
session_start();
require 'conexion.php';

$email = $_SESSION['reset_email'] ?? '';
if (!$email) {
    header('Location: recuperar.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (strlen($pass) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($pass !== $pass2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $pdo->prepare('UPDATE usuarios SET password_hash = ? WHERE email = ?')
            ->execute([$hash, $email]);

        unset($_SESSION['reset_email']);
        echo "<script>
                alert('Contraseña actualizada. Ya puedes iniciar sesión.');
                window.location.href='index.php';
              </script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nueva contraseña</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>
<div class="container">
    <h2>Nueva contraseña</h2>
    <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
    <form method="POST">
        <input type="password" name="password" placeholder="Nueva contraseña (mínimo 8)" required>
        <input type="password" name="password2" placeholder="Repite la contraseña" required>
        <button type="submit">Guardar contraseña</button>
    </form>
</div>
</body>
</html>