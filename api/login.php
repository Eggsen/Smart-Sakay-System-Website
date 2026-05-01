<?php
require_once 'db.php';
header('Content-Type: application/json');

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$role_type = isset($_POST['role_type']) ? trim($_POST['role_type']) : 'Admin'; // 'Admin' or 'Staff'

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
    exit;
}

if ($role_type === 'Staff') {
    $stmt = $conn->prepare("SELECT staff_id, full_name, password_hash, status FROM staff WHERE username = ?");
} else {
    $stmt = $conn->prepare("SELECT employee_id, full_name, password_hash, role FROM admin WHERE username = ?");
}

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
    exit;
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // Verify password (either using password_verify if hashed, or plain text comparison if they saved it directly)
    $password_match = false;
    if (password_verify($password, $user['password_hash'])) {
        $password_match = true;
    } else if ($password === $user['password_hash']) {
        $password_match = true; // Fallback for plain text passwords during testing
    }
    
    if ($password_match) {
        // Check if status is Active (for staff)
        if ($role_type === 'Staff' && isset($user['status']) && $user['status'] !== 'Active') {
            echo json_encode(['success' => false, 'message' => 'Your account is inactive. Please contact support.']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'role' => strtolower($role_type),
            'user' => [
                'id' => $role_type === 'Staff' ? $user['staff_id'] : $user['employee_id'],
                'full_name' => $user['full_name']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
}

$stmt->close();
$conn->close();
?>
