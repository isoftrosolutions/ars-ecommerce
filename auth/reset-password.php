<?php
/**
 * Reset Password Page
 * Easy Shopping A.R.S
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = "Reset Password";

// Check if token is provided
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $_SESSION['error'] = "Invalid reset link.";
    header("Location: ../auth/login.php");
    exit();
}

// Verify token
try {
    $stmt = $pdo->prepare("SELECT id, full_name, email FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = "Invalid or expired reset link.";
        header("Location: ../auth/login.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error.";
    header("Location: ../auth/login.php");
    exit();
}

include_once __DIR__ . '/../includes/header-bootstrap.php';
?>

<link rel="stylesheet" href="<?php echo url('/public/assets/css/auth.css'); ?>">

<div class="auth-wrapper">
    <div class="container d-flex justify-content-center">
        <div class="auth-card">
            <h1 class="auth-title">Reset Password</h1>
            <p class="auth-subtitle">Enter your new password below</p>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="auth-alert auth-alert-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <form action="<?php echo url('/backend/reset-password.php'); ?>" method="POST" class="auth-form" id="resetForm">
                <input type="hidden" name="token" value="<?php echo h($token); ?>">

                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <div class="password-group">
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                        <i class="bi bi-eye password-toggle"></i>
                    </div>
                    <div class="form-text">Must be at least 8 characters long</div>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <div class="password-group">
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="••••••••" required>
                        <i class="bi bi-eye password-toggle"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-auth">
                    Reset Password <i class="bi bi-key ms-2"></i>
                </button>
            </form>

            <div class="auth-footer">
                <a href="<?php echo url('/auth/login'); ?>" class="auth-link">Back to Login</a>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo url('/public/assets/js/auth.js'); ?>"></script>

<script>
// Form validation
document.getElementById('resetForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (password.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long.');
        return false;
    }

    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match. Please check and try again.');
        return false;
    }
});
</script>

<?php include_once __DIR__ . '/../includes/footer-bootstrap.php'; ?>