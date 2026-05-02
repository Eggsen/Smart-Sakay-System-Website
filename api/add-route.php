<?php
ini_set('display_errors', 0);
error_reporting(0);
require_once __DIR__ . '/db.php';

$name = $_POST['name'];
$distance = $_POST['distance'];
$stops = $_POST['stops']; // comma-separated

// 1. Insert route
$sql = "INSERT INTO route (route_name, distance_km) VALUES (?, ?)";
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
        INSERT INTO stop (route_id, stop_name, stop_order)
        VALUES (?, ?, ?)
    ");

    $stmt2->bind_param("isi", $route_id, $stopName, $order);
    $stmt2->execute();

    $order++;
}

echo "Route added successfully";