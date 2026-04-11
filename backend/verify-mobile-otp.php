<?php
/**
 * Verify Mobile OTP Backend
 * Easy Shopping A.R.S
 */
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$mobile = trim($_POST['mobile'] ?? '');
$otp = trim($_POST['otp'] ?? '');
$action = $_POST['action'] ?? '';

if (empty($mobile) || empty($otp)) {
    echo json_encode(['success' => false, 'message' => 'Mobile number and OTP are required']);
    exit;
}

if (!preg_match('/^9[78]\d{8}$/', $mobile)) {
    echo json_encode(['success' => false, 'message' => 'Invalid mobile number format']);
    exit;
}

if (!preg_match('/^\d{6}$/', $otp)) {
    echo json_encode(['success' => false, 'message' => 'Invalid OTP format']);
    exit;
}

try {
    // Check if OTP exists in session and is valid
    if (!isset($_SESSION['temp_otp']) ||
        $_SESSION['temp_otp']['mobile'] !== $mobile ||
        $_SESSION['temp_otp']['expires'] < time()) {
        echo json_encode(['success' => false, 'message' => 'OTP has expired or was not sent']);
        exit;
    }

    // Verify OTP
    if ($_SESSION['temp_otp']['otp'] !== $otp) {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
        exit;
    }

    // OTP verified successfully
    if ($action === 'signup_verify') {
        // For signup, just mark as verified (signup process will handle the rest)
        $_SESSION['mobile_verified'] = $mobile;
        echo json_encode(['success' => true, 'message' => 'Mobile number verified successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    error_log('Mobile OTP Verification Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Verification failed. Please try again.']);
}
?>