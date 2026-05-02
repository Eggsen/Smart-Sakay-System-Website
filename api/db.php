<?php
require_once __DIR__ . '/config.php';
ini_set('display_errors', 0);
error_reporting(0);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$conn->set_charset('utf8mb4');
?>