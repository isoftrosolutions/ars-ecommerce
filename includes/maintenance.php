<?php
/**
 * Maintenance Mode Handler
 * Easy Shopping A.R.S eCommerce Platform
 * 
 * Checks if maintenance mode is active and shows a branded maintenance page.
 * Include this file at the top of index.php (after db.php) to enable.
 * 
 * To activate: Create a file named 'maintenance.flag' in the project root
 * To deactivate: Delete the 'maintenance.flag' file
 * 
 * Admins can bypass maintenance mode by being logged in as admin.
 */

function check_maintenance_mode() {
    global $pdo;
    
    // Check database setting (cached if possible, but simple query for now)
    try {
        $stmt = $pdo->prepare("SELECT value FROM site_settings WHERE `key` = 'maintenance_mode'");
        $stmt->execute();
        $isMaintenance = $stmt->fetchColumn();
        
        if ($isMaintenance !== '1' && $isMaintenance !== true) {
            return false;
        }
    } catch (Exception $e) {
        // If DB fails, assume site is up but broken (db.php handles DB outage)
        return false;
    }
    
    // Allow admins to bypass
    if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
        return false;
    }
    
    // Emergency bypass via secret key (if session fails)
    if (isset($_GET['bypass_maintenance']) && $_GET['bypass_maintenance'] === env('APP_KEY', 'default_secret')) {
        return false;
    }
    
    // Allow health check endpoint to pass through
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, '/api/health') !== false) {
        return false;
    }
    
    // Allow API requests to return JSON
    if (strpos($uri, '/api/') !== false || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        http_response_code(503);
        header('Content-Type: application/json');
        header('Retry-After: 3600');
        echo json_encode([
            'success' => false,
            'message' => 'The site is currently undergoing scheduled maintenance. Please try again soon.',
            'maintenance' => true,
        ]);
        exit;
    }
    
    // Show maintenance page
    http_response_code(503);
    header('Retry-After: 3600');
    show_maintenance_page();
    exit;
}

function show_maintenance_page() {
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance — Easy Shopping A.R.S</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e2e8f0;
        }
        .container {
            text-align: center;
            padding: 40px 20px;
            max-width: 600px;
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
        .brand {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .brand span { color: #ea6c00; }
        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #f1f5f9;
        }
        p {
            font-size: 1rem;
            line-height: 1.7;
            color: #94a3b8;
            margin-bottom: 25px;
        }
        .progress-bar {
            width: 200px;
            height: 4px;
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
            margin: 0 auto 30px;
            overflow: hidden;
        }
        .progress-bar::after {
            content: '';
            display: block;
            width: 40%;
            height: 100%;
            background: linear-gradient(90deg, #ea6c00, #ff9d4d);
            border-radius: 4px;
            animation: loading 1.5s ease-in-out infinite;
        }
        @keyframes loading {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(350%); }
        }
        .contact {
            font-size: 0.85rem;
            color: #64748b;
        }
        .contact a {
            color: #ea6c00;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon" style="margin-bottom: 20px;">
            <div style="width: 80px; height: 80px; overflow: hidden; border-radius: 12px; margin: 0 auto; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
                <img src="/public/assets/img/logo.jpg" alt="ARS Shop Logo" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
        </div>
        <div class="brand">ARS <span style="color: #ea6c00;">Shopping</span></div>
        <h1>We're upgrading your experience</h1>
        <p>
            Our site is currently undergoing scheduled maintenance.
            We'll be back shortly with a better shopping experience!
        </p>
        <div class="progress-bar"></div>
        <div class="contact">
            Need urgent help? Email us at
            <a href="mailto:easyshoppinga.r.s1@gmail.com">easyshoppinga.r.s1@gmail.com</a>
        </div>
    </div>
</body>
</html>
    <?php
}

// Auto-check when included
check_maintenance_mode();
?>
