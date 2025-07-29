<?php

require_once 'db.php'; // Include your database connection (PDO $db expected)

try {
    $userId = $_REQUEST['userid'] ?? 0;

    // Fetch cart items for the logged-in user
    $stmt = $db->prepare("SELECT * FROM pizza_carts WHERE userid = :userId");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($cartItems)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No items found in the cart.',
            'data' => [] // Return empty data array
        ]);
        exit();
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Cart items fetched successfully.',
        'data' => $cartItems
    ]);
    exit();
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage(),
        'data' => [] // Return empty data array on error
    ]);
    exit();
}
