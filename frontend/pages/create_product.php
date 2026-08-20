<?php
/**
 * Create Marketplace Listing Page
 * UniMarket - University Student Marketplace
 * Development Package: DP13-A / DP13-B
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

// Fetch active categories dynamically from database using PDO prepared query
$categories = [];
$catFetchError = '';

try {
    $database = new Database();
    $connection = $database->connect();

    $statement = $connection->query(
        'SELECT category_id, category_name, description FROM categories ORDER BY category_name ASC'
    );
    $categories = $statement->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    error_log('Categories Fetch Error: ' . $exception->getMessage());
    $catFetchError = 'Unable to load categories from database.';
}

// Preserve form draft values if returning from a validation error
$draft = isset($_SESSION['product_form_draft']) && is_array($_SESSION['product_form_draft'])
    ? $_SESSION['product_form_draft']
    : [];
unset($_SESSION['product_form_draft']);

// Retrieve single-use flash error and success messages
$successMessage = isset($_SESSION['flash_success']) ? trim((string) $_SESSION['flash_success']) : '';
$errorMessage   = isset($_SESSION['flash_error'])   ? trim((string) $_SESSION['flash_error'])   : '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Escape draft input values for HTML output safety
$titleDraft            = htmlspecialchars((string) ($draft['title'] ?? ''), ENT_QUOTES, 'UTF-8');
$descriptionDraft      = htmlspecialchars((string) ($draft['description'] ?? ''), ENT_QUOTES, 'UTF-8');
$priceDraft            = htmlspecialchars((string) ($draft['price'] ?? ''), ENT_QUOTES, 'UTF-8');
$categoryIdDraft       = (int) ($draft['category_id'] ?? 0);
$productConditionDraft = htmlspecialchars((string) ($draft['product_condition'] ?? ''), ENT_QUOTES, 'UTF-8');
$tagsDraft             = htmlspecialchars((string) ($draft['tags'] ?? ''), ENT_QUOTES, 'UTF-8');
$imageUrlDraft         = htmlspecialchars((string) ($draft['image_url'] ?? ''), ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Post a product listing on UniMarket to buy, sell, or trade items with fellow university students.">
    <title>Post New Item | UniMarket</title>

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
    <nav class="main-nav" aria-label="Create Product Navigation">
        <div class="container">
            <button
                id="menu-toggle"
                class="menu-toggle"
                aria-label="Toggle Navigation Menu"
                aria-expanded="false"
                aria-controls="product-menu">
                ☰
            </button>
            <ul id="product-menu">
                <li>
                    <a href="dashboard.php">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="create_product.php" class="active">
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
                    <h1>Post a New Item for Sale</h1>
                    <p>Create a verified student listing to reach buyers on your university campus.</p>
                </div>
            </section>

            <!-- Feedback Alerts -->
            <?php if (!empty($successMessage)) : ?>
                <div class="dashboard-card" style="border-left: 4px solid var(--success); background-color: rgba(22, 163, 74, 0.05);">
                    <p style="color: var(--success); font-weight: 600; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (!empty($errorMessage)) : ?>
                <div class="dashboard-card" style="border-left: 4px solid var(--danger); background-color: rgba(220, 38, 38, 0.05);">
                    <p style="color: var(--danger); font-weight: 600; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Listing Form Card -->
            <section class="dashboard-card">
                <h2>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon" style="color: var(--primary);"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Listing Details
                </h2>

                <form method="POST" action="../../backend/api/create_product.php" class="registration-form" style="max-width: 100%; margin: 1.5rem 0 0 0;" novalidate>

                    <!-- Title -->
                    <div class="form-group">
                        <label for="title">Product Title <span style="color: var(--danger);">*</span></label>
                        <div class="input-icon-group">
                            <input
                                type="text"
                                id="title"
                                name="title"
                                value="<?php echo $titleDraft; ?>"
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
                                        <option value="<?php echo (int)$cat['category_id']; ?>" <?php echo ($categoryIdDraft === (int)$cat['category_id']) ? 'selected' : ''; ?>>
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
                                    value="<?php echo $priceDraft; ?>"
                                    placeholder="e.g. 450.00"
                                    required>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Condition & Tags Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
                        <!-- Condition -->
                        <div class="form-group">
                            <label for="product_condition">Item Condition <span style="color: var(--danger);">*</span></label>
                            <div class="input-icon-group">
                                <select id="product_condition" name="product_condition" required style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-family: var(--font-main); font-size: 0.95rem; background-color: var(--white); color: var(--dark);">
                                    <option value="">-- Select Condition --</option>
                                    <option value="New" <?php echo ($productConditionDraft === 'New') ? 'selected' : ''; ?>>New (Unopened / Unused)</option>
                                    <option value="Like New" <?php echo ($productConditionDraft === 'Like New') ? 'selected' : ''; ?>>Like New (Minimal use, no flaws)</option>
                                    <option value="Good" <?php echo ($productConditionDraft === 'Good') ? 'selected' : ''; ?>>Good (Minor wear, fully functional)</option>
                                    <option value="Fair" <?php echo ($productConditionDraft === 'Fair') ? 'selected' : ''; ?>>Fair (Visible wear or markings)</option>
                                </select>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="form-group">
                            <label for="tags">Tags (Optional)</label>
                            <div class="input-icon-group">
                                <input
                                    type="text"
                                    id="tags"
                                    name="tags"
                                    value="<?php echo $tagsDraft; ?>"
                                    placeholder="e.g. textbook, cse, algorithm">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description">Description (Optional)</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            placeholder="Provide details about the item's edition, condition notes, or campus pickup preference..."
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--gray-300); border-radius: var(--radius-md); font-family: var(--font-main); font-size: 0.95rem; background-color: var(--white); color: var(--dark); resize: vertical;"><?php echo $descriptionDraft; ?></textarea>
                    </div>

                    <!-- Image URL -->
                    <div class="form-group">
                        <label for="image_url">Image Relative Path / URL (Optional)</label>
                        <div class="input-icon-group">
                            <input
                                type="text"
                                id="image_url"
                                name="image_url"
                                value="<?php echo $imageUrlDraft; ?>"
                                placeholder="e.g. images/cat-textbooks.png">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div style="margin-top: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                        <button type="submit" class="btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Publish Product Listing
                        </button>
                        <a href="dashboard.php" class="btn-outline">
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
