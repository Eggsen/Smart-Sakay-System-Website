<?php
$conn = mysqli_connect("localhost", "root", "", "smart_sakay_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$routes = [];

// 1. Get all routes
$routeQuery = "SELECT * FROM route ORDER BY route_id ASC";
$routeResult = mysqli_query($conn, $routeQuery);

while ($route = mysqli_fetch_assoc($routeResult)) {
    $route_id = $route['route_id'];

    // 2. Get stops for this route
    $stopQuery = "
        SELECT stop_id, stop_name, stop_order
        FROM stop
        WHERE route_id = $route_id
        ORDER BY stop_order ASC
    ";
    
    $stopResult = mysqli_query($conn, $stopQuery);
    
    $stops = [];
    $first_stop_id = null;
    while ($stop = mysqli_fetch_assoc($stopResult)) {
        if ($first_stop_id === null) {
            $first_stop_id = $stop['stop_id'];
        }
        $stops[] = $stop;
    }
    
    // 3. For each stop, get the fares from the first stop
    $final_stops = [];
    foreach ($stops as $index => $stop) {
        $fare_val = null;
        $student_val = null;
        $senior_val = null;
        if ($index > 0 && $first_stop_id !== null) {
            $fareQuery = "
                SELECT regular_fare, student_fare, senior_fare 
                FROM route_fare 
                WHERE route_id = $route_id 
                AND from_stop_id = $first_stop_id 
                AND to_stop_id = " . $stop['stop_id'] . "
                LIMIT 1
            ";
            $fareResult = mysqli_query($conn, $fareQuery);
            if ($fareRow = mysqli_fetch_assoc($fareResult)) {
                $fare_val = $fareRow['regular_fare'];
                $student_val = $fareRow['student_fare'];
                $senior_val = $fareRow['senior_fare'];
            }
        }
        
        $final_stops[] = [
            "stop_id" => $stop['stop_id'],
            "stop_name" => $stop['stop_name'],
            "fare" => $fare_val,
            "student_fare" => $student_val,
            "senior_fare" => $senior_val
        ];
    }

    $routes[] = [
        "id" => $route_id,
        "name" => $route['route_name'],
        "distance" => $route['distance_km'],
        "stops" => $final_stops
    ];
}

header('Content-Type: application/json');
echo json_encode($routes);

mysqli_close($conn);
?>
