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
?>
