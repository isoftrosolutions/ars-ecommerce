<?php
/**
 * Global Helper Functions
 * Easy Shopping A.R.S eCommerce Platform
 */

// Dynamic Base URL detection
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
$project_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
// If we are in backend or auth or admin subfolders, get the parent
if (preg_match('/(backend|auth|admin|includes)$/', $project_dir)) {
    $project_dir = dirname($project_dir);
}
$project_dir = rtrim(str_replace('\\', '/', $project_dir), '/');
$app_base_path = $project_dir . '/';
$base_url = $protocol . "://" . $host . $project_dir;

/**
 * Helper to generate absolute URLs within the application
 */
function url($path = '') {
    global $app_base_path;
    return $app_base_path . ltrim($path, '/');
}

/**
 * Check if the user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user']);
}

/**
 * Check if the user is logged in as an admin
 */
function is_admin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

/**
 * Redirect to login if not admin
 */
function protect_admin_page() {
    if (!is_admin()) {
        header('Location: ' . url('/auth/login.php'));
        exit();
    }
}

/**
 * Generate (or retrieve) a CSRF token for the current session
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate the CSRF token from POST body or X-CSRF-Token header
 */
function validate_csrf_token() {
    $session_token = $_SESSION['csrf_token'] ?? '';
    if (empty($session_token)) return false;

    $provided = $_POST['csrf_token']
        ?? $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? '';

    return !empty($provided) && hash_equals($session_token, $provided);
}

/**
 * Format currency to Rs.
 */
function format_price($price) {
    return 'Rs. ' . number_format($price, 2);
}

/**
 * Get status badge class
 */
function get_status_badge($status) {
    $status = strtolower($status);
    switch ($status) {
        case 'pending':
            return 'badge-warning';
        case 'paid':
        case 'delivered':
        case 'approved':
            return 'badge-success';
        case 'shipped':
        case 'confirmed':
            return 'badge-info';
        case 'failed':
        case 'cancelled':
        case 'rejected':
            return 'badge-danger';
        default:
            return 'badge-primary';
    }
}

/**
 * Format currency to Rs. (Alias for compatibility)
 */
function formatPrice($price) {
    return format_price($price);
}

/**
 * Get product image path with placeholder fallback
 */
function getProductImage($image) {
    if ($image && file_exists(__DIR__ . '/../uploads/products/' . $image)) {
        return url('/uploads/products/' . $image);
    }
    // Return a professional placeholder if image is missing
    return 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&q=80&w=400';
}

/**
 * Sanitize output
 */
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get cart items for current user/session
 */
function get_cart() {
    global $pdo;

    $user_id = $_SESSION['user']['id'] ?? null;
    $session_id = session_id();

    try {
        $query = "SELECT ci.*, p.name, p.price, p.discount_price, p.image, p.slug, p.stock
                  FROM cart_items ci
                  JOIN products p ON ci.product_id = p.id
                  WHERE 1=1";

        $params = [];
        if ($user_id) {
            $query .= " AND ci.user_id = ?";
            $params[] = $user_id;
        } else {
            $query .= " AND ci.session_id = ?";
            $params[] = $session_id;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get cart total items count
 */
function get_cart_count() {
    $cart_items = get_cart();
    return array_sum(array_column($cart_items, 'quantity'));
}

/**
 * Get cart total amount
 */
function get_cart_total() {
    $cart_items = get_cart();
    $total = 0;

    foreach ($cart_items as $item) {
        $price = $item['discount_price'] ?: $item['price'];
        $total += $price * $item['quantity'];
    }

    return $total;
}

/**
 * Add item to cart
 */
function add_to_cart($product_id, $quantity = 1) {
    global $pdo;

    $user_id = $_SESSION['user']['id'] ?? null;
    $session_id = session_id();

    try {
        // Check if product exists and has stock
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product || $product['stock'] < $quantity) {
            return ['success' => false, 'message' => 'Product not available or insufficient stock'];
        }

        // Check if item already in cart
        $query = "SELECT id, quantity FROM cart_items WHERE product_id = ?";
        $params = [$product_id];

        if ($user_id) {
            $query .= " AND user_id = ?";
            $params[] = $user_id;
        } else {
            $query .= " AND session_id = ?";
            $params[] = $session_id;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update quantity
            $new_quantity = $existing['quantity'] + $quantity;
            if ($new_quantity > $product['stock']) {
                $new_quantity = $product['stock'];
            }

            $update_query = "UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($update_query);
            $stmt->execute([$new_quantity, $existing['id']]);
        } else {
            // Insert new item
            $insert_query = "INSERT INTO cart_items (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($insert_query);
            $stmt->execute([$user_id, $session_id, $product_id, $quantity]);
        }

        return ['success' => true, 'message' => 'Item added to cart'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to add item to cart'];
    }
}

/**
 * Update cart item quantity
 */
function update_cart_quantity($product_id, $quantity) {
    global $pdo;

    if ($quantity <= 0) {
        return remove_from_cart($product_id);
    }

    $user_id = $_SESSION['user']['id'] ?? null;
    $session_id = session_id();

    try {
        // Check stock
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if (!$product || $product['stock'] < $quantity) {
            return ['success' => false, 'message' => 'Insufficient stock'];
        }

        $query = "UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE product_id = ?";
        $params = [$quantity, $product_id];

        if ($user_id) {
            $query .= " AND user_id = ?";
            $params[] = $user_id;
        } else {
            $query .= " AND session_id = ?";
            $params[] = $session_id;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return ['success' => true, 'message' => 'Quantity updated'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to update quantity'];
    }
}

/**
 * Remove item from cart
 */
function remove_from_cart($product_id) {
    global $pdo;

    $user_id = $_SESSION['user']['id'] ?? null;
    $session_id = session_id();

    try {
        $query = "DELETE FROM cart_items WHERE product_id = ?";
        $params = [$product_id];

        if ($user_id) {
            $query .= " AND user_id = ?";
            $params[] = $user_id;
        } else {
            $query .= " AND session_id = ?";
            $params[] = $session_id;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return ['success' => true, 'message' => 'Item removed from cart'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to remove item'];
    }
}

/**
 * Clear entire cart
 */
function clear_cart() {
    global $pdo;

    $user_id = $_SESSION['user']['id'] ?? null;
    $session_id = session_id();

    try {
        $query = "DELETE FROM cart_items WHERE 1=1";

        if ($user_id) {
            $query .= " AND user_id = ?";
            $params = [$user_id];
        } else {
            $query .= " AND session_id = ?";
            $params = [$session_id];
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return ['success' => true, 'message' => 'Cart cleared'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to clear cart'];
    }
}

/**
 * Transfer guest cart to user cart (when user logs in)
 */
function transfer_guest_cart_to_user($user_id) {
    global $pdo;

    $session_id = session_id();

    try {
        // Update cart items from session to user
        $stmt = $pdo->prepare("UPDATE cart_items SET user_id = ?, session_id = NULL WHERE session_id = ?");
        $stmt->execute([$user_id, $session_id]);

        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to transfer cart'];
    }
}
/**
 * Get a specific site setting by key
 */
function get_setting($key, $default = null) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT `value` FROM site_settings WHERE `key` = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['value'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

/**
 * Get all site settings as an associative array
 */
function get_settings() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT `key`, `value` FROM site_settings");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get all categories from the database
 */
function get_categories() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get total number of categories
 */
function get_categories_count() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}
/**
 * Generate, Store and Send OTP for a user
 * @param string $identifier Email or Mobile
 * @param string $action 'password_reset', 'login', 'signup_verify'
 * @return array ['success' => bool, 'message' => string]
 */
function send_otp($identifier, $action = 'login') {
    global $pdo;
    
    // Identify user
    $is_email = filter_var($identifier, FILTER_VALIDATE_EMAIL);
    $column = $is_email ? 'email' : 'mobile';
    
    try {
        $stmt = $pdo->prepare("SELECT id, full_name, email, mobile, otp_attempts, otp_issued_at FROM users WHERE $column = ?");
        $stmt->execute([$identifier]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'No account found with this ' . ($is_email ? 'email' : 'mobile number')];
        }
        
        // Rate limiting: max 3 attempts per hour
        $one_hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));
        if ($user['otp_attempts'] >= 5 && $user['otp_issued_at'] > $one_hour_ago) {
            return ['success' => false, 'message' => 'Too many OTP requests. Please try again later.'];
        }
        
        // Generate 6-digit OTP
        $otp = sprintf('%06d', mt_rand(100000, 999999));
        $hashed_otp = password_hash($otp, PASSWORD_DEFAULT);
        $current_time = date('Y-m-d H:i:s');
        
        // Update database
        $stmt = $pdo->prepare("UPDATE users SET otp_hash = ?, otp_attempts = otp_attempts + 1, otp_issued_at = ? WHERE id = ?");
        $stmt->execute([$hashed_otp, $current_time, $user['id']]);
        
        // Also store in session for backward compatibility and fast access
        $_SESSION['temp_otp'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'mobile' => $user['mobile'],
            'otp' => $hashed_otp,
            'expires' => strtotime('+10 minutes'),
            'action' => $action
        ];
        
        // Send via Email Service
        require_once __DIR__ . '/email-service.php';
        $emailService = getEmailService();
        $sendSuccess = false;
        
        if ($is_email) {
            $sendSuccess = $emailService->sendOTP($user['email'], $otp, $user['full_name']);
        } else {
            $sendSuccess = $emailService->sendSMSOTP($user['mobile'], $otp);
        }
        
        if ($sendSuccess) {
            return ['success' => true, 'message' => 'OTP sent successfully!'];
        } else {
            return ['success' => false, 'message' => 'Failed to deliver OTP.'];
        }
        
    } catch (PDOException $e) {
        error_log('send_otp error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Internal server error.'];
    }
}

/**
 * Verify OTP for a user
 * @param string $identifier Email or Mobile
 * @param string $otp 6-digit code
 * @return array ['success' => bool, 'message' => string]
 */
function verify_otp($identifier, $otp) {
    global $pdo;
    
    $is_email = filter_var($identifier, FILTER_VALIDATE_EMAIL);
    $column = $is_email ? 'email' : 'mobile';
    
    try {
        $stmt = $pdo->prepare("SELECT id, otp_hash, otp_issued_at FROM users WHERE $column = ?");
        $stmt->execute([$identifier]);
        $user = $stmt->fetch();
        
        if (!$user || empty($user['otp_hash'])) {
            return ['success' => false, 'message' => 'No active OTP found.'];
        }
        
        // Check expiration (10 minutes)
        if (strtotime($user['otp_issued_at']) < strtotime('-10 minutes')) {
            return ['success' => false, 'message' => 'OTP has expired.'];
        }
        
        // Verify code
        if (!password_verify($otp, $user['otp_hash'])) {
            return ['success' => false, 'message' => 'Invalid OTP.'];
        }
        
        // Success! Clear OTP from DB
        $stmt = $pdo->prepare("UPDATE users SET otp_hash = NULL, otp_attempts = 0 WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Clear from session too
        unset($_SESSION['temp_otp']);
        
        return ['success' => true, 'message' => 'OTP verified.'];
        
    } catch (PDOException $e) {
        error_log('verify_otp error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Verification failed.'];
    }
}
?>
