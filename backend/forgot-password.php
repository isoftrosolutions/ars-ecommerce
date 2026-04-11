<?php
/**
 * Forgot Password Backend
 * Easy Shopping A.R.S
 */
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/email-service.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = h($_POST['email']);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header("Location: ../auth/forgot-password.php");
        exit();
    }

    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            // Don't reveal if email exists or not for security
            $_SESSION['success'] = "If an account with that email exists, we've sent a password reset link.";
            header("Location: ../auth/forgot-password.php");
            exit();
        }

        // Generate secure reset token
        $reset_token = bin2hex(random_bytes(32));
        $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store reset token in database
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmt->execute([$reset_token, $reset_expires, $email]);

        // Send password reset email
        $emailService = getEmailService();
        $sendSuccess = $emailService->sendPasswordReset($email, $reset_link);

        if (!$sendSuccess) {
            $_SESSION['error'] = "Failed to send password reset email. Please try again.";
            header("Location: ../auth/forgot-password.php");
            exit();
        }

        $_SESSION['success'] = "Password reset link has been sent to your email address.";

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error. Please try again.";
    }

    header("Location: ../auth/forgot-password.php");
    exit();
} else {
    header("Location: ../index.php");
    exit();
}
?>