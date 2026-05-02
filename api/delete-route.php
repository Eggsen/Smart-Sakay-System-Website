<?php
ini_set('display_errors', 0);
error_reporting(0);
require_once __DIR__ . '/db.php';


$id = $_POST['id'];

$stmt = $conn->prepare("DELETE FROM route WHERE route_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

echo "Route deleted";