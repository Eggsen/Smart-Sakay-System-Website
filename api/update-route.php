<?php
ini_set('display_errors', 0);
error_reporting(0);
require_once __DIR__ . '/db.php';


$id = $_POST['id'];
$name = $_POST['name'];
$distance = $_POST['distance'];
$stops = $_POST['stops'];

// 1. Update route
$stmt = $conn->prepare("
    UPDATE route 
    SET route_name = ?, distance_km = ?
    WHERE route_id = ?
");
$stmt->bind_param("sdi", $name, $distance, $id);
$stmt->execute();

// 2. Delete old stops
$conn->query("DELETE FROM stop WHERE route_id = $id");

// 3. Insert new stops
$stopArray = explode(",", $stops);

$order = 1;
foreach ($stopArray as $s) {

    $stopName = trim($s);
    if ($stopName === "") continue;

    $stmt2 = $conn->prepare("
        INSERT INTO stop (route_id, stop_name, stop_order)
        VALUES (?, ?, ?)
    ");

    $stmt2->bind_param("isi", $id, $stopName, $order);
    $stmt2->execute();

    $order++;
}

echo "Route updated";