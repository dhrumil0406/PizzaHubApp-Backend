<?php
require_once 'db.php'; // PDO instance = $db

// Get userid from request (POST or GET)
$userId = isset($_REQUEST['userid']) ? intval($_REQUEST['userid']) : 0;

try {
    if ($userId === 0) {
        // ❌ userid is mandatory in this case
        echo json_encode([
            'status' => 'error',
            'message' => 'userid is required.',
            'data' => []
        ]);
        exit();
    }

    // Fetch addresses for a given user
    $stmt = $db->prepare("
        SELECT 
            addressid,
            userid,
            addressType,
            name,
            apartmentNo,
            buildingName,
            streetArea,
            city,
            createdAt
        FROM addresses
        WHERE userid = :userid
        ORDER BY createdAt DESC
    ");
    $stmt->bindParam(':userid', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($addresses)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No addresses found for this user.',
            'data' => []
        ]);
        exit();
    }

    // ✅ JSON response
    echo json_encode([
        'status' => 'success',
        'message' => 'Addresses loaded successfully.',
        'data' => $addresses
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
