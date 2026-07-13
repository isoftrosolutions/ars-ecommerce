<?php
/**
 * Order Controller
 * Authenticated order creation, listing, and detail viewing.
 *
 * @route /orders/*
 * @auth required
 */

class OrderController
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * POST /orders
     * Create a new order with server-side price calculation.
     */
    public function store($params)
    {
        $data = get_json_input();
        $isGuest = !empty($data['guest_name']);
        $user = null;

        if ($isGuest) {
            validate_required($data, ['items', 'payment_method', 'guest_name', 'guest_phone', 'guest_address']);
            check_rate_limit('guest_order', $_SERVER['REMOTE_ADDR']);
        } else {
            $user = require_auth();
            validate_required($data, ['items', 'payment_method']);
        }

        if (!is_array($data['items']) || empty($data['items'])) {
            json_error('Order must contain at least one item', 400);
        }
        validate_enum($data['payment_method'], ['COD', 'eSewa', 'BankQR'], 'payment_method');
        ValidationErrors::throwIfInvalid();

        $paymentMethod = $data['payment_method'];
        $notes = isset($data['notes']) ? sanitize_string($data['notes']) : null;
        $addressId = isset($data['address_id']) ? (int)$data['address_id'] : null;

        // Resolve address
        $addressData = null;
        if (!$isGuest && $addressId) {
            $stmt = $this->pdo->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$addressId, $user['id']]);
            $addressData = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$addressData) {
                json_error('Address not found', 404);
            }
        }

        // Fetch user info
        $guestName = '';
        $guestEmail = '';
        $guestPhone = '';
        $addressStr = '';
        if ($isGuest) {
            $guestName = sanitize_string($data['guest_name']);
            $guestPhone = preg_replace('/[^0-9]/', '', $data['guest_phone']);
            $guestEmail = isset($data['guest_email']) ? sanitize_string($data['guest_email']) : '';
            $addressStr = isset($data['guest_address']) ? sanitize_string($data['guest_address']) : '';
        } else {
            $stmt = $this->pdo->prepare("SELECT full_name, email, mobile FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $this->pdo->beginTransaction();

        try {
            $totalAmount = 0;
            $orderItems = [];

            foreach ($data['items'] as $item) {
                $productId = (int)($item['product_id'] ?? 0);
                $variantId = isset($item['variant_id']) ? (int)$item['variant_id'] : null;
                $quantity = max(1, (int)($item['quantity'] ?? 1));

                if (!$productId) {
                    throw new \Exception('Invalid product ID in order items');
                }

                // Fetch product from DB — NEVER trust client prices
                $stmt = $this->pdo->prepare("SELECT id, name, price, discount_price, stock, image FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    throw new \Exception("Product ID $productId not found");
                }

                $unitPrice = $product['discount_price'] ?: $product['price'];

                if ($variantId) {
                    // Check variant stock and pricing
                    $stmt = $this->pdo->prepare("SELECT price, discount_price, stock FROM product_variants WHERE id = ? AND product_id = ?");
                    $stmt->execute([$variantId, $productId]);
                    $variant = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$variant) {
                        throw new \Exception("Variant ID $variantId not found for product {$product['name']}");
                    }
                    if ($variant['stock'] < $quantity) {
                        throw new \Exception("Insufficient stock for {$product['name']} (selected variant)");
                    }

                    $unitPrice = $variant['discount_price'] ?: $variant['price'] ?: $unitPrice;
                } else {
                    if ($product['stock'] < $quantity) {
                        throw new \Exception("Insufficient stock for {$product['name']}");
                    }
                }

                $lineTotal = $unitPrice * $quantity;
                $totalAmount += $lineTotal;

                $orderItems[] = [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'product_name' => $product['name'],
                    'product_image' => $product['image'],
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                    'discount_price' => $product['discount_price'],
                ];
            }

            // Generate order number: ARS-YYYY-NNNNNN
            $year = date('Y');
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM orders WHERE YEAR(created_at) = ?");
            $stmt->execute([$year]);
            $orderCount = (int)$stmt->fetchColumn() + 1;
            $orderNumber = 'ARS-' . $year . '-' . str_pad($orderCount, 6, '0', STR_PAD_LEFT);

            // Build address string
            if ($addressData) {
                $parts = [$addressData['full_name'], $addressData['phone']];
                if ($addressData['street']) $parts[] = $addressData['street'];
                $parts[] = $addressData['ward'] . ', ' . $addressData['municipality'];
                $parts[] = $addressData['district'] . ', ' . $addressData['province'];
                $addressStr = implode(', ', $parts);
            }

            // Insert order
            $stmt = $this->pdo->prepare("
                INSERT INTO orders (
                    user_id, order_number, customer_name, customer_email, customer_phone,
                    address, total_amount, payment_method, payment_status, delivery_status, notes, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Pending', ?, NOW())
            ");
            $stmt->execute([
                $isGuest ? null : $user['id'],
                $orderNumber,
                $isGuest ? $guestName : $userInfo['full_name'],
                $isGuest ? $guestEmail : $userInfo['email'],
                $isGuest ? $guestPhone : $userInfo['mobile'],
                $addressStr,
                $totalAmount,
                $paymentMethod,
                $notes,
            ]);

            $orderId = $this->pdo->lastInsertId();

            // Insert order items
            foreach ($orderItems as $oi) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO order_items (order_id, product_id, variant_id, quantity, price, discount_price)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $orderId,
                    $oi['product_id'],
                    $oi['variant_id'],
                    $oi['quantity'],
                    $oi['price'],
                    $oi['discount_price'],
                ]);

                // Decrement stock atomically
                if ($oi['variant_id']) {
                    $stmt = $this->pdo->prepare("UPDATE product_variants SET stock = stock - ? WHERE id = ? AND stock >= ?");
                    $stmt->execute([$oi['quantity'], $oi['variant_id'], $oi['quantity']]);
                } else {
                    $stmt = $this->pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
                    $stmt->execute([$oi['quantity'], $oi['product_id'], $oi['quantity']]);
                }
                if ($stmt->rowCount() === 0) {
                    throw new \Exception("Stock update failed for {$oi['product_name']}");
                }
            }

            // Add status history entry
            $stmt = $this->pdo->prepare("
                INSERT INTO order_status_history (order_id, status, note, created_at)
                VALUES (?, 'Pending', 'Order placed', NOW())
            ");
            $stmt->execute([$orderId]);

            $this->pdo->commit();

            json_success([
                'order_id' => (int)$orderId,
                'order_number' => $orderNumber,
                'total' => (float)$totalAmount,
            ], 'Order placed successfully', 201);

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            $logLine = "[APIv1] Order creation failed: " . $e->getMessage();
            error_log($logLine, 3, __DIR__ . '/../../logs/api-v1.log');
            json_error($e->getMessage(), 400);
        }
    }

    /**
     * GET /orders?page=1&status=
     */
    public function index($params)
    {
        $user = require_auth();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        $status = $_GET['status'] ?? null;

        $where = ['o.user_id = ?'];
        $bindings = [$user['id']];

        if ($status) {
            $where[] = 'o.delivery_status = ?';
            $bindings[] = $status;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        // Count
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM orders o $whereClause");
        $stmt->execute($bindings);
        $total = (int)$stmt->fetchColumn();

        // Fetch orders
        $stmt = $this->pdo->prepare("
            SELECT o.id, o.order_number, o.total_amount, o.delivery_status as status,
                   o.payment_method, o.payment_status, o.created_at,
                   (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
            FROM orders o
            $whereClause
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute(array_merge($bindings, [$limit, $offset]));
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add items_preview for each order
        foreach ($orders as &$order) {
            $stmt = $this->pdo->prepare("
                SELECT p.image FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
                LIMIT 3
            ");
            $stmt->execute([$order['id']]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $order['items_preview'] = array_map(function ($item) {
                return product_image_url($item['image']);
            }, $items);
            $order['total'] = (float)$order['total_amount'];
        }
        unset($order);

        json_paginated($orders, $total, $page, $limit);
    }

    /**
     * GET /orders/{id}
     */
    public function show($params)
    {
        $user = require_auth();
        $id = (int)($params['id'] ?? 0);

        if (!$id) {
            json_error('Order ID is required', 400);
        }

        // Order
        $stmt = $this->pdo->prepare("
            SELECT o.*
            FROM orders o
            WHERE o.id = ? AND o.user_id = ?
        ");
        $stmt->execute([$id, $user['id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            json_error('Order not found', 404);
        }

        // Items
        $stmt = $this->pdo->prepare("
            SELECT oi.id, oi.product_id, oi.quantity, oi.price as unit_price,
                   oi.discount_price, p.name as product_name, p.image as product_image,
                   p.sku
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as &$item) {
            $item['product_image'] = product_image_url($item['product_image']);
        }
        unset($item);

        // Status history
        $stmt = $this->pdo->prepare("
            SELECT id, status, note, created_at FROM order_status_history WHERE order_id = ? ORDER BY created_at ASC
        ");
        $stmt->execute([$id]);
        $statusHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Parse address — return both flat and structured
        $address = null;
        if ($order['address']) {
            $parts = array_map('trim', explode(',', $order['address']));
            $address = ['full' => $order['address']];
            // If address has 4+ comma-separated parts, try structured parse
            if (count($parts) >= 3) {
                $last = $parts[count($parts) - 1]; // province
                $second = $parts[count($parts) - 2]; // district
                $addr = [
                    'full' => $order['address'],
                    'province' => $last,
                    'district' => $second,
                ];
                // municipality-ward is the third-from-last
                if (count($parts) >= 3) {
                    $mw = explode('-', $parts[count($parts) - 3]);
                    $addr['municipality'] = $mw[0];
                    $addr['ward'] = $mw[1] ?? '';
                }
                // everything before that is street
                if (count($parts) > 3) {
                    $addr['street'] = implode(', ', array_slice($parts, 0, count($parts) - 3));
                }
                $address = $addr;
            }
        }

        // Payment info
        $payment = [
            'method' => $order['payment_method'],
            'status' => $order['payment_status'],
            'transaction_id' => $order['transaction_id'],
        ];

        json_success([
            'order' => [
                'id' => (int)$order['id'],
                'order_number' => $order['order_number'],
                'status' => $order['delivery_status'],
                'total' => (float)$order['total_amount'],
                'shipping_charge' => (float)$order['shipping_charge'],
                'payment_method' => $order['payment_method'],
                'payment_status' => $order['payment_status'],
                'notes' => $order['notes'],
                'created_at' => $order['created_at'],
            ],
            'items' => $items,
            'status_history' => $statusHistory,
            'address' => $address,
            'payment' => $payment,
        ]);
    }

    /**
     * GET /orders/{id}/invoice
     * Returns full invoice data for an order.
     */
    public function invoice($params)
    {
        $user = require_auth();
        $id = (int)($params['id'] ?? 0);

        if (!$id) {
            json_error('Order ID is required', 400);
        }

        // Fetch order with user info
        $stmt = $this->pdo->prepare("
            SELECT o.*, u.full_name, u.email, u.mobile
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ? AND o.user_id = ?
        ");
        $stmt->execute([$id, $user['id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            json_error('Order not found', 404);
        }

        // Fetch items
        $stmt = $this->pdo->prepare("
            SELECT oi.product_id, oi.quantity, oi.price as unit_price,
                   oi.discount_price, p.name as product_name, p.sku
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $subtotal = 0;
        foreach ($items as &$item) {
            $item['total'] = (float)$item['unit_price'] * (int)$item['quantity'];
            $subtotal += $item['total'];
        }
        unset($item);

        json_success([
            'order_number' => $order['order_number'],
            'date' => $order['created_at'],
            'customer' => [
                'name' => $order['full_name'],
                'email' => $order['email'],
                'phone' => $order['mobile'],
            ],
            'shipping_address' => $order['address'],
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'shipping_charge' => (float)($order['shipping_charge'] ?? 0),
            'discount' => (float)($order['discount'] ?? 0),
            'total' => (float)$order['total_amount'],
            'payment_method' => $order['payment_method'],
            'payment_status' => $order['payment_status'],
            'delivery_status' => $order['delivery_status'],
        ]);
    }

    /**
     * POST /orders/{id}/return
     * Request a return on a delivered order (5-day window from delivery).
     * Mirrors legacy /api/return-order.php behavior.
     */
    public function returnOrder($params)
    {
        $user = require_auth();
        $id = (int)($params['id'] ?? 0);

        if (!$id) {
            json_error('Order ID is required', 400);
        }

        $stmt = $this->pdo->prepare("SELECT id, delivery_status, location_updated_at FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user['id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            json_error('Order not found', 404);
        }

        if (strtolower($order['delivery_status']) !== 'delivered') {
            json_error('Only delivered orders can be returned', 400);
        }

        if (!empty($order['location_updated_at'])) {
            $deliveredAt = strtotime($order['location_updated_at']);
            $daysSince = floor((time() - $deliveredAt) / 86400);
            if ($daysSince > 5) {
                json_error('Return period has expired (5 days from delivery)', 400);
            }
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE orders SET delivery_status = 'Return Requested' WHERE id = ?");
            $stmt->execute([$id]);

            $stmt = $this->pdo->prepare("INSERT INTO order_status_history (order_id, status, note, created_at) VALUES (?, 'Return Requested', 'Return requested by customer', NOW())");
            $stmt->execute([$id]);

            $this->pdo->commit();
            json_success(null, 'Return request submitted');
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            json_error('Failed to request return', 500);
        }
    }

    /**
     * POST /orders/{id}/payment-proof
     * Upload eSewa payment proof image for an order.
     */
    public function uploadPaymentProof($params)
    {
        $user = require_auth();
        $id = (int)($params['id'] ?? 0);

        if (!$id) {
            json_error('Order ID is required', 400);
        }

        $stmt = $this->pdo->prepare("SELECT id, user_id, payment_method, payment_proof FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user['id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            json_error('Order not found', 404);
        }

        if (strtolower($order['payment_method']) !== 'esewa') {
            json_error('Payment proof is only accepted for eSewa orders', 400);
        }

        if (!isset($_FILES['proof']) || $_FILES['proof']['error'] !== UPLOAD_ERR_OK) {
            json_error('Payment proof image is required', 400);
        }

        $file = $_FILES['proof'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024;

        if (!in_array($file['type'], $allowedTypes)) {
            json_error('Invalid file type. Only JPG and PNG are allowed.', 400);
        }

        if ($file['size'] > $maxSize) {
            json_error('File size exceeds 5MB limit', 400);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'esewa_proof_' . $id . '_' . time() . '_' . uniqid() . '.' . $ext;
        $uploadDir = __DIR__ . '/../../uploads/payments/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            json_error('Failed to save payment proof', 500);
        }

        $relativePath = 'uploads/payments/' . $filename;

        $stmt = $this->pdo->prepare("UPDATE orders SET payment_proof = ? WHERE id = ?");
        $stmt->execute([$relativePath, $id]);

        json_success([
            'proof_url' => absolute_image_url($relativePath),
        ], 'Payment proof uploaded successfully');
    }

    /**
     * POST /orders/{id}/cancel
     * Cancel an order if its status allows it.
     */
    public function cancel($params)
    {
        $user = require_auth();
        $id = (int)($params['id'] ?? 0);

        if (!$id) {
            json_error('Order ID is required', 400);
        }

        $stmt = $this->pdo->prepare("SELECT id, delivery_status FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user['id']]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            json_error('Order not found', 404);
        }

        $cancellable = ['Pending', 'Confirmed', 'Shipped'];
        if (!in_array($order['delivery_status'], $cancellable)) {
            json_error('Order cannot be cancelled in its current state', 400);
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE orders SET delivery_status = 'Cancelled' WHERE id = ?");
            $stmt->execute([$id]);

            $stmt = $this->pdo->prepare("INSERT INTO order_status_history (order_id, status, note, created_at) VALUES (?, 'Cancelled', 'Cancelled by customer', NOW())");
            $stmt->execute([$id]);

            $this->pdo->commit();
            json_success(null, 'Order cancelled successfully');
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            json_error('Failed to cancel order', 500);
        }
    }
}
