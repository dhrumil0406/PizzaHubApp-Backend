<?php
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *'); // Optional: restrict in production
    header('Access-Control-Allow-Methods: POST');

    session_start();

    $host = 'localhost';
    $dbname = 'pizzahub';
    $username = 'root';
    $password = '';

    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    }
