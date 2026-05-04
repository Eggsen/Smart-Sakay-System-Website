<?php
// ================================================================
// config.php — Central environment configuration
// ================================================================
// HOW TO USE ON DEPLOY:
//   1. Set APP_ENV to 'production'
//   2. Set DB_USER and DB_PASS to your InfinityFree cPanel DB credentials
//   3. Set DB_NAME to your full InfinityFree DB name (usually prefixed, e.g. epiz_12345678_smart_sakay_db)
//   4. Leave DB_HOST as 'localhost' (InfinityFree uses localhost internally)
// ================================================================

// ── Environment ────────────────────────────────────────────────
// Change to 'production' when deploying to InfinityFree.
define('APP_ENV', 'local'); // 'local' | 'production'

// ── Database credentials ───────────────────────────────────────
define('DB_HOST', 'localhost');

if (APP_ENV === 'production') {
    // ⚠️ Fill these in before uploading to InfinityFree
    define('DB_USER', 'if0_41809355');        // Your InfinityFree DB username
    define('DB_PASS', 'o9fD2ZLtSoSWxM');     // Your InfinityFree DB password
    define('DB_NAME', 'if0_41809355_Smart_Sakay'); // Your full DB name
} else {
    // Local XAMPP defaults
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'smart_sakay_db');
}

// ── App Base URL ───────────────────────────────────────────────
// Used for building absolute links (e.g., password reset emails).
if (APP_ENV === 'production') {
    define('APP_BASE_URL', 'https://smartsakay.free.nf');
} else {
    define('APP_BASE_URL', 'http://localhost/SmartSakay');
}

// ── SMTP / PHPMailer settings ──────────────────────────────────
define('MAIL_FROM_ADDRESS', 'tanneryoly07@gmail.com');
define('MAIL_FROM_NAME',    'Smart Sakay');
define('SMTP_HOST',         'smtp.gmail.com');
define('SMTP_USER',         'tanneryoly07@gmail.com');
define('SMTP_PASS',         str_replace(' ', '', 'rnov wizh ussr guxs')); // App password
define('SMTP_PORT',         587);
