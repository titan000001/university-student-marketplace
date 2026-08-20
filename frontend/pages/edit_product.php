<?php
/**
 * Edit Marketplace Listing Page
 * UniMarket - University Student Marketplace
 * Development Package: DP14 (Seller Listing Management)
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

// Validate product ID from query parameter
$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($productId <= 0) {
    $_SESSION['flash_error'] = 'Invalid product ID specified.';
    header('Location: my_listings.php');
    exit();
}

$product = null;
$categories = [];

try {
    $database = new Database();
    $connection = $database->connect();

    // 1. Fetch categories for dropdown
    $catStmt = $connection->query(
        'SELECT category_id, category_name FROM categories ORDER BY category_name ASC'
    );
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Fetch product enforcing STRICT ownership: product_id = :id AND seller_id = :seller_id
    $prodStmt = $connection->prepare('
        SELECT product_id, seller_id, category_id, title, description, price,
               tags, image_url, product_condition, status
        FROM products
        WHERE product_id = :product_id AND seller_id = :seller_id
        LIMIT 1
    ');
    $prodStmt->execute([
        'product_id' => $productId,
        'seller_id'  => $userId
    ]);
    $product = $prodStmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $exception) {
    error_log('Edit Product Fetch Error: ' . $exception->getMessage());
    $_SESSION['flash_error'] = 'A database error occurred while fetching product details.';
    header('Location: my_listings.php');
    exit();
}

// IDOR & Existence Protection: If product not found or not owned by user, deny access
if (!$product) {
    $_SESSION['flash_error'] = 'Listing not found or you do not have permission to edit it.';
    header('Location: my_listings.php');
    exit();
}

// Preserve form draft values if returning from a validation error
$draft = isset($_SESSION['product_edit_draft']) && is_array($_SESSION['product_edit_draft'])
    ? $_SESSION['product_edit_draft']
    : [];
unset($_SESSION['product_edit_draft']);

// Retrieve single-use flash error and success messages
$successMessage = isset($_SESSION['flash_success']) ? trim((string) $_SESSION['flash_success']) : '';
$errorMessage   = isset($_SESSION['flash_error'])   ? trim((string) $_SESSION['flash_error'])   : '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Form values with draft fallback
$titleVal            = htmlspecialchars((string) ($draft['title'] ?? $product['title']), ENT_QUOTES, 'UTF-8');
$descriptionVal      = htmlspecialchars((string) ($draft['description'] ?? $product['description'] ?? ''), ENT_QUOTES, 'UTF-8');
$priceVal            = htmlspecialchars((string) ($draft['price'] ?? $product['price']), ENT_QUOTES, 'UTF-8');
$categoryIdVal       = (int) ($draft['category_id'] ?? $product['category_id']);
$productConditionVal = htmlspecialchars((string) ($draft['product_condition'] ?? $product['product_condition']), ENT_QUOTES, 'UTF-8');
$tagsVal             = htmlspecialchars((string) ($draft['tags'] ?? $product['tags'] ?? ''), ENT_QUOTES, 'UTF-8');
$imageUrlVal         = htmlspecialchars((string) ($draft['image_url'] ?? $product['image_url'] ?? ''), ENT_QUOTES, 'UTF-8');
$statusVal           = htmlspecialchars((string) ($draft['status'] ?? $product['status']), ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Edit your marketplace product listing on UniMarket.">
    <title>Edit Listing | UniMarket</title>

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
    <nav class="main-nav" aria-label="Edit Product Navigation">
        <div class="container">
            <button
                id="menu-toggle"
                class="menu-toggle"
                aria-label="Toggle Navigation Menu"
                aria-expanded="false"
                aria-controls="edit-menu">
                ☰
            </button>
            <ul id="edit-menu">
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

            <!-- Banner Section -->
            <section class="dashboard-header">
                <div class="dashboard-welcome-text">
                    <h1>Edit Listing</h1>
                    <p>Update item specifications, adjust price, or modify availability status for this listing.</p>
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

            <!-- Edit Form Card -->
            <section class="dashboard-card">
                <h2>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon" style="color: var(--primary);"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Modify Listing Details
                </h2>

                <form method="POST" action="../../backend/api/update_product.php" class="registration-form" style="max-width: 100%; margin: 1.5rem 0 0 0;" novalidate>

                    <!-- Hidden Product ID -->
                    <input type="hidden" name="product_id" value="<?php echo $productId; ?>">

                    <!-- Title -->
                    <div class="form-group">
                        <label for="title">Product Title <span style="color: var(--danger);">*</span></label>
                        <div class="input-icon-group">
                            <input
                                type="text"
                                id="title"
                                name="title"
                                value="<?php echo $titleVal; ?>"
                                placeholder="e.g. Data Structures & Algorithms Textbook (10th Ed)"
                                required>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20V2H6.5A2.5 2.5 0 0 0 4 4.5v15z"/></svg>
                        </div>
                    </div>

                    <!-- Category & Price Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                        <!-- Category -->
                        <div class="form-group">
                            <label for="category_id">Category <span style="color: var(--danger);">*</span></label>
                            <div class="input-icon-group">
                                <select id="category_id" name="category_id" required style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-family: var(--font-main); font-size: 0.95rem; background-color: var(--white); color: var(--dark);">
                                    <option value="">-- Select Category --</option>
                                    <?php foreach ($categories as $cat) : ?>
                                        <option value="<?php echo (int)$cat['category_id']; ?>" <?php echo ($categoryIdVal === (int)$cat['category_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['category_name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="form-group">
                            <label for="price">Price (৳ BDT) <span style="color: var(--danger);">*</span></label>
                            <div class="input-icon-group">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    id="price"
                                    name="price"
                                    value="<?php echo $priceVal; ?>"
                                    placeholder="e.g. 450.00"
                                    required>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Condition & Status Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                        <!-- Condition -->
                        <div class="form-group">
                            <label for="product_condition">Item Condition <span style="color: var(--danger);">*</span></label>
                            <div class="input-icon-group">
                                <select id="product_condition" name="product_condition" required style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-family: var(--font-main); font-size: 0.95rem; background-color: var(--white); color: var(--dark);">
                                    <option value="New" <?php echo ($productConditionVal === 'New') ? 'selected' : ''; ?>>New (Unopened/Unused)</option>
                                    <option value="Like New" <?php echo ($productConditionVal === 'Like New') ? 'selected' : ''; ?>>Like New (Flawless condition)</option>
                                    <option value="Good" <?php echo ($productConditionVal === 'Good') ? 'selected' : ''; ?>>Good (Minor signs of use)</option>
                                    <option value="Fair" <?php echo ($productConditionVal === 'Fair') ? 'selected' : ''; ?>>Fair (Noticeable wear but fully functional)</option>
                                </select>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                        </div>

                        <!-- Listing Status -->
                        <div class="form-group">
                            <label for="status">Listing Status <span style="color: var(--danger);">*</span></label>
                            <div class="input-icon-group">
                                <select id="status" name="status" required style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-family: var(--font-main); font-size: 0.95rem; background-color: var(--white); color: var(--dark);">
                                    <option value="Available" <?php echo ($statusVal === 'Available' || $statusVal === 'Active') ? 'selected' : ''; ?>>Available (Active in Catalog)</option>
                                    <option value="Reserved" <?php echo ($statusVal === 'Reserved') ? 'selected' : ''; ?>>Reserved (Deal Pending)</option>
                                    <option value="Sold" <?php echo ($statusVal === 'Sold') ? 'selected' : ''; ?>>Sold (Item Exchanged)</option>
                                </select>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description">Detailed Description</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            placeholder="Describe condition, edition, specifications, or campus pickup preferences..."
                            style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-family: var(--font-main); font-size: 0.95rem; resize: vertical;"><?php echo $descriptionVal; ?></textarea>
                    </div>

                    <!-- Tags -->
                    <div class="form-group">
                        <label for="tags">Search Tags</label>
                        <div class="input-icon-group">
                            <input
                                type="text"
                                id="tags"
                                name="tags"
                                value="<?php echo $tagsVal; ?>"
                                placeholder="Comma separated, e.g. cse, algorithms, textbook">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        </div>
                    </div>

                    <!-- Image URL -->
                    <div class="form-group">
                        <label for="image_url">Image Asset Path / URL</label>
                        <div class="input-icon-group">
                            <input
                                type="text"
                                id="image_url"
                                name="image_url"
                                value="<?php echo $imageUrlVal; ?>"
                                placeholder="e.g. images/cat-textbooks.png">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </div>
                    </div>

                    <!-- Submit & Cancel Actions -->
                    <div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                        <button type="submit" class="btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Save Changes
                        </button>
                        <a href="my_listings.php" class="btn-outline">
                            Cancel
                        </a>
                    </div>

                </form>
            </section>

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
