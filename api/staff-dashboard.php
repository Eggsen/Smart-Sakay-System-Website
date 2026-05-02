<?php
ini_set('display_errors', 0);
error_reporting(0);
require_once 'db.php';
header('Content-Type: application/json');

$stats = [
    'complaints' => 0,
    'violations' => 0,
    'active_trips' => 0
];

// Count Pending Complaints
$res = $conn->query("SELECT COUNT(*) as count FROM complaint WHERE status = 'Pending'");
if ($res && $row = $res->fetch_assoc()) {
    $stats['complaints'] = (int)$row['count'];
}

// Count Total Violations
$res = $conn->query("SELECT COUNT(*) as count FROM violation");
if ($res && $row = $res->fetch_assoc()) {
    $stats['violations'] = (int)$row['count'];
}

// Count Active Trips
$res = $conn->query("SELECT COUNT(*) as count FROM trip WHERE status = 'Active'");
if ($res && $row = $res->fetch_assoc()) {
    $stats['active_trips'] = (int)$row['count'];
}

echo json_encode($stats);
$conn->close();
?>
