<?php
$conn = new mysqli("localhost", "root", "", "smart_sakay_db");

if ($conn->connect_error) {
    die("Connection failed:" . $conn->connect_error);
}

$routes = [];

// 1. Get all routes
$routeQuery = "SELECT * FROM route ORDER BY route_id ASC";
$routeResult = $conn->query($routeQuery);

while ($route = $routeResult->fetch_assoc()) {

    $route_id = $route['route_id'];

    // 2. Get stops for this route
    $stopQuery = "
        SELECT stop_name
        FROM STOP
        WHERE route_id = $route_id
        ORDER BY stop_order ASC
    ";

    $stopResult = $conn->query($stopQuery);

    $stops = [];

    while ($stop = $stopResult->fetch_assoc()) {
        $stops[] = $stop['stop_name'];
    }

    // 3. Combine route + stops
    $routes[] = [
        "id" => $route_id,
        "name" => $route['route_name'],
        "distance" => $route['distance_km'],
        "stops" => $stops
    ];
}

header('Content-Type: application/json');
echo json_encode($routes);