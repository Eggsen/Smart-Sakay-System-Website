<?php
require_once 'db.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

if (empty($id) || empty($status)) {
    echo "Invalid data.";
    exit;
}

$stmt = $conn->prepare("UPDATE complaint SET status=? WHERE complaint_id=?");
if ($stmt) {
    $stmt->bind_param("si", $status, $id);
    if ($stmt->execute()) {
        echo "Status updated successfully.";
    } else {
        echo "Failed to update status.";
    }
    $stmt->close();
}

$conn->close();
?>
