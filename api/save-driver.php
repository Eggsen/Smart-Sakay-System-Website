<?php
$conn = new mysqli("localhost", "root", "", "smart_sakay_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id      = $_POST['id'];
$name    = $_POST['name'];
$license = $_POST['license'];
$contact = $_POST['contact'];
$status  = $_POST['status'];

if ($id == "") {
    // INSERT
    $sql = "INSERT INTO DRIVER (full_name, license_number, contact_number, status)
            VALUES ('$name', '$license', '$contact', '$status')";

    if ($conn->query($sql)) {
        echo "Driver added successfully";
    } else {
        echo "Error: " . $conn->error;
    }

} else {
    // UPDATE
    $sql = "UPDATE DRIVER SET
            full_name='$name',
            license_number='$license',
            contact_number='$contact',
            status='$status'
            WHERE driver_id=$id";

    if ($conn->query($sql)) {
        echo "Driver updated successfully";
    } else {
        echo "Error: " . $conn->error;
    }
}