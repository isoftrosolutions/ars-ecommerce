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
            <p class="auth-subtitle">Enter your email address and we'll send you a link to reset your password</p>

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

            <form action="<?php echo url('/backend/forgot-password.php'); ?>" method="POST" class="auth-form" id="forgotForm">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="your@email.com" required autofocus>
                    <div class="form-text">Enter the email address associated with your account</div>
                </div>

                <button type="submit" class="btn btn-auth">
                    Send Reset Link <i class="bi bi-envelope ms-2"></i>
                </button>
            </form>

            <div class="auth-footer">
                Remember your password? <a href="<?php echo url('/auth/login'); ?>" class="auth-link">Back to Login</a>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo url('/public/assets/js/auth.js'); ?>"></script>

<?php include_once __DIR__ . '/../includes/footer-bootstrap.php'; ?>