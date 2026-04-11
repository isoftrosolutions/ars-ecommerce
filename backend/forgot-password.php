<?php
/**
 * Forgot Password Backend
 * Easy Shopping A.R.S
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/email-service.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!validate_csrf_token()) {
        $_SESSION['error'] = "Invalid request. Please try again.";
        header("Location: ../auth/forgot-password.php");
        exit();
    }

    // Use raw trim — h() is for HTML output only, not DB queries
    $email = trim($_POST['email'] ?? '');
    $reset_method = trim($_POST['reset_method'] ?? 'email-otp');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header("Location: ../auth/forgot-password.php");
        exit();
    }

    if ($reset_method !== 'email-otp') {
        $_SESSION['error'] = "Invalid reset method.";
        header("Location: ../auth/forgot-password.php");
        exit();
    }

    // Same success message regardless of outcome (anti-enumeration)
    $generic_success = "If an account with that email exists, we've sent an OTP to reset your password.";

    try {
        // Use centralized helper to generate and send OTP
        $result = send_otp($email, 'password_reset');

        if (!$result['success']) {
            // We still redirect to avoid enumeration, but log the error
            error_log("Failed to send password reset OTP: " . $result['message']);
        }

        header("Location: ../auth/otp-reset-password.php?email=" . urlencode($email));
        exit();

    } catch (PDOException $e) {
        error_log('Forgot Password Error: ' . $e->getMessage());
        $_SESSION['error'] = "An error occurred. Please try again.";
    }

    header("Location: ../auth/forgot-password.php");
    exit();
} else {
    header("Location: ../index.php");
    exit();
}
?>