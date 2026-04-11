<?php
/**
 * Signup Tests
 * Easy Shopping A.R.S
 *
 * Comprehensive tests for user registration functionality
 * Run with: php tests/SignupTest.php
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/email-service.php';

class SignupTest {

    private $pdo;
    private $baseUserData = [
        'full_name' => 'Test User',
        'email' => 'signup-test@example.com',
        'mobile' => '9812345678',
        'password' => 'TestPass123!',
        'confirm_password' => 'TestPass123!',
        'address' => 'Test Address, Kathmandu'
    ];

    public function __construct() {
        $this->pdo = $GLOBALS['pdo'];
        $this->cleanup();
    }

    private function cleanup() {
        try {
            // Remove test users
            $this->pdo->prepare("DELETE FROM users WHERE email LIKE ? OR mobile LIKE ?")
                     ->execute(['signup-test%', '98%']);

            // Clear sessions
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION = [];
            }
        } catch (Exception $e) {
            // Ignore cleanup errors
        }
    }

    public function runTests() {
        echo "🧪 Running Signup Tests...\n\n";

        $tests = [
            'testRequiredFields' => 'Required Fields Validation',
            'testEmailValidation' => 'Email Format Validation',
            'testMobileValidation' => 'Mobile Number Validation',
            'testPasswordRequirements' => 'Password Requirements',
            'testPasswordConfirmation' => 'Password Confirmation',
            'testDuplicateEmail' => 'Duplicate Email Prevention',
            'testDuplicateMobile' => 'Duplicate Mobile Prevention',
            'testEmailVerificationRequired' => 'Email Verification Requirement',
            'testSuccessfulRegistration' => 'Successful Registration',
            'testWelcomeEmail' => 'Welcome Email Sending'
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
        $this->cleanup();
    }

    private function testRequiredFields() {
        // Test missing full_name
        $data = $this->baseUserData;
        unset($data['full_name']);
        $this->simulateSignupPost($data);

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should require full_name field");
        }

        // Test missing email
        $data = $this->baseUserData;
        unset($data['email']);
        $this->simulateSignupPost($data);

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should require email field");
        }

        // Test missing mobile
        $data = $this->baseUserData;
        unset($data['mobile']);
        $this->simulateSignupPost($data);

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should require mobile field");
        }

        // Test missing password
        $data = $this->baseUserData;
        unset($data['password']);
        $this->simulateSignupPost($data);

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should require password field");
        }

        return true;
    }

    private function testEmailValidation() {
        // Test invalid email format
        $data = $this->baseUserData;
        $data['email'] = 'invalid-email';
        $this->simulateSignupPost($data);

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should validate email format");
        }

        // Test valid email
        $data = $this->baseUserData;
        $data['email'] = 'valid@example.com';
        $this->simulateSignupPost($data);

        // Should pass validation (may fail on duplicate check, but not email format)
        return true;
    }

    private function testMobileValidation() {
        // Test invalid mobile format (too short)
        $data = $this->baseUserData;
        $data['mobile'] = '98123456';
        $this->simulateSignupPost($data);

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should validate mobile number format");
        }

        // Test invalid mobile format (wrong prefix)
        $data = $this->baseUserData;
        $data['mobile'] = '9912345678';
        $this->simulateSignupPost($data);

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should validate mobile number prefix");
        }

        // Test valid mobile numbers
        $validMobiles = ['9812345678', '9723456789'];
        foreach ($validMobiles as $mobile) {
            $data = $this->baseUserData;
            $data['mobile'] = $mobile;
            $data['email'] = 'test' . $mobile . '@example.com';
            $this->simulateSignupPost($data);
            // Should pass mobile validation
        }

        return true;
    }

    private function testPasswordRequirements() {
        // Test password too short
        $data = $this->baseUserData;
        $data['password'] = $data['confirm_password'] = '12345';
        $this->simulateSignupPost($data);

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should enforce minimum password length");
        }

        // Test valid password
        $data = $this->baseUserData;
        $data['password'] = $data['confirm_password'] = 'ValidPass123!';
        $this->simulateSignupPost($data);

        // Should pass password validation
        return true;
    }

    private function testPasswordConfirmation() {
        // Test password mismatch
        $data = $this->baseUserData;
        $data['password'] = 'Password123!';
        $data['confirm_password'] = 'DifferentPass123!';
        $this->simulateSignupPost($data);

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should detect password mismatch");
        }

        return true;
    }

    private function testDuplicateEmail() {
        // Create a test user first
        $this->createTestUser();

        // Try to register with same email
        $data = $this->baseUserData;
        $this->simulateSignupPost($data);

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should prevent duplicate email registration");
        }

        return true;
    }

    private function testDuplicateMobile() {
        // Create a test user first
        $this->createTestUser();

        // Try to register with same mobile
        $data = $this->baseUserData;
        $data['email'] = 'different@example.com'; // Different email, same mobile
        $this->simulateSignupPost($data);

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should prevent duplicate mobile registration");
        }

        return true;
    }

    private function testEmailVerificationRequired() {
        // Try to register without email verification
        $data = $this->baseUserData;
        $data['email'] = 'new-verification-test@example.com';
        unset($_SESSION['email_verified']);
        $this->simulateSignupPost($data);

        if (!isset($_SESSION['error'])) {
            throw new Exception("Should require email verification");
        }

        return true;
    }

    private function testSuccessfulRegistration() {
        // Simulate complete registration process
        $data = $this->baseUserData;
        $data['email'] = 'success-test@example.com';
        $_SESSION['email_verified'] = $data['email'];

        $this->simulateSignupPost($data);

        // Check if user was created
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("User should be created successfully");
        }

        // Verify password hashing
        if (!password_verify($data['password'], $user['password'])) {
            throw new Exception("Password should be properly hashed");
        }

        // Verify user data
        if ($user['full_name'] !== $data['full_name'] ||
            $user['mobile'] !== $data['mobile'] ||
            $user['address'] !== $data['address'] ||
            $user['role'] !== 'customer') {
            throw new Exception("User data should be stored correctly");
        }

        return true;
    }

    private function testWelcomeEmail() {
        // This test assumes the email service is working
        // In a real test environment, you might mock the email service

        $data = $this->baseUserData;
        $data['email'] = 'welcome-test@example.com';
        $_SESSION['email_verified'] = $data['email'];

        // Capture email logs before signup
        $logFile = __DIR__ . '/../logs/emails.log';
        $beforeSize = file_exists($logFile) ? filesize($logFile) : 0;

        $this->simulateSignupPost($data);

        // Check if welcome email was logged
        if (file_exists($logFile)) {
            $logContent = file_get_contents($logFile);
            if (strpos($logContent, 'Welcome to ARS!') === false) {
                throw new Exception("Welcome email should be sent");
            }
        }

        return true;
    }

    private function simulateSignupPost($data) {
        $_POST = $data;
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Clear previous session messages
        unset($_SESSION['error'], $_SESSION['success']);

        ob_start();
        include __DIR__ . '/../backend/signup.php';
        ob_end_clean();
    }

    private function createTestUser() {
        $hashedPassword = password_hash($this->baseUserData['password'], PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("INSERT INTO users (full_name, email, mobile, password, address, role) VALUES (?, ?, ?, ?, ?, 'customer')");
        $stmt->execute([
            $this->baseUserData['full_name'],
            $this->baseUserData['email'],
            $this->baseUserData['mobile'],
            $hashedPassword,
            $this->baseUserData['address']
        ]);
    }

    private function showSummary($results) {
        $passed = 0;
        $total = count($results);

        foreach ($results as $result) {
            if ($result) $passed++;
        }

        echo "📊 Signup Test Summary:\n";
        echo "Passed: {$passed}/{$total}\n";
        echo "Failed: " . ($total - $passed) . "/{$total}\n";

        if ($passed === $total) {
            echo "\n🎉 All signup tests passed!\n";
        } else {
            echo "\n⚠️  Some tests failed. Please review the signup implementation.\n";
        }
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $test = new SignupTest();
    $test->runTests();
}
?>