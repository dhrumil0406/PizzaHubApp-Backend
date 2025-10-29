<?php
require_once 'db.php'; // Include your DB connection file

try {
    // Collect inputs
    $userId = $_REQUEST['userid'] ?? 0;
    $contactId = $_REQUEST['contactid'] ?? 0;

    if (!$userId || !$contactId) {
        echo json_encode([
            'status' => 'error',
            'message' => 'User ID and Contact ID are required.',
            'data' => []
        ]);
        exit();
    }

    // Prepare SQL query
    $stmt = $db->prepare("
        SELECT 
            replyid,
            contactid,
            userid,
            message,
            contactdate
        FROM contact_replies
        WHERE userid = :userId AND contactid = :contactId
        ORDER BY contactdate desc
    ");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':contactId', $contactId, PDO::PARAM_INT);
    $stmt->execute();

    $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($replies) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Replies fetched successfully.',
            'data' => $replies
        ]);
    } else {
        echo json_encode([
            'status' => 'empty',
            'message' => 'No replies found for this contact.',
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
