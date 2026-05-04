<?php
require_once 'db.php';
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');

$id   = isset($_GET['id']) ? trim($_GET['id']) : '';
$role = isset($_GET['role']) ? strtolower(trim($_GET['role'])) : 'admin';
$name = isset($_GET['name']) ? trim($_GET['name']) : '';

if (empty($id) && empty($name)) {
    echo json_encode(['success' => false, 'message' => 'User identification missing.']);
    exit;
}

if ($role === 'staff') {
    if (!empty($id)) {
        $stmt = $conn->prepare("SELECT staff_id as id, full_name, username, email, contact_number, status, created_at FROM staff WHERE staff_id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
    } else {
        $stmt = $conn->prepare("SELECT staff_id as id, full_name, username, email, contact_number, status, created_at FROM staff WHERE full_name = ? LIMIT 1");
        $stmt->bind_param("s", $name);
    }
} else {
    if (!empty($id)) {
        $stmt = $conn->prepare("SELECT employee_id as id, full_name, username, email, contact_number, role, created_at FROM admin WHERE employee_id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
    } else {
        $stmt = $conn->prepare("SELECT employee_id as id, full_name, username, email, contact_number, role, created_at FROM admin WHERE full_name = ? LIMIT 1");
        $stmt->bind_param("s", $name);
    }
}

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
}

$stmt->close();
$conn->close();
?>
