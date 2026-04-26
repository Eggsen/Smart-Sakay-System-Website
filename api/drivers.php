<?php
$conn = new mysqli("localhost", "root", "", "smart_sakay_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM DRIVER ORDER BY driver_id ASC";
$result = $conn->query($sql);

$drivers = [];

while ($row = $result->fetch_assoc()) {
    $drivers[] = [
        "id" => $row["driver_id"],
        "name" => $row["full_name"],
        "license" => $row["license_number"],
        "contact" => $row["contact_number"],
        "status" => $row["status"]
    ];
}

header('Content-Type: application/json');
echo json_encode($drivers);