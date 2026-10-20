<?php
require_once 'vendor/autoload.php';

function crearClienteGoogle(): Google_Client {
    $config = require 'config.php';

    $client = new Google_Client();
    $client->setClientId($config['google_client_id']);
    $client->setClientSecret($config['google_client_secret']);
    $client->setRedirectUri($config['google_redirect_uri']);
    $client->addScope('email');
    $client->addScope('profile');
    return $client;
}