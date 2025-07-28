<?php

// Include database connection
require_once 'db.php';

// Get raw POST data from Flutter
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required.']);
    exit();
}

$email = trim($data['email']);
$password = trim($data['password']);

try {
    $stmt = $db->prepare("SELECT * FROM users_admins WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Set session if needed (optional for API)
        $_SESSION['userid'] = $user['userid'];

        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful.',
            'data' => [
                'userid' => $user['userid'],
                'username' => $user['username'],
                'email' => $user['email'],
                'usertype' => $user['usertype'],
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Credentials.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}