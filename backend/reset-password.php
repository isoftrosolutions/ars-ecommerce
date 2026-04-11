<?php
/**
 * Reset Password Backend
 * Easy Shopping A.R.S
 */
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

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
        // Verify token and get user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
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