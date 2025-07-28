<?php
require_once 'db.php'; // PDO instance = $db

// Get category_id from request (POST or GET)
$categoryId = isset($_REQUEST['category_id']) ? intval($_REQUEST['category_id']) : 0;

try {
    if ($categoryId === 0) {
        // Show all categories ordered by iscombo
        $stmt = $db->prepare("SELECT * FROM categories ORDER BY iscombo ASC");
    } elseif ($categoryId === 3) {
        // Show combo categories
        $stmt = $db->prepare("SELECT * FROM categories WHERE iscombo = 1");
    } else {
        // Show categories matching cattype veg or non-veg, not combo
        $stmt = $db->prepare("SELECT * FROM categories WHERE cattype = :cattype AND iscombo = 0");
        $stmt->bindParam(':cattype', $categoryId);
    }

    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($categories)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No categories found for the selected filter.',
            'data' => [] // Return empty data array
        ]);
        exit();
    }

    // ✅ JSON response (for API or Flutter)
    echo json_encode([
        'status' => 'success',
        'message' => 'Categories loaded successfully.',
        'data' => $categories
    ]);
    exit();

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage(),
        'data' => [] // Return empty data array on error
    ]);
    exit();
}
