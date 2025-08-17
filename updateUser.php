<?php
require_once 'db.php'; // PDO instance = $db

// Get request data
$userId    = isset($_REQUEST['userid']) ? intval($_REQUEST['userid']) : 0;
$username  = isset($_REQUEST['username']) ? trim($_REQUEST['username']) : '';
$firstname = isset($_REQUEST['firstname']) ? trim($_REQUEST['firstname']) : '';
$lastname  = isset($_REQUEST['lastname']) ? trim($_REQUEST['lastname']) : '';
$email     = isset($_REQUEST['email']) ? trim($_REQUEST['email']) : '';
$phoneno   = isset($_REQUEST['phoneno']) ? trim($_REQUEST['phoneno']) : '';
$password  = isset($_REQUEST['password']) ? trim($_REQUEST['password']) : '';

try {
    if ($userId === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid or missing userid.',
            'data' => []
        ]);
        exit();
    }

    // Build SQL dynamically (optional password update)
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Secure hash
        $stmt = $db->prepare("
            UPDATE users_admins
            SET username = :username,
                firstname = :firstname,
                lastname = :lastname,
                email = :email,
                phoneno = :phoneno,
                password = :password,
                updated_at = NOW()
            WHERE userid = :userid
        ");
        $stmt->bindParam(':password', $hashedPassword);
    } else {
        $stmt = $db->prepare("
            UPDATE users_admins
            SET username = :username,
                firstname = :firstname,
                lastname = :lastname,
                email = :email,
                phoneno = :phoneno,
                updated_at = NOW()
            WHERE userid = :userid
        ");
    }

    // Bind parameters
    $stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':firstname', $firstname);
    $stmt->bindParam(':lastname', $lastname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phoneno', $phoneno);

    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Profile updated successfully.',
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'No changes made or user not found.',
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage(),
    ]);
}
