<?php
// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$fullName   = $isLoggedIn ? htmlspecialchars((string) ($_SESSION['full_name'] ?? 'Student'), ENT_QUOTES, 'UTF-8') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="UniMarket - A campus-exclusive marketplace for university students to safely buy, sell, and trade textbooks, electronics, and dorm essentials.">
    <title>UniMarket | University Student Marketplace</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="../css/styles.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- Header -->
    <header class="site-header">
        <div class="container header-container">
            <div class="logo-area">
                <div class="logo-badge">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-lg"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </div>
                <div>
                    <a href="index.php" class="logo">UniMarket</a>
                    <span class="tagline">University Student Marketplace</span>
                </div>
            </div>
            <div class="header-actions">
                <?php if ($isLoggedIn) : ?>
                    <a href="dashboard.php" class="btn-outline">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Dashboard (<?php echo $fullName; ?>)
                    </a>
                    <a href="logout.php" class="btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Sign Out
                    </a>
                <?php else : ?>
                    <a href="login.php" class="btn-outline">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M13 12H3"/></svg>
                        Sign In
                    </a>
                    <a href="#register" class="btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Post Item
                    </a>
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
                aria-label="Toggle Navigation Menu"
                aria-expanded="false"
                aria-controls="primary-menu">
                ☰
            </button>
            <ul id="primary-menu">
                <li>
                    <a href="index.php" class="active">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                        Home
                    </a>
                </li>
                <li>
                    <a href="marketplace.php">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        Marketplace
                    </a>
                </li>
                <li>
                    <a href="#categories">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Categories
                    </a>
                </li>
                <?php if ($isLoggedIn) : ?>
                    <li>
                        <a href="dashboard.php">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="my_orders.php">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            My Reservations
                        </a>
                    </li>
                    <li>
                        <a href="my_listings.php">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            My Listings
                        </a>
                    </li>
                <?php else : ?>
                    <li>
                        <a href="#about">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            About
                        </a>
                    </li>
                    <li>
                        <a href="#contact">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            Contact
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Hero Banner (Premium Split-Screen Grid Layout) -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-text-col">
                    <div class="hero-pill-badge">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span>Campus Verified Network</span>
                    </div>

                    <h2>Buy, Sell & Trade with <span>Verified Students</span></h2>
                    <p>
                        UniMarket is a campus-exclusive marketplace where students
                        safely buy and sell course textbooks, tech laptops, dorm gear,
                        and academic services within their trusted university network.
                    </p>

                    <div class="hero-trust-list">
                        <div class="trust-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="trust-check"><polyline points="20 6 9 17 4 12"/></svg>
                            100% Student Verified
                        </div>
                        <div class="trust-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="trust-check"><polyline points="20 6 9 17 4 12"/></svg>
                            Instant Campus Pickup
                        </div>
                        <div class="trust-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="trust-check"><polyline points="20 6 9 17 4 12"/></svg>
                            Zero Listing Fees
                        </div>
                    </div>

                    <div class="hero-cta-group">
                        <a href="marketplace.php" class="btn-primary btn-hero">
                            Explore Marketplace
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                        <a href="<?php echo $isLoggedIn ? 'create_product.php' : 'login.php'; ?>" class="btn-outline btn-hero-outline">
                            Post Your Item
                        </a>
                    </div>
                </div>

                <div class="hero-media-col">
                    <div class="hero-media-card">
                        <img src="../images/hero-marketplace.png" alt="UniMarket Students Trading Illustration" class="hero-media-img" loading="eager">
                        <div class="hero-floating-badge">
                            <div class="badge-icon-box">🎓</div>
                            <div class="badge-text-box">
                                <strong>1,200+ Active Students</strong>
                                <span>Across 12 Verified Campuses</span>
                            </div>
                        </div>
                    </div>
                </div>
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
                    <!-- Populated dynamically via app.js with generated images -->
                </div>
            </section>

            <!-- Marketplace Statistics -->
            <section class="statistics-section">
                <h2>Marketplace Statistics</h2>
                <div
                    id="statistics-container"
                    class="statistics-grid">
                    <!-- Populated dynamically via app.js with metric SVG icons -->
                </div>
            </section>

            <!-- Student Registration -->
            <section id="register" class="registration-section">
                <h2>Student Registration</h2>

                <form id="registration-form" class="registration-form" novalidate>
                    <div class="form-group">
                        <label for="full-name">Full Name</label>
                        <div class="input-icon-group">
                            <input
                                type="text"
                                id="full-name"
                                name="full-name"
                                placeholder="e.g. Alex Morgan"
                                autocomplete="name"
                                required>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">University Email</label>
                        <div class="input-icon-group">
                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="e.g. alex@university.edu"
                                autocomplete="email"
                                required>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="student-id">Student ID</label>
                        <div class="input-icon-group">
                            <input
                                type="text"
                                id="student-id"
                                name="student-id"
                                placeholder="e.g. STU-98765"
                                required>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="7" y1="8" x2="17" y2="8"/><line x1="7" y1="12" x2="13" y2="12"/></svg>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="department">Department</label>
                        <div class="input-icon-group">
                            <input
                                type="text"
                                id="department"
                                name="department"
                                placeholder="e.g. Computer Science"
                                required>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-icon-group">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="At least 6 characters"
                                autocomplete="new-password"
                                required>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">
                        Register Account
                    </button>
                    <div id="form-message" role="alert" aria-live="polite"></div>
                </form>
            </section>

            <!-- How It Works -->
            <section class="workflow-section">
                <h2>How UniMarket Works</h2>
                <div class="workflow-grid">
                    <article class="workflow-card">
                        <div class="workflow-step-num">1</div>
                        <h3>Register</h3>
                        <p>Create your verified student account with campus email.</p>
                    </article>
                    <article class="workflow-card">
                        <div class="workflow-step-num">2</div>
                        <h3>Browse</h3>
                        <p>Explore products listed by fellow students on campus.</p>
                    </article>
                    <article class="workflow-card">
                        <div class="workflow-step-num">3</div>
                        <h3>Chat</h3>
                        <p>Contact the seller directly before making a deal.</p>
                    </article>
                    <article class="workflow-card">
                        <div class="workflow-step-num">4</div>
                        <h3>Reserve</h3>
                        <p>Reserve items for quick and secure campus pickup.</p>
                    </article>
                    <article class="workflow-card">
                        <div class="workflow-step-num">5</div>
                        <h3>Meet</h3>
                        <p>Meet safely at designated university campus spots.</p>
                    </article>
                    <article class="workflow-card">
                        <div class="workflow-step-num">6</div>
                        <h3>Review</h3>
                        <p>Rate and review your transaction after completion.</p>
                    </article>
                </div>
            </section>

            <!-- Planned Platform Features -->
            <section class="roadmap-section">
                <h2>Planned Platform Features</h2>
                <div class="roadmap-grid">
                    <article class="roadmap-card">
                        <div>
                            <h3>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon" style="color: var(--primary);"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                Verified Accounts
                            </h3>
                            <p>Campus email verification for trusted buying and selling.</p>
                        </div>
                        <span class="status planned">Coming Soon</span>
                    </article>
                    <article class="roadmap-card">
                        <div>
                            <h3>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon" style="color: var(--warning);"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
                                Campus Maps
                            </h3>
                            <p>Find safe meetup locations using an interactive campus map.</p>
                        </div>
                        <span class="status progress">In Progress</span>
                    </article>
                    <article class="roadmap-card">
                        <div>
                            <h3>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon" style="color: var(--primary);"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                Secure Chat
                            </h3>
                            <p>Real-time instant messaging between buyers and sellers.</p>
                        </div>
                        <span class="status planned">Coming Soon</span>
                    </article>
                    <article class="roadmap-card">
                        <div>
                            <h3>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon" style="color: var(--gray-600);"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                Storefronts
                            </h3>
                            <p>Create personal student shops to manage your listings.</p>
                        </div>
                        <span class="status future">Planned</span>
                    </article>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <p>© 2026 UniMarket. All rights reserved.</p>
            <p>Designed for Software Development Project III</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="../js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>
