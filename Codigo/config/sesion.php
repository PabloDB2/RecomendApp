<?php
// API KEY de TMDB
$api_key = '6bfc367c7d8bc2e83a9e4f5ced5a2bd4';

// Guardar la sesión en cookies durante X tiempo
$lifetime = 60 * 60 * 24 * 7; // 7 días
session_set_cookie_params([
    'lifetime' => $lifetime,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
?>