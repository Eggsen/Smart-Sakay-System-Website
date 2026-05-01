<?php
require_once 'db.php';
header('Content-Type: application/json');

$query = "
    SELECT 
        c.complaint_id as id,
        c.trip_id as trip,
        d.full_name as driver,
        c.passenger_name as passenger,
        c.complaint_text as text,
        c.status as status,
        DATE_FORMAT(c.created_at, '%b %d, %Y %h:%i %p') as created_at
    FROM complaint c
    LEFT JOIN driver d ON c.driver_id = d.driver_id
    ORDER BY c.created_at DESC
";

$result = $conn->query($query);

$complaints = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $complaints[] = $row;
    }
}

echo json_encode($complaints);
$conn->close();
?>
