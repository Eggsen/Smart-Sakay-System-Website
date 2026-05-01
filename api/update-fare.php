<?php
$conn = mysqli_connect("localhost", "root", "", "smart_sakay_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$stop_id = $_POST['stop_id'] ?? '';
$route_id = $_POST['route_id'] ?? '';
$stop_name = $_POST['stop_name'] ?? '';
$regular_fare = $_POST['regular_fare'] ?? '';
$student_fare = $_POST['student_fare'] ?? '';
$senior_fare = $_POST['senior_fare'] ?? '';

if (!$stop_id || !$route_id || !$stop_name) {
    echo "Missing required fields";
    exit;
}

// Update stop name
$stmt1 = mysqli_prepare($conn, "UPDATE stop SET stop_name = ? WHERE stop_id = ?");
mysqli_stmt_bind_param($stmt1, "si", $stop_name, $stop_id);
mysqli_stmt_execute($stmt1);

// Update fare if provided
if ($regular_fare !== '' && $student_fare !== '' && $senior_fare !== '') {
    // Find the first stop of this route to act as from_stop_id
    $firstStopQuery = "SELECT stop_id FROM stop WHERE route_id = $route_id ORDER BY stop_order ASC LIMIT 1";
    $firstStopResult = mysqli_query($conn, $firstStopQuery);
    
    if ($firstStopRow = mysqli_fetch_assoc($firstStopResult)) {
        $first_stop_id = $firstStopRow['stop_id'];
        
        // Only update if current stop is not the first stop
        if ($first_stop_id != $stop_id) {
            $stmt2 = mysqli_prepare($conn, "UPDATE route_fare SET regular_fare = ?, student_fare = ?, senior_fare = ? WHERE route_id = ? AND from_stop_id = ? AND to_stop_id = ?");
            mysqli_stmt_bind_param($stmt2, "dddiii", $regular_fare, $student_fare, $senior_fare, $route_id, $first_stop_id, $stop_id);
            mysqli_stmt_execute($stmt2);
        }
    }
}

echo "Success";
mysqli_close($conn);
?>
