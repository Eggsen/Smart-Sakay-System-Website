<?php
require_once 'db.php';
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');

$identifier = isset($_POST['username']) ? trim($_POST['username']) : '';
$password   = isset($_POST['password']) ? trim($_POST['password']) : '';
$role_type  = isset($_POST['role_type']) ? trim($_POST['role_type']) : 'Admin';

if (empty($identifier) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username/email and password are required.']);
    exit;
}

// Support login by username OR email
if ($role_type === 'Staff') {
    $stmt = $conn->prepare(
        "SELECT staff_id, full_name, password_hash, status FROM staff WHERE username = ? OR email = ? LIMIT 1"
    );
} else {
    $stmt = $conn->prepare(
        "SELECT employee_id, full_name, password_hash, role FROM admin WHERE username = ? OR email = ? LIMIT 1"
    );
}

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
    exit;
}

$stmt->bind_param("ss", $identifier, $identifier);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    $password_match = false;
    if (password_verify($password, $user['password_hash'])) {
        $password_match = true;
    } elseif ($password === $user['password_hash']) {
        $password_match = true; // fallback for plain-text during dev
    }

    if ($password_match) {
        if ($role_type === 'Staff' && isset($user['status']) && $user['status'] !== 'Active') {
            echo json_encode(['success' => false, 'message' => 'Your account is inactive. Please contact support.']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'role'    => strtolower($role_type),
            'user'    => [
                'id'        => $role_type === 'Staff' ? $user['staff_id'] : $user['employee_id'],
                'full_name' => $user['full_name']
            ]
        ]);
        $stmt->close();
        $conn->close();
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials. Please check your username/email and password.']);
        $stmt->close();
        $conn->close();
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No account found with that username or email.']);
    $stmt->close();
    $conn->close();
    exit;
}
