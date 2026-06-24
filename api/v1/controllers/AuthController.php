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
        validate_required($data, ['name', 'phone', 'password', 'email']);
        validate_phone($data['phone']);
        validate_min_length($data['password'], 'password', 8);
        validate_email($data['email']);
        ValidationErrors::throwIfInvalid();

        $name = sanitize_string($data['name']);
        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        $email = sanitize_string($data['email']);

        // Build address — accept flat string or structured fields
        $address = build_address_string(
            $data['address'] ?? '',
            $data['province'] ?? '',
            $data['district'] ?? '',
            $data['municipality'] ?? '',
            $data['ward'] ?? '',
            $data['street'] ?? ''
        );

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

        // Insert user with status active
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (full_name, email, mobile, address, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, 'customer', 'active', NOW())"
        );
        $stmt->execute([$name, $email, $phone, $address, $hashedPassword]);
        $userId = $this->pdo->lastInsertId();

        // Save structured address to user_addresses table
        $province = $data['province'] ?? '';
        $district = $data['district'] ?? '';
        $municipality = $data['municipality'] ?? '';
        $ward = $data['ward'] ?? '';
        $street = $data['street'] ?? '';
        if (!empty($province) && !empty($district) && !empty($municipality)) {
            try {
                $stmt = $this->pdo->query("SHOW TABLES LIKE 'user_addresses'");
                if ($stmt->rowCount() > 0) {
                    $stmt = $this->pdo->prepare("INSERT INTO user_addresses (user_id, full_name, phone, province, district, municipality, ward, street, tag, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Home', 1)");
                    $stmt->execute([$userId, $name, $phone, $province, $district, $municipality, $ward, $street]);
                }
            } catch (PDOException $e) {
                // Table doesn't exist — skip
            }
        }

        json_success(null, 'Registration successful. Please login.', 201);
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
     * Login with email OR phone and password.
     * Accepts {login_id,password}; also accepts legacy {phone,password}.
     */
    public function login($params)
    {
        $data = get_json_input();

        // Back-compat: legacy clients send `phone`; new clients send `login_id`.
        $loginId = '';
        if (isset($data['login_id']) && trim($data['login_id']) !== '') {
            $loginId = trim($data['login_id']);
        } elseif (isset($data['phone']) && trim($data['phone']) !== '') {
            $loginId = trim($data['phone']);
        }

        check_rate_limit('login', $loginId);

        if ($loginId === '') {
            ValidationErrors::add('login_id', 'The login_id field is required');
        }
        validate_required($data, ['password']);

        $isEmail = filter_var($loginId, FILTER_VALIDATE_EMAIL) !== false;
        if (!$isEmail && $loginId !== '') {
            // Not an email — must be a valid Nepali phone.
            validate_phone($loginId, 'login_id');
        }
        ValidationErrors::throwIfInvalid();

        $password = $data['password'];

        if ($isEmail) {
            $stmt = $this->pdo->prepare("SELECT id, full_name, email, mobile, password, role, status FROM users WHERE email = ?");
            $stmt->execute([$loginId]);
        } else {
            $phone = preg_replace('/[^0-9]/', '', $loginId);
            $stmt = $this->pdo->prepare("SELECT id, full_name, email, mobile, password, role, status FROM users WHERE mobile = ?");
            $stmt->execute([$phone]);
        }
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            json_error('Invalid credentials', 401);
        }

        if ($user['status'] === 'suspended') {
            json_error('Account suspended. Contact support.', 403);
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
     * POST /auth/forgot-password
     * Send OTP to email for password reset.
     */
    public function forgotPassword($params)
    {
        $data = get_json_input();
        validate_required($data, ['email']);
        validate_email($data['email']);
        ValidationErrors::throwIfInvalid();

        $email = sanitize_string($data['email']);

        // Check if email exists (anti-enumeration: always return same message)
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate and store OTP
            $otp = sprintf('%06d', mt_rand(100000, 999999));
            $hashedOtp = password_hash($otp, PASSWORD_DEFAULT);
            $expiresAt = date('Y-m-d H:i:s', time() + 300);

            $stmt = $this->pdo->prepare(
                "INSERT INTO otps (phone, otp_code, hashed_otp, expires_at, created_at) VALUES (?, ?, ?, ?, NOW())"
            );
            $stmt->execute([$email, $otp, $hashedOtp, $expiresAt]);

            // Send email OTP
            $name = $user['name'] ?? $user['full_name'] ?? 'Valued Customer';
            require_once __DIR__ . '/../../../includes/functions.php';
            require_once __DIR__ . '/../../../includes/email-service.php';
            $emailService = getEmailService();
            $emailService->sendOTP($email, $otp, $name);
        }

        json_success(null, 'If your email is registered, you will receive an OTP');
    }

    /**
     * POST /auth/reset-password
     * Reset password using OTP.
     */
    public function resetPassword($params)
    {
        $data = get_json_input();
        validate_required($data, ['email', 'otp', 'new_password']);
        validate_email($data['email']);
        validate_min_length($data['new_password'], 'new_password', 8);
        ValidationErrors::throwIfInvalid();

        $email = sanitize_string($data['email']);
        $otp = trim($data['otp']);

        // Find valid OTP
        $stmt = $this->pdo->prepare(
            "SELECT * FROM otps WHERE phone = ? AND used = 0 AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$email]);
        $otpRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$otpRecord) {
            json_error('No valid OTP found. Request a new one.', 400);
        }

        // Verify OTP
        if ($otpRecord['otp_code'] !== $otp) {
            if (!password_verify($otp, $otpRecord['hashed_otp'])) {
                json_error('Invalid OTP', 400);
            }
        }

        // Mark OTP as used
        $stmt = $this->pdo->prepare("UPDATE otps SET used = 1 WHERE id = ?");
        $stmt->execute([$otpRecord['id']]);

        // Hash new password and update
        $hashed = password_hash($data['new_password'], PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashed, $email]);

        json_success(null, 'Password reset successfully');
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
        if (file_exists(__DIR__ . '/../../../includes/email-service.php')) {
            require_once __DIR__ . '/../../../includes/email-service.php';
            $emailService = getEmailService();
            $emailService->sendSMSOTP($phone, $otp);
        }
    }
}
