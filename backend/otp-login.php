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
    $identifier = !empty($email) ? $email : $mobile;
    $result = verify_otp($identifier, $otp);

    if (!$result['success']) {
        $_SESSION['error'] = $result['message'];
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

    // Reset OTP attempts using user ID (email may be empty if mobile was used)
    $stmt = $pdo->prepare("UPDATE users SET otp_attempts = 0 WHERE id = ?");
    $stmt->execute([$user['id']]);

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