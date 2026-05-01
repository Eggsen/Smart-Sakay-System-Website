<?php
require_once 'db.php';

// Check if admin exists, if not create one
$res = $conn->query("SELECT * FROM admin WHERE username = 'admin123'");
if ($res->num_rows == 0) {
    // We will store passwords as plain text or simple md5 for simplicity since this is beginner friendly, 
    // but password_hash is better. Let's use simple string for beginner friendliness if we don't know what they prefer, 
    // but the DB says 'password_hash'. I will use password_hash.
    $hash = password_hash('password', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admin (full_name, username, contact_number, password_hash, role) VALUES ('Super Admin', 'admin123', 09171234567, '$hash', 'Super Admin')");
    echo "Inserted admin: admin123 / password<br>";
} else {
    echo "Admin already exists.<br>";
}

// Check if staff exists, if not create one
$res = $conn->query("SELECT * FROM staff WHERE username = 'staff123'");
if ($res->num_rows == 0) {
    $hash = password_hash('password', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO staff (full_name, username, contact_number, password_hash, status) VALUES ('Jane Doe', 'staff123', '09181234567', '$hash', 'Active')");
    echo "Inserted staff: staff123 / password<br>";
} else {
    echo "Staff already exists.<br>";
}

// Insert dummy complaints if empty
$res = $conn->query("SELECT * FROM complaint");
if ($res->num_rows == 0) {
    $conn->query("INSERT INTO complaint (trip_id, driver_id, passenger_name, complaint_text, status) VALUES 
        ('T-001', 1, 'Mark Smith', 'The driver was driving too fast.', 'Pending'),
        ('T-002', 2, 'Anna Taylor', 'Driver was rude when asked for change.', 'Resolved')");
    echo "Inserted dummy complaints.<br>";
}

// Insert dummy violations if empty
$res = $conn->query("SELECT * FROM violation");
if ($res->num_rows == 0) {
    $conn->query("INSERT INTO violation (driver_id, trip_id, violation_type, description, severity, penalty_amount) VALUES 
        (1, 'T-001', 'Over-speeding', 'Caught on camera going over 80km/h in a 60km/h zone.', 'High', 1500.00),
        (3, 'T-003', 'No Seatbelt', 'Driver was not wearing seatbelt during inspection.', 'Low', 500.00)");
    echo "Inserted dummy violations.<br>";
}

echo "Database seeding complete.";
?>
