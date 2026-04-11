<?php
/**
 * Reset Password Backend
 * Easy Shopping A.R.S
 */
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/email-service.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!validate_csrf_token()) {
        $_SESSION['error'] = "Invalid request. Please try again.";
        header("Location: ../auth/login.php");
        exit();
    }

    $token            = $_POST['token'] ?? '';
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($token) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../auth/reset-password.php?token=" . urlencode($token));
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header("Location: ../auth/reset-password.php?token=" . urlencode($token));
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../auth/reset-password.php?token=" . urlencode($token));
        exit();
    }

    try {
        // Hash the incoming raw token and compare against the stored hash
        $hashed_token = hash('sha256', $token);
        $stmt = $pdo->prepare("SELECT id, full_name, email FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$hashed_token]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['error'] = "Invalid or expired reset link.";
            header("Location: ../auth/login.php");
            exit();
        }

        // Hash new password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Update password and clear reset token
        $stmt = $pdo->prepare("UPDATE users SET
            password = ?,
            reset_token = NULL,
            reset_expires = NULL,
            reset_token_used_at = NOW()
            WHERE id = ?");
        $stmt->execute([$hashed_password, $user['id']]);

        // Send security confirmation email (non-blocking — failure doesn't stop the reset)
        try {
            $emailService = getEmailService();
            $emailService->sendPasswordResetSuccess($user['email'], $user['full_name']);
        } catch (Exception $e) {
            error_log('[ARS] Password reset success email failed: ' . $e->getMessage());
        }

        $_SESSION['success'] = "Password has been reset successfully. Please login with your new password.";
        header("Location: ../auth/login.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error. Please try again.";
        header("Location: ../auth/reset-password.php?token=" . urlencode($token));
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>