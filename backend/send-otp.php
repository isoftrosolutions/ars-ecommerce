<?php
/**
 * Send OTP Backend
 * Easy Shopping A.R.S
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/email-service.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$action = $_POST['action'] ?? '';

if (empty($email) && empty($mobile)) {
    echo json_encode(['success' => false, 'message' => 'Email or mobile number is required']);
    exit;
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
    exit;
}

if (!empty($mobile) && !preg_match('/^9[78]\d{8}$/', $mobile)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid mobile number (98XXXXXXXX or 97XXXXXXXX)']);
    exit;
}

try {
    $identifier = !empty($email) ? $email : $mobile;
    $result = send_otp($identifier, $action);

    if (!$result['success']) {
        echo json_encode(['success' => false, 'message' => $result['message']]);
        exit;
    }

    // Mask contact for response
    $masked_contact = '';
    if (!empty($email)) {
        $masked_contact = substr($email, 0, 2) . '***' . substr($email, strpos($email, '@'));
    } elseif (!empty($mobile)) {
        $masked_contact = substr($mobile, 0, 2) . '***' . substr($mobile, -3);
    }

    echo json_encode([
        'success' => true,
        'message' => 'OTP sent successfully to ' . $masked_contact
    ]);

} catch (Exception $e) {
    error_log('OTP Send Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to send OTP. Please try again.']);
}
?>