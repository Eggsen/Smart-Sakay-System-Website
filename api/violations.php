<?php
require_once 'db.php';
header('Content-Type: application/json');

$query = "
    SELECT 
        v.violation_id as id,
        d.full_name as driver,
        v.trip_id as trip,
        v.violation_type as type,
        v.description as description,
        v.severity as severity,
        v.penalty_amount as penalty,
        DATE_FORMAT(v.created_at, '%b %d, %Y %h:%i %p') as created_at
    FROM violation v
    JOIN driver d ON v.driver_id = d.driver_id
    ORDER BY v.created_at DESC
";

$result = $conn->query($query);

$violations = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $violations[] = $row;
    }
}

echo json_encode($violations);
$conn->close();
?>
