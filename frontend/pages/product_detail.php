<?php
/**
 * Product Details Page
 * UniMarket - University Student Marketplace
 * Development Package: DP13-B / DP15
 */

require_once __DIR__ . '/../../backend/config/database.php';

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userId     = $isLoggedIn ? (int)$_SESSION['user_id'] : 0;
$fullName   = $isLoggedIn ? htmlspecialchars((string) ($_SESSION['full_name'] ?? 'Student'), ENT_QUOTES, 'UTF-8') : '';

// Single-use session flash messages
$successMessage = isset($_SESSION['flash_success']) ? trim((string) $_SESSION['flash_success']) : '';
$errorMessage   = isset($_SESSION['flash_error'])   ? trim((string) $_SESSION['flash_error'])   : '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// 1. Extract and validate product ID from query parameter safely
$productIdRaw = $_GET['id'] ?? '';
$productId    = (is_numeric($productIdRaw) && (int) $productIdRaw > 0) ? (int) $productIdRaw : 0;

$product    = null;
$fetchError = '';

if ($productId > 0) {
    try {
        $database   = new Database();
        $connection = $database->connect();

        // 2. Fetch single product details with category and seller information using prepared statement
        $stmt = $connection->prepare(
            'SELECT p.product_id, p.seller_id, p.title, p.description, p.price, p.tags, p.image_url,
                    p.product_condition, p.status, p.created_at,
                    c.category_id, c.category_name,
                    u.full_name AS seller_name, u.department AS seller_department, u.email AS seller_email
             FROM products p
             JOIN categories c ON p.category_id = c.category_id
             JOIN users u ON p.seller_id = u.user_id
             WHERE p.product_id = :product_id
             LIMIT 1'
        );

        $stmt->execute(['product_id' => $productId]);
        $fetchedProduct = $stmt->fetch(PDO::FETCH_ASSOC);

        if (is_array($fetchedProduct)) {
            $product = $fetchedProduct;
        }
    } catch (PDOException $exception) {
        error_log('Product Detail Exception: ' . $exception->getMessage());
        $fetchError = 'Unable to retrieve product details due to a database error.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $product ? htmlspecialchars((string)$product['title'], ENT_QUOTES, 'UTF-8') . ' on UniMarket' : 'Product Details | UniMarket'; ?>">
    <title><?php echo $product ? htmlspecialchars((string)$product['title'], ENT_QUOTES, 'UTF-8') . ' | UniMarket' : 'Product Not Found | UniMarket'; ?></title>

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
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Product Detail Content -->
    <main class="main-content">
        <div class="product-detail-container">

            <!-- Breadcrumb Navigation -->
            <nav class="breadcrumb-nav" aria-label="Breadcrumb">
                <a href="index.php">Home</a>
                <span>/</span>
                <a href="marketplace.php">Marketplace</a>
                <?php if ($product) : ?>
                    <span>/</span>
                    <a href="marketplace.php?category_id=<?php echo (int)$product['category_id']; ?>">
                        <?php echo htmlspecialchars((string)$product['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                    <span>/</span>
                    <span><?php echo htmlspecialchars((string)$product['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </nav>

            <!-- Feedback Alerts -->
            <?php if (!empty($successMessage)) : ?>
                <div class="dashboard-card" style="border-left: 4px solid var(--success); background-color: rgba(22, 163, 74, 0.05); margin-bottom: 1.5rem;">
                    <p style="color: var(--success); font-weight: 600; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (!empty($errorMessage)) : ?>
                <div class="dashboard-card" style="border-left: 4px solid var(--danger); background-color: rgba(220, 38, 38, 0.05); margin-bottom: 1.5rem;">
                    <p style="color: var(--danger); font-weight: 600; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (!empty($fetchError)) : ?>
                <div class="dashboard-card" style="border-left: 4px solid var(--danger); background-color: var(--danger-light); margin-bottom: 2rem;">
                    <p style="color: var(--danger); font-weight: 600; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?php echo htmlspecialchars($fetchError, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($product) :
                $pId          = (int) $product['product_id'];
                $pSellerId    = (int) $product['seller_id'];
                $pTitle       = htmlspecialchars((string) $product['title'], ENT_QUOTES, 'UTF-8');
                $pDesc        = !empty($product['description']) ? htmlspecialchars((string) $product['description'], ENT_QUOTES, 'UTF-8') : 'No description provided by seller.';
                $pPrice       = number_format((float) $product['price'], 2);
                $pCondition   = htmlspecialchars((string) $product['product_condition'], ENT_QUOTES, 'UTF-8');
                $pCategory    = htmlspecialchars((string) $product['category_name'], ENT_QUOTES, 'UTF-8');
                $pCatId       = (int) $product['category_id'];
                $pStatus      = htmlspecialchars((string) $product['status'], ENT_QUOTES, 'UTF-8');
                $pSellerName  = htmlspecialchars((string) $product['seller_name'], ENT_QUOTES, 'UTF-8');
                $pSellerDept  = htmlspecialchars((string) $product['seller_department'], ENT_QUOTES, 'UTF-8');
                $pSellerEmail = htmlspecialchars((string) ($product['seller_email'] ?? ''), ENT_QUOTES, 'UTF-8');
                $pCreatedAt   = date('F j, Y', strtotime((string) $product['created_at']));
                $pImg         = !empty($product['image_url']) ? htmlspecialchars((string) $product['image_url'], ENT_QUOTES, 'UTF-8') : '../images/cat-textbooks.png';

                $isAvailable  = ($product['status'] === 'Available' || $product['status'] === 'Active');
                $isOwner      = ($isLoggedIn && $userId === $pSellerId);

                if (!str_starts_with($pImg, 'http') && !str_starts_with($pImg, '../')) {
                    $pImg = '../' . ltrim($pImg, '/');
                }

                // Tags processing
                $tagsList = [];
                if (!empty($product['tags'])) {
                    $rawTags = explode(',', (string) $product['tags']);
                    foreach ($rawTags as $tag) {
                        $trimmed = trim($tag);
                        if ($trimmed !== '') {
                            $tagsList[] = htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
                        }
                    }
                }
            ?>
                <article class="product-detail-card">
                    <div class="product-detail-grid">

                        <!-- Left Column: Gallery & Media -->
                        <div class="product-gallery">
                            <div class="product-main-img-box">
                                <img src="<?php echo $pImg; ?>" alt="<?php echo $pTitle; ?>" class="product-main-img" onerror="this.onerror=null; this.src='../images/cat-textbooks.png';">
                            </div>
                        </div>

                        <!-- Right Column: Product Metadata, Action Card, & Seller Box -->
                        <div class="product-info-col">

                            <div class="product-meta-header">
                                <span class="badge-condition"><?php echo $pCondition; ?></span>
                                <a href="marketplace.php?category_id=<?php echo $pCatId; ?>" class="badge-category" style="text-decoration: none;">
                                    <?php echo $pCategory; ?>
                                </a>
                                <span class="status-badge status-<?php echo strtolower($pStatus); ?>">
                                    ● <?php echo $pStatus; ?>
                                </span>
                            </div>

                            <h1 class="product-detail-title"><?php echo $pTitle; ?></h1>

                            <div class="product-detail-price">
                                $<?php echo $pPrice; ?>
                                <span>USD</span>
                            </div>

                            <!-- Spec Overview Grid -->
                            <div class="product-spec-grid">
                                <div>
                                    <div class="spec-item-label">Condition</div>
                                    <div class="spec-item-value"><?php echo $pCondition; ?></div>
                                </div>
                                <div>
                                    <div class="spec-item-label">Category</div>
                                    <div class="spec-item-value"><?php echo $pCategory; ?></div>
                                </div>
                                <div>
                                    <div class="spec-item-label">Status</div>
                                    <div class="spec-item-value"><?php echo $pStatus; ?></div>
                                </div>
                                <div>
                                    <div class="spec-item-label">Posted Date</div>
                                    <div class="spec-item-value"><?php echo $pCreatedAt; ?></div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="product-description-box">
                                <h3>Description</h3>
                                <p><?php echo nl2br($pDesc); ?></p>

                                <?php if (!empty($tagsList)) : ?>
                                    <div style="margin-top: 1.25rem;">
                                        <div class="spec-item-label">Tags</div>
                                        <div class="tags-list">
                                            <?php foreach ($tagsList as $tag) : ?>
                                                <span class="tag-item">#<?php echo $tag; ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Interactive Reservation Action Area -->
                            <div class="reservation-card" style="background-color: var(--gray-50); border: 1px solid var(--gray-200); border-radius: var(--radius-lg); padding: 1.5rem; margin-top: 1.5rem;">
                                <?php if (!$isLoggedIn) : ?>
                                    <h3 style="margin-top: 0; margin-bottom: 0.5rem; font-size: 1.15rem; color: var(--dark);">Reserve this Item</h3>
                                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Sign in with your verified campus student account to reserve this item for safe on-campus pickup.</p>
                                    <a href="login.php" class="btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; justify-content: center; width: 100%;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M13 12H3"/></svg>
                                        Sign In to Reserve
                                    </a>

                                <?php elseif ($isOwner) : ?>
                                    <div style="display: flex; align-items: center; gap: 0.75rem; color: var(--primary);">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                                        <div>
                                            <strong style="display: block; font-size: 0.95rem;">You are the seller of this listing</strong>
                                            <span style="font-size: 0.85rem; color: var(--gray-600);">You can update details, change pricing, or adjust status in your listings.</span>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 0.75rem; margin-top: 1rem; flex-wrap: wrap;">
                                        <a href="edit_product.php?id=<?php echo $pId; ?>" class="btn-outline" style="flex: 1; text-align: center;">Edit Listing</a>
                                        <a href="my_listings.php" class="btn-primary" style="flex: 1; text-align: center;">My Listings</a>
                                    </div>

                                <?php elseif ($isAvailable) : ?>
                                    <h3 style="margin-top: 0; margin-bottom: 0.35rem; font-size: 1.15rem; color: var(--dark);">Reserve for Campus Pickup</h3>
                                    <p style="color: var(--gray-600); font-size: 0.85rem; margin-bottom: 1.25rem;">
                                        Zero upfront fees. Choose a verified campus spot to inspect and pay cash upon meetup.
                                    </p>

                                    <form method="POST" action="../../backend/api/reserve_product.php" class="reserve-form">
                                        <input type="hidden" name="product_id" value="<?php echo $pId; ?>">

                                        <div class="form-group" style="margin-bottom: 1rem;">
                                            <label for="meetup_spot" style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 0.35rem; color: var(--dark);">
                                                Select Campus Meetup Spot <span style="color: var(--danger);">*</span>
                                            </label>
                                            <select id="meetup_spot" name="meetup_spot" required onchange="updateMeetupCoordinates(this)" style="width: 100%; padding: 0.65rem 0.85rem; border: 1px solid var(--gray-300); border-radius: var(--radius-sm); font-family: var(--font-main); font-size: 0.9rem; background-color: var(--white);">
                                                <option value="23.77717600,90.39945200" selected>📍 Central Library Lobby (Recommended Safe Spot)</option>
                                                <option value="23.73350000,90.39290000">📍 Student Activity Center (TSC)</option>
                                                <option value="23.77750000,90.40010000">📍 Campus Cafeteria Entrance</option>
                                                <option value="23.77800000,90.39900000">📍 Academic Building 1 (Ground Floor)</option>
                                                <option value="23.77650000,90.39800000">📍 University Main Gate Security Post</option>
                                            </select>
                                        </div>

                                        <input type="hidden" id="meetup_latitude" name="meetup_latitude" value="23.77717600">
                                        <input type="hidden" id="meetup_longitude" name="meetup_longitude" value="90.39945200">

                                        <button type="submit" class="btn-primary" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 0.5rem; padding: 0.85rem;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                            Confirm Item Reservation ($<?php echo $pPrice; ?>)
                                        </button>
                                    </form>

                                    <script>
                                        function updateMeetupCoordinates(selectEl) {
                                            const parts = selectEl.value.split(',');
                                            if (parts.length === 2) {
                                                document.getElementById('meetup_latitude').value = parts[0];
                                                document.getElementById('meetup_longitude').value = parts[1];
                                            }
                                        }
                                    </script>

                                <?php elseif ($pStatus === 'Reserved') : ?>
                                    <div style="display: flex; align-items: center; gap: 0.75rem; color: #b45309;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        <div>
                                            <strong style="display: block; font-size: 0.95rem;">Currently Reserved</strong>
                                            <span style="font-size: 0.85rem; color: var(--gray-600);">A student has reserved this item and a campus meetup is in progress.</span>
                                        </div>
                                    </div>

                                <?php else : ?>
                                    <div style="display: flex; align-items: center; gap: 0.75rem; color: var(--gray-600);">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                        <div>
                                            <strong style="display: block; font-size: 0.95rem;">Listing Sold</strong>
                                            <span style="font-size: 0.85rem;">This item has already been purchased and exchanged on campus.</span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Seller Information Card -->
                            <div class="seller-info-card">
                                <div class="seller-header">
                                    <div class="seller-avatar">
                                        <?php echo strtoupper(substr($pSellerName, 0, 1)); ?>
                                    </div>
                                    <div class="seller-details">
                                        <h4>Seller: <?php echo $pSellerName; ?></h4>
                                        <p>Department of <?php echo $pSellerDept; ?> • Verified Student</p>
                                        <?php if (!empty($pSellerEmail) && $isLoggedIn) : ?>
                                            <p style="margin-top: 0.35rem;">
                                                <a href="mailto:<?php echo $pSellerEmail; ?>?subject=Inquiry%20regarding%20<?php echo rawurlencode($product['title']); ?>" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                                                    ✉️ Contact Seller via Campus Email
                                                </a>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </article>

            <?php else : ?>
                <!-- Product Not Found Empty State -->
                <div class="empty-state-card">
                    <div class="empty-state-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-xl"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                    <h3>Product Listing Not Found</h3>
                    <p>The product listing you requested does not exist or may have been removed from the marketplace catalog.</p>
                    <a href="marketplace.php" class="btn-primary">
                        Return to Marketplace Catalog
                    </a>
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
