<?php
/**
 * Student Registration API Endpoint
 * UniMarket - University Student Marketplace
 */

header('Content-Type: application/json; charset=utf-8');

// Ensure request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method Not Allowed. Use POST.'
    ]);
    exit();
}

require_once __DIR__ . '/../config/database.php';

// Accept both JSON payload and form-encoded data
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!is_array($data)) {
    $data = $_POST;
}

// Extract fields with fallbacks for hyphenated/underscored parameter names
$fullName  = isset($data['full_name'])  ? trim($data['full_name'])  : (isset($data['full-name'])  ? trim($data['full-name'])  : '');
$email     = isset($data['email'])      ? trim($data['email'])      : '';
$studentId = isset($data['student_id']) ? trim($data['student_id']) : (isset($data['student-id']) ? trim($data['student-id']) : '');
$department= isset($data['department']) ? trim($data['department']) : '';
$password  = isset($data['password'])   ? $data['password']          : '';

// Validate required fields
if (empty($fullName) || empty($email) || empty($studentId) || empty($department) || empty($password)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required.'
    ]);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please provide a valid university email address.'
    ]);
    exit();
}

// Validate password length
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 6 characters long.'
    ]);
    exit();
}

try {
    $database = new Database();
    $connection = $database->connect();

    // Check if email or student ID already exists
    $checkStmt = $connection->prepare(
        'SELECT user_id FROM users WHERE email = :email OR student_id = :student_id LIMIT 1'
    );
    $checkStmt->execute([
        'email'      => $email,
        'student_id' => $studentId
    ]);

    if ($checkStmt->fetch()) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'An account with this Email or Student ID already exists.'
        ]);
        exit();
    }

    // Hash password and insert record
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $insertStmt = $connection->prepare(
        'INSERT INTO users (full_name, email, student_id, department, password_hash, role)
         VALUES (:full_name, :email, :student_id, :department, :password_hash, "student")'
    );

    $insertStmt->execute([
        'full_name'     => $fullName,
        'email'         => $email,
        'student_id'    => $studentId,
        'department'    => $department,
        'password_hash' => $passwordHash
    ]);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! Account created.'
    ]);

} catch (PDOException $exception) {
    error_log('Registration Error: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'A server error occurred during registration. Please try again.'
    ]);
}
