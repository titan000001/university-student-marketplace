<?php
/**
 * My Listings Management Page
 * UniMarket - University Student Marketplace
 * Development Package: DP14 / DP15 (Seller Listing Management)
 */

require_once __DIR__ . '/../../backend/config/database.php';

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Protect page: redirect unauthenticated users to login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId   = (int) $_SESSION['user_id'];
$fullName = htmlspecialchars((string) ($_SESSION['full_name'] ?? 'Student'), ENT_QUOTES, 'UTF-8');

// Helper function to resolve campus coordinates to human-readable meetup spots
function resolveCampusSpot($lat, $lng) {
    if ($lat === null || $lng === null) {
        return 'Designated Campus Meeting Spot';
    }
    $locations = [
        ['name' => 'Central Library Lobby', 'lat' => 23.77717600, 'lng' => 90.39945200],
        ['name' => 'Student Activity Center (TSC)', 'lat' => 23.73350000, 'lng' => 90.39290000],
        ['name' => 'Campus Cafeteria Entrance', 'lat' => 23.77750000, 'lng' => 90.40010000],
        ['name' => 'Academic Building 1 (Ground Floor)', 'lat' => 23.77800000, 'lng' => 90.39900000],
        ['name' => 'University Main Gate Security Post', 'lat' => 23.77650000, 'lng' => 90.39800000]
    ];
    foreach ($locations as $loc) {
        if (abs((float)$loc['lat'] - (float)$lat) < 0.001 && abs((float)$loc['lng'] - (float)$lng) < 0.001) {
            return $loc['name'];
        }
    }
    return sprintf('Campus Coordinates (%.4f, %.4f)', (float)$lat, (float)$lng);
}

// Single-use session flash feedback messages
$successMessage = isset($_SESSION['flash_success']) ? trim((string) $_SESSION['flash_success']) : '';
$errorMessage   = isset($_SESSION['flash_error'])   ? trim((string) $_SESSION['flash_error'])   : '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$listings = [];
$incomingReservations = [];
$dbError = '';

try {
    $database = new Database();
    $connection = $database->connect();

    // 1. Fetch listings belonging ONLY to the authenticated student
    $statement = $connection->prepare('
        SELECT p.product_id, p.title, p.description, p.price, p.tags, p.image_url,
               p.product_condition, p.status, p.created_at,
               c.category_id, c.category_name
        FROM products p
        JOIN categories c ON p.category_id = c.category_id
        WHERE p.seller_id = :seller_id
        ORDER BY p.created_at DESC
    ');
    $statement->execute(['seller_id' => $userId]);
    $listings = $statement->fetchAll(PDO::FETCH_ASSOC);

    // 2. Fetch incoming reservations for products owned by this seller
    $resStmt = $connection->prepare('
        SELECT t.transaction_id, t.product_id, t.buyer_id, t.amount, t.meetup_latitude, t.meetup_longitude,
               t.status AS trans_status, t.transaction_date,
               p.title AS product_title, p.price, p.image_url,
               u.full_name AS buyer_name, u.student_id AS buyer_student_id, u.department AS buyer_department, u.email AS buyer_email
        FROM transactions t
        JOIN products p ON t.product_id = p.product_id
        JOIN users u ON t.buyer_id = u.user_id
        WHERE t.seller_id = :seller_id
        ORDER BY t.transaction_date DESC
    ');
    $resStmt->execute(['seller_id' => $userId]);
    $incomingReservations = $resStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $exception) {
    error_log('My Listings Fetch Error: ' . $exception->getMessage());
    $dbError = 'Unable to load your listings due to a database error.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manage your active, reserved, and sold marketplace listings on UniMarket.">
    <title>My Listings | UniMarket</title>

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
                <a href="dashboard.php" class="btn-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Dashboard
                </a>
                <a href="logout.php" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="main-nav" aria-label="My Listings Navigation">
        <div class="container">
            <button
                id="menu-toggle"
                class="menu-toggle"
                aria-label="Toggle Navigation Menu"
                aria-expanded="false"
                aria-controls="listings-menu">
                ☰
            </button>
            <ul id="listings-menu">
                <li>
                    <a href="dashboard.php">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="my_listings.php" class="active">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        My Listings
                    </a>
                </li>
                <li>
                    <a href="my_orders.php">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        My Reservations
                    </a>
                </li>
                <li>
                    <a href="create_product.php">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Post New Item
                    </a>
                </li>
                <li>
                    <a href="profile.php">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        My Profile
                    </a>
                </li>
                <li>
                    <a href="marketplace.php">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        Browse Marketplace
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-container">

            <!-- Banner Header -->
            <section class="dashboard-header">
                <div class="dashboard-welcome-text" style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <h1>Seller Listing Management</h1>
                        <p>Manage your active listings, edit pricing and details, or track incoming buyer reservations.</p>
                    </div>
                    <div>
                        <a href="create_product.php" class="btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Post New Listing
                        </a>
                    </div>
                </div>
            </section>

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

            <?php if (!empty($dbError)) : ?>
                <div class="dashboard-card" style="border-left: 4px solid var(--danger); background-color: var(--danger-light); margin-bottom: 1.5rem;">
                    <p style="color: var(--danger); font-weight: 600; margin: 0;">
                        <?php echo htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Incoming Reservations Section -->
            <?php if (!empty($incomingReservations)) : ?>
                <section class="dashboard-card" style="margin-bottom: 2rem; border-top: 4px solid var(--primary);">
                    <h2 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary);"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        Incoming Buyer Reservations (<?php echo count($incomingReservations); ?>)
                    </h2>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1.25rem;">
                        Review campus meetup details with fellow students and mark transactions as completed once items are physically exchanged.
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($incomingReservations as $res) :
                            $rId        = (int) $res['transaction_id'];
                            $rStatus    = htmlspecialchars((string)$res['trans_status'], ENT_QUOTES, 'UTF-8');
                            $rAmount    = number_format((float)$res['amount'], 2);
                            $rDate      = date('M j, Y • g:i A', strtotime((string)$res['transaction_date']));
                            $buyerName  = htmlspecialchars((string)$res['buyer_name'], ENT_QUOTES, 'UTF-8');
                            $buyerDept  = htmlspecialchars((string)$res['buyer_department'], ENT_QUOTES, 'UTF-8');
                            $buyerIdNum = htmlspecialchars((string)$res['buyer_student_id'], ENT_QUOTES, 'UTF-8');
                            $buyerEmail = htmlspecialchars((string)$res['buyer_email'], ENT_QUOTES, 'UTF-8');
                            $spotName   = htmlspecialchars(resolveCampusSpot($res['meetup_latitude'], $res['meetup_longitude']), ENT_QUOTES, 'UTF-8');
                            $itemTitle  = htmlspecialchars((string)$res['product_title'], ENT_QUOTES, 'UTF-8');
                        ?>
                            <div style="background-color: var(--gray-50); border: 1px solid var(--gray-200); border-radius: var(--radius-md); padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                                <div style="flex: 2; min-width: 260px;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                                        <span class="status-badge status-<?php echo strtolower($rStatus); ?>">● <?php echo $rStatus; ?></span>
                                        <span style="font-size: 0.8rem; color: var(--gray-600);">Reservation #<?php echo $rId; ?> • <?php echo $rDate; ?></span>
                                    </div>
                                    <h4 style="margin: 0 0 0.35rem 0; font-size: 1.05rem; color: var(--dark);"><?php echo $itemTitle; ?> ($<?php echo $rAmount; ?>)</h4>
                                    <div style="font-size: 0.85rem; color: var(--gray-600);">
                                        <strong>Buyer:</strong> <?php echo $buyerName; ?> (<?php echo $buyerIdNum; ?> • <?php echo $buyerDept; ?>)
                                        • <a href="mailto:<?php echo $buyerEmail; ?>" style="color: var(--primary); text-decoration: none;">✉️ <?php echo $buyerEmail; ?></a>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--primary); font-weight: 600; margin-top: 0.35rem;">
                                        📍 Meetup Location: <?php echo $spotName; ?>
                                    </div>
                                </div>

                                <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                    <?php if ($rStatus === 'Reserved') : ?>
                                        <form method="POST" action="../../backend/api/manage_transaction.php" onsubmit="return confirm('Confirm that you have completed this cash/meetup exchange with the student? The listing will be marked as Sold.');" style="margin: 0;">
                                            <input type="hidden" name="transaction_id" value="<?php echo $rId; ?>">
                                            <input type="hidden" name="action" value="complete">
                                            <input type="hidden" name="return_url" value="../../frontend/pages/my_listings.php">
                                            <button type="submit" class="btn-primary" style="padding: 0.55rem 0.85rem; font-size: 0.85rem;">
                                                ✓ Complete Deal (Mark Sold)
                                            </button>
                                        </form>

                                        <form method="POST" action="../../backend/api/manage_transaction.php" onsubmit="return confirm('Are you sure you want to cancel this reservation? The listing will become Available again in the marketplace.');" style="margin: 0;">
                                            <input type="hidden" name="transaction_id" value="<?php echo $rId; ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="return_url" value="../../frontend/pages/my_listings.php">
                                            <button type="submit" class="btn-action-delete" style="padding: 0.55rem 0.85rem;">
                                                ✕ Cancel
                                            </button>
                                        </form>
                                    <?php else : ?>
                                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--gray-600);">Deal <?php echo $rStatus; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Listings Container -->
            <?php if (!empty($listings)) : ?>
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: var(--dark);">
                    All Posted Listings (<?php echo count($listings); ?>)
                </h2>
                <div class="my-listings-grid">
                    <?php foreach ($listings as $product) :
                        $pId        = (int) $product['product_id'];
                        $pTitle     = htmlspecialchars((string)$product['title'], ENT_QUOTES, 'UTF-8');
                        $pCategory  = htmlspecialchars((string)$product['category_name'], ENT_QUOTES, 'UTF-8');
                        $pCondition = htmlspecialchars((string)$product['product_condition'], ENT_QUOTES, 'UTF-8');
                        $pPrice     = number_format((float)$product['price'], 2);
                        $pStatus    = htmlspecialchars((string)$product['status'], ENT_QUOTES, 'UTF-8');
                        $pDate      = date('M j, Y', strtotime((string)$product['created_at']));

                        $pImg = !empty($product['image_url']) ? htmlspecialchars((string)$product['image_url'], ENT_QUOTES, 'UTF-8') : '../images/cat-textbooks.png';
                        if (!str_starts_with($pImg, 'http') && !str_starts_with($pImg, '../')) {
                            $pImg = '../' . ltrim($pImg, '/');
                        }
                    ?>
                        <article class="my-listing-card">
                            <div class="my-listing-img-box">
                                <img src="<?php echo $pImg; ?>" alt="<?php echo $pTitle; ?>" onerror="this.onerror=null; this.src='../images/cat-textbooks.png';">
                                <span class="status-badge status-<?php echo strtolower($pStatus); ?>">
                                    ● <?php echo $pStatus; ?>
                                </span>
                            </div>

                            <div class="my-listing-body">
                                <div class="my-listing-meta">
                                    <span class="badge-category"><?php echo $pCategory; ?></span>
                                    <span class="badge-condition"><?php echo $pCondition; ?></span>
                                </div>

                                <h3 class="my-listing-title">
                                    <a href="product_detail.php?id=<?php echo $pId; ?>">
                                        <?php echo $pTitle; ?>
                                    </a>
                                </h3>

                                <div class="my-listing-price-row">
                                    <div class="my-listing-price">$<?php echo $pPrice; ?> <span>USD</span></div>
                                    <div class="my-listing-date"><?php echo $pDate; ?></div>
                                </div>

                                <!-- Inline Status Update Form -->
                                <form method="POST" action="../../backend/api/update_product_status.php" class="status-update-form">
                                    <input type="hidden" name="product_id" value="<?php echo $pId; ?>">
                                    <div class="status-control-group">
                                        <label for="status_<?php echo $pId; ?>" class="sr-only">Change Status</label>
                                        <select id="status_<?php echo $pId; ?>" name="status" class="status-select">
                                            <option value="Available" <?php echo ($pStatus === 'Available' || $pStatus === 'Active') ? 'selected' : ''; ?>>Available</option>
                                            <option value="Reserved" <?php echo ($pStatus === 'Reserved') ? 'selected' : ''; ?>>Reserved</option>
                                            <option value="Sold" <?php echo ($pStatus === 'Sold') ? 'selected' : ''; ?>>Sold</option>
                                        </select>
                                        <button type="submit" class="btn-status-save" title="Save Status">Save</button>
                                    </div>
                                </form>

                                <!-- Action Buttons -->
                                <div class="my-listing-actions">
                                    <a href="edit_product.php?id=<?php echo $pId; ?>" class="btn-action-edit">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                        Edit
                                    </a>

                                    <form method="POST" action="../../backend/api/delete_product.php" onsubmit="return confirm('Are you sure you want to permanently delete this listing? This action cannot be undone.');" style="margin: 0;">
                                        <input type="hidden" name="product_id" value="<?php echo $pId; ?>">
                                        <button type="submit" class="btn-action-delete">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>

                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

            <?php else : ?>
                <!-- Empty State -->
                <div class="empty-state-card">
                    <div class="empty-state-icon">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-xl"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </div>
                    <h3>No Listings Found</h3>
                    <p>You haven't posted any products for sale yet. Turn your unused textbooks, electronics, or dorm essentials into extra campus cash today!</p>
                    <a href="create_product.php" class="btn-primary">
                        Post Your First Item
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
