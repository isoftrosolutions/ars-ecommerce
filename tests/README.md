# ARS Authentication Tests

This directory contains comprehensive test suites for the ARS e-commerce authentication system.

## Test Files

### `AuthTest.php`
- Comprehensive authentication tests covering login, signup, OTP, and password reset
- Tests all major authentication flows end-to-end

### `SignupTest.php`
- Detailed signup functionality tests
- Tests validation, duplicate prevention, email verification, and user creation

### `LoginTest.php`
- Comprehensive login functionality tests
- Tests various login methods, session management, remember me, and logout

### `run-tests.php`
- Test runner script that executes all test suites
- Provides overall test results and summary

## Running Tests

### Prerequisites
- PHP 7.4+ or 8.x
- MySQL/MariaDB database
- ARS project database schema imported
- Web server (Apache/Nginx) running

### Setup
1. Ensure the `ars_ecommerce` database is created and populated
2. Update database credentials in `includes/db.php` if needed
3. Make sure the `logs/` directory exists and is writable

### Execute Tests

#### Run All Tests
```bash
php tests/run-tests.php
```

#### Run Individual Test Suites
```bash
# Comprehensive authentication tests
php tests/AuthTest.php

# Signup-specific tests
php tests/SignupTest.php

# Login-specific tests
php tests/LoginTest.php
```

## Test Coverage

### Authentication Tests (`AuthTest.php`)
- ✅ Signup validation (required fields, email format, password strength)
- ✅ Signup success and user creation
- ✅ Login validation and error handling
- ✅ Login success with email and mobile
- ✅ OTP login flow (send/verify)
- ✅ Forgot password flow
- ✅ Remember me functionality

### Signup Tests (`SignupTest.php`)
- ✅ Required field validation
- ✅ Email format validation
- ✅ Mobile number validation (Nepal format)
- ✅ Password requirements and confirmation
- ✅ Duplicate email prevention
- ✅ Duplicate mobile prevention
- ✅ Email verification requirement
- ✅ Successful registration
- ✅ Welcome email sending

### Login Tests (`LoginTest.php`)
- ✅ Required field validation
- ✅ Invalid credentials handling
- ✅ Email-based login
- ✅ Mobile-based login
- ✅ Case sensitivity handling
- ✅ Session management
- ✅ Admin user login
- ✅ Remember me functionality
- ✅ OTP login flow
- ✅ Logout functionality

## Test Database

Tests create temporary test users and clean them up automatically. Test data includes:
- Email: `test@example.com`, `signup-test@example.com`, `login-test@example.com`
- Mobile: Various Nepal format numbers (98XXXXXXXX, 97XXXXXXXX)
- Password: Test passwords that meet requirements

## Expected Output

Successful test run:
```
🚀 ARS Authentication Test Suite
================================

Setting up test environment...
✅ Database connection established

Running Comprehensive Authentication Tests...
--------------------------------
🧪 Running Authentication Tests...

Running: Signup Validation Tests
✅ PASSED

[... more test results ...]

📊 Test Summary:
Passed: 7/7
Failed: 0/7

🎉 All tests passed!

[... similar for other test suites ...]

📊 Overall Test Results:
========================
AuthTest.php: ✅ PASSED
SignupTest.php: ✅ PASSED
LoginTest.php: ✅ PASSED

Total: 3/3 test suites passed

🎉 All authentication tests passed! The system is working correctly.
```

## Troubleshooting

### Database Connection Issues
- Verify database credentials in `includes/db.php`
- Ensure MySQL/MariaDB is running
- Check database `ars_ecommerce` exists

### Permission Issues
- Ensure `logs/` directory is writable
- Check PHP has write permissions for log files

### Test Failures
- Review error messages for specific issues
- Check database schema matches expectations
- Verify all required files exist and are accessible

## Adding New Tests

To add new test methods:
1. Add the method to the appropriate test class
2. Include it in the `$tests` array in `runTests()` or `runAllTests()`
3. Follow the existing pattern of throwing exceptions for failures
4. Update this README with new test descriptions