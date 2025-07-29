<?php
require_once 'db.php'; // Include your PDO connection in $db

try {
    $userId = $_REQUEST['userid'] ?? 0;
    $catId = isset($_POST['catid']) ? intval($_POST['catid']) : 0;

    if ($catId <= 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid category ID.',
        ]);
        exit();
    }

    $pizzaId = 1; // Fixed pizza ID as per original code

    // Check if the item is already in the cart
    $stmt = $db->prepare("SELECT * FROM pizza_carts WHERE userid = :userId AND pizzaid = :pizzaId AND catid = :catId");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':pizzaId', $pizzaId, PDO::PARAM_INT);
    $stmt->bindValue(':catId', $catId, PDO::PARAM_INT);
    $stmt->execute();
    $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingItem) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Combo already added!',
        ]);
        exit();
    }

    // Insert item into cart
    $insert = $db->prepare("INSERT INTO pizza_carts (pizzaid, catid, userid, quantity, itemadddate)
                            VALUES (:pizzaId, :catId, :userId, 1, :itemAddDate)");
    $insert->bindValue(':pizzaId', $pizzaId, PDO::PARAM_INT);
    $insert->bindValue(':catId', $catId, PDO::PARAM_INT);
    $insert->bindValue(':userId', $userId, PDO::PARAM_INT);
    $insert->bindValue(':itemAddDate', date('Y-m-d H:i:s'), PDO::PARAM_STR);
    $insert->execute();

    echo json_encode([
        'status' => 'success',
        'message' => 'Combo added to cart successfully!',
    ]);
    exit();
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage(),
    ]);
    exit();
}
