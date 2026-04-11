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
    // Check if user exists with this email or mobile
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

    $query = "SELECT id, full_name, otp_attempts, otp_issued_at FROM users WHERE " . implode(" OR ", $where);
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'No account found with this email address']);
        exit;
    }

    // Check OTP attempt limits (max 3 attempts per hour)
    $current_time = date('Y-m-d H:i:s');
    $one_hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));

    if ($user['otp_attempts'] >= 3 && $user['otp_issued_at'] > $one_hour_ago) {
        echo json_encode(['success' => false, 'message' => 'Too many OTP requests. Please try again later.']);
        exit;
    }

    // Generate 6-digit OTP
    $otp = sprintf('%06d', mt_rand(100000, 999999));

    // Update OTP attempts
    $where = [];
    $update_params = [$current_time];

    if (!empty($email)) {
        $where[] = "email = ?";
        $update_params[] = $email;
    }
    if (!empty($mobile)) {
        $where[] = "mobile = ?";
        $update_params[] = $mobile;
    }

    $update_query = "UPDATE users SET otp_attempts = otp_attempts + 1, otp_issued_at = ? WHERE " . implode(" OR ", $where);
    $stmt = $pdo->prepare($update_query);
    $stmt->execute($update_params);

    // For demo purposes, we'll simulate email sending
    // In production, integrate with email service like SendGrid, Mailgun, etc.

    // Store OTP temporarily in session (consider using Redis/Memcache for production)
    $_SESSION['temp_otp'] = [
        'email' => $email,
        'mobile' => $mobile,
        'otp' => password_hash($otp, PASSWORD_DEFAULT), // Hash OTP for additional security
        'expires' => strtotime('+5 minutes')
    ];

    // Send OTP via email or SMS
    $emailService = getEmailService();
    $sendSuccess = false;

    if (!empty($email)) {
        $sendSuccess = $emailService->sendOTP($email, $otp);
    } elseif (!empty($mobile)) {
        $sendSuccess = $emailService->sendSMSOTP($mobile, $otp);
    }

    if (!$sendSuccess) {
        echo json_encode(['success' => false, 'message' => 'Failed to send OTP. Please try again.']);
        exit;
    }

    // Simulate email sending delay
    usleep(500000); // 0.5 seconds

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

} catch (PDOException $e) {
    error_log('OTP Send Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to send OTP. Please try again.']);
}
?>