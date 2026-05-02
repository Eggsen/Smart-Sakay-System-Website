<?php
require_once 'db.php';
header('Content-Type: application/json');

// ── PHPMailer (no Composer) ──────────────────────────────────────────
require_once 'C:/xampp/htdocs/TanNery/phpmailer/src/Exception.php';
require_once 'C:/xampp/htdocs/TanNery/phpmailer/src/PHPMailer.php';
require_once 'C:/xampp/htdocs/TanNery/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── Read input ───────────────────────────────────────────────────────
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// ── Check if email exists in admin OR staff table ────────────────────
// NOTE: We do NOT reveal to the user whether the email was found (security).
$found       = false;
$found_in    = null; // 'admin' or 'staff'
$found_name  = '';

// Check admin table
$stmt = $conn->prepare("SELECT email, full_name FROM admin WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 1) {
    $found      = true;
    $found_in   = 'admin';
    $row        = $res->fetch_assoc();
    $found_name = $row['full_name'];
}
$stmt->close();

// Check staff table (only if not already found)
if (!$found) {
    $stmt = $conn->prepare("SELECT email, full_name FROM staff WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $found      = true;
        $found_in   = 'staff';
        $row        = $res->fetch_assoc();
        $found_name = $row['full_name'];
    }
    $stmt->close();
}

// ── Always respond with a generic success message (security) ─────────
// If email is not found, we stop here silently.
if (!$found) {
    echo json_encode(['success' => true, 'message' => 'If this email exists, a reset link has been sent.']);
    $conn->close();
    exit;
}

// ── Delete any existing token for this email (cleanup) ───────────────
$stmt = $conn->prepare("DELETE FROM password_reset WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->close();

// ── Generate secure token ────────────────────────────────────────────
$token = bin2hex(random_bytes(32)); // 64-char hex token

// ── Save token in password_reset table ──────────────────────────────
$stmt = $conn->prepare(
    "INSERT INTO password_reset (email, token, expires_at)
     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))"
);
$stmt->bind_param("ss", $email, $token);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Could not generate reset token. Please try again.']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();
$conn->close();

// ── Build the reset URL ──────────────────────────────────────────────
$base_url  = 'http://localhost/SmartSakay/auth-pages/auth-admin';
$reset_url = $base_url . '/reset-password.php?token=' . urlencode($token);

// ── Send email via PHPMailer ─────────────────────────────────────────
$mail = new PHPMailer(true);

try {
    // ── SMTP Configuration ──────────────────────────────────────────
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'tanneryoly07@gmail.com';
    $mail->Password   = str_replace(' ', '', 'rnov wizh ussr guxs');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // ── Fix for XAMPP SSL certificate verify failed ──────────────────
    // XAMPP's OpenSSL cannot verify Gmail's cert (missing/outdated CA bundle).
    // This is safe for local development; remove SMTPOptions on a live server
    // that has a proper CA bundle (it will verify automatically).
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ]
    ];

    // ── Email content ───────────────────────────────────────────────
    $mail->setFrom('tanneryoly07@gmail.com', 'Smart Sakay');
    $mail->addAddress($email, $found_name);
    $mail->isHTML(true);
    $mail->Subject = 'Smart Sakay – Password Reset Request';

    $mail->Body = "
    <div style=\"font-family:'Plus Jakarta Sans',Arial,sans-serif;max-width:520px;margin:auto;background:#f4f6f9;padding:32px;border-radius:12px;\">
        <div style=\"text-align:center;margin-bottom:24px;\">
            <span style=\"font-size:1.4rem;font-weight:700;color:#0b486f;\">Smart Sakay</span>
        </div>
        <div style=\"background:#fff;border-radius:10px;padding:28px;\">
            <h2 style=\"color:#0b486f;margin-top:0;\">Password Reset</h2>
            <p style=\"color:#1a2230;\">Hi <strong>{$found_name}</strong>,</p>
            <p style=\"color:#1a2230;\">We received a request to reset your password. Click the button below to create a new password. This link will expire in <strong>15 minutes</strong>.</p>
            <div style=\"text-align:center;margin:28px 0;\">
                <a href=\"{$reset_url}\" style=\"background:#FF7043;color:#fff;padding:12px 32px;border-radius:8px;text-decoration:none;font-weight:600;font-size:1rem;\">Reset Password</a>
            </div>
            <p style=\"color:#6c7a8d;font-size:0.85rem;\">If you did not request a password reset, you can safely ignore this email. Your password will not change.</p>
            <hr style=\"border:none;border-top:1px solid #e3e8ef;margin:20px 0;\">
            <p style=\"color:#6c7a8d;font-size:0.8rem;word-break:break-all;\">Or copy this link into your browser:<br><a href=\"{$reset_url}\" style=\"color:#FF7043;\">{$reset_url}</a></p>
        </div>
        <p style=\"text-align:center;color:#6c7a8d;font-size:0.75rem;margin-top:16px;\">&copy; Smart Sakay. All rights reserved.</p>
    </div>";

    $mail->AltBody = "Hi {$found_name},\n\nReset your Smart Sakay password by visiting:\n{$reset_url}\n\nThis link expires in 15 minutes.\n\nIf you did not request this, ignore this email.";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'If this email exists, a reset link has been sent.']);

} catch (Exception $e) {
    // Log the real error server-side
    error_log('PHPMailer Error: ' . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try again later.']);
}
?>
