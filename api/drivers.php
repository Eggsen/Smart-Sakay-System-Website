<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$result = $conn->query("SELECT * FROM driver ORDER BY driver_id ASC");

$drivers = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $drivers[] = [
            'id'      => $row['driver_id'],
            'name'    => $row['full_name'],
            'license' => $row['license_number'],
            'contact' => $row['contact_number'],
            'status'  => $row['status'],
        ];
    }
}

echo json_encode($drivers);
exit;