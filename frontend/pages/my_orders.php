<?php
/**
 * Buyer Reservations & Orders Page
 * UniMarket - University Student Marketplace
 * Development Package: DP15 (Buyer Reservation & Campus Meetup)
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
function resolveCampusLocation($lat, $lng) {
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

// Retrieve single-use flash messages
$successMessage = isset($_SESSION['flash_success']) ? trim((string) $_SESSION['flash_success']) : '';
$errorMessage   = isset($_SESSION['flash_error'])   ? trim((string) $_SESSION['flash_error'])   : '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Filter status from GET parameter
$filterStatus = isset($_GET['status']) ? trim((string)$_GET['status']) : 'all';

$orders = [];
$dbError = '';

try {
    $database = new Database();
    $connection = $database->connect();

    $sql = '
        SELECT t.transaction_id, t.product_id, t.buyer_id, t.seller_id, t.amount,
               t.meetup_latitude, t.meetup_longitude, t.status AS trans_status, t.transaction_date,
               p.title AS product_title, p.image_url, p.product_condition, p.status AS product_status,
               c.category_name,
               u.full_name AS seller_name, u.department AS seller_department, u.email AS seller_email
        FROM transactions t
        JOIN products p ON t.product_id = p.product_id
        JOIN categories c ON p.category_id = c.category_id
        JOIN users u ON t.seller_id = u.user_id
        WHERE t.buyer_id = :buyer_id
    ';

    if ($filterStatus === 'Reserved' || $filterStatus === 'Completed' || $filterStatus === 'Cancelled') {
        $sql .= ' AND t.status = :status';
    }

    $sql .= ' ORDER BY t.transaction_date DESC';

    $stmt = $connection->prepare($sql);
    $params = ['buyer_id' => $userId];
    if ($filterStatus === 'Reserved' || $filterStatus === 'Completed' || $filterStatus === 'Cancelled') {
        $params['status'] = $filterStatus;
    }
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $exception) {
    error_log('My Orders Fetch Error: ' . $exception->getMessage());
    $dbError = 'Unable to load reservations due to a database error.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View and manage your reserved marketplace items and campus pickup details on UniMarket.">
    <title>My Reservations | UniMarket</title>

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
    <nav class="main-nav" aria-label="My Orders Navigation">
        <div class="container">
            <button
                id="menu-toggle"
                class="menu-toggle"
                aria-label="Toggle Navigation Menu"
                aria-expanded="false"
                aria-controls="orders-menu">
                ☰
            </button>
            <ul id="orders-menu">
                <li>
                    <a href="dashboard.php">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="my_orders.php" class="active">
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
                        <h1>My Item Reservations</h1>
                        <p>Track your reserved items, campus meetup locations, and transaction status.</p>
                    </div>
                    <div>
                        <a href="marketplace.php" class="btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            Explore Marketplace
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

            <!-- Status Filter Tabs -->
            <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                <a href="my_orders.php" class="btn-outline <?php echo ($filterStatus === 'all') ? 'active' : ''; ?>" style="<?php echo ($filterStatus === 'all') ? 'background-color: var(--primary); color: var(--white);' : ''; ?> padding: 0.5rem 1rem; font-size: 0.85rem;">
                    All Reservations
                </a>
                <a href="my_orders.php?status=Reserved" class="btn-outline <?php echo ($filterStatus === 'Reserved') ? 'active' : ''; ?>" style="<?php echo ($filterStatus === 'Reserved') ? 'background-color: var(--primary); color: var(--white);' : ''; ?> padding: 0.5rem 1rem; font-size: 0.85rem;">
                    Pending Meetup (Reserved)
                </a>
                <a href="my_orders.php?status=Completed" class="btn-outline <?php echo ($filterStatus === 'Completed') ? 'active' : ''; ?>" style="<?php echo ($filterStatus === 'Completed') ? 'background-color: var(--primary); color: var(--white);' : ''; ?> padding: 0.5rem 1rem; font-size: 0.85rem;">
                    Completed
                </a>
                <a href="my_orders.php?status=Cancelled" class="btn-outline <?php echo ($filterStatus === 'Cancelled') ? 'active' : ''; ?>" style="<?php echo ($filterStatus === 'Cancelled') ? 'background-color: var(--primary); color: var(--white);' : ''; ?> padding: 0.5rem 1rem; font-size: 0.85rem;">
                    Cancelled
                </a>
            </div>

            <!-- Orders Container -->
            <?php if (!empty($orders)) : ?>
                <div class="my-listings-grid">
                    <?php foreach ($orders as $order) :
                        $tId         = (int) $order['transaction_id'];
                        $pId         = (int) $order['product_id'];
                        $pTitle      = htmlspecialchars((string)$order['product_title'], ENT_QUOTES, 'UTF-8');
                        $pCategory   = htmlspecialchars((string)$order['category_name'], ENT_QUOTES, 'UTF-8');
                        $tAmount     = number_format((float)$order['amount'], 2);
                        $tStatus     = htmlspecialchars((string)$order['trans_status'], ENT_QUOTES, 'UTF-8');
                        $tDate       = date('M j, Y • g:i A', strtotime((string)$order['transaction_date']));
                        $sellerName  = htmlspecialchars((string)$order['seller_name'], ENT_QUOTES, 'UTF-8');
                        $sellerDept  = htmlspecialchars((string)$order['seller_department'], ENT_QUOTES, 'UTF-8');
                        $sellerEmail = htmlspecialchars((string)$order['seller_email'], ENT_QUOTES, 'UTF-8');
                        $meetupPlace = htmlspecialchars(resolveCampusLocation($order['meetup_latitude'], $order['meetup_longitude']), ENT_QUOTES, 'UTF-8');

                        $pImg = !empty($order['image_url']) ? htmlspecialchars((string)$order['image_url'], ENT_QUOTES, 'UTF-8') : '../images/cat-textbooks.png';
                        if (!str_starts_with($pImg, 'http') && !str_starts_with($pImg, '../')) {
                            $pImg = '../' . ltrim($pImg, '/');
                        }
                    ?>
                        <article class="my-listing-card">
                            <div class="my-listing-img-box">
                                <img src="<?php echo $pImg; ?>" alt="<?php echo $pTitle; ?>" onerror="this.onerror=null; this.src='../images/cat-textbooks.png';">
                                <span class="status-badge status-<?php echo strtolower($tStatus); ?>">
                                    ● <?php echo $tStatus; ?>
                                </span>
                            </div>

                            <div class="my-listing-body">
                                <div class="my-listing-meta">
                                    <span class="badge-category"><?php echo $pCategory; ?></span>
                                    <span class="badge-condition">Reservation #<?php echo $tId; ?></span>
                                </div>

                                <h3 class="my-listing-title">
                                    <a href="product_detail.php?id=<?php echo $pId; ?>">
                                        <?php echo $pTitle; ?>
                                    </a>
                                </h3>

                                <div class="my-listing-price-row">
                                    <div class="my-listing-price">$<?php echo $tAmount; ?> <span>USD</span></div>
                                    <div class="my-listing-date"><?php echo $tDate; ?></div>
                                </div>

                                <!-- Meetup & Seller Info -->
                                <div style="background-color: var(--gray-50); border: 1px solid var(--gray-200); border-radius: var(--radius-sm); padding: 0.75rem; font-size: 0.85rem; display: flex; flex-direction: column; gap: 0.35rem;">
                                    <div>
                                        <strong style="color: var(--dark);">📍 Meetup Location:</strong>
                                        <div style="color: var(--primary); font-weight: 600; margin-top: 0.15rem;"><?php echo $meetupPlace; ?></div>
                                    </div>
                                    <div style="margin-top: 0.35rem;">
                                        <strong style="color: var(--dark);">👤 Seller:</strong> <?php echo $sellerName; ?> (<?php echo $sellerDept; ?>)
                                    </div>
                                    <div>
                                        <strong style="color: var(--dark);">✉️ Email:</strong>
                                        <a href="mailto:<?php echo $sellerEmail; ?>?subject=UniMarket%20Reservation%20%23<?php echo $tId; ?>%20(<?php echo rawurlencode($order['product_title']); ?>)" style="color: var(--primary); text-decoration: none;">
                                            <?php echo $sellerEmail; ?>
                                        </a>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="my-listing-actions">
                                    <a href="product_detail.php?id=<?php echo $pId; ?>" class="btn-action-edit">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        View Item
                                    </a>

                                    <?php if ($tStatus === 'Reserved') : ?>
                                        <form method="POST" action="../../backend/api/manage_transaction.php" onsubmit="return confirm('Are you sure you want to cancel your reservation for this item? The listing will be returned to the marketplace.');" style="margin: 0;">
                                            <input type="hidden" name="transaction_id" value="<?php echo $tId; ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="return_url" value="../../frontend/pages/my_orders.php">
                                            <button type="submit" class="btn-action-delete">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                Cancel
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

            <?php else : ?>
                <!-- Empty State -->
                <div class="empty-state-card">
                    <div class="empty-state-icon">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-xl"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    </div>
                    <h3>No Reservations Found</h3>
                    <p>You haven't reserved any items yet. Browse course textbooks, electronics, and dorm items from fellow students on campus.</p>
                    <a href="marketplace.php" class="btn-primary">
                        Browse Marketplace
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
