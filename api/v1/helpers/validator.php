<?php
/**
 * Validation Helpers
 * Input validation and sanitization for the mobile API.
 */

if (!function_exists('json_error')) {
    function json_error($msg, $code) {
        http_response_code($code);
        echo json_encode(['success' => false, 'message' => $msg]);
        exit;
    }
}

class ValidationErrors
{
    private static $errors = [];

    public static function add($field, $message)
    {
        if (!isset(self::$errors[$field])) {
            self::$errors[$field] = [];
        }
        self::$errors[$field][] = $message;
    }

    public static function hasErrors()
    {
        return !empty(self::$errors);
    }

    public static function getErrors()
    {
        return self::$errors;
    }

    public static function reset()
    {
        self::$errors = [];
    }

    public static function throwIfInvalid()
    {
        if (self::hasErrors()) {
            json_error('Validation failed', 422, self::getErrors());
        }
    }
}

function validate_required($data, $fields)
{
    foreach ($fields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            ValidationErrors::add($field, "The {$field} field is required");
        }
    }
}

function validate_email($value, $field = 'email')
{
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        ValidationErrors::add($field, 'Invalid email format');
    }
}

function validate_phone($value, $field = 'phone')
{
    $cleaned = preg_replace('/[^0-9]/', '', $value);
    if (strlen($cleaned) !== 10 || $cleaned[0] !== '9') {
        ValidationErrors::add($field, 'Phone must be exactly 10 digits starting with 9');
    }
}

function validate_min_length($value, $field, $min)
{
    if (strlen(trim($value)) < $min) {
        ValidationErrors::add($field, "The {$field} must be at least {$min} characters");
    }
}

function validate_max_length($value, $field, $max)
{
    if (strlen(trim($value)) > $max) {
        ValidationErrors::add($field, "The {$field} must be at most {$max} characters");
    }
}

function validate_numeric($value, $field = 'value', $min = null, $max = null)
{
    if (!is_numeric($value)) {
        ValidationErrors::add($field, "The {$field} must be numeric");
        return;
    }
    $num = (float)$value;
    if ($min !== null && $num < $min) {
        ValidationErrors::add($field, "The {$field} must be at least {$min}");
    }
    if ($max !== null && $num > $max) {
        ValidationErrors::add($field, "The {$field} must be at most {$max}");
    }
}

function validate_enum($value, $allowed, $field = 'value')
{
    if (!in_array($value, $allowed, true)) {
        ValidationErrors::add($field, "The {$field} must be one of: " . implode(', ', $allowed));
    }
}

function check_rate_limit($action, $identifier)
{
    global $pdo;
    $windowStart = date('Y-m-d H:i:s', time() - 900); // 15 minutes
    $maxAttempts = 5;

    // Clean old entries
    $pdo->prepare("DELETE FROM rate_limits WHERE window_start < ?")->execute([$windowStart]);

    // Count attempts in window
    $stmt = $pdo->prepare(
        "SELECT SUM(attempts) as total FROM rate_limits WHERE identifier = ? AND action = ? AND window_start >= ?"
    );
    $stmt->execute([$identifier, $action, $windowStart]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && (int)$row['total'] >= $maxAttempts) {
        json_error('Too many attempts. Please try again later.', 429);
    }

    // Record attempt
    $stmt = $pdo->prepare(
        "INSERT INTO rate_limits (identifier, action, attempts, window_start) VALUES (?, ?, 1, NOW())"
    );
    $stmt->execute([$identifier, $action]);
}

function sanitize_string($value)
{
    return trim(htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8'));
}

function sanitize_array($data)
{
    $sanitized = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $sanitized[$key] = sanitize_array($value);
        } elseif (is_bool($value)) {
            $sanitized[$key] = $value;
        } elseif (is_numeric($value)) {
            $sanitized[$key] = $value;
        } else {
            $sanitized[$key] = sanitize_string($value);
        }
    }
    return $sanitized;
}

function get_json_input()
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $data = $_POST;
    }
    return sanitize_array($data ?: []);
}
