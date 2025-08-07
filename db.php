<?php
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *'); // Optional: restrict in production
    header('Access-Control-Allow-Methods: POST');

    session_start();

    $host = '127.0.0.1';
    $dbname = 'pizzahub';
    $username = 'root';
    $password = '';

    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    }

// header('Content-Type: application/json');
// header('Access-Control-Allow-Origin: *'); // Optional: restrict in production
// header('Access-Control-Allow-Methods: POST');

// session_start();

// $host = '127.0.0.1'; // ✅ Changed from 'localhost' to '127.0.0.1' to force TCP connection
// $dbname = 'pizzahub';
// $username = 'root';
// $password = '';

// try {
//     $db = new PDO("mysql:host=$host;port=3306;dbname=$dbname;charset=utf8", $username, $password);
//     $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     echo json_encode(['status' => 'success', 'message' => 'Database connection established.']);
// } catch (PDOException $e) {
//     echo json_encode([
//         'status' => 'error',
//         'message' => 'Database connection failed: ' . $e->getMessage()
//     ]);
// }
?>

