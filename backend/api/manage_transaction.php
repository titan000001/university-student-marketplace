<?php
/**
 * Transaction Lifecycle Management API Endpoint
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
    header('Location: ../../frontend/pages/login.php');
    exit();
}

// 2. Accept POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/pages/my_orders.php');
    exit();
}

$transactionId = isset($_POST['transaction_id']) ? (int) $_POST['transaction_id'] : 0;
$action        = isset($_POST['action'])         ? trim((string) $_POST['action']) : '';
$userId        = (int) $_SESSION['user_id'];
$returnUrl     = isset($_POST['return_url'])     ? trim((string) $_POST['return_url']) : '../../frontend/pages/my_orders.php';

// Sanitize returnUrl to prevent open redirects
if (!str_contains($returnUrl, 'my_orders.php') && !str_contains($returnUrl, 'my_listings.php') && !str_contains($returnUrl, 'dashboard.php')) {
    $returnUrl = '../../frontend/pages/my_orders.php';
}

if ($transactionId <= 0 || ($action !== 'complete' && $action !== 'cancel')) {
    $_SESSION['flash_error'] = 'Invalid transaction request parameters.';
    header('Location: ' . $returnUrl);
    exit();
}

try {
    $database = new Database();
    $connection = $database->connect();

    // 3. Begin atomic transaction
    $connection->beginTransaction();

    // Fetch transaction with row lock
    $stmt = $connection->prepare('
        SELECT t.transaction_id, t.product_id, t.buyer_id, t.seller_id, t.status AS trans_status,
               p.title AS product_title, p.status AS product_status
        FROM transactions t
        JOIN products p ON t.product_id = p.product_id
        WHERE t.transaction_id = :transaction_id
        FOR UPDATE
    ');
    $stmt->execute(['transaction_id' => $transactionId]);
    $trans = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trans) {
        $connection->rollBack();
        $_SESSION['flash_error'] = 'Transaction record not found.';
        header('Location: ' . $returnUrl);
        exit();
    }

    $buyerId  = (int) $trans['buyer_id'];
    $sellerId = (int) $trans['seller_id'];
    $prodId   = (int) $trans['product_id'];
    $tStatus  = (string) $trans['trans_status'];

    if ($tStatus !== 'Reserved') {
        $connection->rollBack();
        $_SESSION['flash_error'] = 'This transaction is already ' . htmlspecialchars($tStatus, ENT_QUOTES, 'UTF-8') . ' and cannot be modified.';
        header('Location: ' . $returnUrl);
        exit();
    }

    // 4. Process Cancel Action (Permitted for Buyer or Seller)
    if ($action === 'cancel') {
        if ($userId !== $buyerId && $userId !== $sellerId) {
            $connection->rollBack();
            $_SESSION['flash_error'] = 'You do not have authorization to cancel this transaction.';
            header('Location: ' . $returnUrl);
            exit();
        }

        // Update transaction status to Cancelled
        $updateTrans = $connection->prepare('
            UPDATE transactions SET status = "Cancelled" WHERE transaction_id = :transaction_id
        ');
        $updateTrans->execute(['transaction_id' => $transactionId]);

        // Restore product status to Available (if no other active reservation exists)
        $updateProd = $connection->prepare('
            UPDATE products SET status = "Available" WHERE product_id = :product_id
        ');
        $updateProd->execute(['product_id' => $prodId]);

        $connection->commit();
        $_SESSION['flash_success'] = 'Reservation for "' . htmlspecialchars($trans['product_title'], ENT_QUOTES, 'UTF-8') . '" has been cancelled. The item is now available in the marketplace.';
        header('Location: ' . $returnUrl);
        exit();
    }

    // 5. Process Complete Action (Permitted strictly for Seller)
    if ($action === 'complete') {
        if ($userId !== $sellerId) {
            $connection->rollBack();
            $_SESSION['flash_error'] = 'Only the listing seller can confirm transaction completion.';
            header('Location: ' . $returnUrl);
            exit();
        }

        // Update transaction status to Completed
        $updateTrans = $connection->prepare('
            UPDATE transactions SET status = "Completed" WHERE transaction_id = :transaction_id
        ');
        $updateTrans->execute(['transaction_id' => $transactionId]);

        // Update product status to Sold
        $updateProd = $connection->prepare('
            UPDATE products SET status = "Sold" WHERE product_id = :product_id
        ');
        $updateProd->execute(['product_id' => $prodId]);

        $connection->commit();
        $_SESSION['flash_success'] = 'Deal completed! Transaction has been marked as Completed and listing marked as Sold.';
        header('Location: ' . $returnUrl);
        exit();
    }

} catch (PDOException $exception) {
    if (isset($connection) && $connection->inTransaction()) {
        $connection->rollBack();
    }
    error_log('Transaction Manage Error: ' . $exception->getMessage());
    $_SESSION['flash_error'] = 'A database error occurred while updating the transaction.';
    header('Location: ' . $returnUrl);
    exit();
}
