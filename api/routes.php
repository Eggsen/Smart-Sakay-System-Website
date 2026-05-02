<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$routes = [];

$routeResult = $conn->query("SELECT * FROM route ORDER BY route_id ASC");

if ($routeResult) {
    while ($route = $routeResult->fetch_assoc()) {

        $route_id = $route['route_id'];

        $stopResult = $conn->query("
            SELECT stop_name FROM stop
            WHERE route_id = $route_id
            ORDER BY stop_order ASC
        ");

        $stops = [];
        if ($stopResult) {
            while ($stop = $stopResult->fetch_assoc()) {
                $stops[] = $stop['stop_name'];
            }
        }

        $routes[] = [
            'id'       => $route_id,
            'name'     => $route['route_name'],
            'distance' => $route['distance_km'],
            'stops'    => $stops,
        ];
    }
}

echo json_encode($routes, JSON_INVALID_UTF8_SUBSTITUTE);
exit;