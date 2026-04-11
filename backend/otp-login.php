<?php
/**
 * OTP Login Backend
 * Easy Shopping A.R.S
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../auth/login.php");
    exit;
}

$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$otp = trim($_POST['otp'] ?? '');

if (empty($otp)) {
    $_SESSION['error'] = "OTP is required.";
    header("Location: ../auth/login.php");
    exit;
}

if (empty($email) && empty($mobile)) {
    $_SESSION['error'] = "Email or mobile number is required.";
    header("Location: ../auth/login.php");
    exit;
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Please enter a valid email address.";
    header("Location: ../auth/login.php");
    exit;
}

if (!empty($mobile) && !preg_match('/^9[78]\d{8}$/', $mobile)) {
    $_SESSION['error'] = "Please enter a valid mobile number.";
    header("Location: ../auth/login.php");
    exit;
}

if (!preg_match('/^\d{6}$/', $otp)) {
    $_SESSION['error'] = "Please enter a valid 6-digit OTP.";
    header("Location: ../auth/login.php");
    exit;
}

try {
    // Check if OTP was sent and is still valid
    if (!isset($_SESSION['temp_otp']) ||
        $_SESSION['temp_otp']['expires'] < time()) {
        $_SESSION['error'] = "OTP has expired or was not sent. Please request a new one.";
        header("Location: ../auth/login.php");
        exit;
    }

    // Verify the contact method matches
    $session_email = $_SESSION['temp_otp']['email'] ?? '';
    $session_mobile = $_SESSION['temp_otp']['mobile'] ?? '';

    if ((!empty($email) && $session_email !== $email) ||
        (!empty($mobile) && $session_mobile !== $mobile)) {
        $_SESSION['error'] = "OTP was sent to a different contact method.";
        header("Location: ../auth/login.php");
        exit;
    }

    // Verify OTP
    if (!password_verify($otp, $_SESSION['temp_otp']['otp'])) {
        $_SESSION['error'] = "Invalid OTP. Please check and try again.";
        header("Location: ../auth/login.php");
        exit;
    }

    // OTP verified, get user details
    $where = [];
    $params = [];

    if (!empty($email)) {
        $where[] = "email = ?";
        $params[] = $email;
    }
    if (!empty($mobile)) {
        $where[] = "mobile = ?";
        $params[] = $mobile;
    }

    $query = "SELECT * FROM users WHERE " . implode(" OR ", $where);
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = "Account not found.";
        header("Location: ../auth/login.php");
        exit;
    }

    // Login successful
    unset($user['password']); // Don't store password in session
    unset($_SESSION['temp_otp']); // Clean up temp OTP

    // Reset OTP attempts
    $stmt = $pdo->prepare("UPDATE users SET otp_attempts = 0 WHERE email = ?");
    $stmt->execute([$email]);

    $_SESSION['user'] = $user;

    // Transfer guest cart to user if exists
    transfer_guest_cart_to_user($user['id']);

    if ($user['role'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../index.php");
    }
    exit;

} catch (PDOException $e) {
    $_SESSION['error'] = "Database error. Please try again.";
    header("Location: ../auth/login.php");
    exit;
}
?>