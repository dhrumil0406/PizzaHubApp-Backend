<?php
require_once 'db.php'; // Include your PDO connection in $db

try {
    $userId = $_REQUEST['userid'] ?? 0;
    $cartItemId = isset($_POST['cartitemid']) ? intval($_POST['cartitemid']) : 0;

    if ($cartItemId <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid cart item ID.',
        ]);
        exit();
    }

    // Check if the cart item exists
    $stmt = $db->prepare("SELECT * FROM pizza_carts WHERE userid = :userId AND cartitemid = :cartItemId");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':cartItemId', $cartItemId, PDO::PARAM_INT);
    $stmt->execute();
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cartItem) {
        // Delete the cart item
        $deleteStmt = $db->prepare("DELETE FROM pizza_carts WHERE cartitemid = :cartItemId AND userid = :userId");
        $deleteStmt->bindValue(':cartItemId', $cartItemId, PDO::PARAM_INT);
        $deleteStmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $deleteStmt->execute();

        echo json_encode([
            'status' => 'success',
            'message' => 'Item removed from cart successfully!',
        ]);
        exit();
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Item not found in cart!',
        ]);
        exit();
    }

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage(),
    ]);
    exit();
}
