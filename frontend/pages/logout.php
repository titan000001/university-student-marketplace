<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Remove all session variables.
$_SESSION = [];

// Remove the session cookie when cookies are enabled.
if (ini_get('session.use_cookies')) {
    $parameters = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parameters['path'],
        $parameters['domain'],
        $parameters['secure'],
        $parameters['httponly']
    );
}

// Destroy the server-side session.
session_destroy();

header('Location: login.php');
exit();
