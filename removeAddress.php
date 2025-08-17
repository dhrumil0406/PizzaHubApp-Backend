<?php
require_once 'db.php';

function respond($status, $message)
{
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

// Read raw POST data (JSON body)
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (empty($data['addressid'])) {
    respond('error', 'addressid is required.');
}

$addressid = intval($data['addressid']);

try {
    // Check if address exists
    $check = $db->prepare("SELECT COUNT(*) FROM addresses WHERE addressid = :addressid");
    $check->bindParam(':addressid', $addressid);
    $check->execute();

    if ($check->fetchColumn() == 0) {
        respond('error', 'Address not found.');
    }

    // Delete address
    $stmt = $db->prepare("DELETE FROM addresses WHERE addressid = :addressid");
    $stmt->bindParam(':addressid', $addressid);

    if ($stmt->execute()) {
        respond('success', 'Address removed successfully.');
    } else {
        respond('error', 'Failed to remove address.');
    }
} catch (PDOException $e) {
    respond('error', 'Server error: ' . $e->getMessage());
}
