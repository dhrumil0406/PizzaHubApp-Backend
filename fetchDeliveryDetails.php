<?php
require_once 'db.php'; // Include DB connection

try {
    $orderId = $_REQUEST['orderid'] ?? 0;
    // Prepare SQL query with INNER JOIN
    $stmt = $db->prepare("
        SELECT 
            d.deliveryid,
            d.orderid,
            d.dbid,
            d.deliverytime,
            d.deliverydate,
            d.trackid,
            b.deliveryboyname,
            b.deliveryboyphoneno,
            b.deliveryboyemail,
            o.orderstatus
        FROM delivery_details d
        INNER JOIN delivery_boy_details b ON d.dbid = b.dbid
        INNER JOIN orders o ON d.orderid = o.orderid
        WHERE d.orderid = :orderId
        ORDER BY d.deliverydate DESC
    ");

    $stmt->bindValue(':orderId', $orderId, PDO::PARAM_STR);
    $stmt->execute();
    $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($deliveries) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Delivery details fetched successfully.',
            'data' => $deliveries
        ]);
    } else {
        echo json_encode([
            'status' => 'empty',
            'message' => 'No delivery records found.',
            'data' => []
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>
