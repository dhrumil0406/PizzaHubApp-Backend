<?php
require_once 'db.php'; // Include database connection

// Get categoryId from POST request
$categoryId = isset($_REQUEST['categoryId']) ? intval($_REQUEST['categoryId']) : 0;

try {
    // Query based on categoryId
    if ($categoryId === 0) {
        $stmt = $conn->prepare("SELECT * FROM pizza_items");
    } else {
        $stmt = $db->prepare("SELECT * FROM pizza_items WHERE catid = :categoryId");
        $stmt->bindValue(':categoryId', $categoryId, PDO::PARAM_INT);
    }

    $stmt->execute();
    $pizzas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($pizzas)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No pizza items found for selected category.',
        ]);
        exit();
    }
    // Return JSON response
    echo json_encode([
        'status' => 'success',
        'message' => 'Pizza list loaded successfully.',
        'data' => $pizzas
    ]);
    exit();

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage(),
    ]);
    exit();
}
