<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../../assets/logo/smartsakaylogo.png">
    <meta name="description" content="Smart Sakay – Forgot Password. Request a secure password reset link for your Admin or Staff account.">
    <link rel="stylesheet" href="../../bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../auth-page-style/style.css">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
    <title>Smart Sakay – Forgot Password</title>
    <style>
        /* ── Spinner inside button ─────────────────────────────── */
        #sendResetBtn .spinner-border {
            width: 1rem;
            height: 1rem;
            border-width: 2px;
        }
        /* ── Back-to-login link ────────────────────────────────── */
        .back-to-login {
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        .back-to-login a {
            font-weight: 600;
        }
        /* ── Subtle hint text ──────────────────────────────────── */
        .hint-text {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-top: -4px;
        }
        /* ── Success state ─────────────────────────────────────── */
        .success-icon {
            font-size: 3rem;
            color: var(--success);
        }
    </style>
</head>
<body>

    <!-- Auth card -->
    <div class="auth-card card rounded-4 d-flex">
        <div class="row">
            <!-- Left side – form -->
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

                <!-- Form view -->
                <div id="formView">
                    <div class="title mb-1 text-center">Forgot <span>Password?</span></div>
                    <p class="text-center hint-text mb-4">Enter the email linked to your Admin or Staff account and we'll send you a reset link.</p>

                    <!-- Inline alert -->
                    <div id="forgotAlert" class="alert auth-alert auth-alert--hidden" role="alert"></div>

                    <form id="forgotPasswordForm" class="d-flex flex-column gap-2" novalidate>
                        <label for="resetEmail">Email Address</label>
                        <input
                            id="resetEmail"
                            class="form-control"
                            type="email"
                            placeholder="Enter your registered email"
                            required
                            autocomplete="email"
                        >
                        <button type="submit" id="sendResetBtn" class="btn login-btn w-100 mt-3">
                            <span class="btn-text">Send Reset Link</span>
                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                        </button>
                    </form>

                    <div class="text-center mt-3 back-to-login">
                        Remember your password? <a href="login.html">Sign in</a>
                    </div>
                </div>

                <!-- Success view (shown after submit) -->
                <div id="successView" class="text-center d-none">
                    <div class="success-icon mb-3">
                        <i class="bi bi-envelope-check-fill" style="color: var(--success);"></i>
                    </div>
                    <div class="title mb-2">Check Your <span>Email</span></div>
                    <p class="hint-text mb-4">
                        If that email is registered in our system, a password reset link has been sent.<br>
                        The link will expire in <strong>15 minutes</strong>.
                    </p>
                    <p class="hint-text">Didn't receive it? Check your spam folder or
                        <a href="#" id="resendLink" style="color:var(--primary);font-weight:600;">try again</a>.
                    </p>
                    <div class="text-center mt-4 back-to-login">
                        <a href="login.html" class="btn login-btn px-4">Back to Login</a>
                    </div>
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
        /* ── Helper: show inline alert ─────────────────────────────── */
        function showAlert(type, message) {
            const $alert = $('#forgotAlert');
            // Remove previous state classes
            $alert.removeClass(
                'alert-success alert-danger alert-warning auth-alert--hidden auth-alert--visible'
            );
            $alert.addClass('alert-' + type + ' auth-alert--visible');
            $alert.html('<i class="bi bi-' + (type === 'success' ? 'check-circle' : 'exclamation-triangle') + '-fill me-2"></i>' + message);
        }

        function hideAlert() {
            const $alert = $('#forgotAlert');
            $alert.removeClass('auth-alert--visible alert-success alert-danger alert-warning');
            $alert.addClass('auth-alert--hidden');
        }

        /* ── Set button loading state ──────────────────────────────── */
        function setLoading(loading) {
            const $btn      = $('#sendResetBtn');
            const $text     = $btn.find('.btn-text');
            const $spinner  = $btn.find('.spinner-border');

            if (loading) {
                $btn.prop('disabled', true);
                $text.text('Sending…');
                $spinner.removeClass('d-none');
            } else {
                $btn.prop('disabled', false);
                $text.text('Send Reset Link');
                $spinner.addClass('d-none');
            }
        }

        /* ── Form submit handler ───────────────────────────────────── */
        $('#forgotPasswordForm').on('submit', function (e) {
            e.preventDefault();
            hideAlert();

            const email = $.trim($('#resetEmail').val());

            if (!email) {
                showAlert('danger', 'Please enter your email address.');
                return;
            }

            // Basic email format check
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showAlert('danger', 'Please enter a valid email address.');
                return;
            }

            setLoading(true);

            $.ajax({
                url: '../../api/send-reset.php',
                method: 'POST',
                data: { email: email },
                dataType: 'json',
                success: function (res) {
                    setLoading(false);
                    if (res.success) {
                        // Always show the success screen (never reveal if email exists)
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

        /* ── "Try again" link – show the form back ─────────────────── */
        $('#resendLink').on('click', function (e) {
            e.preventDefault();
            $('#successView').addClass('d-none');
            $('#formView').removeClass('d-none');
            $('#resetEmail').val('').focus();
        });
    </script>
</body>
</html>
