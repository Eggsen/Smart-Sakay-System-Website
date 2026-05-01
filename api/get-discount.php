<?php
$conn = mysqli_connect("localhost", "root", "", "smart_sakay_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch the first fare to determine current discount percentages
$query = "SELECT regular_fare, student_fare, senior_fare FROM route_fare WHERE regular_fare > 0 LIMIT 1";
$result = mysqli_query($conn, $query);

if ($row = mysqli_fetch_assoc($result)) {
    $reg = floatval($row['regular_fare']);
    $stu = floatval($row['student_fare']);
    $sen = floatval($row['senior_fare']);
    
    // Calculate discount percentages
    $stu_discount = ($reg > 0) ? round((1 - ($stu / $reg)) * 100) : 0;
    $sen_discount = ($reg > 0) ? round((1 - ($sen / $reg)) * 100) : 0;
    
    echo json_encode([
        "student_discount" => $stu_discount,
        "senior_discount" => $sen_discount
    ]);
} else {
    // Default if no records exist
    echo json_encode([
        "student_discount" => 20,
        "senior_discount" => 20
    ]);
}

mysqli_close($conn);
?>
