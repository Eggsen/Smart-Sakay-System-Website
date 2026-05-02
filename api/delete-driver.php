<?php
ini_set('display_errors', 0);
error_reporting(0);
require_once __DIR__ . '/db.php';

$id = $_POST['id'];

$sql = "DELETE FROM driver WHERE driver_id=$id";

if ($conn->query($sql)) {
    echo "Driver deleted successfully";
} else {
    echo "Error: " . $conn->error;
}