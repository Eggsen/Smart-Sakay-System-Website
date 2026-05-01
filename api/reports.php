<?php
    header("Content-Type: application/json");
    include "db.php";

    // ─── 1. TOTAL PASSENGERS (boarding only) ────────────────────────────────
    $totalPassengers = (int)$conn->query("
        SELECT COALESCE(SUM(quantity), 0) AS total
        FROM passenger_log
        WHERE action = 'Board'
    ")->fetch_assoc()['total'];

    // ─── 2. TOTAL REVENUE ───────────────────────────────────────────────────
    $totalRevenue = (float)$conn->query("
        SELECT COALESCE(SUM(total_fare), 0) AS total
        FROM trip
    ")->fetch_assoc()['total'];

    // ─── 3. TOTAL TRIPS ─────────────────────────────────────────────────────
    $totalTrips = (int)$conn->query("
        SELECT COUNT(*) AS total
        FROM trip
    ")->fetch_assoc()['total'];

    // ─── 4. REVENUE BY ROUTE ────────────────────────────────────────────────
    $revenueResult = $conn->query("
        SELECT
            r.route_name,
            COALESCE(SUM(t.total_fare), 0) AS total_revenue
        FROM route r
        LEFT JOIN trip t ON r.route_id = t.route_id
        GROUP BY r.route_id, r.route_name
        ORDER BY total_revenue DESC
    ");

    $revenueLabels  = [];
    $revenueValues  = [];
    while ($row = $revenueResult->fetch_assoc()) {
        $revenueLabels[] = $row['route_name'];
        $revenueValues[] = (float)$row['total_revenue'];
    }

    // ─── 5. TRIPS PER ROUTE ─────────────────────────────────────────────────
    $tripsRouteResult = $conn->query("
        SELECT
            r.route_name,
            COUNT(t.trip_id) AS trip_count
        FROM route r
        LEFT JOIN trip t ON r.route_id = t.route_id
        GROUP BY r.route_id, r.route_name
        ORDER BY trip_count DESC
    ");

    $tripsRouteLabels = [];
    $tripsRouteValues = [];
    while ($row = $tripsRouteResult->fetch_assoc()) {
        $tripsRouteLabels[] = $row['route_name'];
        $tripsRouteValues[] = (int)$row['trip_count'];
    }

    // ─── RESPONSE ────────────────────────────────────────────────────────────
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
    ]);
?>
