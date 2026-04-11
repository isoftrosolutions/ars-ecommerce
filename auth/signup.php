<?php
/**
 * Signup Page
 * Easy Shopping A.R.S
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = "Create Account";

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../index.php");
    }
    exit();
}

include_once __DIR__ . '/../includes/header-bootstrap.php';
?>

<link rel="stylesheet" href="<?php echo url('/public/assets/css/auth.css'); ?>">

<div class="auth-wrapper">
    <div class="container d-flex justify-content-center">
        <div class="auth-card" style="max-width: 550px;">
            <h1 class="auth-title">Join ARS</h1>
            <p class="auth-subtitle">Create an account to track orders and save your wishlist</p>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="auth-alert auth-alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <div id="password-match-error" class="auth-alert auth-alert-error" style="display: none;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>Passwords do not match. Please check and try again.</span>
            </div>

            <form action="<?php echo url('/backend/signup.php'); ?>" method="POST" class="auth-form" id="signupForm">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" name="full_name" id="full_name" class="form-control" placeholder="John Doe" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <input type="email" name="email" id="email" class="form-control" placeholder="john@example.com" required>
                            <button type="button" class="btn btn-outline-secondary" id="verifyEmailBtn" onclick="sendEmailOTP()">
                                <i class="bi bi-envelope"></i> Verify
                            </button>
                        </div>
                        <div class="form-text">We'll send an OTP to verify your email address</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="mobile" class="form-label">Mobile Number</label>
                        <input type="tel" name="mobile" id="mobile" class="form-control" placeholder="98XXXXXXXX" pattern="9[78]\d{8}" required>
                    </div>
                </div>

                <!-- OTP Verification Section -->
                <div id="otpVerification" class="mb-3" style="display: none;">
                    <label for="email_otp" class="form-label">Enter Email OTP</label>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" name="email_otp" id="email_otp" class="form-control text-center" placeholder="000000" maxlength="6" pattern="\d{6}">
                            <div class="form-text" id="emailOtpTimer">OTP expires in 5:00</div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-success" id="verifyOtpBtn" onclick="verifyEmailOTP()">
                                <i class="bi bi-check-circle"></i> Verify OTP
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Shipping Address</label>
                    <input type="text" name="address" id="address" class="form-control" placeholder="Street, City, Nepal" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-group">
                            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                            <i class="bi bi-eye password-toggle"></i>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="password-group">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="••••••••" required>
                            <i class="bi bi-eye password-toggle"></i>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="email_verified" id="email_verified" value="0">

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="terms" required>
                    <label class="form-check-label text-muted" style="font-size: 0.85rem;" for="terms">
                        I agree to the <a href="<?php echo url('/terms'); ?>" class="auth-link">Terms & Conditions</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-auth">
                    Create Account <i class="bi bi-person-plus ms-2"></i>
                </button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="<?php echo url('/auth/login'); ?>" class="auth-link">Login Here</a>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo url('/public/assets/js/auth.js'); ?>"></script>

<script>
// Email OTP functionality for signup
let emailOtpTimer;
let emailTimeLeft = 300;

function sendEmailOTP() {
    const email = document.getElementById('email').value;
    const btn = document.getElementById('verifyEmailBtn');

    if (!email || !email.includes('@')) {
        alert('Please enter a valid email address');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';

    fetch('<?php echo url("/backend/send-otp.php"); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'email=' + encodeURIComponent(email) + '&action=verify_signup'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('otpVerification').style.display = 'block';
            startEmailOTPTimer();
            alert('OTP sent to your email address');
        } else {
            alert(data.message || 'Failed to send OTP');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while sending OTP');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-envelope"></i> Verify';
    });
}

function verifyEmailOTP() {
    const email = document.getElementById('email').value;
    const otp = document.getElementById('email_otp').value;

    if (!otp.match(/^\d{6}$/)) {
        alert('Please enter a valid 6-digit OTP');
        return;
    }

    fetch('<?php echo url("/backend/verify-email-otp.php"); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'email=' + encodeURIComponent(email) + '&otp=' + encodeURIComponent(otp) + '&action=signup_verify'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('email_verified').value = '1';
            document.getElementById('otpVerification').style.display = 'none';
            document.getElementById('verifyEmailBtn').innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> Verified';
            document.getElementById('verifyEmailBtn').disabled = true;
            document.getElementById('email').readOnly = true;
            alert('Email verified successfully!');
            clearInterval(emailOtpTimer);
        } else {
            alert(data.message || 'OTP verification failed');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during verification');
    });
}

function startEmailOTPTimer() {
    emailTimeLeft = 300;
    updateEmailTimerDisplay();

    emailOtpTimer = setInterval(() => {
        emailTimeLeft--;
        updateEmailTimerDisplay();

        if (emailTimeLeft <= 0) {
            clearInterval(emailOtpTimer);
            document.getElementById('emailOtpTimer').textContent = 'OTP expired. Please request a new one.';
            document.getElementById('otpVerification').style.display = 'none';
        }
    }, 1000);
}

function updateEmailTimerDisplay() {
    const minutes = Math.floor(emailTimeLeft / 60);
    const seconds = emailTimeLeft % 60;
    document.getElementById('emailOtpTimer').textContent = `OTP expires in ${minutes}:${seconds.toString().padStart(2, '0')}`;
}

// Form validation
document.getElementById('signupForm').addEventListener('submit', function(e) {
    const emailVerified = document.getElementById('email_verified').value;

    if (emailVerified !== '1') {
        e.preventDefault();
        alert('Please verify your email address with OTP before signing up.');
        return false;
    }

    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (password !== confirmPassword) {
        e.preventDefault();
        document.getElementById('password-match-error').style.display = 'block';
        return false;
    }
});

// Auto-format OTP input
document.getElementById('email_otp').addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').substring(0, 6);
});
</script>

<?php include_once __DIR__ . '/../includes/footer-bootstrap.php'; ?>
