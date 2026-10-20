<?php
session_start();
require 'conexion.php';

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

session_regenerate_id(true);
$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['email']      = $usuario['email'];

echo "<script>
        alert('¡Acceso concedido! Inicio de sesión tradicional exitoso.');
        window.location.href='dashboard.php';
      </script>";
exit;