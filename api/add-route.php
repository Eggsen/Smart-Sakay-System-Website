<?php
$conn = new mysqli("localhost", "root", "", "smart_sakay_db");

if ($conn->connect_error) {
    die("Connection failed");
}

$name = $_POST['name'];
$distance = $_POST['distance'];
$stops = $_POST['stops']; // comma-separated

// 1. Insert route
$sql = "INSERT INTO ROUTE (route_name, distance_km) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sd", $name, $distance);
$stmt->execute();

$route_id = $stmt->insert_id;

// 2. Insert stops
$stopArray = explode(",", $stops);

$order = 1;
foreach ($stopArray as $s) {
    $stopName = trim($s);

    if ($stopName === "") continue;

    $stmt2 = $conn->prepare("
        INSERT INTO STOP (route_id, stop_name, stop_order)
        VALUES (?, ?, ?)
    ");

    $stmt2->bind_param("isi", $route_id, $stopName, $order);
    $stmt2->execute();

    $order++;
}

echo "Route added successfully";