<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel</title>
<style>
body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
.container { background: #fff; padding: 20px 40px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; width: 100%; max-width: 400px; }
a { display: block; padding: 10px; background: #dc3545; color: #fff; border-radius: 4px; text-decoration: none; margin-top: 15px; }
</style>
</head>
<body>
<div class="container">
    <h2>¡Bienvenido!</h2>
    <p>Sesión iniciada como<br><strong><?php echo htmlspecialchars($_SESSION['email']); ?></strong></p>
    <a href="logout.php">Cerrar sesión</a>
</div>
</body>
</html>