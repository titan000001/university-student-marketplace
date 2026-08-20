<?php
/**
 * Update Product Listing Status API Endpoint
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
$status    = isset($_POST['status'])     ? trim((string) $_POST['status']) : '';
$sellerId  = (int) $_SESSION['user_id'];

$allowedStatuses = ['Available', 'Active', 'Reserved', 'Sold'];

if ($productId <= 0 || !in_array($status, $allowedStatuses, true)) {
    $_SESSION['flash_error'] = 'Invalid product ID or status value.';
    header('Location: ../../frontend/pages/my_listings.php');
    exit();
}

// Normalize 'Active' to 'Available'
$dbStatus = ($status === 'Active') ? 'Available' : $status;

try {
    $database = new Database();
    $connection = $database->connect();

    // 3. Verify ownership and update status in single prepared statement
    $stmt = $connection->prepare('
        UPDATE products
        SET status = :status
        WHERE product_id = :product_id AND seller_id = :seller_id
    ');
    $stmt->execute([
        'status'     => $dbStatus,
        'product_id' => $productId,
        'seller_id'  => $sellerId
    ]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['flash_success'] = "Listing status updated to \"$dbStatus\".";
    } else {
        // Check if listing belongs to another user vs already has that status
        $check = $connection->prepare('SELECT status FROM products WHERE product_id = :product_id AND seller_id = :seller_id LIMIT 1');
        $check->execute(['product_id' => $productId, 'seller_id' => $sellerId]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $_SESSION['flash_error'] = 'Listing not found or you do not have permission to modify it.';
        } else {
            $_SESSION['flash_success'] = "Listing status is already \"$dbStatus\".";
        }
    }

    header('Location: ../../frontend/pages/my_listings.php');
    exit();

} catch (PDOException $exception) {
    error_log('Update Product Status Exception: ' . $exception->getMessage());
    $_SESSION['flash_error'] = 'A database error occurred while updating status. Please try again.';
    header('Location: ../../frontend/pages/my_listings.php');
    exit();
}
