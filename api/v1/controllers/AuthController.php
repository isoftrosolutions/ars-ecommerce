<?php
/**
 * Auth Controller
 * Handles customer registration, login, OTP verification, and password management.
 *
 * @route /auth/*
 * @auth public (except logout, change-password)
 */

class AuthController
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * POST /auth/register
     * Register a new customer account.
     */
    public function register($params)
    {
        $data = get_json_input();
        check_rate_limit('register', $data['phone'] ?? '');
        validate_required($data, ['name', 'phone', 'password']);
        validate_phone($data['phone']);
        validate_min_length($data['password'], 'password', 8);
        if (!empty($data['email'])) {
            validate_email($data['email']);
        }
        ValidationErrors::throwIfInvalid();

        $name = sanitize_string($data['name']);
        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        $email = !empty($data['email']) ? sanitize_string($data['email']) : null;
        $password = $data['password'];

        // Check phone uniqueness
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE mobile = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            json_error('Phone number already registered', 409);
        }

        // Check email uniqueness if provided
        if ($email) {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                json_error('Email already registered', 409);
            }
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert user with status pending
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (full_name, email, mobile, password, role, created_at) VALUES (?, ?, ?, ?, 'customer', NOW())"
        );
        $stmt->execute([$name, $email, $phone, $hashedPassword]);
        $userId = $this->pdo->lastInsertId();

        // Generate and store OTP
        $otp = sprintf('%06d', mt_rand(100000, 999999));
        $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', time() + 300); // 5 minutes

        $stmt = $this->pdo->prepare(
            "INSERT INTO otps (phone, otp_code, hashed_otp, expires_at, created_at) VALUES (?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$phone, $otp, $hashedOtp, $expiresAt]);

        // Send OTP via SMS
        $this->sendOtpSms($phone, $otp);

        json_success([
            'user_id' => (int)$userId,
            'otp_sent' => true,
        ], 'Registration successful. Please verify OTP.', 201);
    }

    /**
     * POST /auth/verify-otp
     * Verify OTP and activate the user account.
     */
    public function verifyOtp($params)
    {
        $data = get_json_input();
        check_rate_limit('verify_otp', $data['phone'] ?? '');
        validate_required($data, ['phone', 'otp']);
        validate_phone($data['phone']);
        ValidationErrors::throwIfInvalid();

        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        $otp = trim($data['otp']);

        // Find valid OTP
        $stmt = $this->pdo->prepare(
            "SELECT * FROM otps WHERE phone = ? AND used = 0 AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$phone]);
        $otpRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$otpRecord) {
            json_error('No valid OTP found. Request a new one.', 400);
        }

        // Verify OTP (plaintext match since we stored both for SMS)
        if ($otpRecord['otp_code'] !== $otp) {
            // Also try hashed verification
            if (!password_verify($otp, $otpRecord['hashed_otp'])) {
                json_error('Invalid OTP', 400);
            }
        }

        // Mark OTP as used
        $stmt = $this->pdo->prepare("UPDATE otps SET used = 1 WHERE id = ?");
        $stmt->execute([$otpRecord['id']]);

        // Find and activate user (or create if needed)
        $stmt = $this->pdo->prepare("SELECT id, full_name, email, mobile, role FROM users WHERE mobile = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            json_error('User not found', 404);
        }

        // Generate JWT
        $token = generate_token($user['id'], $user['role']);

        json_success([
            'token' => $token,
            'user' => [
                'id' => (int)$user['id'],
                'name' => $user['full_name'],
                'phone' => $user['mobile'],
                'email' => $user['email'],
            ],
        ], 'OTP verified successfully');
    }

    /**
     * POST /auth/login
     * Login with phone and password.
     */
    public function login($params)
    {
        $data = get_json_input();
        check_rate_limit('login', $data['phone'] ?? '');
        validate_required($data, ['phone', 'password']);
        validate_phone($data['phone']);
        ValidationErrors::throwIfInvalid();

        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        $password = $data['password'];

        $stmt = $this->pdo->prepare("SELECT id, full_name, email, mobile, password, role, status FROM users WHERE mobile = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            json_error('Invalid credentials', 401);
        }

        if ($user['status'] === 'pending' && $user['role'] !== 'admin') {
            json_error('Account not verified. Please verify OTP first.', 403);
        }

        $token = generate_token($user['id'], $user['role']);

        json_success([
            'token' => $token,
            'user' => [
                'id' => (int)$user['id'],
                'name' => $user['full_name'],
                'phone' => $user['mobile'],
                'email' => $user['email'],
            ],
        ], 'Login successful');
    }

    /**
     * POST /auth/resend-otp
     * Invalidate old OTP and send a new one.
     */
    public function resendOtp($params)
    {
        $data = get_json_input();
        check_rate_limit('resend_otp', $data['phone'] ?? '');
        validate_required($data, ['phone']);
        validate_phone($data['phone']);
        ValidationErrors::throwIfInvalid();

        $phone = preg_replace('/[^0-9]/', '', $data['phone']);

        // Invalidate old OTPs
        $stmt = $this->pdo->prepare("UPDATE otps SET used = 1 WHERE phone = ? AND used = 0");
        $stmt->execute([$phone]);

        // Generate new OTP
        $otp = sprintf('%06d', mt_rand(100000, 999999));
        $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', time() + 300);

        $stmt = $this->pdo->prepare(
            "INSERT INTO otps (phone, otp_code, hashed_otp, expires_at, created_at) VALUES (?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$phone, $otp, $hashedOtp, $expiresAt]);

        $this->sendOtpSms($phone, $otp);

        json_success(['otp_sent' => true], 'OTP resent successfully');
    }

    /**
     * POST /auth/logout
     * Stateless JWT logout — just acknowledge.
     */
    public function logout($params)
    {
        require_auth();
        json_success(null, 'Logged out successfully');
    }

    /**
     * POST /auth/change-password
     * Change the authenticated user's password.
     */
    public function changePassword($params)
    {
        $user = require_auth();

        $data = get_json_input();
        validate_required($data, ['current_password', 'new_password']);
        validate_min_length($data['new_password'], 'new_password', 8);
        ValidationErrors::throwIfInvalid();

        // Verify current password
        $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !password_verify($data['current_password'], $row['password'])) {
            json_error('Current password is incorrect', 400);
        }

        // Update password
        $hashed = password_hash($data['new_password'], PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $user['id']]);

        json_success(null, 'Password changed successfully');
    }

    /**
     * Send OTP via SMS (logs to file in dev, returns OTP in debug mode).
     */
    private function sendOtpSms($phone, $otp)
    {
        $message = "Your ARS Easy Shopping verification code is: $otp. Valid for 5 minutes.";

        // Log to file
        $log = "[" . date('Y-m-d H:i:s') . "] SMS to: $phone\n";
        $log .= "Message: $message\n";
        $log .= str_repeat("-", 50) . "\n\n";
        error_log($log, 3, __DIR__ . '/../../logs/api-v1.log');

        // Use existing email service for SMS if available
        if (file_exists(__DIR__ . '/../../includes/email-service.php')) {
            require_once __DIR__ . '/../../includes/email-service.php';
            $emailService = getEmailService();
            $emailService->sendSMSOTP($phone, $otp);
        }
    }
}
