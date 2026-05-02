<?php
ini_set('display_errors', 0);
error_reporting(0);
require_once __DIR__ . '/db.php';

$student_pct = isset($_POST['student_discount']) ? floatval($_POST['student_discount']) : null;
$senior_pct = isset($_POST['senior_discount']) ? floatval($_POST['senior_discount']) : null;

if ($student_pct === null || $senior_pct === null) {
    echo "Missing required fields";
    exit;
}

// Ensure percentages are between 0 and 100
$student_pct = max(0, min(100, $student_pct));
$senior_pct = max(0, min(100, $senior_pct));

$stu_multiplier = 1 - ($student_pct / 100);
$sen_multiplier = 1 - ($senior_pct / 100);

$query = "UPDATE route_fare SET student_fare = regular_fare * $stu_multiplier, senior_fare = regular_fare * $sen_multiplier";

if (mysqli_query($conn, $query)) {
    echo "Success";
} else {
    http_response_code(500);
    echo "Error updating discounts: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
