<?php
session_start();

$error = "";

$validEmail = "student@university.edu";
$validPassword = "password123";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if ($email === $validEmail && $password === $validPassword) {

        $_SESSION["email"] = $email;

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
    <title>UniMarket - Login</title>

    <!-- Reuse your existing stylesheet -->
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body class="login-page">

    <div class="login-container">
        <h1>UniMarket</h1>
        <p>Student Marketplace Login</p>

        <form method="POST" action="">
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
