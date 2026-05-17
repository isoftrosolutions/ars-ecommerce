<?php
/**
 * ARS Easy Shopping Mobile API v1 — CLI Installer
 *
 * ════════════════════════════════════════════════════════════════
 *  USAGE
 * ════════════════════════════════════════════════════════════════
 *  php install.php             Run the full installation wizard
 *  php install.php --step N    Jump to a specific step (1-9)
 *  php install.php --help      Show this help
 * ════════════════════════════════════════════════════════════════
 */

// ── Bootstrap ──
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('INSTALL_LOCK_FILE', __DIR__ . '/.installed');
define('COMPOSER_PHAR',     __DIR__ . '/composer.phar');
define('PROJECT_ROOT',      __DIR__);
define('MIGRATION_FILE',    __DIR__ . '/api/v1/migrations/001_mobile_api.sql');

// ── ANSI color codes ──
define('C_GREEN',  "\033[32m");
define('C_RED',    "\033[31m");
define('C_YELLOW', "\033[33m");
define('C_CYAN',   "\033[36m");
define('C_BOLD',   "\033[1m");
define('C_DIM',    "\033[2m");
define('C_RESET',  "\033[0m");
define('C_CLEAR',  "\033[2J\033[H");

// ── Lock check ──
if (file_exists(INSTALL_LOCK_FILE)) {
    echo C_RED . " ⚠ Installation already completed.\n"
       . "   Delete .installed to re-run." . C_RESET . "\n";
    exit(1);
}

// ── Parse args ──
$start_step = 1;
$args = array_slice($argv ?? [], 1);
foreach ($args as $i => $a) {
    if ($a === '--help') {
        echo file_get_contents(__FILE__);
        exit;
    }
    if ($a === '--step' && isset($args[$i + 1])) {
        $start_step = max(1, min(9, (int)$args[$i + 1]));
    }
}

// ──────────────────────────────────────────────────────────────
//  Helper functions
// ──────────────────────────────────────────────────────────────

function heading($num, $title) {
    echo "\n" . C_CYAN . str_repeat('═', 56) . "\n"
       . "  STEP {$num}  {$title}\n"
       . str_repeat('═', 56) . C_RESET . "\n\n";
}

function pass($label, $detail = '') {
    $d = $detail ? C_DIM . " — {$detail}" . C_RESET : '';
    echo "  " . C_GREEN . "✔" . C_RESET . "  {$label}{$d}\n";
}

function fail($label, $detail = '') {
    $d = $detail ? "\n       " . C_DIM . "{$detail}" . C_RESET : '';
    echo "  " . C_RED . "✘" . C_RESET . "  {$label}{$d}\n";
}

function warn($label, $detail = '') {
    $d = $detail ? C_DIM . " — {$detail}" . C_RESET : '';
    echo "  " . C_YELLOW . "!" . C_RESET . "  {$label}{$d}\n";
}

function prompt($label, $default = '') {
    $def = $default ? " [{$default}]" : '';
    echo C_BOLD . "  {$label}{$def}: " . C_RESET;
    $input = trim(fgets(STDIN));
    return $input !== '' ? $input : $default;
}

function prompt_silent($label) {
    echo C_BOLD . "  {$label}: " . C_RESET;
    // Read password without echo (best-effort on *nix)
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        system('stty -echo');
    }
    $input = trim(fgets(STDIN));
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        system('stty echo');
    }
    echo "\n";
    return $input;
}

function console_yes_no($label, $default_yes = true) {
    $yn = $default_yes ? 'Y/n' : 'y/N';
    echo C_BOLD . "  {$label} [{$yn}]: " . C_RESET;
    $input = strtolower(trim(fgets(STDIN)));
    if ($input === '') return $default_yes;
    return in_array($input, ['y', 'yes']);
}

function pause() {
    echo "\n  " . C_DIM . "Press Enter to continue..." . C_RESET;
    fgets(STDIN);
}

function parse_env_file($path) {
    $vars = [];
    if (!file_exists($path)) return $vars;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        if (preg_match('/^"(.*)"$/', $value, $m)) $value = $m[1];
        elseif (preg_match("/^'(.*)'$/", $value, $m)) $value = $m[1];
        $vars[$key] = $value;
    }
    return $vars;
}

// ──────────────────────────────────────────────────────────────
//  STEP 1 — System Checks
// ──────────────────────────────────────────────────────────────

function step1() {
    heading(1, 'System Requirements');

    $all_ok = true;

    // PHP version
    $php_ok = version_compare(PHP_VERSION, '8.1.0', '>=');
    $php_ok ? pass("PHP version ≥ 8.1", PHP_VERSION)
            : fail("PHP version ≥ 8.1", "Got " . PHP_VERSION);
    $all_ok = $all_ok && $php_ok;

    // Extensions
    $extensions = ['pdo_mysql', 'mbstring', 'openssl', 'json', 'curl', 'fileinfo'];
    foreach ($extensions as $ext) {
        $loaded = extension_loaded($ext);
        $loaded ? pass("Extension: {$ext}")
                : fail("Extension: {$ext}");
        $all_ok = $all_ok && $loaded;
    }

    // mod_rewrite
    $mod_rewrite = false;
    if (function_exists('apache_get_modules')) {
        $mod_rewrite = in_array('mod_rewrite', apache_get_modules());
    } else {
        $mod_rewrite = file_exists(PROJECT_ROOT . '/.htaccess');
    }
    $mod_rewrite ? pass("mod_rewrite available")
                 : warn("mod_rewrite not detected", "Skipping — may still work");
    $all_ok = $all_ok && $mod_rewrite;

    // Write permission
    $writable = is_writable(PROJECT_ROOT);
    $writable ? pass("Project root writable")
              : fail("Project root writable", "chmod " . PROJECT_ROOT);
    $all_ok = $all_ok && $writable;

    // shell_exec
    $shell_avail = function_exists('shell_exec')
                && !in_array('shell_exec', explode(',', ini_get('disable_functions') ?? ''));
    $shell_avail ? pass("shell_exec available")
                 : warn("shell_exec disabled", "Composer steps skipped, use manual vendor/");

    if (!$all_ok) {
        echo "\n" . C_RED . "  ✘ Some checks failed. Fix issues and re-run." . C_RESET . "\n";
        exit(1);
    }

    pass("All system checks passed");
    return true;
}

// ──────────────────────────────────────────────────────────────
//  STEP 2 — Download Composer
// ──────────────────────────────────────────────────────────────

function step2() {
    heading(2, 'Composer Bootstrap');

    if (!extension_loaded('openssl')) {
        fail("Download composer.phar", "OpenSSL extension required");
        return false;
    }

    if (file_exists(COMPOSER_PHAR) && filesize(COMPOSER_PHAR) > 10000) {
        pass("composer.phar already present", number_format(filesize(COMPOSER_PHAR)) . ' bytes');
        $skip = console_yes_no("Re-download?");
        if (!$skip) return true;
    }

    echo "\n  " . C_DIM . "Downloading from https://getcomposer.org/composer.phar ..." . C_RESET . "\n";

    $url = 'https://getcomposer.org/composer.phar';
    $data = false;

    if (ini_get('allow_url_fopen')) {
        $data = @file_get_contents($url);
        if ($data !== false) echo "  → file_get_contents: OK\n";
    }

    if ($data === false && function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $data = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($data !== false) echo "  → cURL: OK\n";
        else echo "  → cURL: {$err}\n";
    }

    if ($data === false) {
        $copy_ok = @copy($url, COMPOSER_PHAR);
        if ($copy_ok) {
            $data = @file_get_contents(COMPOSER_PHAR);
            echo "  → copy(): OK\n";
        } else {
            echo "  → copy(): Failed\n";
        }
    } else {
        file_put_contents(COMPOSER_PHAR, $data);
    }

    if ($data === false || strlen($data) < 10000) {
        fail("Download composer.phar", "File too small or failed");
        return false;
    }

    pass("Downloaded composer.phar", number_format(strlen($data)) . ' bytes');

    // Verify
    $shell_avail = function_exists('shell_exec')
                && !in_array('shell_exec', explode(',', ini_get('disable_functions') ?? ''));
    if ($shell_avail) {
        $out = trim((string)shell_exec('php ' . escapeshellarg(COMPOSER_PHAR) . ' --version 2>&1'));
        if (stripos($out, 'Composer version') !== false) {
            pass("Composer verified", $out);
        } else {
            fail("Composer verify", $out);
            return false;
        }
    }

    return true;
}

// ──────────────────────────────────────────────────────────────
//  STEP 3 — Composer Install
// ──────────────────────────────────────────────────────────────

function step3() {
    heading(3, 'Install Dependencies');

    if (!file_exists(COMPOSER_PHAR)) {
        fail("composer.phar not found", "Run step 2 first or upload manually");
        return false;
    }

    $shell_avail = function_exists('shell_exec')
                && !in_array('shell_exec', explode(',', ini_get('disable_functions') ?? ''));
    if (!$shell_avail) {
        fail("shell_exec disabled", "Upload vendor/ manually and skip this step");
        return false;
    }

    echo "\n" . C_DIM . "Running composer install (this may take a while)..." . C_RESET . "\n\n";

    $cmd = 'php ' . escapeshellarg(COMPOSER_PHAR)
         . ' install --no-interaction --no-dev --prefer-dist 2>&1';
    $output = shell_exec($cmd);
    echo "  " . str_replace("\n", "\n  ", trim((string)$output)) . "\n";

    $vendor_ok = is_dir(PROJECT_ROOT . '/vendor')
              && file_exists(PROJECT_ROOT . '/vendor/autoload.php');

    $vendor_ok ? pass("Dependencies installed", "vendor/autoload.php found")
               : fail("Dependencies installed", "vendor/ directory missing or incomplete");

    return $vendor_ok;
}

// ──────────────────────────────────────────────────────────────
//  STEP 4 — .env Setup
// ──────────────────────────────────────────────────────────────

function step4() {
    heading(4, 'Environment Configuration');

    // Detect defaults
    $default_host = 'localhost';
    $default_url  = 'https://easyshoppingars.com';

    // Read existing .env if present
    $existing = [];
    if (file_exists(PROJECT_ROOT . '/.env')) {
        $existing = parse_env_file(PROJECT_ROOT . '/.env');
        warn("Existing .env found");
        if (!console_yes_no("Overwrite?")) {
            pass("Using existing .env");
            return true;
        }
    }

    echo "\n" . C_DIM . "Enter database credentials and app URL." . C_RESET . "\n";
    echo C_DIM . "A JWT secret will be generated automatically." . C_RESET . "\n\n";

    $db_host = prompt("Database Host", $existing['DB_HOST'] ?? $default_host);
    $db_name = prompt("Database Name", $existing['DB_NAME'] ?? '');
    $db_user = prompt("Database User", $existing['DB_USER'] ?? '');
    $db_pass = prompt_silent("Database Password");
    $app_url = prompt("App Base URL", $existing['APP_BASE_URL'] ?? $existing['APP_URL'] ?? $default_url);

    // Validate
    $errors = [];
    if ($db_name === '') $errors[] = 'Database name is required.';
    if ($db_user === '') $errors[] = 'Database user is required.';
    if (!preg_match('#^https?://#', $app_url)) $errors[] = 'App URL must start with http:// or https://';

    if (!empty($errors)) {
        echo "\n";
        foreach ($errors as $e) fail($e);
        return false;
    }

    $jwt_secret = bin2hex(random_bytes(32));
    $env_content = "# ARS eCommerce — Environment Configuration\n"
                 . "# Generated by install.php\n"
                 . "APP_ENV=production\n"
                 . "APP_DEBUG=false\n"
                 . "APP_URL={$app_url}\n"
                 . "APP_KEY=ars_k3y_" . bin2hex(random_bytes(8)) . "\n"
                 . "DB_HOST={$db_host}\n"
                 . "DB_NAME={$db_name}\n"
                 . "DB_USER={$db_user}\n"
                 . "DB_PASS={$db_pass}\n"
                 . "DB_CHARSET=utf8mb4\n"
                 . "SESSION_LIFETIME=2592000\n"
                 . "SESSION_SECURE=true\n"
                 . "JWT_SECRET={$jwt_secret}\n"
                 . "JWT_EXPIRY_DAYS=7\n"
                 . "API_DEBUG=false\n"
                 . "APP_BASE_URL={$app_url}\n";

    $written = file_put_contents(PROJECT_ROOT . '/.env', $env_content, LOCK_EX);
    if ($written === false) {
        fail("Write .env", "Check permissions on " . PROJECT_ROOT);
        return false;
    }

    @chmod(PROJECT_ROOT . '/.env', 0600);
    pass(".env written", PROJECT_ROOT . '/.env (chmod 600)');
    warn("JWT secret and DB password saved — not displayed for security");

    return true;
}

// ──────────────────────────────────────────────────────────────
//  STEP 5 — Database Test
// ──────────────────────────────────────────────────────────────

function step5() {
    heading(5, 'Database Connection Test');

    if (!file_exists(PROJECT_ROOT . '/.env')) {
        fail(".env not found", "Complete step 4 first");
        return false;
    }

    $env = parse_env_file(PROJECT_ROOT . '/.env');
    $host = $env['DB_HOST'] ?? 'localhost';
    $name = $env['DB_NAME'] ?? '';
    $user = $env['DB_USER'] ?? '';
    $pass = $env['DB_PASS'] ?? '';

    if (empty($name) || empty($user)) {
        fail("DB_NAME or DB_USER missing in .env");
        return false;
    }

    try {
        $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $stmt = $pdo->query('SELECT VERSION() AS ver');
        $row  = $stmt->fetch();
        $ver  = $row['ver'] ?? 'unknown';
        pass("Connected to MySQL", "Server: {$ver} | Database: {$name}");
    } catch (PDOException $e) {
        fail("Connection failed", $e->getMessage());
        return false;
    }

    return true;
}

// ──────────────────────────────────────────────────────────────
//  STEP 6 — Run Migrations
// ──────────────────────────────────────────────────────────────

function step6() {
    heading(6, 'Run Migrations');

    if (!file_exists(MIGRATION_FILE)) {
        fail("Migration file not found", MIGRATION_FILE);
        return false;
    }

    // Connect
    $env = parse_env_file(PROJECT_ROOT . '/.env');
    $host = $env['DB_HOST'] ?? 'localhost';
    $name = $env['DB_NAME'] ?? '';
    $user = $env['DB_USER'] ?? '';
    $pass = $env['DB_PASS'] ?? '';

    try {
        $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 30,
        ]);
    } catch (PDOException $e) {
        fail("Database connection", $e->getMessage());
        return false;
    }

    $sql = file_get_contents(MIGRATION_FILE);
    $statements = explode(';', $sql);
    $ran   = 0;
    $errs  = 0;

    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (empty($stmt) || preg_match('/^--/', $stmt)) continue;

        $label = substr(str_replace("\n", ' ', $stmt), 0, 100);

        try {
            $pdo->exec($stmt);
            $ran++;
            pass("[{$ran}] {$label}");
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            if ($e->getCode() === '42S01'
                || stripos($msg, 'already exists') !== false
                || stripos($msg, 'Duplicate column') !== false
                || stripos($msg, 'Duplicate key') !== false) {
                $ran++;
                warn("[{$ran}] {$label}", "skipped (already applied)");
            } else {
                $errs++;
                fail("[FAIL] {$label}", $msg);
            }
        }
    }

    if ($errs > 0) {
        echo "\n";
        fail("Migrations completed with {$errs} error(s)");
        return false;
    }

    echo "\n";
    pass("All migrations applied successfully", "{$ran} statements executed");
    return true;
}

// ──────────────────────────────────────────────────────────────
//  STEP 7 — Create Upload Directories
// ──────────────────────────────────────────────────────────────

function step7() {
    heading(7, 'Upload Directories');

    $dirs = ['uploads/banners'];
    $all_ok = true;

    foreach ($dirs as $dir) {
        $path = PROJECT_ROOT . '/' . $dir;
        if (!is_dir($path)) {
            $created = @mkdir($path, 0775, true);
            if ($created) {
                @chmod($path, 0775);
                pass("Created: {$dir}");
            } else {
                $all_ok = false;
                fail("Create: {$dir}", "Check parent directory permissions");
            }
        } else {
            pass("Exists: {$dir}");
        }
    }

    $products_dir = PROJECT_ROOT . '/uploads/products';
    if (is_dir($products_dir)) {
        is_writable($products_dir) ? pass("uploads/products writable")
                                   : warn("uploads/products not writable");
    }

    return $all_ok;
}

// ──────────────────────────────────────────────────────────────
//  STEP 8 — Smoke Test
// ──────────────────────────────────────────────────────────────

function step8() {
    heading(8, 'Smoke Test');

    $env = parse_env_file(PROJECT_ROOT . '/.env');
    $app_url = $env['APP_BASE_URL'] ?? $env['APP_URL'] ?? '';
    if (empty($app_url)) {
        fail("APP_BASE_URL not set", "Complete step 4 first");
        return false;
    }

    $test_url = rtrim($app_url, '/') . '/api/v1/products';
    echo "  " . C_DIM . "GET {$test_url}" . C_RESET . "\n\n";

    $ch = curl_init($test_url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        fail("cURL request failed", $curl_error);
        return false;
    }

    $decoded = json_decode($response, true);
    $is_json = ($decoded !== null);

    if ($is_json && $http_code < 500) {
        $preview = substr(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 0, 500);
        pass("HTTP {$http_code} — Valid JSON response");
        echo C_DIM . "{$preview}" . C_RESET . "\n";
    } else {
        warn("HTTP {$http_code}", "Expected JSON, got: " . substr($response, 0, 200));
        if (!console_yes_no("Continue anyway?")) return false;
    }

    return true;
}

// ──────────────────────────────────────────────────────────────
//  STEP 9 — Finish & Cleanup
// ──────────────────────────────────────────────────────────────

function step9() {
    heading(9, 'Finish & Cleanup');

    pass("All setup steps completed");
    echo "\n";

    if (console_yes_no("Delete installer files (install.php and composer.phar)?")) {
        $targets = [__FILE__];
        if (file_exists(COMPOSER_PHAR)) $targets[] = COMPOSER_PHAR;

        $failed = [];
        foreach ($targets as $f) {
            if (!@unlink($f)) $failed[] = basename($f);
        }

        if (empty($failed)) {
            pass("Installer files removed");
        } else {
            foreach ($failed as $f) warn("Could not delete {$f}", "Remove manually");
        }
    } else {
        warn("Skipped deletion", "Delete install.php and composer.phar manually");
    }

    $lock_content = "Installed: " . date('Y-m-d H:i:s') . "\n"
                  . "PHP: " . PHP_VERSION . "\n";
    file_put_contents(INSTALL_LOCK_FILE, $lock_content, LOCK_EX);
    @chmod(INSTALL_LOCK_FILE, 0600);
    pass("Lock file created", ".installed");

    echo "\n" . C_GREEN . C_BOLD . "  ✔ ARS Easy Shopping Mobile API v1 is ready!" . C_RESET . "\n";
}

// ──────────────────────────────────────────────────────────────
//  MAIN
// ──────────────────────────────────────────────────────────────

echo C_CLEAR;
echo C_BOLD . C_CYAN . "  ┌──────────────────────────────────────┐\n"
   . "  │  ARS Easy Shopping — Mobile API v1  │\n"
   . "  │        CLI Installer v2.0            │\n"
   . "  └──────────────────────────────────────┘" . C_RESET . "\n";

$steps = [
    1 => ['System Checks',        'step1'],
    2 => ['Composer Bootstrap',   'step2'],
    3 => ['Install Dependencies', 'step3'],
    4 => ['Environment Setup',    'step4'],
    5 => ['Database Test',        'step5'],
    6 => ['Run Migrations',       'step6'],
    7 => ['Upload Directories',   'step7'],
    8 => ['Smoke Test',           'step8'],
    9 => ['Finish & Cleanup',     'step9'],
];

for ($s = $start_step; $s <= 9; $s++) {
    list($label, $func) = $steps[$s];
    echo "\n" . C_DIM . "  ── Step {$s}/9: {$label} ──" . C_RESET . "\n";

    $ok = $func();

    if (!$ok) {
        echo "\n" . C_RED . "  ✘ Step {$s} failed." . C_RESET . "\n";
        if (console_yes_no("Retry step {$s}?")) {
            $s--;
            continue;
        }
        if (console_yes_no("Skip step {$s} and continue?")) {
            continue;
        }
        echo C_RED . "\n  Installation aborted at step {$s}.\n" . C_RESET . "\n";
        exit(1);
    }

    if ($s < 9) pause();
}

echo "\n" . C_GREEN . C_BOLD . "  ── DONE ──" . C_RESET . "\n";
