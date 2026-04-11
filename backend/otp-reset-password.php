<?php
/**
 * OTP Reset Password Backend
 * Easy Shopping A.R.S
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!validate_csrf_token()) {
        $_SESSION['error'] = "Invalid request. Please try again.";
        header("Location: ../auth/forgot-password.php");
        exit();
    }

    $email = trim($_POST['email'] ?? '');
    $otp = trim($_POST['otp'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email address.";
        header("Location: ../auth/otp-reset-password.php?email=" . urlencode($email));
        exit();
    }

    if (empty($otp) || !preg_match('/^\d{6}$/', $otp)) {
        $_SESSION['error'] = "Please enter a valid 6-digit OTP.";
        header("Location: ../auth/otp-reset-password.php?email=" . urlencode($email));
        exit();
    }

    if (empty($new_password) || strlen($new_password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header("Location: ../auth/otp-reset-password.php?email=" . urlencode($email));
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../auth/otp-reset-password.php?email=" . urlencode($email));
        exit();
    }

    try {
        // Use centralized helper to verify OTP
        $result = verify_otp($email, $otp);

        if (!$result['success']) {
            $_SESSION['error'] = $result['message'];
            header("Location: ../auth/otp-reset-password.php?email=" . urlencode($email));
            exit();
        }

        // Check if user still exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['error'] = "Account not found.";
            header("Location: ../auth/forgot-password.php");
            exit();
        }

        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password and clear OTP attempts
        $stmt = $pdo->prepare("
            UPDATE users
            SET password = ?, otp_attempts = 0, otp_issued_at = NULL
            WHERE email = ?
        ");
        $stmt->execute([$hashed_password, $email]);

        // Clear the OTP from session
        unset($_SESSION['temp_otp']);

        // Clear any existing reset tokens
        $stmt = $pdo->prepare("
            UPDATE users
            SET reset_token = NULL, reset_expires = NULL, reset_token_used_at = NULL
            WHERE email = ?
        ");
        $stmt->execute([$email]);

        $_SESSION['success'] = "Password reset successfully! You can now log in with your new password.";

        // Log the password reset
        error_log("Password reset successful for email: " . $email);

    } catch (PDOException $e) {
        error_log('OTP Password Reset Error: ' . $e->getMessage());
        $_SESSION['error'] = "Database error. Please try again.";
        header("Location: ../auth/otp-reset-password.php?email=" . urlencode($email));
        exit();
    }

    header("Location: ../auth/login.php");
    exit();
} else {
    header("Location: ../index.php");
    exit();
}
?>