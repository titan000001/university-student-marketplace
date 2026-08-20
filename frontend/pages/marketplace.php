<?php
/**
 * Marketplace Catalog & Search Page
 * UniMarket - University Student Marketplace
 * Development Package: DP13-B
 */

require_once __DIR__ . '/../../backend/config/database.php';

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$fullName   = $isLoggedIn ? htmlspecialchars((string) ($_SESSION['full_name'] ?? 'Student'), ENT_QUOTES, 'UTF-8') : '';

// 1. Extract search & filter parameters safely
$searchQuery   = isset($_GET['search']) ? trim((string) $_GET['search']) : '';
$categoryIdRaw = isset($_GET['category_id']) ? trim((string) $_GET['category_id']) : '';
$categoryId    = (is_numeric($categoryIdRaw) && (int) $categoryIdRaw > 0) ? (int) $categoryIdRaw : 0;

$categories      = [];
$products        = [];
$queryError      = '';
$selectedCatName = '';

try {
    $database   = new Database();
    $connection = $database->connect();

    // 2. Fetch categories for filter dropdown
    $catStmt = $connection->query('SELECT category_id, category_name FROM categories ORDER BY category_name ASC');
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

    // Find name of selected category if filter is applied
    if ($categoryId > 0) {
        foreach ($categories as $cat) {
            if ((int) $cat['category_id'] === $categoryId) {
                $selectedCatName = $cat['category_name'];
                break;
            }
        }
    }

    // 3. Build parameterized query for active marketplace products
    // Only display products with status Available or Active according to DB schema
    $sql = 'SELECT p.product_id, p.title, p.description, p.price, p.tags, p.image_url,
                   p.product_condition, p.status, p.created_at,
                   c.category_id, c.category_name,
                   u.full_name AS seller_name
            FROM products p
            JOIN categories c ON p.category_id = c.category_id
            JOIN users u ON p.seller_id = u.user_id
            WHERE p.status IN ("Available", "Active")';

    $params = [];

    if ($searchQuery !== '') {
        $sql .= ' AND (p.title LIKE :search OR p.tags LIKE :search_tags)';
        $params['search']      = '%' . $searchQuery . '%';
        $params['search_tags'] = '%' . $searchQuery . '%';
    }

    if ($categoryId > 0) {
        $sql .= ' AND p.category_id = :category_id';
        $params['category_id'] = $categoryId;
    }

    $sql .= ' ORDER BY p.created_at DESC';

    $productStmt = $connection->prepare($sql);
    $productStmt->execute($params);
    $products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $exception) {
    error_log('Marketplace Catalog Exception: ' . $exception->getMessage());
    $queryError = 'Unable to load marketplace items at this time due to a server error.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse, search, and filter verified student marketplace items on UniMarket.">
    <title>Marketplace Catalog | UniMarket</title>

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
                    <a href="create_product.php" class="btn-outline">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Post Item
                    </a>
                    <a href="dashboard.php" class="btn-outline">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
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
                    <a href="login.php" class="btn-primary">
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
                    <a href="index.php">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                        Home
                    </a>
                </li>
                <li>
                    <a href="marketplace.php" class="active">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        Marketplace
                    </a>
                </li>
                <li>
                    <a href="index.php#categories">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Categories
                    </a>
                </li>
                <?php if ($isLoggedIn) : ?>
                    <li>
                        <a href="dashboard.php">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Dashboard
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Marketplace Main Content -->
    <main class="main-content">
        <div class="container marketplace-container">

            <!-- Banner Header -->
            <div class="marketplace-banner">
                <div class="marketplace-title-group">
                    <h1>Campus Marketplace Catalog</h1>
                    <p>Explore textbook listings, electronics, dorm gear, and services offered by verified students.</p>
                </div>
                <div>
                    <a href="<?php echo $isLoggedIn ? 'create_product.php' : 'login.php'; ?>" class="btn-primary" style="background-color: var(--white); color: var(--primary); font-weight: 700;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Post Listing
                    </a>
                </div>
            </div>

            <!-- Search & Filter Controls -->
            <section class="marketplace-filter-card">
                <form method="GET" action="marketplace.php" class="filter-bar-form">

                    <!-- Search Input -->
                    <div class="search-input-wrapper input-icon-group">
                        <input
                            type="text"
                            name="search"
                            id="search"
                            placeholder="Search by title or keywords (e.g. Java, Calculator)..."
                            value="<?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </div>

                    <!-- Dynamic Category Select Dropdown -->
                    <div class="category-select-wrapper input-icon-group">
                        <select name="category_id" id="category_id" class="filter-select">
                            <option value="0">All Categories</option>
                            <?php foreach ($categories as $cat) : ?>
                                <option
                                    value="<?php echo (int) $cat['category_id']; ?>"
                                    <?php echo ($categoryId === (int) $cat['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    </div>

                    <!-- Action Buttons -->
                    <div class="filter-actions-group">
                        <button type="submit" class="btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            Filter Results
                        </button>

                        <?php if ($searchQuery !== '' || $categoryId > 0) : ?>
                            <a href="marketplace.php" class="btn-outline" style="border-color: var(--gray-300); color: var(--gray-600);">
                                Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>

            <!-- Query Metadata & Active Filter Indicators -->
            <div class="results-meta-bar">
                <div class="results-count">
                    Showing <strong><?php echo count($products); ?></strong> product<?php echo count($products) === 1 ? '' : 's'; ?>
                </div>

                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <?php if ($searchQuery !== '') : ?>
                        <span class="active-filter-badge">
                            Search: "<?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>"
                        </span>
                    <?php endif; ?>

                    <?php if (!empty($selectedCatName)) : ?>
                        <span class="active-filter-badge">
                            Category: <?php echo htmlspecialchars($selectedCatName, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Error Notification -->
            <?php if (!empty($queryError)) : ?>
                <div class="dashboard-card" style="border-left: 4px solid var(--danger); background-color: var(--danger-light); margin-bottom: 2rem;">
                    <p style="color: var(--danger); font-weight: 600; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?php echo htmlspecialchars($queryError, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Product Cards Grid -->
            <?php if (!empty($products)) : ?>
                <div class="marketplace-grid">
                    <?php foreach ($products as $item) :
                        $pId         = (int) $item['product_id'];
                        $pTitle      = htmlspecialchars((string) $item['title'], ENT_QUOTES, 'UTF-8');
                        $pPrice      = number_format((float) $item['price'], 2);
                        $pCondition  = htmlspecialchars((string) $item['product_condition'], ENT_QUOTES, 'UTF-8');
                        $pCategory   = htmlspecialchars((string) $item['category_name'], ENT_QUOTES, 'UTF-8');
                        $pSeller     = htmlspecialchars((string) $item['seller_name'], ENT_QUOTES, 'UTF-8');
                        $pStatus     = htmlspecialchars((string) $item['status'], ENT_QUOTES, 'UTF-8');
                        $pImg        = !empty($item['image_url']) ? htmlspecialchars((string) $item['image_url'], ENT_QUOTES, 'UTF-8') : '../images/cat-textbooks.png';

                        // Handle relative image path adjustments if needed
                        if (!str_starts_with($pImg, 'http') && !str_starts_with($pImg, '../')) {
                            $pImg = '../' . ltrim($pImg, '/');
                        }
                    ?>
                        <article class="product-card">
                            <div class="product-card-img-wrapper">
                                <img src="<?php echo $pImg; ?>" alt="<?php echo $pTitle; ?>" class="product-card-img" loading="lazy" onerror="this.onerror=null; this.src='../images/cat-textbooks.png';">
                                <div class="badge-tag-overlay">
                                    <span class="badge-condition"><?php echo $pCondition; ?></span>
                                    <span class="badge-category"><?php echo $pCategory; ?></span>
                                </div>
                            </div>

                            <div class="product-card-body">
                                <h2 class="product-card-title">
                                    <a href="product_detail.php?id=<?php echo $pId; ?>">
                                        <?php echo $pTitle; ?>
                                    </a>
                                </h2>
                                <div class="product-card-price">
                                    $<?php echo $pPrice; ?>
                                </div>

                                <div class="product-card-seller">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm" style="color: var(--primary);"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    Seller: <strong><?php echo $pSeller; ?></strong>
                                </div>
                            </div>

                            <div class="product-card-footer">
                                <span class="status-badge status-<?php echo strtolower($pStatus); ?>">
                                    ● <?php echo $pStatus; ?>
                                </span>

                                <a href="product_detail.php?id=<?php echo $pId; ?>" class="btn-primary" style="padding: 0.4rem 0.85rem; font-size: 0.85rem;">
                                    View Details
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

            <?php else : ?>
                <!-- Empty State Component -->
                <div class="empty-state-card">
                    <div class="empty-state-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-xl"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                    </div>
                    <h3>No Matching Products Found</h3>
                    <p>We couldn't find any active listings matching your current search terms or selected category. Try broadening your criteria or resetting filters.</p>
                    <?php if ($searchQuery !== '' || $categoryId > 0) : ?>
                        <a href="marketplace.php" class="btn-primary">
                            Clear Filters & View All
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

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
