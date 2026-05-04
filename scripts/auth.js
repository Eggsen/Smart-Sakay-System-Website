// ─── AUTHENTICATION LOGIC ──────────────────────────────────────────

/**
 * Show an inline alert inside the auth card.
 * @param {string} containerId  – ID of the alert container element
 * @param {string} message      – Text to display
 * @param {'danger'|'success'|'warning'} type
 */
function showAuthAlert(containerId, message, type = 'danger') {
    const iconMap = {
        danger:  'bi-exclamation-circle-fill',
        success: 'bi-check-circle-fill',
        warning: 'bi-exclamation-triangle-fill'
    };
    const $container = $('#' + containerId);
    $container
        .removeClass('alert-danger alert-success alert-warning auth-alert--hidden')
        .addClass('alert-' + type + ' auth-alert--visible')
        .html(`<i class="bi ${iconMap[type] || 'bi-info-circle-fill'} me-2"></i>${message}`);

    // Auto-hide after 6 seconds
    clearTimeout($container.data('hideTimer'));
    $container.data('hideTimer', setTimeout(function () {
        $container.removeClass('auth-alert--visible').addClass('auth-alert--hidden');
    }, 6000));
}

function hideAuthAlert(containerId) {
    $('#' + containerId).removeClass('auth-alert--visible').addClass('auth-alert--hidden');
}

/**
 * Set a button's loading state.
 * @param {jQuery} $btn
 * @param {boolean} loading
 * @param {string} originalText
 */
function setBtnLoading($btn, loading, originalText = 'Submit') {
    if (loading) {
        $btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Please wait…'
        );
    } else {
        $btn.prop('disabled', false).html(originalText);
    }
}

// ────────────────────────────────────────────────────────────────────
$(document).ready(function () {

    // ── Admin Login ─────────────────────────────────────────────────
    $('#adminLoginForm').on('submit', function (e) {
        e.preventDefault();
        hideAuthAlert('adminLoginAlert');

        const username = $('#adminUsername').val().trim();
        const password = $('#adminPassword').val();
        const $btn     = $(this).find('button[type="submit"]');

        if (!username || !password) {
            showAuthAlert('adminLoginAlert', 'Please enter your username/email and password.');
            return;
        }

        setBtnLoading($btn, true, 'Login');

        $.ajax({
            url: '../../api/login.php',
            method: 'POST',
            data: { username, password, role_type: 'Admin' },
            dataType: 'json',
            success: function (response) {
                setBtnLoading($btn, false, 'Login');
                if (response.success) {
                    localStorage.setItem('userRole', 'admin');
                    localStorage.setItem('userName', response.user.full_name);
                    localStorage.setItem('userId', response.user.id);
                    showAuthAlert('adminLoginAlert', 'Login successful! Redirecting…', 'success');
                    setTimeout(() => { window.location.href = '../../admin/dashboard.html'; }, 800);
                } else {
                    showAuthAlert('adminLoginAlert', response.message);
                }
            },
            error: function () {    
                setBtnLoading($btn, false, 'Login');
                showAuthAlert('adminLoginAlert', 'A network error occurred. Please try again.');
            }
        });
    });

    // ── Staff Login ─────────────────────────────────────────────────
    $('#staffLoginForm').on('submit', function (e) {
        e.preventDefault();
        hideAuthAlert('staffLoginAlert');

        const username = $('#staffUsername').val().trim();
        const password = $('#staffPassword').val();
        const $btn     = $(this).find('button[type="submit"]');

        if (!username || !password) {
            showAuthAlert('staffLoginAlert', 'Please enter your username/email and password.');
            return;
        }

        setBtnLoading($btn, true, 'Login');

        $.ajax({
            url: '../../api/login.php',
            method: 'POST',
            data: { username, password, role_type: 'Staff' },
            dataType: 'json',
            success: function (response) {
                setBtnLoading($btn, false, 'Login');
                if (response.success) {
                    localStorage.setItem('userRole', 'staff');
                    localStorage.setItem('userName', response.user.full_name);
                    localStorage.setItem('userId', response.user.id);
                    showAuthAlert('staffLoginAlert', 'Login successful! Redirecting…', 'success');
                    setTimeout(() => { window.location.href = '../../staff/dashboard.html'; }, 800);
                } else {
                    showAuthAlert('staffLoginAlert', response.message);
                }
            },
            error: function () {
                setBtnLoading($btn, false, 'Login');
                showAuthAlert('staffLoginAlert', 'A network error occurred. Please try again.');
            }
        });
    });

    // ── Admin Sign Up ───────────────────────────────────────────────
    $('#adminSignupForm').on('submit', function (e) {
        e.preventDefault();
        hideAuthAlert('adminSignupAlert');

        const first_name       = $('#adminFirstName').val().trim();
        const last_name        = $('#adminLastName').val().trim();
        const username         = $('#adminRegUsername').val().trim();
        const email            = $('#adminEmail').val().trim();
        const contact          = $('#adminContact').val().trim();
        const password         = $('#adminRegPassword').val();
        const confirm_password = $('#adminConfirmPassword').val();
        const $btn             = $(this).find('button[type="submit"]');

        // Client-side validation
        if (!first_name || !last_name || !username || !email || !password || !confirm_password) {
            showAuthAlert('adminSignupAlert', 'Please fill in all required fields.');
            return;
        }
        if (password.length < 8) {
            showAuthAlert('adminSignupAlert', 'Password must be at least 8 characters long.');
            $('#adminRegPassword').focus();
            return;
        }
        if (password !== confirm_password) {
            showAuthAlert('adminSignupAlert', 'Passwords do not match. Please re-enter them.');
            $('#adminConfirmPassword').val('').focus();
            return;
        }

        setBtnLoading($btn, true, 'Register');

        $.ajax({
            url: '../../api/signup.php',
            method: 'POST',
            data: { role_type: 'Admin', first_name, last_name, username, email, contact, password, confirm_password },
            dataType: 'json',
            success: function (response) {
                setBtnLoading($btn, false, 'Register');
                if (response.success) {
                    showAuthAlert('adminSignupAlert', response.message, 'success');
                    setTimeout(() => { window.location.href = 'login.html'; }, 2000);
                } else {
                    showAuthAlert('adminSignupAlert', response.message);
                }
            },
            error: function () {
                setBtnLoading($btn, false, 'Register');
                showAuthAlert('adminSignupAlert', 'A network error occurred. Please try again.');
            }
        });
    });

    // ── Staff Sign Up ───────────────────────────────────────────────
    $('#staffSignupForm').on('submit', function (e) {
        e.preventDefault();
        hideAuthAlert('staffSignupAlert');

        const first_name       = $('#staffFirstName').val().trim();
        const last_name        = $('#staffLastName').val().trim();
        const username         = $('#staffRegUsername').val().trim();
        const email            = $('#staffEmail').val().trim();
        const contact          = $('#staffContact').val().trim();
        const password         = $('#staffRegPassword').val();
        const confirm_password = $('#staffConfirmPassword').val();
        const $btn             = $(this).find('button[type="submit"]');

        // Client-side validation
        if (!first_name || !last_name || !username || !email || !password || !confirm_password) {
            showAuthAlert('staffSignupAlert', 'Please fill in all required fields.');
            return;
        }
        if (password.length < 8) {
            showAuthAlert('staffSignupAlert', 'Password must be at least 8 characters long.');
            $('#staffRegPassword').focus();
            return;
        }
        if (password !== confirm_password) {
            showAuthAlert('staffSignupAlert', 'Passwords do not match. Please re-enter them.');
            $('#staffConfirmPassword').val('').focus();
            return;
        }

        setBtnLoading($btn, true, 'Register');

        $.ajax({
            url: '../../api/signup.php',
            method: 'POST',
            data: { role_type: 'Staff', first_name, last_name, username, email, contact, password, confirm_password },
            dataType: 'json',
            success: function (response) {
                setBtnLoading($btn, false, 'Register');
                if (response.success) {
                    showAuthAlert('staffSignupAlert', response.message, 'success');
                    setTimeout(() => { window.location.href = 'login.html'; }, 2000);
                } else {
                    showAuthAlert('staffSignupAlert', response.message);
                }
            },
            error: function () {
                setBtnLoading($btn, false, 'Register');
                showAuthAlert('staffSignupAlert', 'A network error occurred. Please try again.');
            }
        });
    });

    // ── Logout ──────────────────────────────────────────────────────
    $(document).on('click', '.btn-logout', function (e) {
        e.preventDefault();
        const role = localStorage.getItem('userRole');
        localStorage.removeItem('userRole');
        localStorage.removeItem('userName');

        if (role === 'staff') {
            window.location.href = '../auth-pages/auth-staff/login.html';
        } else {
            window.location.href = '../auth-pages/auth-admin/login.html';
        }
    });

});

// ── Guard function for protected pages ───────────────────────────────
function checkAuth(requiredRole) {
    const userRole = localStorage.getItem('userRole');

    if (!userRole) {
        window.location.href = '../auth-pages/auth-admin/login.html';
        return;
    }

    if (requiredRole && userRole !== requiredRole) {
        if (userRole === 'admin') {
            window.location.href = '../admin/dashboard.html';
        } else if (userRole === 'staff') {
            window.location.href = '../staff/dashboard.html';
        }
    }
}
