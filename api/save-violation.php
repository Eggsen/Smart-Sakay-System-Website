<?php
require_once 'db.php';

$id = isset($_POST['id']) ? trim($_POST['id']) : '';
$driver_id = isset($_POST['driver_id']) ? (int)$_POST['driver_id'] : 0;
$trip_id = isset($_POST['trip_id']) ? trim($_POST['trip_id']) : '';
$type = isset($_POST['type']) ? trim($_POST['type']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$severity = isset($_POST['severity']) ? trim($_POST['severity']) : 'Low';
$penalty = isset($_POST['penalty']) ? (float)$_POST['penalty'] : 0.00;

if (empty($driver_id) || empty($type)) {
    echo "Driver ID and Violation Type are required.";
    exit;
}

if (!empty($id)) {
    // Update
    $stmt = $conn->prepare("UPDATE violation SET driver_id=?, trip_id=?, violation_type=?, description=?, severity=?, penalty_amount=? WHERE violation_id=?");
    if ($stmt) {
        $stmt->bind_param("issssdi", $driver_id, $trip_id, $type, $description, $severity, $penalty, $id);
        if ($stmt->execute()) {
            echo "Violation updated successfully.";
        } else {
            echo "Failed to update violation.";
        }
        $stmt->close();
    }
} else {
    // Insert
    $stmt = $conn->prepare("INSERT INTO violation (driver_id, trip_id, violation_type, description, severity, penalty_amount) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issssd", $driver_id, $trip_id, $type, $description, $severity, $penalty);
        if ($stmt->execute()) {
            echo "Violation added successfully.";
        } else {
            echo "Failed to add violation.";
        }
        $stmt->close();
    }
}

$conn->close();
?>
