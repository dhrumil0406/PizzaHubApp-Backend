<?php
require_once 'db.php'; // Include your database connection (PDO $db expected)

try {
    $userId = $_REQUEST['userid'] ?? 0;
    $pizzaId = isset($_REQUEST['pizzaid']) ? intval($_REQUEST['pizzaid']) : 0;

    if ($pizzaId <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid pizza ID.',
        ]);
        exit();
    }

    // Check if item already exists in cart
    $stmt = $db->prepare("SELECT * FROM pizza_carts WHERE userid = :userId AND pizzaid = :pizzaId AND catid IS NULL");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':pizzaId', $pizzaId, PDO::PARAM_INT);
    $stmt->execute();
    $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingItem) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Item already added!',
        ]);
        exit();
    }

    // Insert new item into cart
    $insert = $db->prepare("INSERT INTO pizza_carts (pizzaid, catid, userid, quantity, itemadddate)
                            VALUES (:pizzaId, NULL, :userId, 1, :itemAddDate)");
    $insert->bindValue(':pizzaId', $pizzaId, PDO::PARAM_INT);
    $insert->bindValue(':userId', $userId, PDO::PARAM_INT);
    $insert->bindValue(':itemAddDate', date('Y-m-d H:i:s'), PDO::PARAM_STR); // You can use Carbon in Laravel, but here we use native PHP
    $insert->execute();

    echo json_encode([
        'status' => 'success',
        'message' => 'Item added to cart successfully!',
    ]);
    exit();
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage(),
    ]);
    exit();
}
