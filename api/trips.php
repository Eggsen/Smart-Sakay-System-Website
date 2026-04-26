<?php
    header("Content-Type: application/json");
    include "db.php";

    // GET ALL TRIPS
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        $sql = "
            SELECT 
                t.trip_id,
                d.full_name AS driver,
                d.license_number,
                d.contact_number,
                v.plate_number,
                v.vehicle_type,
                r.route_name,
                t.status,
                t.total_fare
            FROM TRIP t
            JOIN DRIVER d ON t.driver_id = d.driver_id
            JOIN VEHICLE v ON t.vehicle_id = v.vehicle_id
            JOIN ROUTE r ON t.route_id = r.route_id
            ORDER BY t.created_at DESC
        ";

        $result = $conn->query($sql);

        $trips = [];

        while ($row = $result->fetch_assoc()) {

            // Count passengers per type
            $tripId = $row['trip_id'];

            $paxQuery = "
                SELECT 
                    passenger_type,
                    SUM(quantity) as total
                FROM PASSENGER_LOG
                WHERE trip_id = '$tripId' AND action = 'Board'
                GROUP BY passenger_type
            ";

            $paxResult = $conn->query($paxQuery);

            $pax = [
                "Student" => 0,
                "Regular" => 0,
                "Senior"  => 0
            ];

            while ($p = $paxResult->fetch_assoc()) {
                $pax[$p['passenger_type']] = (int)$p['total'];
            }

            $trips[] = [
                "id" => $row['trip_id'],
                "driver" => $row['driver'],
                "vehicle" => $row['plate_number'] . " (" . $row['vehicle_type'] . ")",
                "route" => $row['route_name'],
                "status" => $row['status'],
                "paxStudent" => $pax["Student"],
                "paxRegular" => $pax["Regular"],
                "paxSenior" => $pax["Senior"],
                "fare" => "₱" . number_format($row['total_fare'], 2),
                "license" => $row['license_number'],
                "contact" => $row['contact_number']
            ];
        }

        echo json_encode($trips);
    }
?>