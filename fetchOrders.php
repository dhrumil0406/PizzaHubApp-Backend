<?php
require_once 'db.php'; // $db: PDO instance

try {
    // Collect User Inputs
    $userId = $_REQUEST['userid'] ?? 0;

    if (!$userId) {
        echo json_encode([
            'status' => 'error',
            'message' => 'User ID is required.',
            'data' => []
        ]);
        exit();
    }

    // Fetch orders for the user
    $stmt = $db->prepare("
        SELECT 
            orderid,
            address,
            phoneno,
            discountedtotalprice,
            paymentmethod,
            orderstatus,
            orderdate
        FROM orders
        WHERE userid = :userId
        ORDER BY orderdate DESC
    ");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($orders)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No orders found for this user.',
            'data' => []
        ]);
        exit();
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Orders fetched successfully.',
        'data' => $orders
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
