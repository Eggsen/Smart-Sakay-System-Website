<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$routes = [];

$routeResult = $conn->query("SELECT * FROM route ORDER BY route_id ASC");

if ($routeResult) {
    while ($route = $routeResult->fetch_assoc()) {

        $route_id      = $route['route_id'];
        $first_stop_id = null;
        $stops         = [];

        // 2. Get stops for this route
        $stopResult = $conn->query("
            SELECT stop_id, stop_name, stop_order
            FROM stop
            WHERE route_id = $route_id
            ORDER BY stop_order ASC
        ");

        if ($stopResult) {
            while ($stop = $stopResult->fetch_assoc()) {
                if ($first_stop_id === null) {
                    $first_stop_id = $stop['stop_id'];
                }
                $stops[] = $stop;
            }
        }

        // 3. For each stop, get fares from the first stop
        $final_stops = [];
        foreach ($stops as $index => $stop) {
            $fare_val    = null;
            $student_val = null;
            $senior_val  = null;

            if ($index > 0 && $first_stop_id !== null) {
                $fareResult = $conn->query("
                    SELECT regular_fare, student_fare, senior_fare
                    FROM route_fare
                    WHERE route_id     = $route_id
                      AND from_stop_id = $first_stop_id
                      AND to_stop_id   = " . (int)$stop['stop_id'] . "
                    LIMIT 1
                ");
                if ($fareResult) {
                    $fareRow = $fareResult->fetch_assoc();
                    if ($fareRow) {
                        $fare_val    = $fareRow['regular_fare'];
                        $student_val = $fareRow['student_fare'];
                        $senior_val  = $fareRow['senior_fare'];
                    }
                }
            }

            $final_stops[] = [
                'stop_id'      => $stop['stop_id'],
                'stop_name'    => $stop['stop_name'],
                'fare'         => $fare_val,
                'student_fare' => $student_val,
                'senior_fare'  => $senior_val,
            ];
        }

        $routes[] = [
            'id'       => $route_id,
            'name'     => $route['route_name'],
            'distance' => $route['distance_km'],
            'stops'    => $final_stops,
        ];
    }
}

echo json_encode($routes, JSON_INVALID_UTF8_SUBSTITUTE);
exit;
