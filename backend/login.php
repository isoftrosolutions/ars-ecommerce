<?php
/**
 * User Login Backend
 * Easy Shopping A.R.S
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = h($_POST['login_id']); // This can be email or mobile
    $password = $_POST['password'];

    if (empty($login_id) || empty($password)) {
        $_SESSION['error'] = "Both fields are required.";
        header("Location: ../auth/login.php");
        exit();
    }

    try {
        // Find user by email OR mobile
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR mobile = ?");
        $stmt->execute([$login_id, $login_id]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Handle remember me
            if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
                // Generate secure remember token
                $remember_token = bin2hex(random_bytes(32));
                $hashed_token = hash('sha256', $remember_token);

                // Store hashed token in database
                $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $stmt->execute([$hashed_token, $user['id']]);

                // Set cookie for 30 days
                setcookie('remember_token', $remember_token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            }

            // Success: Set session
            unset($user['password']); // Don't store password in session
            $_SESSION['user'] = $user;

            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../profile.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Invalid email/mobile or password.";
            header("Location: ../auth/login.php");
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: ../auth/login.php");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>
