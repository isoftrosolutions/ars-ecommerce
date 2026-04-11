<?php
/**
 * Order Details Page
 * Easy Shopping A.R.S
 */

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ' . url('/auth/login'));
    exit;
}

$user = $_SESSION['user'];
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : null;

if (!$order_id) {
    header('Location: ' . url('/orders'));
    exit;
}

$page_title = "Order Details";
include 'includes/header-bootstrap.php';

// Get order details with security check (user can only view their own orders)
try {
    $stmt = $pdo->prepare("
        SELECT o.*, oi.quantity, oi.price, oi.discount_price, p.name, p.image, p.slug
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.id = ? AND (o.user_id = ? OR o.customer_email = ?)
        ORDER BY oi.id
    ");
    $stmt->execute([$order_id, $user['id'], $user['email']]);
    $order_data = $stmt->fetchAll();

    if (empty($order_data)) {
        // Order not found or not owned by user
        $order = null;
    } else {
        $order = $order_data[0]; // Order info
        $order['items'] = $order_data; // All items
    }
} catch (Exception $e) {
    $order = null;
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-12">
            <?php if (!$order): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-exclamation-triangle text-warning display-1"></i>
                        <h3 class="mt-3">Order Not Found</h3>
                        <p class="text-muted">The order you're looking for doesn't exist or you don't have permission to view it.</p>
                        <a href="<?php echo url('/orders'); ?>" class="btn btn-primary">View My Orders</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">Order #<?php echo $order['id']; ?></h4>
                            <small class="text-muted">Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></small>
                        </div>
                        <div>
                            <?php
                            $status_class = 'secondary';
                            $status_text = ucfirst($order['status']);
                            switch ($order['status']) {
                                case 'pending': $status_class = 'warning'; break;
                                case 'processing': $status_class = 'info'; break;
                                case 'shipped': $status_class = 'primary'; break;
                                case 'delivered': $status_class = 'success'; break;
                                case 'cancelled': $status_class = 'danger'; break;
                            }
                            ?>
                            <span class="badge bg-<?php echo $status_class; ?> fs-6"><?php echo $status_text; ?></span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <!-- Order Items -->
                            <div class="col-lg-8">
                                <h5 class="mb-3">Order Items</h5>
                                <div class="order-items">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <div class="card border mb-3">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-md-2">
                                                        <img src="<?php echo getProductImage($item['image'] ?? ''); ?>" alt="<?php echo h($item['name']); ?>" class="img-fluid rounded">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="card-title mb-1"><?php echo h($item['name']); ?></h6>
                                                        <p class="card-text small text-muted mb-0">Quantity: <?php echo $item['quantity']; ?></p>
                                                    </div>
                                                    <div class="col-md-4 text-end">
                                                        <div class="mb-1">
                                                            <span class="fw-bold">Rs. <?php echo format_price(($item['discount_price'] ?? $item['price']) * $item['quantity']); ?></span>
                                                        </div>
                                                        <small class="text-muted">
                                                            Rs. <?php echo format_price($item['discount_price'] ?? $item['price']); ?> each
                                                            <?php if ($item['discount_price'] && $item['discount_price'] < $item['price']): ?>
                                                                <span class="text-decoration-line-through">Rs. <?php echo format_price($item['price']); ?></span>
                                                            <?php endif; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Order Summary -->
                            <div class="col-lg-4">
                                <div class="order-summary">
                                    <h5 class="mb-3">Order Summary</h5>

                                    <div class="card border">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Subtotal:</span>
                                                <span>Rs. <?php echo format_price($order['total_amount']); ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Shipping:</span>
                                                <span class="text-success">Free</span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between fw-bold fs-5">
                                                <span>Total:</span>
                                                <span>Rs. <?php echo format_price($order['total_amount']); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <h6>Payment Method</h6>
                                        <p class="mb-0">
                                            <?php if ($order['payment_method'] === 'cod'): ?>
                                                <i class="bi bi-cash text-success me-2"></i>Cash on Delivery
                                            <?php elseif ($order['payment_method'] === 'esewa'): ?>
                                                <i class="bi bi-credit-card text-primary me-2"></i>eSewa
                                            <?php endif; ?>
                                        </p>
                                    </div>

                                    <div class="mt-3">
                                        <h6>Shipping Address</h6>
                                        <p class="mb-0">
                                            <?php echo h($order['customer_name']); ?><br>
                                            <?php echo h($order['shipping_address']); ?><br>
                                            <?php echo h($order['shipping_city']); ?>, Nepal<br>
                                            <i class="bi bi-telephone me-1"></i><?php echo h($order['customer_phone']); ?><br>
                                            <i class="bi bi-envelope me-1"></i><?php echo h($order['customer_email']); ?>
                                        </p>
                                    </div>

                                    <?php if ($order['status'] === 'pending'): ?>
                                        <div class="mt-3">
                                            <button class="btn btn-outline-danger w-100" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                                <i class="bi bi-x-circle me-2"></i>Cancel Order
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Order Timeline -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">Order Status</h5>
                                <div class="order-timeline">
                                    <div class="timeline-item <?php echo $order['status'] === 'pending' || $order['status'] === 'processing' || $order['status'] === 'shipped' || $order['status'] === 'delivered' ? 'active' : ''; ?>">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6>Order Placed</h6>
                                            <p class="mb-0 small text-muted"><?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
                                        </div>
                                    </div>

                                    <?php if (in_array($order['status'], ['processing', 'shipped', 'delivered'])): ?>
                                        <div class="timeline-item <?php echo $order['status'] === 'processing' || $order['status'] === 'shipped' || $order['status'] === 'delivered' ? 'active' : ''; ?>">
                                            <div class="timeline-marker bg-info"></div>
                                            <div class="timeline-content">
                                                <h6>Processing</h6>
                                                <p class="mb-0 small text-muted">Your order is being prepared</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (in_array($order['status'], ['shipped', 'delivered'])): ?>
                                        <div class="timeline-item <?php echo $order['status'] === 'shipped' || $order['status'] === 'delivered' ? 'active' : ''; ?>">
                                            <div class="timeline-marker bg-primary"></div>
                                            <div class="timeline-content">
                                                <h6>Shipped</h6>
                                                <p class="mb-0 small text-muted">Your order has been shipped</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($order['status'] === 'delivered'): ?>
                                        <div class="timeline-item active">
                                            <div class="timeline-marker bg-success"></div>
                                            <div class="timeline-content">
                                                <h6>Delivered</h6>
                                                <p class="mb-0 small text-muted">Your order has been delivered</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($order['status'] === 'cancelled'): ?>
                                        <div class="timeline-item active">
                                            <div class="timeline-marker bg-danger"></div>
                                            <div class="timeline-content">
                                                <h6>Cancelled</h6>
                                                <p class="mb-0 small text-muted">This order has been cancelled</p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.order-timeline {
    position: relative;
    padding-left: 30px;
}

.order-timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
    opacity: 0.5;
}

.timeline-item.active {
    opacity: 1;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-item.active .timeline-marker {
    box-shadow: 0 0 0 2px #007bff;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-size: 14px;
}
</style>

<script>
function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order? This action cannot be undone.')) {
        // Implement order cancellation logic
        alert('Order cancellation feature will be implemented soon.');
    }
}
</script>

<?php include 'includes/footer-bootstrap.php'; ?>