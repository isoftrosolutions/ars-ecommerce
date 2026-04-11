<?php
/**
 * Login Page
 * Easy Shopping A.R.S
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = "Login";

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

<style>
.login-tabs {
    display: flex;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
}

.login-tab {
    flex: 1;
    padding: 12px 16px;
    border: none;
    background: transparent;
    color: #6b7280;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.login-tab:hover {
    background: rgba(234, 108, 0, 0.1);
    color: var(--ember);
}

.login-tab.active {
    background: var(--ember);
    color: white;
}

.otp-input-group {
    position: relative;
}

#otpTimer {
    color: var(--ember);
    font-weight: 500;
}
</style>

<div class="auth-wrapper">
    <div class="container d-flex justify-content-center">
        <div class="auth-card">
            <h1 class="auth-title">Welcome Back</h1>
            <p class="auth-subtitle">Login to access your account and orders</p>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="auth-alert auth-alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="auth-alert auth-alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Method Tabs -->
            <div class="login-tabs mb-4">
                <button type="button" class="login-tab active" onclick="switchLoginMethod('password')">
                    <i class="bi bi-key"></i> Password Login
                </button>
                <button type="button" class="login-tab" onclick="switchLoginMethod('otp')">
                    <i class="bi bi-phone"></i> OTP Login
                </button>
            </div>

            <!-- Password Login Form -->
            <form action="<?php echo url('/backend/login.php'); ?>" method="POST" class="auth-form" id="passwordForm">
                <div class="mb-3">
                    <label for="login_id" class="form-label">Email or Mobile Number</label>
                    <input type="text" name="login_id" id="login_id" class="form-control" placeholder="example@mail.com or 98XXXXXXXX" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-group">
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                        <i class="bi bi-eye password-toggle"></i>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label text-muted" style="font-size: 0.85rem;" for="remember">
                            Remember me
                        </label>
                    </div>
                    <a href="<?php echo url('/forgot-password'); ?>" class="auth-link" style="font-size: 0.85rem;">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-auth">
                    Sign In <i class="bi bi-box-arrow-in-right ms-2"></i>
                </button>
            </form>

            <!-- OTP Login Form -->
            <form action="<?php echo url('/backend/otp-login.php'); ?>" method="POST" class="auth-form" id="otpForm" style="display: none;">
                <!-- OTP Method Selection -->
                <div class="mb-3">
                    <label class="form-label">Send OTP to:</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="otp_method" id="otp_email" value="email" checked>
                            <label class="form-check-label" for="otp_email">
                                Email
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="otp_method" id="otp_mobile" value="mobile">
                            <label class="form-check-label" for="otp_mobile">
                                Mobile
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3" id="emailField">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email_otp" class="form-control" placeholder="your@email.com">
                    <div class="form-text">Enter your registered email address</div>
                </div>

                <div class="mb-3" id="mobileField" style="display: none;">
                    <label for="mobile" class="form-label">Mobile Number</label>
                    <input type="tel" name="mobile" id="mobile_otp" class="form-control" placeholder="98XXXXXXXX" pattern="9[78]\d{8}">
                    <div class="form-text">Enter your registered mobile number</div>
                </div>

                <div class="mb-3" id="otpSection" style="display: none;">
                    <label for="otp" class="form-label">Enter OTP</label>
                    <div class="otp-input-group">
                        <input type="text" name="otp" id="otp" class="form-control text-center" placeholder="000000" maxlength="6" pattern="\d{6}">
                        <div class="form-text text-center">
                            <small id="otpTimer">OTP expires in 5:00</small>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <button type="button" class="btn btn-outline-primary w-100" id="sendOtpBtn" onclick="sendOTP()">
                        <i class="bi bi-send me-2"></i>Send OTP
                    </button>
                    <button type="submit" class="btn btn-auth w-100 mt-2" id="verifyOtpBtn" style="display: none;">
                        <i class="bi bi-check-circle me-2"></i>Verify & Login
                    </button>
                </div>
            </form>

            <div class="auth-footer">
                Don't have an account? <a href="<?php echo url('/auth/signup'); ?>" class="auth-link">Create Account</a>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo url('/public/assets/js/auth.js'); ?>"></script>

<script>
// Login method switching
function switchLoginMethod(method) {
    const passwordForm = document.getElementById('passwordForm');
    const otpForm = document.getElementById('otpForm');
    const tabs = document.querySelectorAll('.login-tab');

    tabs.forEach(tab => tab.classList.remove('active'));

    if (method === 'password') {
        passwordForm.style.display = 'block';
        otpForm.style.display = 'none';
        tabs[0].classList.add('active');
    } else {
        passwordForm.style.display = 'none';
        otpForm.style.display = 'block';
        tabs[1].classList.add('active');
    }
}

// OTP functionality
let otpTimer;
let timeLeft = 300; // 5 minutes

function sendOTP() {
    const method = document.querySelector('input[name="otp_method"]:checked').value;
    const email = document.getElementById('email_otp').value;
    const mobile = document.getElementById('mobile_otp').value;
    const sendBtn = document.getElementById('sendOtpBtn');

    let contact = '';
    let contactType = '';

    if (method === 'email') {
        if (!email || !email.includes('@')) {
            alert('Please enter a valid email address');
            return;
        }
        contact = email;
        contactType = 'email';
    } else {
        if (!mobile || !mobile.match(/^9[78]\d{8}$/)) {
            alert('Please enter a valid mobile number (98XXXXXXXX or 97XXXXXXXX)');
            return;
        }
        contact = mobile;
        contactType = 'mobile';
    }

    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Sending...';

    fetch('<?php echo url("/backend/send-otp.php"); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: contactType + '=' + encodeURIComponent(contact) + '&action=send'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('otpSection').style.display = 'block';
            document.getElementById('sendOtpBtn').style.display = 'none';
            document.getElementById('verifyOtpBtn').style.display = 'block';
            startOTPTimer();
            alert('OTP sent to your mobile number');
        } else {
            alert(data.message || 'Failed to send OTP');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while sending OTP');
    })
    .finally(() => {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="bi bi-send me-2"></i>Send OTP';
    });
}

function startOTPTimer() {
    timeLeft = 300;
    updateTimerDisplay();

    otpTimer = setInterval(() => {
        timeLeft--;
        updateTimerDisplay();

        if (timeLeft <= 0) {
            clearInterval(otpTimer);
            document.getElementById('otpTimer').textContent = 'OTP expired. Please request a new one.';
            document.getElementById('sendOtpBtn').style.display = 'block';
            document.getElementById('verifyOtpBtn').style.display = 'none';
        }
    }, 1000);
}

function updateTimerDisplay() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    document.getElementById('otpTimer').textContent = `OTP expires in ${minutes}:${seconds.toString().padStart(2, '0')}`;
}

// Auto-format OTP input
document.getElementById('otp').addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').substring(0, 6);
});

// OTP method switching
document.querySelectorAll('input[name="otp_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const emailField = document.getElementById('emailField');
        const mobileField = document.getElementById('mobileField');
        const emailInput = document.getElementById('email_otp');
        const mobileInput = document.getElementById('mobile_otp');

        if (this.value === 'email') {
            emailField.style.display = 'block';
            mobileField.style.display = 'none';
            emailInput.required = true;
            mobileInput.required = false;
            mobileInput.value = '';
        } else {
            emailField.style.display = 'none';
            mobileField.style.display = 'block';
            emailInput.required = false;
            mobileInput.required = true;
            emailInput.value = '';
        }
    });
});
</script>

<?php include_once __DIR__ . '/../includes/footer-bootstrap.php'; ?>
