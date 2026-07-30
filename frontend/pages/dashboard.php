<?php

/**
 * UniMarket - Protected Student Dashboard
 */

require_once __DIR__ . '/../includes/session.php';

startApplicationSession();

// Access Control - Guard Unauthenticated Users
if (!isset($_SESSION["email"])) {
    header("Location: login.php");
    exit();
}

$csrfToken = getCsrfToken();
$userEmail = htmlspecialchars($_SESSION["email"], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="UniMarket - Protected Student Dashboard">
    <title>UniMarket | Student Dashboard</title>

    <!-- Global Stylesheet -->
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

    <!-- Header -->
    <header class="site-header">
        <div class="container header-container">
            <div class="logo-area">
                <h1 class="logo"><a href="index.php">UniMarket</a></h1>
                <span class="tagline">University Student Marketplace</span>
            </div>
            <div class="header-actions">
                <form class="logout-form" method="POST" action="logout.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8"); ?>">
                    <button type="submit" class="btn-outline">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="main-nav" aria-label="Main Navigation">
        <div class="container">
            <button
                id="menu-toggle"
                class="menu-toggle"
                aria-label="Toggle Navigation"
                aria-expanded="false"
                aria-controls="primary-navigation">
                ☰
            </button>
            <ul id="primary-navigation">
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php" class="active" aria-current="page">Dashboard</a></li>
                <li><a href="index.php#categories">Marketplace</a></li>
                <li><a href="index.php#workflow">About</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-container">
            <div class="dashboard-card">
                <h1>Student Dashboard</h1>

                <p class="dashboard-welcome">Welcome to your UniMarket portal!</p>

                <div class="user-badge">
                    <span class="user-badge-label">Logged in as:</span>
                    <strong class="user-badge-email"><?php echo $userEmail; ?></strong>
                </div>

                <div class="dashboard-actions">
                    <form class="logout-form" method="POST" action="logout.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8"); ?>">
                        <button type="submit" class="btn-primary">Logout</button>
                    </form>
                    <a href="index.php" class="btn-outline btn-outline-primary">Return Home</a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <p>© 2026 UniMarket</p>
            <p>Designed for Software Development Project III</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="../js/app.js"></script>
</body>
</html>
