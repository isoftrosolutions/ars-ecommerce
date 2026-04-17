<?php
/**
 * OTP Reset Password Page
 * Easy Shopping A.R.S
 */

// Error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = "Reset Password with OTP";

// Get email from URL parameter
$email = trim($_GET['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email address.";
    header("Location: forgot-password.php");
    exit();
}

// Check if user exists
try {
    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = "No account found with this email.";
        header("Location: forgot-password.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("OTP Reset Password Error: " . $e->getMessage());
    $_SESSION['error'] = "Something went wrong. Please try again.";
    header("Location: forgot-password.php");
    exit();
}

include_once __DIR__ . '/../includes/header-bootstrap.php';
?>

<link rel="stylesheet" href="<?php echo url('/public/assets/css/auth.css'); ?>">

<div class="auth-wrapper">
    <div class="container d-flex justify-content-center">
        <div class="auth-card">
            <h1 class="auth-title">Reset Password</h1>
            <p class="auth-subtitle">We've sent a 6-digit OTP to <strong><?php echo h($email); ?></strong></p>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="auth-alert auth-alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span><?php echo h($_SESSION['error']); unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="auth-alert auth-alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <span><?php echo h($_SESSION['success']); unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>

            <!-- OTP Input Form -->
            <form action="<?php echo url('/backend/otp-reset-password.php'); ?>" method="POST" class="auth-form" id="otpForm">
                <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                <input type="hidden" name="email" value="<?php echo h($email); ?>">

                <div class="mb-4">
                    <label class="form-label">Enter 6-digit OTP</label>
                    <div class="otp-input-container">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                        <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric">
                    </div>
                    <input type="hidden" name="otp" id="otp" required>
                </div>

                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Enter new password" required>
                    <div class="form-text">Password must be at least 8 characters long</div>
                </div>

                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm new password" required>
                </div>

                <button type="submit" class="btn btn-auth" id="submitBtn">
                    Reset Password <i class="bi bi-key ms-2"></i>
                </button>
            </form>

            <div class="auth-footer">
                <button class="btn btn-link p-0" onclick="resendOTP()">
                    Didn't receive OTP? <span class="text-primary">Resend</span>
                </button>
                <br>
                <a href="<?php echo url('/auth/forgot-password'); ?>" class="auth-link mt-2 d-inline-block">Try different method</a>
            </div>
        </div>
    </div>
</div>

<style>
.otp-input-container {
    display: flex;
    gap: 8px;
    justify-content: center;
    margin: 20px 0;
}

.otp-input {
    width: 45px;
    height: 50px;
    text-align: center;
    font-size: 20px;
    font-weight: bold;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    background-color: #fff;
    transition: border-color 0.2s ease;
}

.otp-input:focus {
    outline: none;
    border-color: #ea6c00;
    box-shadow: 0 0 0 0.2rem rgba(234, 108, 0, 0.25);
}

.otp-input.filled {
    border-color: #ea6c00;
    background-color: #fff8f3;
}

.otp-input.error {
    border-color: #dc3545;
}
</style>

<script>
// OTP Input Handling
document.addEventListener('DOMContentLoaded', function() {
    const otpInputs = document.querySelectorAll('.otp-input');
    const otpHidden = document.getElementById('otp');

    otpInputs.forEach((input, index) => {
        input.addEventListener('input', function(e) {
            // Only allow numbers
            this.value = this.value.replace(/[^0-9]/g, '');

            // Mark as filled
            if (this.value.length > 0) {
                this.classList.add('filled');
            } else {
                this.classList.remove('filled');
            }

            // Auto-focus next input
            if (this.value.length === 1 && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }

            // Update hidden OTP field
            updateOTP();
        });

        input.addEventListener('keydown', function(e) {
            // Handle backspace
            if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                otpInputs[index - 1].focus();
            }
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = e.clipboardData.getData('text').replace(/[^0-9]/g, '').substring(0, 6);
            for (let i = 0; i < paste.length && index + i < otpInputs.length; i++) {
                otpInputs[index + i].value = paste[i];
                otpInputs[index + i].classList.add('filled');
            }
            updateOTP();
            // Focus next empty input or last input
            const nextIndex = Math.min(index + paste.length, otpInputs.length - 1);
            otpInputs[nextIndex].focus();
        });
    });

    function updateOTP() {
        const otp = Array.from(otpInputs).map(input => input.value).join('');
        otpHidden.value = otp;
    }

    // Form validation
    document.getElementById('otpForm').addEventListener('submit', function(e) {
        const otp = otpHidden.value;
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (otp.length !== 6) {
            e.preventDefault();
            alert('Please enter the complete 6-digit OTP');
            return;
        }

        if (newPassword.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long');
            return;
        }

        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match');
            return;
        }
    });
});

function resendOTP() {
    const userEmail = <?php echo json_encode($email); ?>;
    if (!confirm('Resend OTP to ' + userEmail + '?')) {
        return;
    }

    // Show loading state
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = 'Sending...';
    btn.disabled = true;

    // Send resend request
    const baseUrl = <?php echo json_encode(url('')); ?>;
    fetch(baseUrl + '/backend/send-otp.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'email=' + encodeURIComponent(<?php echo json_encode($email); ?>) + '&action=password_reset&csrf_token=' + encodeURIComponent(<?php echo json_encode(generate_csrf_token()); ?>)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('OTP sent successfully!');
        } else {
            alert('Failed to send OTP: ' + data.message);
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>

<script src="<?php echo url('/public/assets/js/auth.js'); ?>"></script>

<?php include_once __DIR__ . '/../includes/footer-bootstrap.php'; ?>