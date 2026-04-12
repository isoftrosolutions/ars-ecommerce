<?php
/**
 * Environment Configuration Loader
 * Easy Shopping A.R.S eCommerce Platform
 * 
 * Loads .env file and provides env() helper function.
 * Must be included before any other configuration.
 */

/**
 * Load .env file into $_ENV and putenv()
 */
function load_env($path = null) {
    $envFile = $path ?: dirname(__DIR__) . '/.env';
    
    if (!file_exists($envFile)) {
        // In production, env vars should be set by the server
        return false;
    }
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments
        $line = trim($line);
        if (empty($line) || $line[0] === '#') {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') === false) {
            continue;
        }
        
        list($key, $value) = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        
        // Remove surrounding quotes
        if (preg_match('/^"(.*)"$/', $value, $m)) {
            $value = $m[1];
        } elseif (preg_match("/^'(.*)'$/", $value, $m)) {
            $value = $m[1];
        }
        
        // Convert boolean-ish strings
        $lower = strtolower($value);
        if ($lower === 'true')  $value = '1';
        if ($lower === 'false') $value = '0';
        if ($lower === 'null')  $value = '';
        
        // Set in all available superglobals
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv("$key=$value");
    }
    
    return true;
}

/**
 * Get environment variable with optional default
 * 
 * @param string $key     Environment variable name
 * @param mixed  $default Default value if not set
 * @return mixed
 */
function env($key, $default = null) {
    $value = getenv($key);
    
    if ($value === false) {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;
    }
    
    if ($value === null) {
        return $default;
    }
    
    return $value;
}

// Auto-load on include
load_env();
?>
