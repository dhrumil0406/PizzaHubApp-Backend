<?php
require_once 'db.php'; // Include your DB connection file

try {
    // Prepare SQL query to fetch all contact messages
    $stmt = $db->prepare("
        SELECT 
            contactid, 
            userid,
            orderid,
            email, 
            phoneno, 
            message, 
            contactdate 
        FROM contacts 
        ORDER BY contactdate DESC
    ");
    $stmt->execute();

    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($contacts) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Contacts fetched successfully.',
            'data' => $contacts
        ]);
    } else {
        echo json_encode([
            'status' => 'empty',
            'message' => 'No contacts found.',
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
