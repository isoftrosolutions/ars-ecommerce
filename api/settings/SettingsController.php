<?php
/**
 * Settings Controller
 * Handles site settings management
 */
class SettingsController extends BaseController {
    public function handleRequest($method, $action) {
        AuthMiddleware::authenticate();
        AuthMiddleware::checkRateLimit('settings', 30, 3600);

        switch ($method) {
            case 'GET':
                switch ($action) {
                    case 'all':
                        return $this->getSettings();
                    case 'defaults':
                        return $this->getDefaultSettings();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            case 'POST':
                switch ($action) {
                    case 'update':
                        return $this->updateSettings();
                    case 'bulk-update':
                        return $this->bulkUpdateSettings();
                    default:
                        Response::error('Invalid action', 400);
                }
                break;

            default:
                Response::error('Method not allowed', 405);
        }
    }

    /**
     * Get all settings
     */
    private function getSettings() {
        $stmt = $this->executeQuery("SELECT * FROM site_settings ORDER BY `key`");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Convert boolean strings to actual booleans
        $booleanKeys = ['maintenance_mode', 'reviews_enabled', 'auto_approve_reviews', 'cod_enabled', 'esewa_enabled', 'bank_qr_enabled'];
        foreach ($booleanKeys as $key) {
            if (isset($settings[$key])) {
                $settings[$key] = $settings[$key] === '1';
            }
        }

        Response::success($settings, 'Settings retrieved successfully');
    }

    /**
     * Get default settings
     */
    private function getDefaultSettings() {
        $defaults = [
            // General
            'site_name' => 'Easy Shopping A.R.S',
            'site_description' => 'Your one-stop shop for quality products',
            'site_url' => 'http://localhost/ars',
            'admin_email' => 'admin@easyshoppingars.com',
            'support_email' => 'support@easyshoppingars.com',
            'currency_symbol' => 'Rs.',
            'currency_code' => 'NPR',
            'timezone' => 'Asia/Kathmandu',
            'maintenance_mode' => false,

            // Company
            'company_name' => 'Easy Shopping A.R.S',
            'company_address' => '',
            'company_phone' => '',
            'company_email' => '',

            // Social Media
            'facebook_url' => '',
            'twitter_url' => '',
            'instagram_url' => '',
            'linkedin_url' => '',

            // Payment
            'esewa_enabled' => true,
            'bank_qr_enabled' => true,
            'cod_enabled' => true,
            'esewa_merchant_id' => '',
            'bank_account_details' => '',

            // Shipping
            'shipping_cost' => 150,
            'free_shipping_threshold' => 5000,
            'estimated_delivery_days' => '3-5',

            // SEO
            'meta_title' => 'Easy Shopping A.R.S - Quality Products Online',
            'meta_description' => 'Shop quality products online with fast delivery and secure payment options.',
            'meta_keywords' => 'shopping, online store, quality products',
            'google_analytics_id' => '',
            'facebook_pixel_id' => '',

            // Products
            'products_per_page' => 12,
            'featured_products_limit' => 8,
            'low_stock_threshold' => 5,
            'order_prefix' => 'ARS',

            // Reviews
            'reviews_enabled' => true,
            'auto_approve_reviews' => false,
            'reviews_per_page' => 10
        ];

        Response::success($defaults, 'Default settings retrieved successfully');
    }

    /**
     * Update single setting
     */
    private function updateSettings() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['key', 'value']);
        ValidationMiddleware::throwIfInvalid();

        $this->validateSettingKey($data['key']);

        $stmt = $this->executeQuery(
            "INSERT INTO site_settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
            [$data['key'], $data['value']]
        );

        $this->logAction('update_setting', ['key' => $data['key']]);

        Response::success(null, 'Setting updated successfully');
    }

    /**
     * Bulk update settings
     */
    private function bulkUpdateSettings() {
        $data = $this->getInputData();
        ValidationMiddleware::validateRequired($data, ['settings']);
        ValidationMiddleware::throwIfInvalid();

        if (!is_array($data['settings'])) {
            Response::error('Settings must be an array', 400);
        }

        $this->beginTransaction();

        try {
            $updated = 0;
            foreach ($data['settings'] as $key => $value) {
                $this->validateSettingKey($key);

                // Convert boolean to string for storage
                if (is_bool($value)) {
                    $value = $value ? '1' : '0';
                }

                $this->executeQuery(
                    "INSERT INTO site_settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
                    [$key, $value]
                );
                $updated++;
            }

            $this->commit();
            $this->logAction('bulk_update_settings', ['count' => $updated]);

            Response::success(['updated' => $updated], 'Settings updated successfully');

        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Validate setting key
     */
    private function validateSettingKey($key) {
        $allowedKeys = [
            // General
            'site_name', 'site_description', 'site_url', 'admin_email', 'support_email',
            'currency_symbol', 'currency_code', 'timezone', 'maintenance_mode',

            // Company
            'company_name', 'company_address', 'company_phone', 'company_email',

            // Social Media
            'facebook_url', 'twitter_url', 'instagram_url', 'linkedin_url',

            // Payment
            'esewa_enabled', 'bank_qr_enabled', 'cod_enabled', 'esewa_merchant_id', 'bank_account_details', 'qr_code_path',

            // Shipping
            'shipping_cost', 'free_shipping_threshold', 'estimated_delivery_days',

            // SEO
            'meta_title', 'meta_description', 'meta_keywords', 'google_analytics_id', 'facebook_pixel_id',

            // Products
            'products_per_page', 'featured_products_limit', 'low_stock_threshold', 'order_prefix',

            // Reviews
            'reviews_enabled', 'auto_approve_reviews', 'reviews_per_page'
        ];

        if (!in_array($key, $allowedKeys)) {
            Response::error('Invalid setting key', 400);
        }
    }
}
?>