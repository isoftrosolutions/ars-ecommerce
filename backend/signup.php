<?php
/**
 * User Registration Backend
 * Easy Shopping A.R.S
 */
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/email-service.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = h($_POST['full_name']);
    $email     = h($_POST['email']);
    $mobile    = h($_POST['mobile']);
    $address   = h($_POST['address'] ?? '');
    $password  = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email_verified = $_POST['email_verified'] ?? '0';

    // Basic Validation
    if (empty($full_name) || empty($email) || empty($mobile) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../auth/signup.php");
        exit();
    }

    // Check email verification
    if ($email_verified !== '1' || !isset($_SESSION['email_verified']) || $_SESSION['email_verified'] !== $email) {
        $_SESSION['error'] = "Please verify your email address with OTP before signing up.";
        header("Location: ../auth/signup.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../auth/signup.php");
        exit();
    }

    try {
        // Check if email or mobile already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR mobile = ?");
        $stmt->execute([$email, $mobile]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Email or Mobile number already registered.";
            header("Location: ../auth/signup.php");
            exit();
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, mobile, password, address, role) VALUES (?, ?, ?, ?, ?, 'customer')");
        $stmt->execute([$full_name, $email, $mobile, $hashed_password, $address]);

        // Send welcome email
        $emailService = getEmailService();
        $emailService->sendWelcome($email, $full_name);

        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: ../auth/login.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: ../auth/signup.php");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>
