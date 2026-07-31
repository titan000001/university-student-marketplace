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
    <title>UniMarket - Login</title>

    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body class="login-page">

    <div class="login-container">
        <h1>UniMarket</h1>
        <p>Student Marketplace Login</p>

        <?php if (!empty($error)) : ?>
            <p class="error-message"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">University Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your university email"
                    required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required>
            </div>

            <button type="submit" class="btn-primary">Login</button>
        </form>

        <p>
            Don't have an account?
            <a href="index.php#register">Register here</a>
        </p>
    </div>

</body>
</html>

