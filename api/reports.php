<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

// Helper: run a query and return first row's column, or default on failure
function queryVal($conn, $sql, $col, $default = 0) {
    $r = $conn->query($sql);
    if (!$r) return $default;
    $row = $r->fetch_assoc();
    return $row[$col] ?? $default;
}

// ─── 1. SUMMARY STATS ────────────────────────────────────────────────────────
$totalPassengers = (int)queryVal($conn,
    "SELECT COALESCE(SUM(quantity), 0) AS total FROM passenger_log WHERE action = 'Board'",
    'total');

$totalRevenue = (float)queryVal($conn,
    "SELECT COALESCE(SUM(total_fare), 0) AS total FROM trip",
    'total');

$totalTrips = (int)queryVal($conn,
    "SELECT COUNT(*) AS total FROM trip",
    'total');

// ─── 2. REVENUE BY ROUTE ─────────────────────────────────────────────────────
$revenueLabels = [];
$revenueValues = [];

$revenueResult = $conn->query("
    SELECT r.route_name, COALESCE(SUM(t.total_fare), 0) AS total_revenue
    FROM route r
    LEFT JOIN trip t ON r.route_id = t.route_id
    GROUP BY r.route_id, r.route_name
    ORDER BY total_revenue DESC
");
if ($revenueResult) {
    while ($row = $revenueResult->fetch_assoc()) {
        $revenueLabels[] = $row['route_name'];
        $revenueValues[] = (float)$row['total_revenue'];
    }
}

// ─── 3. TRIPS PER ROUTE ──────────────────────────────────────────────────────
$tripsRouteLabels = [];
$tripsRouteValues = [];

$tripsRouteResult = $conn->query("
    SELECT r.route_name, COUNT(t.trip_id) AS trip_count
    FROM route r
    LEFT JOIN trip t ON r.route_id = t.route_id
    GROUP BY r.route_id, r.route_name
    ORDER BY trip_count DESC
");
if ($tripsRouteResult) {
    while ($row = $tripsRouteResult->fetch_assoc()) {
        $tripsRouteLabels[] = $row['route_name'];
        $tripsRouteValues[] = (int)$row['trip_count'];
    }
}

// ─── RESPONSE ────────────────────────────────────────────────────────────────
echo json_encode([
    'summary' => [
        'totalPassengers' => $totalPassengers,
        'totalRevenue'    => $totalRevenue,
        'totalTrips'      => $totalTrips,
    ],
    'revenueByRoute' => [
        'labels' => $revenueLabels,
        'values' => $revenueValues,
    ],
    'tripsPerRoute' => [
        'labels' => $tripsRouteLabels,
        'values' => $tripsRouteValues,
    ],
], JSON_INVALID_UTF8_SUBSTITUTE);
exit;
