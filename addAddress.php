<?php
require_once 'db.php';

function respond($status, $message)
{
    echo json_encode(['status' => $status, 'message' => $message]);
    exit();
}

// Read JSON input
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    respond('error', 'Invalid JSON input.');
}

$required = ['userid', 'addressType', 'name', 'city'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        respond('error', ucfirst($field) . ' is required.');
    }
}

$userid      = intval($data['userid']);
$addressType = in_array($data['addressType'], ['Home', 'Office', 'Other']) ? $data['addressType'] : 'Home';
$name        = trim($data['name']);
$apartmentNo = isset($data['apartmentNo']) ? trim($data['apartmentNo']) : null;
$building    = isset($data['buildingName']) ? trim($data['buildingName']) : null;
$streetArea  = isset($data['streetArea']) ? trim($data['streetArea']) : null;
$city        = trim($data['city']);
$lat = trim($data['latitude'] ?? '');
$lng = trim($data['longitude'] ?? '');

try {
    // Check user exists
    $checkUser = $db->prepare("SELECT COUNT(*) FROM users_admins WHERE userid = :userid");
    $checkUser->bindParam(':userid', $userid);
    $checkUser->execute();
    if ($checkUser->fetchColumn() == 0) {
        respond('error', 'User not found.');
    }

    // Insert address
    $stmt = $db->prepare("
        INSERT INTO addresses (userid, addressType, name, apartmentNo, buildingName, streetArea, city, latitude, longitude, createdAt)
        VALUES (:userid, :addressType, :name, :apartmentNo, :buildingName, :streetArea, :city, :lat, :lng, NOW())
    ");

    $stmt->bindParam(':userid', $userid);
    $stmt->bindParam(':addressType', $addressType);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':apartmentNo', $apartmentNo);
    $stmt->bindParam(':buildingName', $building);
    $stmt->bindParam(':streetArea', $streetArea);
    $stmt->bindParam(':city', $city);
    $stmt->bindParam(':lat', $lat);
    $stmt->bindParam(':lng', $lng);

    if ($stmt->execute()) {
        respond('success', 'Address added successfully.');
    } else {
        respond('error', 'Failed to add address.');
    }
} catch (PDOException $e) {
    respond('error', 'Server error: ' . $e->getMessage());
}
