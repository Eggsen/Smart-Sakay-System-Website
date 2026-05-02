<?php
require_once __DIR__ . '/db.php';
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');

$sql = "SELECT * FROM vehicle ORDER BY vehicle_id ASC";
$result = $conn->query($sql);

$vehicles = [];

while ($row = $result->fetch_assoc()) {
    $vehicles[] = [
        "id" => $row["vehicle_id"],
        "plate" => $row["plate_number"],
        "type" => $row["vehicle_type"],
        "capacity" => $row["capacity"],
        "color" => $row["color_markings"],
        "status" => $row["status"],
        "created_at" => $row["created_at"]
    ];
}

header('Content-Type: application/json');
echo json_encode($vehicles);
exit;