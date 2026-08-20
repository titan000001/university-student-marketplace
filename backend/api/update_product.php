<?php
/**
 * Update Marketplace Listing API Endpoint
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

// 3. Extract and sanitize inputs
$productIdRaw     = isset($_POST['product_id'])        ? trim((string) $_POST['product_id'])        : '';
$title            = isset($_POST['title'])             ? trim((string) $_POST['title'])             : '';
$description      = isset($_POST['description'])       ? trim((string) $_POST['description'])       : '';
$priceRaw         = isset($_POST['price'])             ? trim((string) $_POST['price'])             : '';
$categoryIdRaw    = isset($_POST['category_id'])       ? trim((string) $_POST['category_id'])       : '';
$productCondition = isset($_POST['product_condition']) ? trim((string) $_POST['product_condition']) : '';
$tags             = isset($_POST['tags'])              ? trim((string) $_POST['tags'])              : '';
$imageUrl         = isset($_POST['image_url'])         ? trim((string) $_POST['image_url'])         : '';
$status           = isset($_POST['status'])            ? trim((string) $_POST['status'])            : 'Available';

$productId  = (int) $productIdRaw;
$categoryId = (int) $categoryIdRaw;
$price      = is_numeric($priceRaw) ? (float) $priceRaw : -1.0;

// Authoritative seller identity MUST come exclusively from session
$sellerId = (int) $_SESSION['user_id'];

// 4. Validate product ID
if ($productId <= 0) {
    $_SESSION['flash_error'] = 'Invalid product ID specified.';
    header('Location: ../../frontend/pages/my_listings.php');
    exit();
}

// 5. Server-Side Input Validation
$allowedConditions = ['New', 'Like New', 'Good', 'Fair'];
$allowedStatuses   = ['Available', 'Active', 'Reserved', 'Sold'];

$validationError = '';

if (empty($title)) {
    $validationError = 'Product title is required.';
} elseif (strlen($title) < 3 || strlen($title) > 150) {
    $validationError = 'Product title must be between 3 and 150 characters in length.';
} elseif ($categoryId <= 0) {
    $validationError = 'Please select a valid product category.';
} elseif ($price <= 0 || $price > 999999.99) {
    $validationError = 'Price must be a positive numeric value (e.g. 250.00).';
} elseif (empty($productCondition) || !in_array($productCondition, $allowedConditions, true)) {
    $validationError = 'Please select a valid product condition (New, Like New, Good, or Fair).';
} elseif (!in_array($status, $allowedStatuses, true)) {
    $validationError = 'Please select a valid status (Available, Reserved, or Sold).';
} elseif (strlen($tags) > 255) {
    $validationError = 'Tags must not exceed 255 characters in length.';
} elseif (strlen($imageUrl) > 255) {
    $validationError = 'Image URL must not exceed 255 characters in length.';
}

if (!empty($validationError)) {
    $_SESSION['product_edit_draft'] = [
        'title'             => $title,
        'description'       => $description,
        'price'             => $priceRaw,
        'category_id'       => $categoryIdRaw,
        'product_condition' => $productCondition,
        'tags'              => $tags,
        'image_url'         => $imageUrl,
        'status'            => $status,
    ];
    $_SESSION['flash_error'] = $validationError;
    header('Location: ../../frontend/pages/edit_product.php?id=' . $productId);
    exit();
}

try {
    $database = new Database();
    $connection = $database->connect();

    // 6. Verify category exists
    $catStmt = $connection->prepare('SELECT category_id FROM categories WHERE category_id = :category_id LIMIT 1');
    $catStmt->execute(['category_id' => $categoryId]);
    if (!$catStmt->fetch()) {
        $_SESSION['product_edit_draft'] = [
            'title'             => $title,
            'description'       => $description,
            'price'             => $priceRaw,
            'category_id'       => $categoryIdRaw,
            'product_condition' => $productCondition,
            'tags'              => $tags,
            'image_url'         => $imageUrl,
            'status'            => $status,
        ];
        $_SESSION['flash_error'] = 'Selected category does not exist.';
        header('Location: ../../frontend/pages/edit_product.php?id=' . $productId);
        exit();
    }

    // 7. Verify listing exists and belongs strictly to the authenticated seller
    $checkStmt = $connection->prepare('
        SELECT product_id FROM products WHERE product_id = :product_id AND seller_id = :seller_id LIMIT 1
    ');
    $checkStmt->execute([
        'product_id' => $productId,
        'seller_id'  => $sellerId
    ]);
    if (!$checkStmt->fetch()) {
        $_SESSION['flash_error'] = 'Listing not found or you do not have permission to modify it.';
        header('Location: ../../frontend/pages/my_listings.php');
        exit();
    }

    // Default image fallback if left empty
    if (empty($imageUrl)) {
        $imageUrl = 'images/cat-textbooks.png';
    }

    // Standardize 'Active' status to 'Available'
    $dbStatus = ($status === 'Active') ? 'Available' : $status;

    // 8. Prepared UPDATE statement enforcing seller_id
    $updateStmt = $connection->prepare('
        UPDATE products
        SET category_id       = :category_id,
            title             = :title,
            description       = :description,
            price             = :price,
            tags              = :tags,
            image_url         = :image_url,
            product_condition = :product_condition,
            status            = :status
        WHERE product_id = :product_id AND seller_id = :seller_id
    ');

    $updateStmt->execute([
        'category_id'       => $categoryId,
        'title'             => $title,
        'description'       => empty($description) ? null : $description,
        'price'             => round($price, 2),
        'tags'              => empty($tags) ? null : $tags,
        'image_url'         => $imageUrl,
        'product_condition' => $productCondition,
        'status'            => $dbStatus,
        'product_id'        => $productId,
        'seller_id'         => $sellerId,
    ]);

    unset($_SESSION['product_edit_draft']);
    $_SESSION['flash_success'] = 'Listing updated successfully!';
    header('Location: ../../frontend/pages/my_listings.php');
    exit();

} catch (PDOException $exception) {
    error_log('Update Product Exception: ' . $exception->getMessage());
    $_SESSION['flash_error'] = 'A database error occurred while updating your listing. Please try again.';
    header('Location: ../../frontend/pages/edit_product.php?id=' . $productId);
    exit();
}
