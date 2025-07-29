<?php
require_once 'db.php'; // Include your PDO connection ($db)

try {
    $userId = $_REQUEST['userid'] ?? 0;

    // Delete all cart items for the user
    $stmt = $db->prepare("DELETE FROM pizza_carts WHERE userid = :userId");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode([
        'status' => 'success',
        'message' => 'Cart cleared successfully!',
    ]);
    exit();

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage(),
    ]);
    exit();
}
