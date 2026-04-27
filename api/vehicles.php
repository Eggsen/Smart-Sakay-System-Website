<?php
$conn = new mysqli("localhost", "root", "", "smart_sakay_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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