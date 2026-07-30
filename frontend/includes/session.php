<?php

declare(strict_types=1);

/**
 * Starts the application session with secure cookie defaults.
 */
function startApplicationSession(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    $isSecureConnection = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['SERVER_PORT'] ?? '') === '443';

    session_set_cookie_params([
        'httponly' => true,
        'lifetime' => 0,
        'path' => '/',
        'samesite' => 'Lax',
        'secure' => $isSecureConnection,
    ]);

    session_start();
}

/**
 * Returns the session's CSRF token, generating it when necessary.
 */
function getCsrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Checks a submitted CSRF token against the current session token.
 */
function isValidCsrfToken(mixed $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
