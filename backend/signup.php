<?php
/**
 * User Registration Backend
 * Easy Shopping A.R.S
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/email-service.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!validate_csrf_token()) {
        $_SESSION['error'] = "Invalid request. Please try again.";
        header("Location: ../auth/signup.php");
        exit();
    }

    // Use raw trimmed values for DB insertion — h() is for HTML output only
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $mobile    = trim($_POST['mobile'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic Validation
    if (empty($full_name) || empty($email) || empty($mobile) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../auth/signup.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header("Location: ../auth/signup.php");
        exit();
    }

    if (!preg_match('/^9[78]\d{8}$/', $mobile)) {
        $_SESSION['error'] = "Please enter a valid mobile number (e.g. 98XXXXXXXX).";
        header("Location: ../auth/signup.php");
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters.";
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
