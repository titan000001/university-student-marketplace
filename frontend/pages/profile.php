<?php
/**
 * Student Profile Page
 * UniMarket - University Student Marketplace
 * Development Package: DP12-C (Integration & QA Refinement)
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

$userId = (int) $_SESSION['user_id'];
$user = [
    'full_name'  => '',
    'email'      => '',
    'student_id' => '',
    'department' => ''
];

$fetchError = '';
$userRecordFound = true;

// Fetch currently logged-in user profile details using PDO prepared statement
try {
    $database = new Database();
    $connection = $database->connect();

    $statement = $connection->prepare(
        'SELECT full_name, email, student_id, department FROM users WHERE user_id = :user_id LIMIT 1'
    );
    $statement->execute(['user_id' => $userId]);
    $fetchedUser = $statement->fetch(PDO::FETCH_ASSOC);

    if (is_array($fetchedUser)) {
        $user = array_merge($user, $fetchedUser);
    } else {
        $userRecordFound = false;
        $fetchError = 'Unable to locate user profile record. Please log in again.';
    }
} catch (PDOException $exception) {
    error_log('Profile Fetch Error: ' . $exception->getMessage());
    $userRecordFound = false;
    $fetchError = 'Unable to retrieve user profile information due to a database error.';
}

// Preserve form draft values if returning from a validation error
if (isset($_SESSION['profile_form_draft']) && is_array($_SESSION['profile_form_draft'])) {
    if (isset($_SESSION['profile_form_draft']['full_name'])) {
        $user['full_name'] = $_SESSION['profile_form_draft']['full_name'];
    }
    if (isset($_SESSION['profile_form_draft']['department'])) {
        $user['department'] = $_SESSION['profile_form_draft']['department'];
    }
    unset($_SESSION['profile_form_draft']);
}

// Single-use session flash messages (fallback to GET params for backwards compatibility)
$successMessage = isset($_SESSION['flash_success']) ? trim((string) $_SESSION['flash_success']) : (isset($_GET['success']) ? trim((string) $_GET['success']) : '');
$errorMessage   = isset($_SESSION['flash_error'])   ? trim((string) $_SESSION['flash_error'])   : (isset($_GET['error'])   ? trim((string) $_GET['error'])   : '');

unset($_SESSION['flash_success'], $_SESSION['flash_error']);

if (!$userRecordFound && empty($errorMessage)) {
    $errorMessage = $fetchError;
}

// Escape all outputs safely to prevent XSS and PHP notices
$fullNameEscaped   = htmlspecialchars((string) ($user['full_name'] ?? $_SESSION['full_name'] ?? 'Student'), ENT_QUOTES, 'UTF-8');
$emailEscaped      = htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$studentIdEscaped  = htmlspecialchars((string) ($user['student_id'] ?? ''), ENT_QUOTES, 'UTF-8');
$departmentEscaped = htmlspecialchars((string) ($user['department'] ?? ''), ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="UniMarket Student Profile - View and manage your student account details.">
    <title>My Profile | UniMarket</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="../css/styles.css">
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
    <nav class="main-nav" aria-label="Profile Navigation">
        <div class="container">
            <button
                id="menu-toggle"
                class="menu-toggle"
                aria-label="Toggle Navigation Menu"
                aria-expanded="false"
                aria-controls="profile-menu">
                ☰
            </button>
            <ul id="profile-menu">
                <li>
                    <a href="dashboard.php">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="profile.php" class="active">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        My Profile
                    </a>
                </li>
                <li>
                    <a href="index.php#categories">
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
            
            <!-- Page Header Banner -->
            <section class="dashboard-header">
                <div class="dashboard-avatar-wrapper">
                    <img src="../images/student-avatar.png" alt="<?php echo $fullNameEscaped; ?> Avatar" class="dashboard-avatar-img">
                </div>
                <div class="dashboard-welcome-text">
                    <h1>My Profile</h1>
                    <p>View your verified student profile and manage editable account details.</p>
                </div>
            </section>

            <!-- Feedback Message Alerts -->
            <?php if (!empty($successMessage)) : ?>
                <div class="dashboard-card" style="border-left: 4px solid var(--success); background-color: rgba(16, 185, 129, 0.05);">
                    <p style="color: var(--success); font-weight: 600; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (!empty($errorMessage)) : ?>
                <div class="dashboard-card" style="border-left: 4px solid var(--danger); background-color: rgba(239, 68, 68, 0.05);">
                    <p style="color: var(--danger); font-weight: 600; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Profile Form Card -->
            <section class="dashboard-card">
                <h2>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon" style="color: var(--primary);"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Profile Details
                </h2>

                <form method="POST" action="../../backend/api/update_profile.php" class="registration-form" style="max-width: 100%; margin: 1.5rem 0 0 0;" novalidate>
                    
                    <!-- Editable Field: Full Name -->
                    <div class="form-group">
                        <label for="full_name">Full Name (Editable)</label>
                        <div class="input-icon-group">
                            <input
                                type="text"
                                id="full_name"
                                name="full_name"
                                value="<?php echo $fullNameEscaped; ?>"
                                placeholder="Enter your full name"
                                <?php echo $userRecordFound ? 'required' : 'disabled'; ?>>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                    </div>

                    <!-- Read-Only Field: Email -->
                    <div class="form-group">
                        <label for="email">University Email (Read-Only)</label>
                        <div class="input-icon-group">
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?php echo $emailEscaped; ?>"
                                readonly
                                style="background-color: var(--gray-100); color: var(--gray-600); cursor: not-allowed;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                    </div>

                    <!-- Read-Only Field: Student ID -->
                    <div class="form-group">
                        <label for="student_id">Student ID (Read-Only)</label>
                        <div class="input-icon-group">
                            <input
                                type="text"
                                id="student_id"
                                name="student_id"
                                value="<?php echo $studentIdEscaped; ?>"
                                readonly
                                style="background-color: var(--gray-100); color: var(--gray-600); cursor: not-allowed;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="7" y1="8" x2="17" y2="8"/><line x1="7" y1="12" x2="13" y2="12"/></svg>
                        </div>
                    </div>

                    <!-- Editable Field: Department -->
                    <div class="form-group">
                        <label for="department">Department (Editable)</label>
                        <div class="input-icon-group">
                            <input
                                type="text"
                                id="department"
                                name="department"
                                value="<?php echo $departmentEscaped; ?>"
                                placeholder="Enter your department"
                                <?php echo $userRecordFound ? 'required' : 'disabled'; ?>>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon input-icon"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        </div>
                    </div>

                    <div style="margin-top: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                        <button type="submit" class="btn-primary" <?php echo !$userRecordFound ? 'disabled' : ''; ?>>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Save Profile Changes
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
    <script src="../js/app.js"></script>
</body>
</html>
