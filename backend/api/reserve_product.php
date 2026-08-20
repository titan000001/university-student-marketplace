<?php
/**
 * Product Reservation API Endpoint
 * UniMarket - University Student Marketplace
 * Development Package: DP15 (Buyer Reservation & Campus Meetup)
 */

require_once __DIR__ . '/../config/database.php';

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Require authentication
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_error'] = 'Please sign in to your student account to reserve items.';
    header('Location: ../../frontend/pages/login.php');
    exit();
}

// 2. Accept POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/pages/marketplace.php');
    exit();
}

$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$buyerId   = (int) $_SESSION['user_id'];

// Retrieve meetup location coordinates
$meetupLat = isset($_POST['meetup_latitude']) && is_numeric($_POST['meetup_latitude']) ? (float) $_POST['meetup_latitude'] : null;
$meetupLng = isset($_POST['meetup_longitude']) && is_numeric($_POST['meetup_longitude']) ? (float) $_POST['meetup_longitude'] : null;

// Validate product ID
if ($productId <= 0) {
    $_SESSION['flash_error'] = 'Invalid product ID specified.';
    header('Location: ../../frontend/pages/marketplace.php');
    exit();
}

// Default fallback coordinates (Central Library Lobby) if coordinates not specified
if ($meetupLat === null || $meetupLng === null) {
    $meetupLat = 23.77717600;
    $meetupLng = 90.39945200;
}

try {
    $database = new Database();
    $connection = $database->connect();

    // 3. Begin atomic database transaction with row-level lock
    $connection->beginTransaction();

    // Fetch product details locking row to prevent race conditions
    $prodStmt = $connection->prepare('
        SELECT product_id, seller_id, title, price, status
        FROM products
        WHERE product_id = :product_id
        FOR UPDATE
    ');
    $prodStmt->execute(['product_id' => $productId]);
    $product = $prodStmt->fetch(PDO::FETCH_ASSOC);

    // Validate product existence
    if (!$product) {
        $connection->rollBack();
        $_SESSION['flash_error'] = 'The requested product listing does not exist.';
        header('Location: ../../frontend/pages/marketplace.php');
        exit();
    }

    $sellerId = (int) $product['seller_id'];
    $price    = (float) $product['price'];
    $pStatus  = (string) $product['status'];

    // 4. Reject self-reservation (a student cannot buy/reserve their own listing)
    if ($buyerId === $sellerId) {
        $connection->rollBack();
        $_SESSION['flash_error'] = 'You cannot reserve your own product listing.';
        header('Location: ../../frontend/pages/product_detail.php?id=' . $productId);
        exit();
    }

    // 5. Verify product is currently Available/Active
    if ($pStatus !== 'Available' && $pStatus !== 'Active') {
        $connection->rollBack();
        $_SESSION['flash_error'] = 'This item is no longer available for reservation (Status: ' . htmlspecialchars($pStatus, ENT_QUOTES, 'UTF-8') . ').';
        header('Location: ../../frontend/pages/product_detail.php?id=' . $productId);
        exit();
    }

    // 6. Verify no other pending reservation exists for this product
    $activeTransStmt = $connection->prepare('
        SELECT transaction_id FROM transactions
        WHERE product_id = :product_id AND status = "Reserved"
        LIMIT 1
    ');
    $activeTransStmt->execute(['product_id' => $productId]);
    if ($activeTransStmt->fetch()) {
        $connection->rollBack();
        $_SESSION['flash_error'] = 'This item has already been reserved by another student.';
        header('Location: ../../frontend/pages/product_detail.php?id=' . $productId);
        exit();
    }

    // 7. Create the transaction record
    $insertTransStmt = $connection->prepare('
        INSERT INTO transactions (product_id, buyer_id, seller_id, amount, meetup_latitude, meetup_longitude, status)
        VALUES (:product_id, :buyer_id, :seller_id, :amount, :meetup_latitude, :meetup_longitude, "Reserved")
    ');
    $insertTransStmt->execute([
        'product_id'        => $productId,
        'buyer_id'          => $buyerId,
        'seller_id'         => $sellerId,
        'amount'            => $price,
        'meetup_latitude'   => $meetupLat,
        'meetup_longitude'  => $meetupLng
    ]);

    // 8. Update product status to 'Reserved'
    $updateProdStmt = $connection->prepare('
        UPDATE products SET status = "Reserved" WHERE product_id = :product_id
    ');
    $updateProdStmt->execute(['product_id' => $productId]);

    // Commit atomic transaction
    $connection->commit();

    $_SESSION['flash_success'] = 'Item "' . htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8') . '" was reserved successfully! Meetup details are available in your reservations.';
    header('Location: ../../frontend/pages/my_orders.php');
    exit();

} catch (PDOException $exception) {
    if (isset($connection) && $connection->inTransaction()) {
        $connection->rollBack();
    }
    error_log('Reservation Error: ' . $exception->getMessage());
    $_SESSION['flash_error'] = 'A database error occurred while reserving the item. Please try again.';
    header('Location: ../../frontend/pages/product_detail.php?id=' . $productId);
    exit();
}
