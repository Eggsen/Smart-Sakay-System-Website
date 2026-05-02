<?php
require_once 'db.php';
header('Content-Type: application/json');

// Lightweight endpoint: just check if token exists and has not expired.
// Returns { valid: true/false }

$token = isset($_POST['token']) ? trim($_POST['token']) : '';

if (empty($token)) {
    echo json_encode(['valid' => false]);
    $conn->close();
    exit;
}

$stmt = $conn->prepare(
    "SELECT reset_id FROM password_reset WHERE token = ? AND expires_at > NOW() LIMIT 1"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

$valid = ($res->num_rows === 1);
$stmt->close();
$conn->close();

echo json_encode(['valid' => $valid]);
?>
