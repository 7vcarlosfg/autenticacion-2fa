<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Seguro - 2FA</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background-color: #fff; padding: 20px 40px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        input[type="email"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        button:hover { background-color: #218838; }
        .google-btn { background-color: #4285F4; margin-top: 20px; }
        .google-btn:hover { background-color: #357ae8; }
        .toggle-link { color: #007bff; cursor: pointer; margin-top: 15px; display: inline-block; font-size: 14px; text-decoration: underline; }
    </style>
</head>
<body>

<div class="container" id="login-container">
    <h2>Iniciar Sesión</h2>
    <!-- Flujo A: Acceso local -->
    <form action="login.php" method="POST">
        <input type="email" name="email" placeholder="Correo electrónico" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Ingresar</button>
    </form>

    <div style="margin: 15px 0;">O</div>

    <!-- Flujo B: Acceso Google -->
    <button class="google-btn" onclick="window.location.href='auth_google.php'">
        Iniciar sesión con Google
    </button>

    <a href="recuperar.php" class="toggle-link">¿Olvidaste tu contraseña?</a><br>
    <span class="toggle-link" onclick="toggleForms()">¿No tienes cuenta? Regístrate aquí</span>
</div>

<div class="container" id="register-container" style="display: none;">
    <h2>Registro</h2>
    <form action="registro.php" method="POST">
        <input type="email" name="email" placeholder="Correo electrónico" required>
        <input type="password" name="password" placeholder="Contraseña (mínimo 8)" minlength="8" required>
        <button type="submit">Registrarse</button>
    </form>
    <span class="toggle-link" onclick="toggleForms()">¿Ya tienes cuenta? Inicia sesión</span>
</div>

<script>
    function toggleForms() {
        var login = document.getElementById('login-container');
        var register = document.getElementById('register-container');
        if (login.style.display === 'none') {
            login.style.display = 'block';
            register.style.display = 'none';
        } else {
            login.style.display = 'none';
            register.style.display = 'block';
        }
    }
</script>

</body>
</html>