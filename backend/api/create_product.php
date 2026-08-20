<?php
/**
 * Product Listing Creation API Endpoint
 * UniMarket - University Student Marketplace
 * Development Package: DP13-A / DP13-B
 */

require_once __DIR__ . '/../config/database.php';

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Require authentication: redirect unauthenticated users to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../frontend/pages/login.php');
    exit();
}

// 2. Accept POST requests only: redirect non-POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/pages/create_product.php');
    exit();
}

// 3. Extract and trim inputs safely
$title            = isset($_POST['title'])             ? trim((string) $_POST['title'])             : '';
$description      = isset($_POST['description'])       ? trim((string) $_POST['description'])       : '';
$priceRaw         = isset($_POST['price'])             ? trim((string) $_POST['price'])             : '';
$categoryIdRaw    = isset($_POST['category_id'])       ? trim((string) $_POST['category_id'])       : '';
$productCondition = isset($_POST['product_condition']) ? trim((string) $_POST['product_condition']) : '';
$tags             = isset($_POST['tags'])              ? trim((string) $_POST['tags'])              : '';
$imageUrl         = isset($_POST['image_url'])         ? trim((string) $_POST['image_url'])         : '';

// 4. Server-Side Input Validation
$validationError = '';
$categoryId = (int) $categoryIdRaw;
$price = is_numeric($priceRaw) ? (float) $priceRaw : -1.0;

$allowedConditions = ['New', 'Like New', 'Good', 'Fair'];

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
} elseif (strlen($tags) > 255) {
    $validationError = 'Tags must not exceed 255 characters in length.';
} elseif (strlen($imageUrl) > 255) {
    $validationError = 'Image URL must not exceed 255 characters in length.';
}

if (!empty($validationError)) {
    // Preserve form draft so typed entries are retained on validation error
    $_SESSION['product_form_draft'] = [
        'title'             => $title,
        'description'       => $description,
        'price'             => $priceRaw,
        'category_id'       => $categoryIdRaw,
        'product_condition' => $productCondition,
        'tags'              => $tags,
        'image_url'         => $imageUrl,
    ];
    $_SESSION['flash_error'] = $validationError;
    header('Location: ../../frontend/pages/create_product.php');
    exit();
}

// 5. Authoritative seller identity MUST come exclusively from session
$sellerId = (int) $_SESSION['user_id'];

try {
    $database = new Database();
    $connection = $database->connect();

    // 6. Validate category exists in database
    $categoryStmt = $connection->prepare(
        'SELECT category_id FROM categories WHERE category_id = :category_id LIMIT 1'
    );
    $categoryStmt->execute(['category_id' => $categoryId]);
    if (!$categoryStmt->fetch()) {
        $_SESSION['product_form_draft'] = [
            'title'             => $title,
            'description'       => $description,
            'price'             => $priceRaw,
            'category_id'       => $categoryIdRaw,
            'product_condition' => $productCondition,
            'tags'              => $tags,
            'image_url'         => $imageUrl,
        ];
        $_SESSION['flash_error'] = 'Selected category does not exist. Please select a valid category.';
        header('Location: ../../frontend/pages/create_product.php');
        exit();
    }

    // Default image fallback if left empty
    if (empty($imageUrl)) {
        $imageUrl = 'images/cat-textbooks.png';
    }

    // 7. Prepared INSERT statement for product creation
    $insertStmt = $connection->prepare(
        'INSERT INTO products (seller_id, category_id, title, description, price, tags, image_url, product_condition, status)
         VALUES (:seller_id, :category_id, :title, :description, :price, :tags, :image_url, :product_condition, "Available")'
    );

    $insertStmt->execute([
        'seller_id'         => $sellerId,
        'category_id'       => $categoryId,
        'title'             => $title,
        'description'       => empty($description) ? null : $description,
        'price'             => round($price, 2),
        'tags'              => empty($tags) ? null : $tags,
        'image_url'         => $imageUrl,
        'product_condition' => $productCondition,
    ]);

    // Clear form draft on success
    unset($_SESSION['product_form_draft']);

    // Set flash success message and redirect to dashboard
    $_SESSION['flash_success'] = 'Marketplace listing created successfully!';
    header('Location: ../../frontend/pages/dashboard.php');
    exit();

} catch (PDOException $exception) {
    error_log('Create Product Exception: ' . $exception->getMessage());
    $_SESSION['flash_error'] = 'A database error occurred while creating your listing. Please try again.';
    header('Location: ../../frontend/pages/create_product.php');
    exit();
}
