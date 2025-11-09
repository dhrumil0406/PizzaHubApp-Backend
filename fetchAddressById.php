<?php
require_once 'db.php';

$orderId = isset($_REQUEST['orderid']) ? trim($_REQUEST['orderid']) : '';

try {
    if ($orderId === '') {
        echo json_encode([
            'status' => 'error',
            'message' => 'orderid is required.',
            'data' => []
        ]);
        exit();
    }

    $stmt = $db->prepare("
        SELECT 
            a.addressid,
            a.userid,
            a.addressType,
            a.name,
            a.apartmentNo,
            a.buildingName,
            a.streetArea,
            a.city,
            a.latitude,
            a.longitude,
            a.createdAt
        FROM addresses a
        INNER JOIN orders o ON o.addressid = a.addressid
        WHERE o.orderid = :orderid
        LIMIT 1
    ");
    $stmt->bindParam(':orderid', $orderId, PDO::PARAM_STR);
    $stmt->execute();

    $address = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$address) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No address found for this order.',
            'data' => []
        ]);
        exit();
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Address found for the order.',
        'data' => $address
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
