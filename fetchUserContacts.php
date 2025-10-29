<?php
require_once 'db.php'; // Include your DB connection file


try {
    // Prepare SQL query to fetch all contact messages
    $userId = $_REQUEST['userid'] ?? 0;

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
        where userid=:userId
        ORDER BY contactdate DESC
    ");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
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
