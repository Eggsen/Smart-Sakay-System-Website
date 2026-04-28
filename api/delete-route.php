<?php
$conn = new mysqli("localhost", "root", "", "smart_sakay_db");

$id = $_POST['id'];

$stmt = $conn->prepare("DELETE FROM ROUTE WHERE route_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

echo "Route deleted";