<?php
    header("Content-Type: application/json");
    include "db.php";

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        if (!isset($_GET['id'])) {
            echo json_encode(["error" => "Trip ID is required"]);
            exit;
        }

        $trip_id = $_GET['id'];

        // ─── TIMELINE (PASSENGER LOGS) ─────────────────────
        $timelineQuery = "
            SELECT 
                pl.logged_at,
                pl.action,
                pl.quantity,
                pl.passenger_type,
                s.stop_name
            FROM PASSENGER_LOG pl
            JOIN STOP s ON pl.stop_id = s.stop_id
            WHERE pl.trip_id = '$trip_id'
            ORDER BY pl.logged_at ASC
        ";

        $timelineResult = $conn->query($timelineQuery);

        $timeline = [];

        while ($row = $timelineResult->fetch_assoc()) {
            $timeline[] = [
                "time" => date("H:i:s", strtotime($row['logged_at'])),
                "action" => $row['action'],
                "qty" => (int)$row['quantity'],
                "type" => $row['passenger_type'],
                "stop" => $row['stop_name']
            ];
        }

        // ─── PASSENGER BREAKDOWN ───────────────────────────
        $breakdownQuery = "
            SELECT 
                passenger_type,
                SUM(quantity) as total
            FROM PASSENGER_LOG
            WHERE trip_id = '$trip_id' AND action = 'Board'
            GROUP BY passenger_type
        ";

        $breakdownResult = $conn->query($breakdownQuery);

        $breakdown = [
            "Student" => 0,
            "Regular" => 0,
            "Senior"  => 0
        ];

        while ($row = $breakdownResult->fetch_assoc()) {
            $breakdown[$row['passenger_type']] = (int)$row['total'];
        }

        echo json_encode([
            "timeline" => $timeline,
            "breakdown" => $breakdown
        ]);
    }
?>