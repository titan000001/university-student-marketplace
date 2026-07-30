<?php

/**
 * UniMarket - University Student Marketplace Homepage
 */

require_once __DIR__ . '/../includes/session.php';

startApplicationSession();

$isLoggedIn = isset($_SESSION["email"]);

$csrfToken = $isLoggedIn ? getCsrfToken() : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="UniMarket - A closed, campus-exclusive peer-to-peer marketplace for university students.">
    <title>UniMarket | University Student Marketplace</title>

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
                <?php if ($isLoggedIn) : ?>
                    <a href="dashboard.php" class="btn-outline">Dashboard</a>
                    <form class="logout-form" method="POST" action="logout.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8"); ?>">
                        <button type="submit" class="btn-primary">Logout</button>
                    </form>
                <?php else : ?>
                    <a href="login.php" class="btn-outline">Sign In</a>
                    <a href="#register" class="btn-primary">Post Item</a>
                <?php endif; ?>
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
                <li><a href="index.php" class="active" aria-current="page">Home</a></li>
                <li><a href="#categories">Marketplace</a></li>
                <li><a href="#categories">Categories</a></li>
                <li><a href="#workflow">About</a></li>
                <li><a href="#register">Contact</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Banner -->
    <section class="hero-section">
        <div class="container hero-content">
            <h2>Buy, Sell & Trade with Students</h2>
            <p>
                UniMarket is a campus-exclusive marketplace where verified students
                can safely buy and sell textbooks, electronics, dorm essentials,
                and more.
            </p>
            <div class="hero-buttons">
                <a href="#categories" class="btn-primary">Explore Marketplace</a>
                <a href="#register" class="btn-outline">Post Your Item</a>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">

            <!-- Featured Categories -->
            <section id="categories" class="categories-section">
                <h2>Featured Categories</h2>
                <div
                    id="categories-container"
                    class="categories-grid">
                </div>
            </section>

            <!-- Marketplace Statistics -->
            <section id="statistics" class="statistics-section">
                <h2>Marketplace Statistics</h2>
                <div
                    id="statistics-container"
                    class="statistics-grid">
                </div>
            </section>

            <!-- Student Registration -->
            <section id="register" class="registration-section">
                <h2>Student Registration</h2>

                <form id="registration-form" class="registration-form" action="#" method="POST">
                    <div class="form-group">
                        <label for="full-name">Full Name</label>
                        <input
                            type="text"
                            id="full-name"
                            name="full-name"
                            placeholder="Enter your full name"
                            autocomplete="name"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="email">University Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="student@university.edu"
                            autocomplete="email"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="student-id">Student ID</label>
                        <input
                            type="text"
                            id="student-id"
                            name="student-id"
                            placeholder="e.g. 2026-10492"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="department">Department</label>
                        <input
                            type="text"
                            id="department"
                            name="department"
                            placeholder="e.g. Computer Science"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="At least 6 characters"
                            autocomplete="new-password"
                            required>
                    </div>

                    <button type="submit" class="btn-primary">
                        Register
                    </button>
                    <p id="form-message" role="status" aria-live="polite"></p>
                </form>
            </section>

            <!-- How It Works -->
            <section id="workflow" class="workflow-section">
                <h2>How UniMarket Works</h2>
                <div class="workflow-grid">
                    <article class="workflow-card">
                        <h3>1. Register</h3>
                        <p>Create your verified student account.</p>
                    </article>
                    <article class="workflow-card">
                        <h3>2. Browse</h3>
                        <p>Explore products listed by other students.</p>
                    </article>
                    <article class="workflow-card">
                        <h3>3. Chat</h3>
                        <p>Contact the seller before making a reservation.</p>
                    </article>
                    <article class="workflow-card">
                        <h3>4. Reserve</h3>
                        <p>Reserve the product for campus pickup.</p>
                    </article>
                    <article class="workflow-card">
                        <h3>5. Meet</h3>
                        <p>Meet safely at your university campus.</p>
                    </article>
                    <article class="workflow-card">
                        <h3>6. Review</h3>
                        <p>Rate the transaction after completion.</p>
                    </article>
                </div>
            </section>

            <!-- Planned Platform Features -->
            <section id="roadmap" class="roadmap-section">
                <h2>Planned Platform Features</h2>
                <div class="roadmap-grid">
                    <article class="roadmap-card">
                        <h3>Verified Student Accounts</h3>
                        <p>Campus email verification for trusted buying and selling.</p>
                        <span class="status planned">Coming Soon</span>
                    </article>
                    <article class="roadmap-card">
                        <h3>Campus Maps</h3>
                        <p>Find safe meetup locations using an interactive campus map.</p>
                        <span class="status progress">In Progress</span>
                    </article>
                    <article class="roadmap-card">
                        <h3>Secure Chat</h3>
                        <p>Real-time messaging between buyers and sellers.</p>
                        <span class="status planned">Coming Soon</span>
                    </article>
                    <article class="roadmap-card">
                        <h3>Storefronts</h3>
                        <p>Create personal student shops to manage listings.</p>
                        <span class="status future">Planned</span>
                    </article>
                </div>
            </section>
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
