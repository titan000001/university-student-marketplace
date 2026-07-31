<?php
/**
 * User Logout Script
 * UniMarket - University Student Marketplace
 *
 * Safe session cleanup and cookie invalidation script.
 */

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = [];

// Expire and delete the session cookie if enabled
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

// Destroy server-side session instance
session_destroy();

// Redirect user to the login page
header('Location: login.php');
exit();
