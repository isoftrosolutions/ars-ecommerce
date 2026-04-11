<?php
/**
 * Authentication Tests
 * Easy Shopping A.R.S
 *
 * This file contains tests for login and signup functionality
 * Run with: php tests/AuthTest.php
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/email-service.php';

class AuthTest {

    private $pdo;
    private $testUser = [
        'full_name' => 'Test User',
        'email' => 'test@example.com',
        'mobile' => '9812345678',
        'password' => 'TestPass123!',
        'address' => 'Test Address, Kathmandu'
    ];

    public function __construct() {
        $this->pdo = $GLOBALS['pdo'];
        $this->setupTestEnvironment();
    }

    private function setupTestEnvironment() {
        // Clean up any existing test data
        $this->cleanupTestData();

        // Create logs directory if it doesn't exist
        if (!is_dir(__DIR__ . '/../logs')) {
            mkdir(__DIR__ . '/../logs', 0755, true);
        }
    }

    private function cleanupTestData() {
        try {
            // Remove test user if exists
            $this->pdo->prepare("DELETE FROM users WHERE email = ? OR mobile = ?")
                     ->execute([$this->testUser['email'], $this->testUser['mobile']]);

            // Clear test sessions
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
        } catch (Exception $e) {
            // Ignore cleanup errors
        }
    }

    public function runAllTests() {
        echo "🧪 Running Authentication Tests...\n\n";

        $tests = [
            'testSignupValidation' => 'Signup Validation Tests',
            'testSignupSuccess' => 'Signup Success Test',
            'testLoginValidation' => 'Login Validation Tests',
            'testLoginSuccess' => 'Login Success Test',
            'testOTPLogin' => 'OTP Login Test',
            'testForgotPassword' => 'Forgot Password Test',
            'testRememberMe' => 'Remember Me Functionality Test'
        ];

        $results = [];

        foreach ($tests as $method => $description) {
            echo "Running: {$description}\n";
            try {
                $result = $this->{$method}();
                $results[$method] = $result;
                echo "✅ PASSED\n";
            } catch (Exception $e) {
                $results[$method] = false;
                echo "❌ FAILED: " . $e->getMessage() . "\n";
            }
            echo "\n";
        }

        $this->showSummary($results);
        $this->cleanupTestData();
    }

    private function testSignupValidation() {
        // Test empty fields
        $_POST = [];
        ob_start();
        include __DIR__ . '/../backend/signup.php';
        ob_end_clean();

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should show error for empty fields");
        }

        // Test invalid email
        $_POST = [
            'full_name' => 'Test User',
            'email' => 'invalid-email',
            'mobile' => '9812345678',
            'password' => 'password123',
            'confirm_password' => 'password123',
            'address' => 'Test Address'
        ];
        ob_start();
        include __DIR__ . '/../backend/signup.php';
        ob_end_clean();

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should validate email format");
        }

        // Test password mismatch
        $_POST = [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'mobile' => '9812345678',
            'password' => 'password123',
            'confirm_password' => 'differentpass',
            'address' => 'Test Address'
        ];
        ob_start();
        include __DIR__ . '/../backend/signup.php';
        ob_end_clean();

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should detect password mismatch");
        }

        return true;
    }

    private function testSignupSuccess() {
        // Simulate successful signup
        $_POST = $this->testUser;
        $_POST['confirm_password'] = $this->testUser['password'];
        $_SESSION['email_verified'] = $this->testUser['email'];

        ob_start();
        include __DIR__ . '/../backend/signup.php';
        ob_end_clean();

        // Check if user was created
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$this->testUser['email']]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("User should be created successfully");
        }

        if (!password_verify($this->testUser['password'], $user['password'])) {
            throw new Exception("Password should be hashed correctly");
        }

        if ($user['role'] !== 'customer') {
            throw new Exception("New user should have customer role");
        }

        return true;
    }

    private function testLoginValidation() {
        // Test empty fields
        $_POST = [];
        ob_start();
        include __DIR__ . '/../backend/login.php';
        ob_end_clean();

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should show error for empty login fields");
        }

        // Test invalid credentials
        $_POST = [
            'login_id' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ];
        ob_start();
        include __DIR__ . '/../backend/login.php';
        ob_end_clean();

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should show error for invalid credentials");
        }

        return true;
    }

    private function testLoginSuccess() {
        // First ensure test user exists
        $this->ensureTestUserExists();

        // Test successful login with email
        $_POST = [
            'login_id' => $this->testUser['email'],
            'password' => $this->testUser['password']
        ];

        ob_start();
        include __DIR__ . '/../backend/login.php';
        ob_end_clean();

        if (!isset($_SESSION['user']) || $_SESSION['user']['email'] !== $this->testUser['email']) {
            throw new Exception("Should login successfully with email");
        }

        // Test successful login with mobile
        session_destroy();
        session_start();
        $_POST = [
            'login_id' => $this->testUser['mobile'],
            'password' => $this->testUser['password']
        ];

        ob_start();
        include __DIR__ . '/../backend/login.php';
        ob_end_clean();

        if (!isset($_SESSION['user']) || $_SESSION['user']['mobile'] !== $this->testUser['mobile']) {
            throw new Exception("Should login successfully with mobile");
        }

        return true;
    }

    private function testOTPLogin() {
        $this->ensureTestUserExists();

        // Test OTP sending
        $_POST = ['email' => $this->testUser['email'], 'action' => 'send'];

        ob_start();
        include __DIR__ . '/../backend/send-otp.php';
        ob_end_clean();

        // Check if OTP was stored in session
        if (!isset($_SESSION['temp_otp']) || $_SESSION['temp_otp']['email'] !== $this->testUser['email']) {
            throw new Exception("OTP should be stored in session");
        }

        // Test OTP verification
        $otp = '123456'; // Mock OTP
        $_SESSION['temp_otp']['otp'] = password_hash($otp, PASSWORD_DEFAULT);

        $_POST = ['email' => $this->testUser['email'], 'otp' => $otp, 'action' => 'verify'];

        ob_start();
        include __DIR__ . '/../backend/otp-login.php';
        ob_end_clean();

        if (!isset($_SESSION['user'])) {
            throw new Exception("Should login successfully with OTP");
        }

        return true;
    }

    private function testForgotPassword() {
        $this->ensureTestUserExists();

        // Test forgot password request
        $_POST = ['email' => $this->testUser['email']];

        ob_start();
        include __DIR__ . '/../backend/forgot-password.php';
        ob_end_clean();

        // Check if reset token was generated
        $stmt = $this->pdo->prepare("SELECT reset_token FROM users WHERE email = ?");
        $stmt->execute([$this->testUser['email']]);
        $user = $stmt->fetch();

        if (!$user['reset_token']) {
            throw new Exception("Reset token should be generated");
        }

        // Test password reset
        $newPassword = 'NewTestPass123!';
        $_POST = [
            'token' => $user['reset_token'],
            'password' => $newPassword,
            'confirm_password' => $newPassword
        ];

        ob_start();
        include __DIR__ . '/../backend/reset-password.php';
        ob_end_clean();

        // Verify password was changed
        $stmt = $this->pdo->prepare("SELECT password, reset_token FROM users WHERE email = ?");
        $stmt->execute([$this->testUser['email']]);
        $user = $stmt->fetch();

        if (!password_verify($newPassword, $user['password'])) {
            throw new Exception("Password should be updated");
        }

        if ($user['reset_token']) {
            throw new Exception("Reset token should be cleared");
        }

        return true;
    }

    private function testRememberMe() {
        $this->ensureTestUserExists();

        // Test remember me login
        $_POST = [
            'login_id' => $this->testUser['email'],
            'password' => $this->testUser['password'],
            'remember' => 'on'
        ];

        ob_start();
        include __DIR__ . '/../backend/login.php';
        ob_end_clean();

        // Check if remember token was set
        $stmt = $this->pdo->prepare("SELECT remember_token FROM users WHERE email = ?");
        $stmt->execute([$this->testUser['email']]);
        $user = $stmt->fetch();

        if (!$user['remember_token']) {
            throw new Exception("Remember token should be set");
        }

        // Simulate cookie-based login
        $rememberToken = bin2hex(random_bytes(32));
        setcookie('remember_token', $rememberToken, time() + 3600, '/');
        $_COOKIE['remember_token'] = $rememberToken;

        // Update user with the token
        $hashedToken = hash('sha256', $rememberToken);
        $stmt = $this->pdo->prepare("UPDATE users SET remember_token = ? WHERE email = ?");
        $stmt->execute([$hashedToken, $this->testUser['email']]);

        // Test remember me functionality by including db.php
        session_destroy();
        session_start();
        include __DIR__ . '/../includes/db.php';

        if (!isset($_SESSION['user'])) {
            throw new Exception("Should auto-login with remember token");
        }

        return true;
    }

    private function ensureTestUserExists() {
        // Check if test user exists, create if not
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$this->testUser['email']]);
        $user = $stmt->fetch();

        if (!$user) {
            $hashedPassword = password_hash($this->testUser['password'], PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare("INSERT INTO users (full_name, email, mobile, password, address, role) VALUES (?, ?, ?, ?, ?, 'customer')");
            $stmt->execute([
                $this->testUser['full_name'],
                $this->testUser['email'],
                $this->testUser['mobile'],
                $hashedPassword,
                $this->testUser['address']
            ]);
        }
    }

    private function showSummary($results) {
        $passed = 0;
        $total = count($results);

        foreach ($results as $result) {
            if ($result) $passed++;
        }

        echo "📊 Test Summary:\n";
        echo "Passed: {$passed}/{$total}\n";
        echo "Failed: " . ($total - $passed) . "/{$total}\n";

        if ($passed === $total) {
            echo "\n🎉 All tests passed!\n";
        } else {
            echo "\n⚠️  Some tests failed. Please review the implementation.\n";
        }
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $test = new AuthTest();
    $test->runAllTests();
}
?>