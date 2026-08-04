<?php
/**
 * Student Profile Update API Endpoint
 * UniMarket - University Student Marketplace
 * Development Package: DP12-B
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

// 2. Accept POST requests only: reject non-POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../frontend/pages/profile.php');
    exit();
}

// 3. Retrieve inputs using trim() - do not trust client-side data
$fullName   = isset($_POST['full_name'])  ? trim((string) $_POST['full_name'])  : '';
$department = isset($_POST['department']) ? trim((string) $_POST['department']) : '';

// 4. Validate inputs (Full Name & Department)
if (empty($fullName) || empty($department)) {
    $error = 'Both Full Name and Department are required fields.';
    header('Location: ../../frontend/pages/profile.php?error=' . urlencode($error));
    exit();
}

if (strlen($fullName) < 2 || strlen($fullName) > 100) {
    $error = 'Full Name must be between 2 and 100 characters in length.';
    header('Location: ../../frontend/pages/profile.php?error=' . urlencode($error));
    exit();
}

if (strlen($department) < 2 || strlen($department) > 100) {
    $error = 'Department must be between 2 and 100 characters in length.';
    header('Location: ../../frontend/pages/profile.php?error=' . urlencode($error));
    exit();
}

// 5. Use session user_id exclusively (prevents IDOR attacks)
$userId = (int) $_SESSION['user_id'];

try {
    // 6. Connect to database using existing Database class & PDO wrapper
    $database = new Database();
    $connection = $database->connect();

    // 7. Prepared UPDATE statement targeting only full_name and department
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

    // 8. Update active session so dashboard immediately reflects the new name
    $_SESSION['full_name'] = $fullName;

    // 9. Redirect back to profile page with success notification
    $success = 'Profile details updated successfully!';
    header('Location: ../../frontend/pages/profile.php?success=' . urlencode($success));
    exit();

} catch (PDOException $exception) {
    // 10. Log PDO exception without exposing raw SQL/database details to user
    error_log('Profile Update Exception: ' . $exception->getMessage());
    $error = 'A database error occurred while updating your profile. Please try again.';
    header('Location: ../../frontend/pages/profile.php?error=' . urlencode($error));
    exit();
}
