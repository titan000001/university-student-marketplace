<?php
/**
 * User Login Page
 * UniMarket - University Student Marketplace
 */

require_once __DIR__ . '/../../backend/config/database.php';

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect already authenticated users to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (!empty($email) && !empty($password)) {
        try {
            $database = new Database();
            $connection = $database->connect();

            $statement = $connection->prepare(
                'SELECT user_id, full_name, role, password_hash FROM users WHERE email = :email LIMIT 1'
            );
            $statement->execute(['email' => $email]);
            $user = $statement->fetch();

            $passwordIsValid = false;

            if ($user) {
                // Verify password against hash, or fallback to plain-text check
                if (password_verify($password, $user['password_hash'])) {
                    $passwordIsValid = true;
                } elseif (hash_equals($user['password_hash'], $password)) {
                    $passwordIsValid = true;
                }
            }

            if ($passwordIsValid) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                header('Location: dashboard.php');
                exit();
            }
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
        }
    }

    $error = 'Invalid email or password.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sign in to your UniMarket student account to access the campus marketplace.">
    <title>UniMarket - Student Login</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body class="login-page">

    <div class="login-wrapper">
        <div class="login-brand">
            <div class="login-brand-badge">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-xl"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            </div>
            <h1>UniMarket</h1>
            <p>University Student Marketplace</p>
        </div>

        <main class="login-container">
            <h2>Welcome Back</h2>
            <p class="login-subtitle">Sign in to access your student portal</p>

            <?php if (!empty($error)) : ?>
                <div class="error-message" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" novalidate>
                <div class="form-group">
                    <label for="email">University Email</label>
                    <div class="input-icon-group">
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="e.g. student@university.edu"
                            autocomplete="email"
                            required>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon-group">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M13 12H3"/></svg>
                    Sign In
                </button>
            </form>

            <div class="login-trust-seal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm" style="color: var(--success);"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                100% Secure Campus Verification
            </div>

            <div class="login-footer">
                <p>
                    Don't have an account?
                    <a href="index.php#register">Register here</a>
                </p>
            </div>
        </main>

        <div style="text-align: center;">
            <a href="index.php" class="back-home-link">← Back to Homepage</a>
        </div>
    </div>

</body>
</html>
