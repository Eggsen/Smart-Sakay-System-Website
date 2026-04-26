<?php
$conn = new mysqli("localhost", "root", "", "smart_sakay_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['id'];

$sql = "DELETE FROM DRIVER WHERE driver_id=$id";

if ($conn->query($sql)) {
    echo "Driver deleted successfully";
} else {
    echo "Error: " . $conn->error;
}