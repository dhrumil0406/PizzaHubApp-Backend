<?php
require_once 'db.php'; // $db: PDO instance
require_once 'sendMailByAdmin.php'; // include PHPMailer configuration
require_once 'sendMailByUser.php'; // include PHPMailer configuration

try {
    // Collect user inputs safely
    $userId   = $_REQUEST['userid'] ?? 0;
    $orderId  = $_REQUEST['orderid'] ?? '';
    $email    = trim($_REQUEST['email'] ?? '');
    $phoneNo  = trim($_REQUEST['phoneno'] ?? '');
    $message  = trim($_REQUEST['message'] ?? '');
    $password = trim($_REQUEST['password'] ?? '');

    // Basic validation
    if (!$userId || !$email || !$phoneNo || !$password || !$message) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All fields are required.',
        ]);
        exit;
    }

    // 🔹 Step 1: Validate user and password
    $stmt = $db->prepare("SELECT * FROM users_admins WHERE userid = :userId AND email = :email");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':email', $email);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Email not verified!',
        ]);
        exit;
    }

    if (password_verify($password, $user['password']) === false) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid password!',
        ]);
        exit;
    }

    // ✅ Begin Transaction
    $db->beginTransaction();

    // 🔹 Step 2: Insert into contacts table
    $insert = $db->prepare("
        INSERT INTO contacts (userid, orderid, email, phoneno, message, contactdate)
        VALUES (:userid, :orderid, :email, :phoneno, :message, NOW())
    ");
    $insert->bindValue(':userid', $userId, PDO::PARAM_INT);
    $insert->bindValue(':orderid', $orderId);
    $insert->bindValue(':email', $email);
    $insert->bindValue(':phoneno', $phoneNo);
    $insert->bindValue(':message', $message);
    $insert->execute();

    // 🔹 Step 3: Send mail to admin
    $adminSubject = "New Contact Request from {$user['firstname']} {$user['lastname']}";
    $adminBody = "
        <h3>New Contact Request Received</h3>
        <ul>
            <li><strong>User ID:</strong> {$userId}</li>
            <li><strong>Name:</strong> {$user['firstname']} {$user['lastname']}</li>
            <li><strong>Email:</strong> {$email}</li>
            <li><strong>Order ID:</strong> {$orderId}</li>
            <li><strong>Phone:</strong> {$phoneNo}</li>
            <li><strong>Message:</strong> {$message}</li>
        </ul>
        <br>
        <p>🍕 <strong>PizzaHub System</strong></p>
    ";

    $mailSentToAdmin = sendMailByUser($email, $adminSubject, $adminBody, "{$user['firstname']} {$user['lastname']}");

    // 🔹 Step 4: Send confirmation email to user
    $subject = "Contact Request Received - PizzaHub Support";
    $body = "
        <h3>Hello {$user['firstname']} {$user['lastname']},</h3>
        <p>We’ve received your contact request with the following details:</p>
        <ul>
            <li><strong>Order ID:</strong> {$orderId}</li>
            <li><strong>Phone:</strong> {$phoneNo}</li>
            <li><strong>Message:</strong> {$message}</li>
        </ul>
        <p>Our support team will contact you soon.</p>
        <br>
        <p>🍕 <strong>PizzaHub Support</strong></p>
    ";

    $mailSentToUser = sendMailByAdmin($email, $subject, $body);

    // ✅ Check both mail success
    if ($mailSentToAdmin && $mailSentToUser) {
        // Both emails sent successfully → commit transaction
        $db->commit();
        echo json_encode([
            'status' => 'success',
            'message' => 'Your message has been sent successfully! Our support team will reach you soon.',
        ]);
    } else {
        // If any email fails → rollback transaction
        $db->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to send email(s). Your message was not saved.',
        ]);
    }

    exit;

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage(),
    ]);
    exit;
}
