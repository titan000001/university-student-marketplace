<?php

/**
 * UniMarket - Logout Handler
 *
 * Clears user session data, invalidates session cookie,
 * and redirects user back to the login page.
 */

require_once __DIR__ . '/../includes/session.php';

startApplicationSession();

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isValidCsrfToken($_POST["csrf_token"] ?? null)) {
    header("Location: index.php");
    exit();
}

// Clear all session variables
$_SESSION = [];

// Destroy session cookie if present
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session on server
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
