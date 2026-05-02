<?php
ini_set('display_errors', 0);
error_reporting(0);
require_once 'db.php';
header('Content-Type: application/json');

$token       = isset($_POST['token'])            ? trim($_POST['token'])            : '';
$new_pw      = isset($_POST['new_password'])     ? $_POST['new_password']           : '';
$confirm_pw  = isset($_POST['confirm_password']) ? $_POST['confirm_password']       : '';

// ── Basic validation ─────────────────────────────────────────────────
if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing reset token.']);
    exit;
}

if (empty($new_pw) || empty($confirm_pw)) {
    echo json_encode(['success' => false, 'message' => 'Both password fields are required.']);
    exit;
}

if (strlen($new_pw) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
    exit;
}

if ($new_pw !== $confirm_pw) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit;
}

// ── Validate token (must exist and not be expired) ───────────────────
$stmt = $conn->prepare(
    "SELECT reset_id, email FROM password_reset WHERE token = ? AND expires_at > NOW() LIMIT 1"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'This reset link is invalid or has expired. Please request a new one.']);
    $stmt->close();
    $conn->close();
    exit;
}

$row      = $res->fetch_assoc();
$reset_id = $row['reset_id'];
$email    = $row['email'];
$stmt->close();

// ── Hash the new password ────────────────────────────────────────────
$password_hash = password_hash($new_pw, PASSWORD_DEFAULT);

// ── Update password in admin table (if email found there) ────────────
$updated = false;

$stmt = $conn->prepare("UPDATE admin SET password_hash = ? WHERE email = ?");
$stmt->bind_param("ss", $password_hash, $email);
$stmt->execute();
if ($stmt->affected_rows > 0) {
    $updated = true;
}
$stmt->close();

// ── Update password in staff table (if not updated in admin) ─────────
if (!$updated) {
    $stmt = $conn->prepare("UPDATE staff SET password_hash = ? WHERE email = ?");
    $stmt->bind_param("ss", $password_hash, $email);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $updated = true;
    }
    $stmt->close();
}

if (!$updated) {
    echo json_encode(['success' => false, 'message' => 'Could not update password. Please try again.']);
    $conn->close();
    exit;
}

// ── Delete the used token (one-time use) ─────────────────────────────
$stmt = $conn->prepare("DELETE FROM password_reset WHERE reset_id = ?");
$stmt->bind_param("i", $reset_id);
$stmt->execute();
$stmt->close();

$conn->close();

echo json_encode(['success' => true, 'message' => 'Your password has been reset successfully! You can now log in.']);
?>
