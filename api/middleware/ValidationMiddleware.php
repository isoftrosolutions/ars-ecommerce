<?php
/**
 * Validation Middleware
 * Handles input validation and sanitization
 */
class ValidationMiddleware {
    public static $errors = [];

    /**
     * Add validation error
     */
    public static function addError($message) {
        self::$errors[] = $message;
    }

    /**
     * Validate required fields
     */
    public static function validateRequired($data, $fields) {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                self::$errors[] = "Field '{$field}' is required";
            }
        }
    }

    /**
     * Validate email format
     */
    public static function validateEmail($email, $field = 'email') {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            self::$errors[] = "Invalid {$field} format";
        }
    }

    /**
     * Validate numeric value
     */
    public static function validateNumeric($value, $field = 'value', $min = null, $max = null) {
        if (!is_numeric($value)) {
            self::$errors[] = "Field '{$field}' must be numeric";
            return;
        }

        $num = (float)$value;
        if ($min !== null && $num < $min) {
            self::$errors[] = "Field '{$field}' must be at least {$min}";
        }
        if ($max !== null && $num > $max) {
            self::$errors[] = "Field '{$field}' must be at most {$max}";
        }
    }

    /**
     * Validate string length
     */
    public static function validateLength($value, $field = 'value', $min = null, $max = null) {
        $length = strlen(trim($value));

        if ($min !== null && $length < $min) {
            self::$errors[] = "Field '{$field}' must be at least {$min} characters";
        }
        if ($max !== null && $length > $max) {
            self::$errors[] = "Field '{$field}' must be at most {$max} characters";
        }
    }

    /**
     * Validate enum values
     */
    public static function validateEnum($value, $allowed, $field = 'value') {
        if (!in_array($value, $allowed)) {
            self::$errors[] = "Field '{$field}' must be one of: " . implode(', ', $allowed);
        }
    }

    /**
     * Validate date format
     */
    public static function validateDate($date, $field = 'date', $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        if (!$d || $d->format($format) !== $date) {
            self::$errors[] = "Invalid {$field} format (expected {$format})";
        }
    }

    /**
     * Validate URL format
     */
    public static function validateUrl($url, $field = 'url') {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            self::$errors[] = "Invalid {$field} format";
        }
    }

    /**
     * Sanitize string input
     */
    public static function sanitizeString($value) {
        return trim(htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Sanitize array input recursively
     */
    public static function sanitizeArray($data) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } else {
                $sanitized[$key] = self::sanitizeString($value);
            }
        }
        return $sanitized;
    }

    /**
     * Get validation errors
     */
    public static function getErrors() {
        return self::$errors;
    }

    /**
     * Check if validation passed
     */
    public static function isValid() {
        return empty(self::$errors);
    }

    /**
     * Reset validation errors
     */
    public static function reset() {
        self::$errors = [];
    }

    /**
     * Throw validation error if any
     */
    public static function throwIfInvalid() {
        if (!self::isValid()) {
            Response::error('Validation failed: ' . implode(', ', self::getErrors()), 422);
        }
    }
}
?>