<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

// Safe single-value query helper
function qVal($conn, $sql, $col, $default = 0) {
    $r = $conn->query($sql);
    if (!$r) return $default;
    $row = $r->fetch_assoc();
    return $row[$col] ?? $default;
}

// ─── 1. TOTAL TRIPS TODAY ─────────────────────────────────────────────────
$tripsToday = (int)qVal($conn,
    "SELECT COUNT(*) AS total FROM trip WHERE DATE(created_at) = CURDATE()",
    'total');

// Fallback: count all trips when none today
if ($tripsToday === 0) {
    $tripsToday = (int)qVal($conn, "SELECT COUNT(*) AS total FROM trip", 'total');
}

// ─── 2. TOTAL PASSENGERS ─────────────────────────────────────────────────
$totalPassengers = (int)qVal($conn,
    "SELECT COALESCE(SUM(quantity), 0) AS total FROM passenger_log WHERE action = 'Board'",
    'total');

// ─── 3. TOTAL REVENUE ────────────────────────────────────────────────────
$totalRevenue = (float)qVal($conn,
    "SELECT COALESCE(SUM(total_fare), 0) AS total FROM trip",
    'total');

// ─── 4. ACTIVE TRIPS ─────────────────────────────────────────────────────
$activeTrips = (int)qVal($conn,
    "SELECT COUNT(*) AS total FROM trip WHERE status = 'Active'",
    'total');

// ─── 5. PASSENGER TYPE BREAKDOWN ─────────────────────────────────────────
$breakdown = ['Student' => 0, 'Regular' => 0, 'Senior' => 0];
$breakdownResult = $conn->query("
    SELECT passenger_type, COALESCE(SUM(quantity), 0) AS total
    FROM passenger_log
    WHERE action = 'Board'
    GROUP BY passenger_type
");
if ($breakdownResult) {
    while ($row = $breakdownResult->fetch_assoc()) {
        if (isset($breakdown[$row['passenger_type']])) {
            $breakdown[$row['passenger_type']] = (int)$row['total'];
        }
    }
}

// ─── 6. HOURLY PASSENGER FLOW ────────────────────────────────────────────
$hourly = [];
for ($h = 0; $h < 24; $h++) {
    $hourly[$h] = ['board' => 0, 'drop' => 0];
}

$hourlyResult = $conn->query("
    SELECT HOUR(logged_at) AS hour, action, COALESCE(SUM(quantity), 0) AS total
    FROM passenger_log
    GROUP BY HOUR(logged_at), action
    ORDER BY hour ASC
");
if ($hourlyResult) {
    while ($row = $hourlyResult->fetch_assoc()) {
        $h = (int)$row['hour'];
        if ($row['action'] === 'Board') {
            $hourly[$h]['board'] = (int)$row['total'];
        } else {
            $hourly[$h]['drop'] = (int)$row['total'];
        }
    }
}

// Trim empty leading/trailing hours
$firstHour = 0;
$lastHour  = 23;
foreach ($hourly as $h => $v) {
    if ($v['board'] > 0 || $v['drop'] > 0) { $firstHour = $h; break; }
}
for ($h = 23; $h >= 0; $h--) {
    if ($hourly[$h]['board'] > 0 || $hourly[$h]['drop'] > 0) { $lastHour = $h; break; }
}

$hourlyLabels = [];
$hourlyBoard  = [];
$hourlyDrop   = [];
for ($h = $firstHour; $h <= $lastHour; $h++) {
    $hourlyLabels[] = sprintf('%02d:00', $h);
    $hourlyBoard[]  = $hourly[$h]['board'];
    $hourlyDrop[]   = $hourly[$h]['drop'];
}

// ─── RESPONSE ────────────────────────────────────────────────────────────
echo json_encode([
    'stats' => [
        'tripsToday'      => $tripsToday,
        'totalPassengers' => $totalPassengers,
        'totalRevenue'    => $totalRevenue,
        'activeTrips'     => $activeTrips,
    ],
    'breakdown' => $breakdown,
    'hourly' => [
        'labels' => $hourlyLabels,
        'board'  => $hourlyBoard,
        'drop'   => $hourlyDrop,
    ],
], JSON_INVALID_UTF8_SUBSTITUTE);
exit;
