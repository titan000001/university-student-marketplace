<?php
/**
 * Student Profile Update API Endpoint
 * UniMarket - University Student Marketplace
 * Development Package: DP12-C (Integration & QA Refinement)
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
    header('Location: ../../frontend/pages/profile.php');
    exit();
}

// 3. Retrieve inputs using trim()
$fullName   = isset($_POST['full_name'])  ? trim((string) $_POST['full_name'])  : '';
$department = isset($_POST['department']) ? trim((string) $_POST['department']) : '';

// 4. Validate inputs (Full Name & Department)
$validationError = '';

if (empty($fullName) || empty($department)) {
    $validationError = 'Both Full Name and Department are required fields.';
} elseif (strlen($fullName) < 2 || strlen($fullName) > 100) {
    $validationError = 'Full Name must be between 2 and 100 characters in length.';
} elseif (strlen($department) < 2 || strlen($department) > 100) {
    $validationError = 'Department must be between 2 and 100 characters in length.';
}

if (!empty($validationError)) {
    // Preserve form draft so typed entries are not lost on validation failure
    $_SESSION['profile_form_draft'] = [
        'full_name'  => $fullName,
        'department' => $department
    ];
    $_SESSION['flash_error'] = $validationError;
    header('Location: ../../frontend/pages/profile.php');
    exit();
}

// 5. Use session user_id exclusively (prevents IDOR attacks)
$userId = (int) $_SESSION['user_id'];

try {
    // 6. Connect to database
    $database = new Database();
    $connection = $database->connect();

    // 7. Check current values to prevent unnecessary UPDATE operations
    $checkStmt = $connection->prepare(
        'SELECT full_name, department FROM users WHERE user_id = :user_id LIMIT 1'
    );
    $checkStmt->execute(['user_id' => $userId]);
    $currentRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (is_array($currentRecord)) {
        if ($currentRecord['full_name'] === $fullName && $currentRecord['department'] === $department) {
            $_SESSION['flash_success'] = 'No changes were made to your profile.';
            header('Location: ../../frontend/pages/profile.php');
            exit();
        }
    }

    // 8. Prepared UPDATE statement targeting only full_name and department
    $statement = $connection->prepare(
        'UPDATE users
         SET full_name = :full_name,
             department = :department
         WHERE user_id = :user_id'
    );

    $statement->execute([
        'full_name'  => $fullName,
        'department' => $department,
        'user_id'    => $userId
    ]);

    // 9. Update active session state so dashboard immediately reflects new name
    $_SESSION['full_name'] = $fullName;

    // 10. Set single-use session flash success message and redirect
    $_SESSION['flash_success'] = 'Profile details updated successfully!';
    header('Location: ../../frontend/pages/profile.php');
    exit();

} catch (PDOException $exception) {
    error_log('Profile Update Exception: ' . $exception->getMessage());
    $_SESSION['flash_error'] = 'A database error occurred while updating your profile. Please try again.';
    header('Location: ../../frontend/pages/profile.php');
    exit();
}
