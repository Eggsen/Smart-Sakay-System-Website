<?php
require_once 'db.php';
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');

$role_type   = isset($_POST['role_type'])   ? trim($_POST['role_type'])   : 'Admin';
$first_name  = isset($_POST['first_name'])  ? trim($_POST['first_name'])  : '';
$last_name   = isset($_POST['last_name'])   ? trim($_POST['last_name'])   : '';
$username    = isset($_POST['username'])    ? trim($_POST['username'])    : '';
$email       = isset($_POST['email'])       ? trim($_POST['email'])       : '';
$contact     = isset($_POST['contact'])     ? trim($_POST['contact'])     : '';
$password    = isset($_POST['password'])    ? $_POST['password']          : '';
$confirm_pw  = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// ── Basic validation ────────────────────────────────────────────────
if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($password) || empty($confirm_pw)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
    exit;
}

if ($password !== $confirm_pw) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit;
}

$full_name = $first_name . ' ' . $last_name;
$table     = ($role_type === 'Staff') ? 'staff' : 'admin';

// ── Username uniqueness check ───────────────────────────────────────
$stmt = $conn->prepare("SELECT 1 FROM `$table` WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'That username is already taken. Please choose a different one.']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// ── Email uniqueness check ──────────────────────────────────────────
$stmt = $conn->prepare("SELECT 1 FROM `$table` WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'An account with that email already exists.']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// ── Insert new user ─────────────────────────────────────────────────
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    if ($role_type === 'Staff') {
        $stmt = $conn->prepare(
            "INSERT INTO staff (full_name, username, email, contact_number, password_hash, status) VALUES (?, ?, ?, ?, ?, 'Active')"
        );
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("sssss", $full_name, $username, $email, $contact, $password_hash);
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO admin (full_name, username, email, contact_number, password_hash, role) VALUES (?, ?, ?, ?, ?, 'Super Admin')"
        );
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("sssss", $full_name, $username, $email, $contact, $password_hash);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Account created successfully! You can now log in.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    $conn->close();
    exit;
}
