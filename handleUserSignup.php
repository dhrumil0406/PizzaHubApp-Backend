<?php

require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

// Helper function to respond
function respond($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

// Validate keys exist
$required = ['username', 'fname', 'lname', 'email', 'phone', 'password'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        respond('error', ucfirst($field) . ' is required.');
    }
}

// Extract and sanitize input
$username = trim($data['username']);
$fname    = trim($data['fname']);
$lname    = trim($data['lname']);
$email    = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
$phone    = preg_replace("/[^0-9]/", "", trim($data['phone']));
$password = trim($data['password']);

// Additional validation
if (!$email) {
    respond('error', 'Invalid email format.');
}

if (strlen($phone) != 10) {
    respond('error', 'Phone number must be between 10 digits.');
}

if (strlen($password) < 6) {
    respond('error', 'Password must be at least 6 characters.');
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    // Check if email already exists
    $checkStmt = $db->prepare("SELECT COUNT(*) FROM users_admins WHERE email = :email");
    $checkStmt->bindParam(':email', $data['email']);
    $checkStmt->execute();
    if ($checkStmt->fetchColumn() > 0) {
        respond('error', 'Email already exists.');
    }

    // Insert new user
    $stmt = $db->prepare("
        INSERT INTO users_admins (username, firstname, lastname, email, phoneno, usertype, password,created_at, updated_at)
        VALUES (:username, :fname, :lname, :email, :phone, 0, :password, NOW(), NOW())
    ");

    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':fname', $fname);
    $stmt->bindParam(':lname', $lname);
    $stmt->bindParam(':email', $data['email']); // use original email string
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':password', $hashedPassword);

    if ($stmt->execute()) {
        respond('success', 'Signup successful.');
    } else {
        respond('error', 'Failed to register user.');
    }
} catch (PDOException $e) {
    respond('error', 'Server error: ' . $e->getMessage());
}
