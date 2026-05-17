<?php
/**
 * ARS Easy Shopping Mobile API v1 — Browser-based Installer
 *
 * ════════════════════════════════════════════════════════════════
 *  USAGE
 * ════════════════════════════════════════════════════════════════
 *  1. Upload this file to your project root, e.g.:
 *     └── ~/easyshoppingars.com/install.php
 *  2. Visit in your browser:
 *     ── https://easyshoppingars.com/install.php
 *  3. The first visit generates a lock token and saves it in
 *     .install_token.  All subsequent visits require:
 *     ── https://easyshoppingars.com/install.php?token=TOKEN
 *  4. Follow the on-screen steps.  Delete this file when done.
 * ════════════════════════════════════════════════════════════════
 */

// ── Prevent any output before headers ──
ob_start();

// ── Bootstrap ──
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// ── Lock file paths ──
define('INSTALL_TOKEN_FILE', __DIR__ . '/.install_token');
define('INSTALL_LOCK_FILE',  __DIR__ . '/.installed');
define('COMPOSER_PHAR',      __DIR__ . '/composer.phar');
define('PROJECT_ROOT',       __DIR__);

// ──────────────────────────────────────────────────────────────
//  LOCK / TOKEN CHECK
//  Runs before ANY output so we can safely redirect.
// ──────────────────────────────────────────────────────────────

// If the lock file exists, the install is already done.
if (file_exists(INSTALL_LOCK_FILE)) {
    show_error_page('Installation already completed.',
        'The .installed lock file exists.  Delete it if you need to re-run the installer.');
    exit;
}

// First run: generate a token and save it.
$stored_token = '';
if (!file_exists(INSTALL_TOKEN_FILE)) {
    $stored_token = bin2hex(random_bytes(16));
    file_put_contents(INSTALL_TOKEN_FILE, $stored_token, LOCK_EX);
    @chmod(INSTALL_TOKEN_FILE, 0600);
} else {
    $stored_token = trim(file_get_contents(INSTALL_TOKEN_FILE));
}

// On every request (except the very first token generation), verify the token.
$given_token = $_GET['token'] ?? '';

if (file_exists(INSTALL_TOKEN_FILE) && $given_token === '') {
    // Token exists but none provided → tell user.
    show_error_page('Access token required.',
        'This installer is locked.  Visit the URL below with your token:<br>'
        . '<code style="background:#1e293b;padding:4px 8px;border-radius:4px;display:inline-block;margin-top:8px;">'
        . htmlspecialchars(
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
            . ($_SERVER['SCRIPT_NAME'] ?? '/install.php')
            . '?token=' . htmlspecialchars($stored_token)
        )
        . '</code>');
    exit;
}

// Token provided but wrong?
if ($given_token !== '' && $given_token !== $stored_token) {
    show_error_page('Invalid access token.', 'The token you provided does not match.');
    exit;
}

// ── Determine current step ──
$step = isset($_POST['step']) ? (int)$_POST['step']
      : (isset($_GET['step']) ? (int)$_GET['step'] : 1);

$step = max(1, min(9, $step));

// ──────────────────────────────────────────────────────────────
//  Helper functions
// ──────────────────────────────────────────────────────────────

function show_error_page($title, $body) {
    ob_end_clean();
    ?><!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Installer — <?= htmlspecialchars($title) ?></title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:#0f172a;color:#e2e8f0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
display:flex;align-items:center;justify-content:center;min-height:100vh;padding:1rem}
.card{background:#1e293b;border-radius:12px;padding:2rem;max-width:520px;width:100%;box-shadow:0 25px 50px rgba(0,0,0,.5)}
h1{color:#f87171;font-size:1.4rem;margin-bottom:1rem;display:flex;align-items:center;gap:8px}
p{line-height:1.6;color:#94a3b8;font-size:.95rem}
code{font-family:'Cascadia Code','JetBrains Mono','Fira Code',monospace;font-size:.85rem;word-break:break-all}
</style>
</head>
<body><div class="card"><h1>&#x26A0; <?= htmlspecialchars($title) ?></h1><p><?= $body ?></p></div></body></html>
<?php exit;
}

function step_url($s) {
    $token = $_GET['token'] ?? '';
    return '?step=' . (int)$s . ($token ? '&token=' . urlencode($token) : '');
}

function render_header($current_step) {
    $steps = [
        1 => 'System Checks',
        2 => 'Composer',
        3 => 'Install Dependencies',
        4 => 'Environment Setup',
        5 => 'Database Test',
        6 => 'Run Migrations',
        7 => 'Upload Directories',
        8 => 'Smoke Test',
        9 => 'Cleanup',
    ];
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>ARS Installer — Step <?= $current_step ?></title>
    <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{background:#0f172a;color:#e2e8f0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
    padding:1rem;min-height:100vh}
    .container{max-width:800px;margin:2rem auto}
    h1{font-size:1.5rem;font-weight:700;color:#f8fafc;margin-bottom:.25rem}
    .subtitle{color:#64748b;font-size:.85rem;margin-bottom:2rem}
    .step-grid{display:flex;gap:4px;margin-bottom:2rem;flex-wrap:wrap}
    .step-dot{padding:6px 14px;border-radius:20px;font-size:.75rem;font-weight:600;
    background:#1e293b;color:#475569;transition:all .2s}
    .step-dot.active{background:#3b82f6;color:#fff}
    .step-dot.done{background:#166534;color:#86efac}
    .step-dot.fail{background:#7f1d1d;color:#fca5a5}
    .card{background:#1e293b;border-radius:12px;padding:2rem;box-shadow:0 4px 6px rgba(0,0,0,.3);margin-bottom:1.5rem}
    .card h2{font-size:1.1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:8px}
    .result{display:flex;align-items:flex-start;gap:10px;padding:10px 14px;border-radius:8px;margin-bottom:8px;font-size:.9rem}
    .result.pass{background:#052e16;color:#86efac}
    .result.fail{background:#450a0a;color:#fca5a5}
    .result .icon{font-size:1.2rem;flex-shrink:0;margin-top:1px}
    .result .msg{flex:1;word-break:break-word}
    .result .detail{display:block;margin-top:4px;font-family:'Cascadia Code','JetBrains Mono','Fira Code',monospace;
    font-size:.8rem;color:#94a3b8;white-space:pre-wrap;max-height:300px;overflow-y:auto}
    pre.terminal{background:#0f172a;color:#e2e8f0;padding:1rem;border-radius:8px;font-family:'Cascadia Code','JetBrains Mono','Fira Code',monospace;
    font-size:.8rem;line-height:1.5;overflow-x:auto;white-space:pre-wrap;max-height:400px;margin-bottom:1rem;border:1px solid #334155}
    label{display:block;font-size:.85rem;font-weight:600;margin-bottom:4px;color:#94a3b8;margin-top:1rem}
    label:first-child{margin-top:0}
    input[type=text],input[type=password],input[type=url]{width:100%;padding:10px 12px;border-radius:8px;
    border:1px solid #334155;background:#0f172a;color:#e2e8f0;font-size:.9rem;font-family:inherit;outline:none;transition:border .2s}
    input:focus{border-color:#3b82f6}
    .btn{display:inline-flex;align-items:center;gap:6px;padding:10px 24px;border-radius:8px;font-weight:600;
    font-size:.9rem;border:none;cursor:pointer;text-decoration:none;transition:opacity .2s}
    .btn:hover{opacity:.85}
    .btn-primary{background:#3b82f6;color:#fff}
    .btn-danger{background:#dc2626;color:#fff}
    .btn-retry{background:#f59e0b;color:#0f172a}
    .btn-success{background:#16a34a;color:#fff}
    .btn-disabled{background:#334155;color:#64748b;cursor:not-allowed}
    .actions{margin-top:1.5rem;display:flex;gap:10px;flex-wrap:wrap}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
    .form-row .full{grid-column:1/-1}
    .jwt-box{background:#0f172a;border:1px solid #334155;border-radius:8px;padding:1rem;margin-top:1rem}
    .jwt-box .label{font-size:.75rem;color:#64748b;margin-bottom:4px}
    .jwt-box .value{font-family:'Cascadia Code','JetBrains Mono','Fira Code',monospace;font-size:.8rem;color:#e2e8f0;word-break:break-all}
    @media(max-width:600px){.form-row{grid-template-columns:1fr}}
    </style>
    </head>
    <body>
    <div class="container">
        <h1>ARS Easy Shopping — Installer</h1>
        <p class="subtitle">Mobile API v1 &middot; Step <?= $current_step ?> of 9</p>
        <div class="step-grid">
            <?php foreach ($steps as $num => $label): ?>
                <div class="step-dot <?= $num < $current_step ? 'done' : ($num === $current_step ? 'active' : '') ?>">
                    <?= $num ?>. <?= htmlspecialchars($label) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php
}

function render_footer() {
    echo '</div></body></html>';
}

function run_check($label, $ok, $detail = '') {
    $icon  = $ok ? '&#x2714;' : '&#x2718;';
    $class = $ok ? 'pass' : 'fail';
    echo '<div class="result ', $class, '">';
    echo '<span class="icon">', $icon, '</span>';
    echo '<div class="msg">', htmlspecialchars($label);
    if ($detail) {
        echo '<span class="detail">', htmlspecialchars($detail), '</span>';
    }
    echo '</div></div>';
    return $ok;
}

// ──────────────────────────────────────────────────────────────
//  STEP HANDLERS
// ──────────────────────────────────────────────────────────────

function step1_system_checks() {
    $all_ok = true;
    render_header(1);

    echo '<div class="card"><h2>&#x1F6E1; System Requirements</h2>';

    // PHP version
    $php_ok = version_compare(PHP_VERSION, '8.1.0', '>=');
    $all_ok &= run_check('PHP version >= 8.1', $php_ok, PHP_VERSION);

    // Required extensions
    $extensions = ['pdo_mysql', 'mbstring', 'openssl', 'json', 'curl', 'fileinfo'];
    foreach ($extensions as $ext) {
        $loaded = extension_loaded($ext);
        $all_ok &= run_check("Extension: $ext", $loaded);
    }

    // mod_rewrite detection
    $mod_rewrite = false;
    if (function_exists('apache_get_modules')) {
        $mod_rewrite = in_array('mod_rewrite', apache_get_modules());
    } elseif (isset($_SERVER['HTTP_MOD_REWRITE'])) {
        $mod_rewrite = (bool)$_SERVER['HTTP_MOD_REWRITE'];
    } else {
        // Try checking .htaccess via a test
        $test_file = __DIR__ . '/.htaccess';
        $mod_rewrite = file_exists($test_file);
    }
    $all_ok &= run_check('mod_rewrite available', $mod_rewrite);

    // Write permissions
    $writable = is_writable(__DIR__);
    $all_ok &= run_check('Project root writable', $writable, $writable ? '' : 'Check CHMOD on ' . __DIR__);

    // shell_exec available?
    $shell_avail = function_exists('shell_exec') && !in_array('shell_exec', explode(',', ini_get('disable_functions') ?? ''));
    if (!$shell_avail) {
        run_check('shell_exec available', false, 'shell_exec is disabled. Composer steps will be skipped.');
    } else {
        run_check('shell_exec available', true);
    }

    echo '</div>';

    if ($all_ok) {
        echo '<form method="get" action="', step_url(2), '">';
        echo '<button type="submit" class="btn btn-primary">Proceed to Step 2 &rarr;</button>';
        echo '</form>';
    } else {
        echo '<div class="actions">';
        echo '<button onclick="location.reload()" class="btn btn-retry">&#x21BB; Retry Checks</button>';
        echo '</div>';
    }

    render_footer();
}

function step2_composer() {
    render_header(2);
    echo '<div class="card"><h2>&#x1F4E6; Composer Bootstrap</h2>';

    $shell_avail = function_exists('shell_exec') && !in_array('shell_exec', explode(',', ini_get('disable_functions') ?? ''));

    if (!extension_loaded('openssl')) {
        run_check('Download composer.phar', false, 'OpenSSL extension required');
        echo '</div>';
        echo '<div class="actions"><a href="', step_url(2), '" class="btn btn-retry">&#x21BB; Retry</a></div>';
        render_footer();
        return;
    }

    // Download composer.phar
    $download_ok = false;
    $download_msg = '';
    if (file_exists(COMPOSER_PHAR) && filesize(COMPOSER_PHAR) > 10000) {
        $download_ok = true;
        $download_msg = 'Already present (' . number_format(filesize(COMPOSER_PHAR)) . ' bytes)';
    } else {
        $url = 'https://getcomposer.org/composer.phar';
        $data = @file_get_contents($url);
        if ($data === false) {
            // Try copy()
            $copy_ok = @copy($url, COMPOSER_PHAR);
            if ($copy_ok) {
                $data = file_get_contents(COMPOSER_PHAR);
            }
        } else {
            file_put_contents(COMPOSER_PHAR, $data);
        }

        if ($data !== false && strlen($data) > 10000) {
            $download_ok = true;
            $download_msg = 'Downloaded (' . number_format(strlen($data)) . ' bytes)';
        } else {
            $download_msg = 'Download failed or file too small';
        }
    }
    run_check('Download composer.phar', $download_ok, $download_msg);

    // Verify
    $version_ok = false;
    $version_out = '';
    if ($download_ok && $shell_avail) {
        $cmd = 'php ' . escapeshellarg(COMPOSER_PHAR) . ' --version 2>&1';
        $version_out = trim((string)shell_exec($cmd));
        $version_ok = (stripos($version_out, 'Composer version') !== false);
        run_check('Verify composer.phar', $version_ok, $version_out);
    } elseif ($download_ok) {
        run_check('Verify composer.phar', false, 'shell_exec disabled — cannot verify');
    }

    echo '</div>';

    $can_proceed = $download_ok && ($shell_avail ? $version_ok : true);

    if ($can_proceed) {
        echo '<form method="get" action="', step_url(3), '">';
        echo '<button type="submit" class="btn btn-primary">Proceed to Step 3 &rarr;</button>';
        echo '</form>';
    } else {
        echo '<div class="actions">';
        echo '<a href="', step_url(2), '" class="btn btn-retry">&#x21BB; Retry</a>';
        echo '<a href="', step_url(3), '" class="btn btn-primary">Skip (manual vendor/)</a>';
        echo '</div>';
        echo '<p style="color:#94a3b8;font-size:.85rem;margin-top:8px">';
        echo 'If you uploaded vendor/ manually you can skip this step.</p>';
    }

    render_footer();
}

function step3_composer_install() {
    render_header(3);
    echo '<div class="card"><h2>&#x2699; Install Dependencies</h2>';

    $shell_avail = function_exists('shell_exec') && !in_array('shell_exec', explode(',', ini_get('disable_functions') ?? ''));

    if (!file_exists(COMPOSER_PHAR)) {
        run_check('Composer install', false, 'composer.phar not found. Go back to step 2.');
        echo '</div>';
        echo '<a href="', step_url(2), '" class="btn btn-retry">&#x21BB; Back to Step 2</a>';
        render_footer();
        return;
    }

    if (!$shell_avail) {
        run_check('Composer install', false, 'shell_exec is disabled on this server.');
        echo '</div>';
        echo '<p style="color:#94a3b8;font-size:.85rem;margin-top:8px">';
        echo 'Upload vendor/ manually, then <a href="', step_url(4), '" style="color:#3b82f6">skip to step 4</a>.</p>';
        render_footer();
        return;
    }

    $cmd = 'php ' . escapeshellarg(COMPOSER_PHAR)
         . ' install --no-interaction --no-dev --prefer-dist 2>&1';
    $output = shell_exec($cmd);
    $output = trim((string)$output);

    $success = (strpos($output, 'Nothing to install or update') !== false)
            || (strpos($output, 'Generating optimized autoload') !== false)
            || (strpos($output, 'Installed packages') !== false)
            || (strpos($output, 'generated') !== false)
            || (strpos($output, 'Nothing to modify') !== false);

    echo '<pre class="terminal">', htmlspecialchars($output ?: '(no output)'), '</pre>';

    $vendor_exists = is_dir(__DIR__ . '/vendor');
    $autoload_exists = file_exists(__DIR__ . '/vendor/autoload.php');

    $check_ok = $vendor_exists && $autoload_exists;
    if ($success || $check_ok) {
        run_check('Composer install', true,
            $vendor_exists ? 'vendor/autoload.php found' : 'packages installed');
    } else {
        run_check('Composer install', false,
            $output ? substr($output, 0, 500) : 'No output from composer');
    }

    echo '</div>';

    if ($check_ok) {
        echo '<form method="get" action="', step_url(4), '">';
        echo '<button type="submit" class="btn btn-primary">Proceed to Step 4 &rarr;</button>';
        echo '</form>';
    } else {
        echo '<div class="actions">';
        echo '<a href="', step_url(3), '" class="btn btn-retry">&#x21BB; Retry</a>';
        echo '<a href="', step_url(4), '" class="btn btn-primary">Skip (manual vendor/)</a>';
        echo '</div>';
    }

    render_footer();
}

function step4_env_form() {
    render_header(4);

    $submitted = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['env_submit']));

    if ($submitted) {
        $db_host = trim($_POST['db_host'] ?? 'localhost');
        $db_name = trim($_POST['db_name'] ?? '');
        $db_user = trim($_POST['db_user'] ?? '');
        $db_pass = $_POST['db_pass'] ?? '';
        $app_url = trim($_POST['app_url'] ?? '');
        $errors = [];

        if ($db_name === '') $errors[] = 'Database name is required.';
        if ($db_user === '') $errors[] = 'Database user is required.';
        if (!preg_match('#^https?://#', $app_url)) $errors[] = 'App URL must start with http:// or https://';

        if (empty($errors)) {
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
            if ($written !== false) {
                @chmod(PROJECT_ROOT . '/.env', 0600);
                echo '<div class="card">';
                echo '<h2>&#x2705; .env Written Successfully</h2>';
                run_check('.env file created', true, PROJECT_ROOT . '/.env (chmod 600)');
                echo '<p style="color:#94a3b8;font-size:.85rem;margin-top:8px">';
                echo 'JWT secret and database password have been saved and will not be displayed again.</p>';
                echo '</div>';
                echo '<form method="get" action="', step_url(5), '">';
                echo '<input type="hidden" name="token" value="', htmlspecialchars($_GET['token'] ?? ''), '">';
                echo '<button type="submit" class="btn btn-primary">Proceed to Step 5 &rarr;</button>';
                echo '</form>';
                render_footer();
                return;
            } else {
                $errors[] = 'Failed to write .env file. Check permissions.';
            }
        }

        if (!empty($errors)) {
            echo '<div class="card"><h2>&#x274C; Validation Errors</h2>';
            foreach ($errors as $e) {
                run_check($e, false);
            }
            echo '</div>';
        }
    }

    ?>
    <div class="card">
        <h2>&#x1F4CB; Database &amp; Application Configuration</h2>
        <p style="color:#94a3b8;font-size:.85rem;margin-bottom:1rem">
            Enter your database credentials and app URL. A JWT secret will be generated automatically.
        </p>
        <form method="post" action="<?= htmlspecialchars(step_url(4)) ?>">
            <input type="hidden" name="step" value="4">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
            <input type="hidden" name="env_submit" value="1">

            <div class="form-row">
                <div>
                    <label for="db_host">Database Host</label>
                    <input type="text" id="db_host" name="db_host"
                           value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>" required>
                </div>
                <div>
                    <label for="db_name">Database Name</label>
                    <input type="text" id="db_name" name="db_name"
                           value="<?= htmlspecialchars($_POST['db_name'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="db_user">Database User</label>
                    <input type="text" id="db_user" name="db_user"
                           value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>" required>
                </div>
                <div>
                    <label for="db_pass">Database Password</label>
                    <input type="password" id="db_pass" name="db_pass"
                           value="<?= htmlspecialchars($_POST['db_pass'] ?? '') ?>">
                </div>
                <div class="full">
                    <label for="app_url">App Base URL</label>
                    <input type="url" id="app_url" name="app_url"
                           value="<?= htmlspecialchars($_POST['app_url']
                               ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                               . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                               . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\')
                           ) ?>"
                           placeholder="https://easyshoppingars.com" required>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">&#x1F512; Generate &amp; Save</button>
            </div>
        </form>
    </div>
    <?php

    render_footer();
}

function step5_db_test() {
    render_header(5);
    echo '<div class="card"><h2>&#x1F5A5; Database Connection Test</h2>';

    $env_file = PROJECT_ROOT . '/.env';
    if (!file_exists($env_file)) {
        run_check('Database test', false, '.env file not found. Complete step 4 first.');
        echo '</div>';
        echo '<a href="', step_url(4), '" class="btn btn-primary">Go to Step 4</a>';
        render_footer();
        return;
    }

    // Load env vars from .env manually
    $env_vars = parse_env_file($env_file);
    $host = $env_vars['DB_HOST'] ?? 'localhost';
    $name = $env_vars['DB_NAME'] ?? '';
    $user = $env_vars['DB_USER'] ?? '';
    $pass = $env_vars['DB_PASS'] ?? '';

    if (empty($name) || empty($user)) {
        run_check('Database test', false, 'DB_NAME or DB_USER missing in .env');
        echo '</div>';
        echo '<a href="', step_url(4), '" class="btn btn-primary">Back to Step 4</a>';
        render_footer();
        return;
    }

    try {
        $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $stmt = $pdo->query('SELECT VERSION() AS ver');
        $row = $stmt->fetch();
        $version = $row['ver'] ?? 'unknown';
        run_check('Connected to MySQL', true, "Server: $version | Database: $name");
    } catch (PDOException $e) {
        run_check('Connection failed', false, $e->getMessage());
        echo '</div>';
        echo '<div class="actions">';
        echo '<a href="', step_url(4), '" class="btn btn-retry">&#x21BB; Edit Credentials</a>';
        echo '<a href="', step_url(5), '" class="btn btn-retry">&#x21BB; Retry Test</a>';
        echo '</div>';
        render_footer();
        return;
    }

    echo '</div>';
    echo '<form method="get" action="', step_url(6), '">';
    echo '<button type="submit" class="btn btn-primary">Proceed to Step 6 &rarr;</button>';
    echo '</form>';
    render_footer();
}

function step6_migrations() {
    render_header(6);
    echo '<div class="card"><h2>&#x1F4C2; Run Migrations</h2>';

    $migration_file = PROJECT_ROOT . '/api/v1/migrations/001_mobile_api.sql';
    if (!file_exists($migration_file)) {
        run_check('Migration file', false, "Not found: $migration_file");
        echo '</div>';
        echo '<a href="', step_url(6), '" class="btn btn-retry">&#x21BB; Retry</a>';
        render_footer();
        return;
    }

    // Connect
    $env_vars = parse_env_file(PROJECT_ROOT . '/.env');
    $host = $env_vars['DB_HOST'] ?? 'localhost';
    $name = $env_vars['DB_NAME'] ?? '';
    $user = $env_vars['DB_USER'] ?? '';
    $pass = $env_vars['DB_PASS'] ?? '';

    try {
        $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 30,
        ]);
    } catch (PDOException $e) {
        run_check('Database connection', false, $e->getMessage());
        echo '</div>';
        echo '<a href="', step_url(5), '" class="btn btn-primary">Back to Step 5</a>';
        render_footer();
        return;
    }

    $sql = file_get_contents($migration_file);
    $statements = explode(';', $sql);
    $ran = 0;
    $errors = 0;

    foreach ($statements as $i => $stmt) {
        $stmt = trim($stmt);
        if (empty($stmt)) continue;
        // Skip comment-only lines
        if (preg_match('/^--/', $stmt)) continue;

        try {
            $pdo->exec($stmt);
            $ran++;
            $label = '[' . ($ran + $errors) . '] ' . substr(str_replace("\n", ' ', $stmt), 0, 120);
            run_check($label, true);
        } catch (PDOException $e) {
            $code = $e->getCode();
            // 42S01 = "Base table or view already exists" — handle gracefully
            if ($code === '42S01' || stripos($e->getMessage(), 'already exists') !== false) {
                $ran++;
                $label = '[' . ($ran + $errors) . '] ' . substr(str_replace("\n", ' ', $stmt), 0, 120);
                run_check($label, true, '(already exists — skipped)');
            } elseif (stripos($e->getMessage(), 'Duplicate column') !== false
                   || stripos($e->getMessage(), 'Duplicate key') !== false
                   || $code === '42S21') {
                $ran++;
                $label = '[' . ($ran + $errors) . '] ' . substr(str_replace("\n", ' ', $stmt), 0, 120);
                run_check($label, true, '(already applied — skipped)');
            } else {
                $errors++;
                $label = '[' . ($ran + $errors) . '] FAILED: ' . substr(str_replace("\n", ' ', $stmt), 0, 120);
                run_check($label, false, $e->getMessage());
            }
        }
    }

    echo '</div>';

    if ($errors === 0) {
        echo '<form method="get" action="', step_url(7), '">';
        echo '<button type="submit" class="btn btn-primary">Proceed to Step 7 &rarr;</button>';
        echo '</form>';
    } else {
        echo '<div class="actions">';
        echo '<a href="', step_url(6), '" class="btn btn-retry">&#x21BB; Retry Migrations</a>';
        echo '</div>';
    }

    render_footer();
}

function step7_directories() {
    render_header(7);
    echo '<div class="card"><h2>&#x1F4C1; Create Upload Directories</h2>';

    $dirs = ['uploads/banners'];
    $all_ok = true;

    foreach ($dirs as $dir) {
        $path = PROJECT_ROOT . '/' . $dir;
        if (!is_dir($path)) {
            $created = @mkdir($path, 0775, true);
            if ($created) {
                @chmod($path, 0775);
                run_check("Created: $dir", true);
            } else {
                $all_ok = false;
                run_check("Create: $dir", false, 'Check parent directory permissions');
            }
        } else {
            run_check("Exists: $dir", true);
        }
    }

    // Also check uploads/products exists
    $products_dir = PROJECT_ROOT . '/uploads/products';
    if (is_dir($products_dir)) {
        $writable = is_writable($products_dir);
        run_check("uploads/products writable", $writable);
        if (!$writable) $all_ok = false;
    }

    echo '</div>';

    if ($all_ok) {
        echo '<form method="get" action="', step_url(8), '">';
        echo '<button type="submit" class="btn btn-primary">Proceed to Step 8 &rarr;</button>';
        echo '</form>';
    } else {
        echo '<div class="actions">';
        echo '<a href="', step_url(7), '" class="btn btn-retry">&#x21BB; Retry</a>';
        echo '</div>';
    }

    render_footer();
}

function step8_smoke_test() {
    render_header(8);
    echo '<div class="card"><h2>&#x1F52C; Smoke Test</h2>';

    $env_vars = parse_env_file(PROJECT_ROOT . '/.env');
    $app_url = $env_vars['APP_BASE_URL'] ?? $env_vars['APP_URL'] ?? '';

    if (empty($app_url)) {
        $app_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                 . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    $test_url = rtrim($app_url, '/') . '/api/v1/products';

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
        run_check('cURL to /api/v1/products', false, "cURL error: $curl_error");
        echo '</div>';
        echo '<div class="actions">';
        echo '<a href="', step_url(8), '" class="btn btn-retry">&#x21BB; Retry</a>';
        echo '<a href="', step_url(9), '" class="btn btn-primary">Skip to Cleanup</a>';
        echo '</div>';
        render_footer();
        return;
    }

    $decoded = json_decode($response, true);
    $is_json = ($decoded !== null);

    if ($is_json && $http_code < 500) {
        run_check("HTTP $http_code — JSON response received", true,
            'Response preview: ' . substr(htmlspecialchars($response), 0, 300));
    } else {
        run_check("HTTP $http_code", $http_code < 500,
            substr(htmlspecialchars($response), 0, 500));
    }

    echo '</div>';

    echo '<div class="actions">';
    echo '<a href="', step_url(8), '" class="btn btn-retry">&#x21BB; Retry</a>';
    echo '<a href="', step_url(9), '" class="btn btn-primary">Proceed to Cleanup &rarr;</a>';
    echo '</div>';

    render_footer();
}

function step9_cleanup() {
    render_header(9);
    echo '<div class="card"><h2>&#x1F9F9; Cleanup &amp; Finish</h2>';

    $delete_requested = isset($_POST['delete_installer']);

    if ($delete_requested) {
        $deleted = [];
        $failed = [];

        $targets = [__FILE__];
        if (file_exists(COMPOSER_PHAR)) {
            $targets[] = COMPOSER_PHAR;
        }

        foreach ($targets as $file) {
            if (@unlink($file)) {
                $deleted[] = basename($file);
            } else {
                $failed[] = basename($file);
            }
        }

        foreach ($deleted as $name) {
            run_check("Deleted: $name", true);
        }
        foreach ($failed as $name) {
            run_check("Delete: $name", false, 'Could not remove — delete manually');
        }

        // Write .installed lock file
        $lock_content = "Installed: " . date('Y-m-d H:i:s') . "\n"
                      . "PHP: " . PHP_VERSION . "\n"
                      . "URL: " . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'unknown') . "\n";
        file_put_contents(INSTALL_LOCK_FILE, $lock_content, LOCK_EX);
        @chmod(INSTALL_LOCK_FILE, 0600);

        if (empty($failed)) {
            run_check('Installation complete!', true, 'Installer files removed. Lock file created.');
            echo '<p style="color:#86efac;font-size:1rem;margin-top:1rem">';
            echo '&#x2705; ARS Easy Shopping Mobile API v1 is ready. '
               . 'Visit your site to verify.</p>';
        }

        echo '</div>';
        render_footer();
        return;
    }

    // Write .installed if they haven't deleted but want to finish
    $lock_content = "Installed: " . date('Y-m-d H:i:s') . "\n"
                  . "PHP: " . PHP_VERSION . "\n";
    file_put_contents(INSTALL_LOCK_FILE, $lock_content, LOCK_EX);
    @chmod(INSTALL_LOCK_FILE, 0600);

    echo '<p style="color:#94a3b8;font-size:.85rem;margin-bottom:1.5rem">';
    echo 'All setup steps are complete. Remove the installer files from the server.</p>';

    ?>
    <form method="post" action="<?= htmlspecialchars(step_url(9)) ?>"
          onsubmit="return confirm('Delete installer files? This cannot be undone.');">
        <input type="hidden" name="step" value="9">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
        <input type="hidden" name="delete_installer" value="1">
        <button type="submit" class="btn btn-danger" style="width:100%;padding:14px;font-size:1.1rem;justify-content:center">
            &#x1F5D1; Delete Installer Files
        </button>
    </form>
    <p style="color:#64748b;font-size:.8rem;margin-top:1rem;text-align:center">
        Removes install.php and composer.phar from the server.
        A .installed lock file is created to prevent re-running.
    </p>
    <?php

    echo '</div>';
    render_footer();
}

// ──────────────────────────────────────────────────────────────
//  Utility: parse .env files
// ──────────────────────────────────────────────────────────────

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
//  ROUTE TO CURRENT STEP
// ──────────────────────────────────────────────────────────────

switch ($step) {
    case 1:  step1_system_checks();  break;
    case 2:  step2_composer();       break;
    case 3:  step3_composer_install(); break;
    case 4:  step4_env_form();       break;
    case 5:  step5_db_test();        break;
    case 6:  step6_migrations();     break;
    case 7:  step7_directories();    break;
    case 8:  step8_smoke_test();     break;
    case 9:  step9_cleanup();        break;
    default: step1_system_checks();  break;
}

ob_end_flush();
