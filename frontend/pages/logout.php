<?php
/**
 * User Logout Script
 * UniMarket - University Student Marketplace
 */

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Remove all session variables
$_SESSION = [];

// Expire and remove session cookie if active
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy server-side session
session_destroy();

// Redirect to login page
header('Location: login.php');
exit();

