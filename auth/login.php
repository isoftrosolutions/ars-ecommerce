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



            <div class="auth-footer">
                Don't have an account? <a href="<?php echo url('/auth/signup'); ?>" class="auth-link">Create Account</a>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo url('/public/assets/js/auth.js'); ?>"></script>



<?php include_once __DIR__ . '/../includes/footer-bootstrap.php'; ?>
