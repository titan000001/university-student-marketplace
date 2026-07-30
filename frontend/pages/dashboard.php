<?php
session_start();

if (!isset($_SESSION["email"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniMarket Dashboard</title>

    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="container">

    <h1>Dashboard</h1>

    <p>Welcome to UniMarket!</p>

    <p>
        Logged in as:
        <strong><?php echo htmlspecialchars($_SESSION["email"]); ?></strong>
    </p>

    <div class="dashboard-actions">
        <a href="logout.php" class="btn-primary">Logout</a>
    </div>

</div>

</body>
</html>