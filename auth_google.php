<?php
session_start();
require 'google_client.php';

$client = crearClienteGoogle();

// Valor aleatorio que Google nos devuelve; protege contra CSRF
$_SESSION['oauth_state'] = bin2hex(random_bytes(16));
$client->setState($_SESSION['oauth_state']);

header('Location: ' . $client->createAuthUrl());
exit;