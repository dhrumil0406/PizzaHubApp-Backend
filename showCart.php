<?php
require_once 'db.php';

try {
    $userId = $_REQUEST['userid'] ?? 0;

    // Join pizza_carts with pizzas and categories
    $stmt = $db->prepare("
        SELECT 
            c.cartitemid,
            c.userid,
            c.pizzaid,
            c.catid,
            c.quantity,
            c.itemadddate,
            p.pizzaname,
            p.pizzaprice,
            p.pizzaimage,
            p.discount AS pizzadiscount,
            cat.catname,
            cat.catimage,
            cat.comboprice,
            cat.discount AS catdiscount
        FROM pizza_carts c
        LEFT JOIN pizza_items p ON c.pizzaid = p.pizzaid
        LEFT JOIN categories cat ON c.catid = cat.catid
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
