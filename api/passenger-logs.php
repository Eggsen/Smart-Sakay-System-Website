<?php
$conn = new mysqli("localhost", "root", "", "smart_sakay_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "
SELECT 
    pl.log_id,
    pl.trip_id,
    pl.passenger_type,
    pl.action,
    pl.quantity,
    pl.payment_status,
    pl.logged_at,
    s.stop_name
FROM passenger_log pl
JOIN stop s ON pl.stop_id = s.stop_id
ORDER BY pl.logged_at DESC
";

$result = $conn->query($sql);

$logs = [];

while ($row = $result->fetch_assoc()) {
    $logs[] = [
        "time" => $row["logged_at"],
        "trip" => $row["trip_id"],
        "type" => $row["passenger_type"],
        "action" => $row["action"],
        "qty" => $row["quantity"],
        "stop" => $row["stop_name"],
        "payment" => $row["payment_status"]
    ];
}

header('Content-Type: application/json');
echo json_encode($logs);