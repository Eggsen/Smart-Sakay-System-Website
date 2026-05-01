// ─── AUTHENTICATION LOGIC ──────────────────────────────────────────

$(document).ready(function() {
    
    // Admin Login Form
    $('#adminLoginForm').on('submit', function(e) {
        e.preventDefault();
        
        const username = $('#adminUsername').val();
        const password = $('#adminPassword').val();
        
        if (!username || !password) {
            alert('Please enter both username and password.');
            return;
        }
        
        $.ajax({
            url: '../../api/login.php',
            method: 'POST',
            data: {
                username: username,
                password: password,
                role_type: 'Admin'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Save session
                    localStorage.setItem('userRole', 'admin');
                    localStorage.setItem('userName', response.user.full_name);
                    
                    // Redirect to admin dashboard
                    window.location.href = '../../admin/dashboard.html';
                } else {
                    alert('Login failed: ' + response.message);
                }
            },
            error: function(err) {
                console.error('Login error:', err);
                alert('An error occurred during login. Please try again.');
            }
        });
    });

    // Staff Login Form
    $('#staffLoginForm').on('submit', function(e) {
        e.preventDefault();
        
        const username = $('#staffUsername').val();
        const password = $('#staffPassword').val();
        
        if (!username || !password) {
            alert('Please enter both username and password.');
            return;
        }
        
        $.ajax({
            url: '../../api/login.php',
            method: 'POST',
            data: {
                username: username,
                password: password,
                role_type: 'Staff'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Save session
                    localStorage.setItem('userRole', 'staff');
                    localStorage.setItem('userName', response.user.full_name);
                    
                    // Redirect to staff dashboard
                    window.location.href = '../../staff/dashboard.html';
                } else {
                    alert('Login failed: ' + response.message);
                }
            },
            error: function(err) {
                console.error('Login error:', err);
                alert('An error occurred during login. Please try again.');
            }
        });
    });

    // Handle logout button clicks anywhere
    $(document).on('click', '.btn-logout', function(e) {
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

// Call this function at the top of protected pages
function checkAuth(requiredRole) {
    const userRole = localStorage.getItem('userRole');
    
    if (!userRole) {
        // Not logged in, redirect to index/landing page or admin login as fallback
        window.location.href = '../auth-pages/auth-admin/login.html';
        return;
    }

    if (requiredRole && userRole !== requiredRole) {
        // Logged in but wrong role
        if (userRole === 'admin') {
            window.location.href = '../admin/dashboard.html';
        } else if (userRole === 'staff') {
            window.location.href = '../staff/dashboard.html';
        }
    }
}
