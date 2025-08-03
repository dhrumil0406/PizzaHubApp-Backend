<?php
require_once 'db.php';

try {
    $cartItemId = intval($_REQUEST['cartitemid'] ?? 0);
    $newQty = intval($_REQUEST['quantity'] ?? 1);

    $stmt = $db->prepare("UPDATE pizza_carts SET quantity = :quantity WHERE cartitemid = :cartitemid");
    $stmt->bindValue(':quantity', $newQty, PDO::PARAM_INT);
    $stmt->bindValue(':cartitemid', $cartItemId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Quantity updated successfully.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Update failed or no change.'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
