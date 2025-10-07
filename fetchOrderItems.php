<?php
require_once 'db.php'; // $db: PDO instance

try {
    // Collect User Inputs (string orderid like "O2944")
    $orderId = isset($_REQUEST['orderid']) ? trim($_REQUEST['orderid']) : "";

    if ($orderId === "") {
        echo json_encode([
            'status' => 'error',
            'message' => 'Order ID is required.',
            'data' => []
        ]);
        exit();
    }

    // Fetch order items with joins
    $stmt = $db->prepare("
        SELECT 
            oi.orderitemid,
            oi.orderid,
            oi.pizzaid,
            oi.catid,
            oi.quantity,
            oi.discount,

            p.pizzaname,
            p.pizzaprice,
            p.pizzaimage,
            p.pizzadesc,

            c.catname,
            c.catimage,
            c.catdesc,
            c.cattype,
            c.iscombo,
            c.comboprice

        FROM order_items oi
        LEFT JOIN pizza_items p ON oi.pizzaid = p.pizzaid
        LEFT JOIN categories c ON (oi.catid != 0 AND oi.catid = c.catid)
        WHERE oi.orderid = :orderId
    ");
    $stmt->bindValue(':orderId', $orderId, PDO::PARAM_STR);
    $stmt->execute();

    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($orderItems)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No items found for this order.',
            'data' => []
        ]);
        exit();
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Order items fetched successfully.',
        'data' => $orderItems
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
