<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$fullName = htmlspecialchars((string) $_SESSION['full_name'], ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniMarket Dashboard</title>

    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<div class="container">

    <h1>Dashboard</h1>

    <p>Welcome to UniMarket, <strong><?php echo $fullName; ?></strong>!</p>

    <p>
        Logged in as:
        <strong><?php echo $fullName; ?></strong>
    </p>

    <div class="dashboard-actions">
        <a href="logout.php" class="btn-primary">Logout</a>
    </div>

</div>

</body>
</html>
