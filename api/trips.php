<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$sql = "
    SELECT
        t.trip_id,
        d.full_name     AS driver,
        d.license_number,
        d.contact_number,
        v.plate_number,
        v.vehicle_type,
        r.route_name,
        t.status,
        t.total_fare,
        t.created_at
    FROM trip t
    JOIN driver  d ON t.driver_id  = d.driver_id
    JOIN vehicle v ON t.vehicle_id = v.vehicle_id
    JOIN route   r ON t.route_id   = r.route_id
    ORDER BY t.created_at DESC
";

$result = $conn->query($sql);

// If the main query fails (e.g. table issue), return empty array — not a fatal crash
if (!$result) {
    echo json_encode([]);
    exit;
}

$trips = [];

while ($row = $result->fetch_assoc()) {

    $tripId = $row['trip_id'];

    // Passenger counts — use normal query instead of get_result() for shared hosting compatibility
    $pax = ['Student' => 0, 'Regular' => 0, 'Senior' => 0];

    // Escape $tripId as a string to match the VARCHAR format (e.g. 'T-001')
    $tripIdSafe = $conn->real_escape_string($tripId);
    $paxQuery = "
        SELECT passenger_type, COALESCE(SUM(quantity), 0) AS total
        FROM passenger_log
        WHERE trip_id = '$tripIdSafe' AND action = 'Board'
        GROUP BY passenger_type
    ";
    
    $paxResult = $conn->query($paxQuery);
    if ($paxResult) {
        while ($p = $paxResult->fetch_assoc()) {
            if (isset($pax[$p['passenger_type']])) {
                $pax[$p['passenger_type']] = (int)$p['total'];
            }
        }
    }

    $trips[] = [
        'id'         => $row['trip_id'],
        'driver'     => $row['driver'],
        'vehicle'    => $row['plate_number'] . ' (' . $row['vehicle_type'] . ')',
        'route'      => $row['route_name'],
        'status'     => $row['status'],
        'paxStudent' => $pax['Student'],
        'paxRegular' => $pax['Regular'],
        'paxSenior'  => $pax['Senior'],
        'fare'       => '&#8369;' . number_format((float)$row['total_fare'], 2),
        'license'    => $row['license_number'],
        'contact'    => $row['contact_number'],
        'created_at' => $row['created_at'],
    ];
}

// JSON_INVALID_UTF8_SUBSTITUTE prevents json_encode from failing if DB contains bad characters
echo json_encode($trips, JSON_INVALID_UTF8_SUBSTITUTE);
exit;