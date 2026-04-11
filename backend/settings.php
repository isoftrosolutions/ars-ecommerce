<?php
/**
 * Settings Management Backend Logic
 * Easy Shopping A.R.S eCommerce Platform
 */
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Protect admin page
protect_admin_page();

// Handle AJAX requests for settings management
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if (!validate_csrf_token()) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
        exit();
    }

    try {
        switch ($_POST['action']) {
            case 'get_settings':

                // Get all site settings
                $stmt = $pdo->query("SELECT * FROM site_settings ORDER BY `key` ASC");
                $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                echo json_encode(['success' => true, 'data' => $settings]);
                break;

            case 'update_setting':
                // Update a single setting
                $key = h($_POST['key']);
                $value = h($_POST['value']);

                $stmt = $pdo->prepare("
                    INSERT INTO site_settings (`key`, `value`)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
                ");
                $stmt->execute([$key, $value]);

                echo json_encode(['success' => true]);
                break;

            case 'bulk_update':
                // Update multiple settings at once
                $settings = isset($_POST['settings']) ? $_POST['settings'] : [];

                if (empty($settings)) {
                    echo json_encode(['success' => false, 'message' => 'No settings provided']);
                    exit();
                }

                $pdo->beginTransaction();

                foreach ($settings as $key => $value) {
                    $stmt = $pdo->prepare("
                        INSERT INTO site_settings (`key`, `value`)
                        VALUES (?, ?)
                        ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
                    ");
                    $stmt->execute([h($key), h($value)]);
                }

                $pdo->commit();
                echo json_encode(['success' => true, 'updated' => count($settings)]);
                break;

            case 'get_default_settings':
                // Get default settings structure
                $default_settings = [
                    // General Settings
                    'site_name' => 'Easy Shopping A.R.S',
                    'site_description' => 'Professional product showcase and online ordering platform',
                    'site_url' => 'http://localhost/ARS',
                    'admin_email' => 'admin@easyshoppingars.com',
                    'support_email' => 'support@easyshoppingars.com',

                    // Contact Information
                    'company_name' => 'Easy Shopping A.R.S',
                    'company_address' => '',
                    'company_phone' => '',
                    'company_email' => 'info@easyshoppingars.com',

                    // Social Media Links
                    'facebook_url' => '',
                    'twitter_url' => '',
                    'instagram_url' => '',
                    'linkedin_url' => '',

                    // Payment Settings
                    'esewa_enabled' => '1',
                    'bank_qr_enabled' => '1',
                    'cod_enabled' => '1',
                    'esewa_merchant_id' => '',
                    'bank_account_details' => '',

                    // Shipping Settings
                    'free_shipping_threshold' => '0',
                    'shipping_cost' => '100',
                    'estimated_delivery_days' => '3-5',

                    // Email Settings (for future email integration)
                    'smtp_host' => '',
                    'smtp_port' => '587',
                    'smtp_username' => '',
                    'smtp_password' => '',
                    'smtp_encryption' => 'tls',

                    // SEO Settings
                    'meta_title' => 'Easy Shopping A.R.S - Professional Online Store',
                    'meta_description' => 'Shop quality products online with fast delivery and secure payments.',
                    'meta_keywords' => 'shopping, online store, e-commerce, products, delivery',

                    // Maintenance Mode
                    'maintenance_mode' => '0',
                    'maintenance_message' => 'Site is under maintenance. Please check back soon.',

                    // Analytics
                    'google_analytics_id' => '',
                    'facebook_pixel_id' => '',

                    // Currency and Localization
                    'currency_symbol' => 'Rs.',
                    'currency_code' => 'NPR',
                    'timezone' => 'Asia/Kathmandu',
                    'language' => 'en',

                    // Product Settings
                    'products_per_page' => '12',
                    'featured_products_limit' => '8',
                    'low_stock_threshold' => '10',

                    // Order Settings
                    'order_prefix' => 'ARS',
                    'auto_order_status' => 'Pending',
                    'order_confirmation_email' => '1',

                    // Review Settings
                    'reviews_enabled' => '1',
                    'auto_approve_reviews' => '0',
                    'reviews_per_page' => '10'
                ];

                echo json_encode(['success' => true, 'data' => $default_settings]);
                break;

            case 'reset_to_defaults':
                // Reset settings to defaults
                $defaults = [
                    'site_name' => 'Easy Shopping A.R.S',
                    'site_description' => 'Professional product showcase and online ordering platform',
                    'site_url' => 'http://localhost/ARS',
                    'admin_email' => 'admin@easyshoppingars.com',
                    'support_email' => 'support@easyshoppingars.com',
                    'company_name' => 'Easy Shopping A.R.S',
                    'esewa_enabled' => '1',
                    'bank_qr_enabled' => '1',
                    'cod_enabled' => '1',
                    'maintenance_mode' => '0',
                    'reviews_enabled' => '1',
                    'auto_approve_reviews' => '0',
                    'currency_symbol' => 'Rs.',
                    'products_per_page' => '12'
                ];

                $pdo->beginTransaction();

                foreach ($defaults as $key => $value) {
                    $stmt = $pdo->prepare("
                        INSERT INTO site_settings (`key`, `value`)
                        VALUES (?, ?)
                        ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
                    ");
                    $stmt->execute([$key, $value]);
                }

                $pdo->commit();
                echo json_encode(['success' => true]);
                break;

            case 'export_settings':
                // Export settings as JSON
                $stmt = $pdo->query("SELECT * FROM site_settings ORDER BY `key` ASC");
                $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                echo json_encode([
                    'success' => true,
                    'data' => $settings,
                    'exported_at' => date('Y-m-d H:i:s')
                ]);
                break;

            case 'import_settings':
                // Import settings from JSON
                $settings_json = $_POST['settings_json'];

                $settings = json_decode($settings_json, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo json_encode(['success' => false, 'message' => 'Invalid JSON format']);
                    exit();
                }

                if (empty($settings)) {
                    echo json_encode(['success' => false, 'message' => 'No settings found in JSON']);
                    exit();
                }

                $pdo->beginTransaction();

                foreach ($settings as $key => $value) {
                    $stmt = $pdo->prepare("
                        INSERT INTO site_settings (`key`, `value`)
                        VALUES (?, ?)
                        ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
                    ");
                    $stmt->execute([h($key), h($value)]);
                }

                $pdo->commit();
                echo json_encode(['success' => true, 'imported' => count($settings)]);
                break;

            case 'get_setting_categories':
                // Get settings organized by categories
                $categories = [
                    'general' => [
                        'title' => 'General Settings',
                        'settings' => ['site_name', 'site_description', 'site_url', 'admin_email', 'support_email']
                    ],
                    'company' => [
                        'title' => 'Company Information',
                        'settings' => ['company_name', 'company_address', 'company_phone', 'company_email']
                    ],
                    'social' => [
                        'title' => 'Social Media',
                        'settings' => ['facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url']
                    ],
                    'payment' => [
                        'title' => 'Payment Settings',
                        'settings' => ['esewa_enabled', 'bank_qr_enabled', 'cod_enabled', 'esewa_merchant_id', 'bank_account_details']
                    ],
                    'shipping' => [
                        'title' => 'Shipping Settings',
                        'settings' => ['free_shipping_threshold', 'shipping_cost', 'estimated_delivery_days']
                    ],
                    'seo' => [
                        'title' => 'SEO Settings',
                        'settings' => ['meta_title', 'meta_description', 'meta_keywords']
                    ],
                    'products' => [
                        'title' => 'Product Settings',
                        'settings' => ['products_per_page', 'featured_products_limit', 'low_stock_threshold']
                    ],
                    'reviews' => [
                        'title' => 'Review Settings',
                        'settings' => ['reviews_enabled', 'auto_approve_reviews', 'reviews_per_page']
                    ]
                ];

                echo json_encode(['success' => true, 'data' => $categories]);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

// If not AJAX request, redirect to settings page
header('Location: ../admin/settings.php');
exit();
?>