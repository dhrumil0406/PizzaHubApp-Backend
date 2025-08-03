<?php
require_once 'db.php';

try {
    $userId = $_REQUEST['userid'] ?? 0;

    // Join pizza_carts with pizzas to get full pizza details
    $stmt = $db->prepare("
        SELECT c.cartitemid, c.userid, c.pizzaid, c.quantity, c.catid, c.itemadddate,
               p.pizzaname, p.pizzaprice, p.pizzaimage, p.discount
        FROM pizza_carts c
        JOIN pizza_items p ON c.pizzaid = p.pizzaid
        WHERE c.userid = :userId
    ");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cartItems)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No items found in the cart.',
            'data' => []
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
        'data' => []
    ]);
    exit();
}