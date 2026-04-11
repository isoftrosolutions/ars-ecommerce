<?php
/**
 * Forgot Password Page
 * Easy Shopping A.R.S
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = "Forgot Password";

include_once __DIR__ . '/../includes/header-bootstrap.php';
?>

<link rel="stylesheet" href="<?php echo url('/public/assets/css/auth.css'); ?>">

<div class="auth-wrapper">
    <div class="container d-flex justify-content-center">
        <div class="auth-card">
            <h1 class="auth-title">Forgot Password</h1>
            <p class="auth-subtitle">Enter your email address and we'll send you an OTP to reset your password</p>

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

            <form action="<?php echo url('/backend/forgot-password.php'); ?>" method="POST" class="auth-form" id="forgotForm">
                <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                <input type="hidden" name="reset_method" value="email-otp">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="your@email.com" required autofocus>
                    <div class="form-text">Enter the email address associated with your account</div>
                </div>

                <button type="submit" class="btn btn-auth" id="submitBtn">
                    Send OTP <i class="bi bi-shield-check ms-2"></i>
                </button>
            </form>

            <div class="auth-footer">
                Remember your password? <a href="<?php echo url('/auth/login'); ?>" class="auth-link">Back to Login</a>
            </div>

            <!-- Processing Modal -->
            <div class="modal fade" id="processingModal" tabindex="-1" aria-labelledby="processingModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center p-4">
                            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h5 class="modal-title mb-2" id="processingModalLabel">Sending OTP</h5>
                            <p class="text-muted mb-0">Please wait while we send the verification code to your email...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Processing Modal Styles */
.modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.spinner-border {
    border-width: 0.25em;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('forgotForm');
    const submitBtn = document.getElementById('submitBtn');
    const processingModal = new bootstrap.Modal(document.getElementById('processingModal'));

    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        // Show processing modal
        processingModal.show();

        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';

        // Submit form after a brief delay to show modal
        setTimeout(() => {
            form.submit();
        }, 500);
    });
});
</script>

<script src="<?php echo url('/public/assets/js/auth.js'); ?>"></script>

<?php include_once __DIR__ . '/../includes/footer-bootstrap.php'; ?>