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
                    <span><?php echo h($_SESSION['error']); unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <div id="password-match-error" class="auth-alert auth-alert-error" style="display: none;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>Passwords do not match. Please check and try again.</span>
            </div>

            <form action="<?php echo url('/backend/signup.php'); ?>" method="POST" class="auth-form" id="signupForm">
                <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" name="full_name" id="full_name" class="form-control" placeholder="John Doe" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="john@example.com" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="mobile" class="form-label">Mobile Number</label>
                        <input type="tel" name="mobile" id="mobile" class="form-control" placeholder="98XXXXXXXX" pattern="9[78]\d{8}" required>
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
// Form validation
document.getElementById('signupForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (password !== confirmPassword) {
        e.preventDefault();
        document.getElementById('password-match-error').style.display = 'block';
        return false;
    }
});
</script>

<?php include_once __DIR__ . '/../includes/footer-bootstrap.php'; ?>
