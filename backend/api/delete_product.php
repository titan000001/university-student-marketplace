<?php
/**
 * Delete Marketplace Listing API Endpoint
 * UniMarket - University Student Marketplace
 * Development Package: DP14 (Seller Listing Management)
 */

require_once __DIR__ . '/../config/database.php';

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Require authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../frontend/pages/login.php');
    exit();
}

// 2. Accept POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/pages/my_listings.php');
    exit();
}

$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$sellerId  = (int) $_SESSION['user_id'];

if ($productId <= 0) {
    $_SESSION['flash_error'] = 'Invalid product ID specified.';
    header('Location: ../../frontend/pages/my_listings.php');
    exit();
}

try {
    $database = new Database();
    $connection = $database->connect();

    // 3. Verify product exists and is owned strictly by the authenticated student
    $checkStmt = $connection->prepare('
        SELECT product_id, title FROM products WHERE product_id = :product_id AND seller_id = :seller_id LIMIT 1
    ');
    $checkStmt->execute([
        'product_id' => $productId,
        'seller_id'  => $sellerId
    ]);
    $product = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $_SESSION['flash_error'] = 'Listing not found or you do not have permission to delete it.';
        header('Location: ../../frontend/pages/my_listings.php');
        exit();
    }

    // 4. Inspect foreign key relationships (transactions.product_id -> products.product_id ON DELETE RESTRICT)
    $transStmt = $connection->prepare('
        SELECT COUNT(*) FROM transactions WHERE product_id = :product_id
    ');
    $transStmt->execute(['product_id' => $productId]);
    $transCount = (int) $transStmt->fetchColumn();

    if ($transCount > 0) {
        // Prevent hard deletion to honor foreign key constraint integrity
        $_SESSION['flash_error'] = 'This listing has associated transaction records and cannot be permanently deleted. You may change its status to "Sold" or "Reserved" instead.';
        header('Location: ../../frontend/pages/my_listings.php');
        exit();
    }

    // 5. Safe hard deletion for un-transacted listings
    $delStmt = $connection->prepare('
        DELETE FROM products WHERE product_id = :product_id AND seller_id = :seller_id
    ');
    $delStmt->execute([
        'product_id' => $productId,
        'seller_id'  => $sellerId
    ]);

    $_SESSION['flash_success'] = 'Listing "' . htmlspecialchars($product['title'], ENT_QUOTES, 'UTF-8') . '" was deleted successfully.';
    header('Location: ../../frontend/pages/my_listings.php');
    exit();

} catch (PDOException $exception) {
    error_log('Delete Product Exception: ' . $exception->getMessage());
    $_SESSION['flash_error'] = 'A database error occurred while deleting the listing. Please try again.';
    header('Location: ../../frontend/pages/my_listings.php');
    exit();
}
