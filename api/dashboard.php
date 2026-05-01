<?php
    header("Content-Type: application/json");
    include "db.php";

    $response = [];

    // ─── 1. TOTAL TRIPS TODAY ─────────────────────────────────────────────────
    $tripsToday = $conn->query("
        SELECT COUNT(*) AS total
        FROM trip
        WHERE DATE(created_at) = CURDATE()
    ")->fetch_assoc()['total'];

    // Fallback: if no trips today, count all trips (for demo data)
    if ((int)$tripsToday === 0) {
        $tripsToday = $conn->query("SELECT COUNT(*) AS total FROM trip")->fetch_assoc()['total'];
    }

    // ─── 2. TOTAL PASSENGERS ─────────────────────────────────────────────────
    $totalPassengers = $conn->query("
        SELECT COALESCE(SUM(quantity), 0) AS total
        FROM passenger_log
        WHERE action = 'Board'
    ")->fetch_assoc()['total'];

    // ─── 3. TOTAL REVENUE ────────────────────────────────────────────────────
    $totalRevenue = $conn->query("
        SELECT COALESCE(SUM(total_fare), 0) AS total
        FROM trip
    ")->fetch_assoc()['total'];

    // ─── 4. ACTIVE TRIPS ─────────────────────────────────────────────────────
    $activeTrips = $conn->query("
        SELECT COUNT(*) AS total
        FROM trip
        WHERE status = 'Active'
    ")->fetch_assoc()['total'];

    // ─── 5. PASSENGER TYPE BREAKDOWN ────────────────────────────────────────
    $breakdownResult = $conn->query("
        SELECT passenger_type, COALESCE(SUM(quantity), 0) AS total
        FROM passenger_log
        WHERE action = 'Board'
        GROUP BY passenger_type
    ");

    $breakdown = ['Student' => 0, 'Regular' => 0, 'Senior' => 0];
    while ($row = $breakdownResult->fetch_assoc()) {
        $breakdown[$row['passenger_type']] = (int)$row['total'];
    }

    // ─── 6. HOURLY PASSENGER FLOW ────────────────────────────────────────────
    // Group boarding and drop-off counts by hour (0–23)
    $hourlyResult = $conn->query("
        SELECT 
            HOUR(logged_at) AS hour,
            action,
            COALESCE(SUM(quantity), 0) AS total
        FROM passenger_log
        GROUP BY HOUR(logged_at), action
        ORDER BY hour ASC
    ");

    // Initialize all 24 hours
    $hourly = [];
    for ($h = 0; $h < 24; $h++) {
        $hourly[$h] = ['board' => 0, 'drop' => 0];
    }

    while ($row = $hourlyResult->fetch_assoc()) {
        $h = (int)$row['hour'];
        if ($row['action'] === 'Board') {
            $hourly[$h]['board'] = (int)$row['total'];
        } else {
            $hourly[$h]['drop'] = (int)$row['total'];
        }
    }

    // Only return hours that have data (trim leading/trailing empty hours)
    $firstHour = 0;
    $lastHour  = 23;
    foreach ($hourly as $h => $v) {
        if ($v['board'] > 0 || $v['drop'] > 0) {
            $firstHour = $h;
            break;
        }
    }
    for ($h = 23; $h >= 0; $h--) {
        if ($hourly[$h]['board'] > 0 || $hourly[$h]['drop'] > 0) {
            $lastHour = $h;
            break;
        }
    }

    $hourlyLabels = [];
    $hourlyBoard  = [];
    $hourlyDrop   = [];

    for ($h = $firstHour; $h <= $lastHour; $h++) {
        $label = sprintf('%02d:00', $h);
        $hourlyLabels[] = $label;
        $hourlyBoard[]  = $hourly[$h]['board'];
        $hourlyDrop[]   = $hourly[$h]['drop'];
    }

    // ─── RESPONSE ────────────────────────────────────────────────────────────
    $response = [
        'stats' => [
            'tripsToday'      => (int)$tripsToday,
            'totalPassengers' => (int)$totalPassengers,
            'totalRevenue'    => (float)$totalRevenue,
            'activeTrips'     => (int)$activeTrips,
        ],
        'breakdown' => $breakdown,
        'hourly' => [
            'labels' => $hourlyLabels,
            'board'  => $hourlyBoard,
            'drop'   => $hourlyDrop,
        ]
    ];

    echo json_encode($response);
?>
