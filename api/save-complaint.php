<?php
ini_set('display_errors', 0);
error_reporting(0);
require_once 'db.php';

$id = isset($_POST['id']) ? trim($_POST['id']) : '';
$trip_id = isset($_POST['trip_id']) ? trim($_POST['trip_id']) : '';
$driver_id = isset($_POST['driver_id']) ? (int)$_POST['driver_id'] : 0;
$passenger = isset($_POST['passenger']) ? trim($_POST['passenger']) : '';
$text = isset($_POST['text']) ? trim($_POST['text']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : 'Pending';

if (empty($text)) {
    echo "Complaint text is required.";
    exit;
}

if (!empty($id)) {
    // Update
    $stmt = $conn->prepare("UPDATE complaint SET trip_id=?, driver_id=?, passenger_name=?, complaint_text=?, status=? WHERE complaint_id=?");
    if ($stmt) {
        $stmt->bind_param("sisssi", $trip_id, $driver_id, $passenger, $text, $status, $id);
        if ($stmt->execute()) {
            echo "Complaint updated successfully.";
        } else {
            echo "Failed to update complaint.";
        }
        $stmt->close();
    }
} else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO complaint (trip_id, driver_id, passenger_name, complaint_text, status) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sisss", $trip_id, $driver_id, $passenger, $text, $status);
        if ($stmt->execute()) {
            echo "Complaint added successfully.";
        } else {
            echo "Failed to add complaint.";
        }
        $stmt->close();
    }
}

$conn->close();
?>
