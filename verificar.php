<?php
session_start();
require 'conexion.php';

$email = $_SESSION['email_pendiente'] ?? '';
if (!$email) {
    header('Location: registro.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['intentos'] = ($_SESSION['intentos'] ?? 0) + 1;

    if ($_SESSION['intentos'] > 5) {
        $error = 'Demasiados intentos. Regístrate de nuevo para recibir otro código.';
    } else {
        $codigo = trim($_POST['codigo'] ?? '');

        $stmt = $pdo->prepare(
            "SELECT codigo_verificacion, codigo_expiracion > NOW() AS vigente
             FROM usuarios WHERE email = ?"
        );
        $stmt->execute([$email]);
        $u = $stmt->fetch();

        if ($u && $u['codigo_verificacion']
            && hash_equals($u['codigo_verificacion'], hash('sha256', $codigo))) {

            if ($u['vigente']) {
                $pdo->prepare(
                    "UPDATE usuarios
                     SET cuenta_activa = 1, codigo_verificacion = NULL, codigo_expiracion = NULL
                     WHERE email = ?"
                )->execute([$email]);

                unset($_SESSION['email_pendiente'], $_SESSION['intentos']);
                echo "<script>
                        alert('¡Cuenta verificada con éxito! Ya puedes iniciar sesión.');
                        window.location.href='index.php';
                      </script>";
                exit;
            }
            $error = 'El código ha expirado. Regístrate de nuevo.';
        } else {
            $error = 'Código incorrecto. Intenta nuevamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verificar Código 2FA</title>
<style>
body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
.container { background: #fff; padding: 20px 40px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; width: 100%; max-width: 400px; }
input[type="text"] { width: 100%; padding: 10px; margin: 15px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 24px; text-align: center; letter-spacing: 5px; font-weight: bold; }
button { width: 100%; padding: 10px; background: #28a745; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
button:hover { background: #218838; }
.error { color: #c00; margin: 10px 0; }
</style>
</head>
<body>
<div class="container">
    <h2>Verificación de Seguridad</h2>
    <p>Ingresa el código de 6 dígitos que enviamos a <strong><?php echo htmlspecialchars($email); ?></strong></p>
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="codigo" maxlength="6" required placeholder="------" autocomplete="off">
        <button type="submit">Verificar Cuenta</button>
    </form>
</div>
</body>
</html>