<?php

/**
 * UniMarket - Login Page & Mock Authentication Handler
 */

require_once __DIR__ . '/../includes/session.php';

startApplicationSession();

// Redirect logged-in user straight to dashboard
if (isset($_SESSION["email"])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$validEmail = "student@university.edu";
$validPassword = "password123";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
    $password = isset($_POST["password"]) ? $_POST["password"] : "";

    if ($email === $validEmail && $password === $validPassword) {
        session_regenerate_id(true);
        $_SESSION["email"] = $email;
        getCsrfToken();
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="UniMarket - Secure Student Login">
    <title>UniMarket | Student Login</title>

    <!-- Project Stylesheets -->
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>

    <main class="login-page">
        <div class="login-container">
            <h1>UniMarket</h1>
            <p>Student Marketplace Login</p>

            <?php if (!empty($error)) : ?>
                <div class="error-message" role="alert">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">University Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="e.g. student@university.edu"
                        autocomplete="email"
                        required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required>
                </div>

                <button type="submit" class="btn-primary">Login</button>
            </form>

            <div class="login-footer-link">
                Don't have an account?
                <a href="index.php#register">Register here</a>
            </div>
        </div>
    </main>

</body>
</html>
