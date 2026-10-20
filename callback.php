<?php
session_start();
require 'conexion.php';
require 'google_client.php';

if (!isset($_GET['code'])) {
    die('Error en la autenticación. No se recibió el código de Google.');
}

// 1. Validar el parámetro state
if (!isset($_GET['state'], $_SESSION['oauth_state'])
    || !hash_equals($_SESSION['oauth_state'], $_GET['state'])) {
    die('Solicitud no válida (state incorrecto).');
}
unset($_SESSION['oauth_state']);

// 2. Intercambiar el código por el token
$client = crearClienteGoogle();
$token  = $client->fetchAccessTokenWithAuthCode($_GET['code']);

if (isset($token['error'])) {
    die('Error devuelto por Google: ' . htmlspecialchars($token['error']));
}
$client->setAccessToken($token);

// 3. Obtener el perfil
$info     = (new Google_Service_Oauth2($client))->userinfo->get();
$email    = $info->getEmail();
$googleId = $info->getId();

if (!$info->getVerifiedEmail()) {
    die('El correo de tu cuenta de Google no está verificado.');
}

// 4. Buscar al usuario por google_id y, si no, por correo
$stmt = $pdo->prepare('SELECT id, cuenta_activa FROM usuarios WHERE google_id = ?');
$stmt->execute([$googleId]);
$usuario = $stmt->fetch();

if (!$usuario) {
    $stmt = $pdo->prepare('SELECT id, cuenta_activa FROM usuarios WHERE email = ?');
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        if ($usuario['cuenta_activa']) {
            // Cuenta ya verificada: solo se vincula Google
            $pdo->prepare('UPDATE usuarios SET google_id = ? WHERE id = ?')
                ->execute([$googleId, $usuario['id']]);
        } else {
            // Cuenta sin verificar: se descarta la contraseña previa, porque
            // pudo haberla puesto otra persona con el correo de la víctima
            $pdo->prepare('UPDATE usuarios
                           SET google_id = ?, cuenta_activa = 1, password_hash = NULL,
                               codigo_verificacion = NULL, codigo_expiracion = NULL
                           WHERE id = ?')
                ->execute([$googleId, $usuario['id']]);
        }
    } else {
        $pdo->prepare('INSERT INTO usuarios (email, google_id, cuenta_activa) VALUES (?, ?, 1)')
            ->execute([$email, $googleId]);
        $usuario = ['id' => $pdo->lastInsertId()];
    }
}

// 5. Crear la sesión igual que en el login tradicional
session_regenerate_id(true);
$_SESSION['usuario_id'] = $usuario['id'];
$_SESSION['email']      = $email;

echo "<script>
        alert('Inicio de sesión exitoso con Google');
        window.location.href='dashboard.php';
      </script>";
exit;