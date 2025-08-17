<?php
require_once 'db.php'; // PDO instance = $db

// Get userid from request (POST or GET)
$userId = isset($_REQUEST['userid']) ? intval($_REQUEST['userid']) : 0;

try {
    if ($userId === 0) {
        // Fetch all users
        $stmt = $db->prepare("SELECT userid, username, firstname, lastname, email, phoneno, usertype, updated_at FROM users_admins");
    } else {
        // Fetch single user by ID
        $stmt = $db->prepare("SELECT userid, username, firstname, lastname, email, phoneno, usertype, updated_at, password FROM users_admins WHERE userid = :userid");
        $stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
    }

    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($users)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No user(s) found.',
            'data' => []
        ]);
        exit();
    }

    // ✅ JSON response
    echo json_encode([
        'status' => 'success',
        'message' => 'User data loaded successfully.',
        'data' => $users
    ]);
    exit();

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage(),
        'data' => []
    ]);
    exit();
}
