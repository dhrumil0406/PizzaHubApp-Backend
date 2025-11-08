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

    // Fetch the last (most recent) order for the user
    $stmt = $db->prepare("
        SELECT 
            orderid,
            address,
            zip,
            phoneno,
            discountedtotalprice,
            paymentmethod,
            orderstatus,
            orderdate
        FROM orders
        WHERE userid = :userId
        ORDER BY orderdate DESC
        LIMIT 1
    ");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $lastOrder = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lastOrder) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No recent order found for this user.',
            'data' => []
        ]);
        exit();
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Last order fetched successfully.',
        'data' => $lastOrder
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
