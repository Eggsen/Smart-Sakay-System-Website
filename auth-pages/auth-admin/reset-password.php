<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../../assets/logo/smartsakaylogo.png">
    <meta name="description" content="Smart Sakay – Reset Password. Set a new password for your account.">
    <link rel="stylesheet" href="../../bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../auth-page-style/style.css">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
    <title>Smart Sakay – Reset Password</title>
    <style>
        /* ── Spinner inside button ─────────────────────────────── */
        #resetSubmitBtn .spinner-border {
            width: 1rem;
            height: 1rem;
            border-width: 2px;
        }
        /* ── Hint / helper text ────────────────────────────────── */
        .hint-text {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-top: -4px;
        }
        /* ── Password strength bar ─────────────────────────────── */
        .strength-bar-wrap {
            height: 5px;
            background: var(--border);
            border-radius: 99px;
            overflow: hidden;
            margin-top: 4px;
        }
        .strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 99px;
            transition: width 0.3s ease, background 0.3s ease;
        }
        /* ── Invalid token / expired state ────────────────────── */
        .error-icon  { font-size: 3rem; }
        .success-icon { font-size: 3rem; }
    </style>
</head>
<body>

    <!-- Auth card -->
    <div class="auth-card card rounded-4 d-flex">
        <div class="row">
            <!-- Left side -->
            <div class="left-side col-xl-6 col-sm-12 card-body p-5">
                <div class="header d-flex justify-content-between align-items-center gap-2 mb-5">
                    <div>
                        <div class="logo-box d-flex align-items-center gap-2">
                            <img src="../../assets/logo/smartsakaylogo.png" alt="Smart Sakay Logo">
                            <span>Smart Sakay</span>
                        </div>
                    </div>
                    <a href="login.html" class="back-btn text-decoration-none d-flex align-items-center gap-1 text-muted">
                        <i class="bi bi-arrow-return-left text-muted"></i>Back to Login
                    </a>
                </div>

                <!-- Loading view while we validate the token -->
                <div id="loadingView" class="text-center py-4">
                    <div class="spinner-border" style="color:var(--primary);" role="status">
                        <span class="visually-hidden">Validating token…</span>
                    </div>
                    <p class="hint-text mt-3">Validating your reset link…</p>
                </div>

                <!-- Invalid / expired token view -->
                <div id="invalidView" class="text-center d-none">
                    <div class="error-icon mb-3">
                        <i class="bi bi-x-octagon-fill" style="color:var(--danger);"></i>
                    </div>
                    <div class="title mb-2">Link <span>Expired</span></div>
                    <p class="hint-text mb-4">
                        This password reset link is invalid or has already expired.<br>
                        Reset links are only valid for <strong>15 minutes</strong>.
                    </p>
                    <a href="forgot-password.php" class="btn login-btn px-4">Request a New Link</a>
                </div>

                <!-- Reset form view (shown when token is valid) -->
                <div id="formView" class="d-none">
                    <div class="title mb-1 text-center">Set New <span>Password</span></div>
                    <p class="text-center hint-text mb-4">Choose a strong password of at least 8 characters.</p>

                    <!-- Inline alert -->
                    <div id="resetAlert" class="alert auth-alert auth-alert--hidden" role="alert"></div>

                    <form id="resetPasswordForm" class="d-flex flex-column gap-2" novalidate>
                        <!-- Hidden token field -->
                        <input type="hidden" id="resetToken">

                        <label for="newPassword">New Password</label>
                        <div class="input-group">
                            <input
                                id="newPassword"
                                class="form-control"
                                type="password"
                                placeholder="Enter new password"
                                required
                                autocomplete="new-password"
                            >
                            <button class="btn toggle-pw" type="button" tabindex="-1" data-target="newPassword">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        <!-- Password strength indicator -->
                        <div class="strength-bar-wrap">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                        <small id="strengthLabel" class="hint-text"></small>

                        <label for="confirmPassword" class="mt-1">Confirm New Password</label>
                        <div class="input-group">
                            <input
                                id="confirmPassword"
                                class="form-control"
                                type="password"
                                placeholder="Re-enter new password"
                                required
                                autocomplete="new-password"
                            >
                            <button class="btn toggle-pw" type="button" tabindex="-1" data-target="confirmPassword">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>

                        <button type="submit" id="resetSubmitBtn" class="btn login-btn w-100 mt-3">
                            <span class="btn-text">Reset Password</span>
                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </form>
                </div>

                <!-- Success view -->
                <div id="successView" class="text-center d-none">
                    <div class="success-icon mb-3">
                        <i class="bi bi-shield-fill-check" style="color:var(--success);"></i>
                    </div>
                    <div class="title mb-2">Password <span>Updated!</span></div>
                    <p class="hint-text mb-4">
                        Your password has been reset successfully.<br>
                        You can now log in with your new password.
                    </p>
                    <a href="login.html" class="btn login-btn px-4">Go to Login</a>
                </div>
            </div>

            <!-- Right side – decorative panel -->
            <div class="right-side col-xl-6 d-flex flex-column justify-content-center align-items-center">
                <div class="mockup-img mb-4">
                    <img src="../../assets/images/webiste-mockup.png" alt="Smart Sakay Mockup">
                </div>
                <div class="subtitle">Secure Account Recovery</div>
                <div class="subtitle-2">
                    <i>Quick, safe, and easy password reset.</i>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
    $(function () {

        /* ── Read token from URL ─────────────────────────────────── */
        function getQueryParam(name) {
            const params = new URLSearchParams(window.location.search);
            return params.get(name) || '';
        }

        const token = getQueryParam('token');

        /* ── Validate token on page load via lightweight PHP check ── */
        if (!token) {
            $('#loadingView').addClass('d-none');
            $('#invalidView').removeClass('d-none');
        } else {
            // Store token in hidden field
            $('#resetToken').val(token);

            // Validate token with backend
            $.ajax({
                url: '../../api/validate-token.php',
                method: 'POST',
                data: { token: token },
                dataType: 'json',
                success: function (res) {
                    $('#loadingView').addClass('d-none');
                    if (res.valid) {
                        $('#formView').removeClass('d-none');
                    } else {
                        $('#invalidView').removeClass('d-none');
                    }
                },
                error: function () {
                    $('#loadingView').addClass('d-none');
                    $('#invalidView').removeClass('d-none');
                }
            });
        }

        /* ── Helper: show inline alert ───────────────────────────── */
        function showAlert(type, message) {
            const $alert = $('#resetAlert');
            $alert.removeClass('alert-success alert-danger auth-alert--hidden auth-alert--visible');
            $alert.addClass('alert-' + type + ' auth-alert--visible');
            $alert.html('<i class="bi bi-' + (type === 'success' ? 'check-circle' : 'exclamation-triangle') + '-fill me-2"></i>' + message);
        }

        function hideAlert() {
            $('#resetAlert').removeClass('auth-alert--visible alert-success alert-danger')
                            .addClass('auth-alert--hidden');
        }

        /* ── Loading state ───────────────────────────────────────── */
        function setLoading(loading) {
            const $btn     = $('#resetSubmitBtn');
            const $text    = $btn.find('.btn-text');
            const $spinner = $btn.find('.spinner-border');
            if (loading) {
                $btn.prop('disabled', true);
                $text.text('Updating…');
                $spinner.removeClass('d-none');
            } else {
                $btn.prop('disabled', false);
                $text.text('Reset Password');
                $spinner.addClass('d-none');
            }
        }

        /* ── Password visibility toggle ──────────────────────────── */
        $(document).on('click', '.toggle-pw', function () {
            const targetId = $(this).data('target');
            const $input   = $('#' + targetId);
            const $icon    = $(this).find('i');
            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $icon.removeClass('bi-eye-slash').addClass('bi-eye');
            } else {
                $input.attr('type', 'password');
                $icon.removeClass('bi-eye').addClass('bi-eye-slash');
            }
        });

        /* ── Password strength meter ─────────────────────────────── */
        $('#newPassword').on('input', function () {
            const val = $(this).val();
            const $bar = $('#strengthBar');
            const $lbl = $('#strengthLabel');
            let score = 0;

            if (val.length >= 8)                    score++;
            if (/[A-Z]/.test(val))                  score++;
            if (/[0-9]/.test(val))                  score++;
            if (/[^A-Za-z0-9]/.test(val))           score++;

            const configs = [
                { w: '0%',   bg: 'transparent', lbl: '' },
                { w: '25%',  bg: '#e74c3c',      lbl: 'Weak' },
                { w: '50%',  bg: '#f39c12',      lbl: 'Fair' },
                { w: '75%',  bg: '#3498db',      lbl: 'Good' },
                { w: '100%', bg: '#2ecc71',      lbl: 'Strong' },
            ];
            const cfg = configs[val.length === 0 ? 0 : score];
            $bar.css({ width: cfg.w, background: cfg.bg });
            $lbl.text(val.length === 0 ? '' : cfg.lbl).css('color', cfg.bg);
        });

        /* ── Form submit ─────────────────────────────────────────── */
        $('#resetPasswordForm').on('submit', function (e) {
            e.preventDefault();
            hideAlert();

            const currentToken  = $('#resetToken').val();
            const newPw         = $('#newPassword').val();
            const confirmPw     = $('#confirmPassword').val();

            if (!newPw || !confirmPw) {
                showAlert('danger', 'Please fill in both password fields.');
                return;
            }
            if (newPw.length < 8) {
                showAlert('danger', 'Password must be at least 8 characters.');
                return;
            }
            if (newPw !== confirmPw) {
                showAlert('danger', 'Passwords do not match.');
                return;
            }

            setLoading(true);

            $.ajax({
                url: '../../api/reset-password.php',
                method: 'POST',
                data: {
                    token:            currentToken,
                    new_password:     newPw,
                    confirm_password: confirmPw
                },
                dataType: 'json',
                success: function (res) {
                    setLoading(false);
                    if (res.success) {
                        $('#formView').addClass('d-none');
                        $('#successView').removeClass('d-none');
                    } else {
                        showAlert('danger', res.message || 'Something went wrong. Please try again.');
                    }
                },
                error: function () {
                    setLoading(false);
                    showAlert('danger', 'Network error. Please check your connection and try again.');
                }
            });
        });

    });
    </script>
</body>
</html>
