<?php
require_once 'db.php';
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$id       = isset($_POST['id']) ? trim($_POST['id']) : '';
$role     = isset($_POST['role']) ? strtolower(trim($_POST['role'])) : 'admin';
$fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email    = isset($_POST['email']) ? trim($_POST['email']) : '';
$contact  = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';

if (empty($id) || empty($fullName) || empty($username) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'All fields except contact number are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

$table = ($role === 'staff') ? 'staff' : 'admin';
$idCol = ($role === 'staff') ? 'staff_id' : 'employee_id';

// Check uniqueness for username
$stmt = $conn->prepare("SELECT 1 FROM `$table` WHERE username = ? AND $idCol != ? LIMIT 1");
$stmt->bind_param("si", $username, $id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username is already taken by another user.']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Check uniqueness for email
$stmt = $conn->prepare("SELECT 1 FROM `$table` WHERE email = ? AND $idCol != ? LIMIT 1");
$stmt->bind_param("si", $email, $id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email is already registered to another user.']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Update user info
$stmt = $conn->prepare("UPDATE `$table` SET full_name = ?, username = ?, email = ?, contact_number = ? WHERE $idCol = ?");
$stmt->bind_param("ssssi", $fullName, $username, $email, $contact, $id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Profile updated successfully!',
        'user' => [
            'full_name' => $fullName,
            'username' => $username,
            'email' => $email,
            'contact_number' => $contact
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
