<?php
/**
 * User Orders History Page
 * Easy Shopping A.R.S
 */

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ' . url('/auth/login'));
    exit;
}

$user = $_SESSION['user'];
$page_title = "My Orders";
include 'includes/header-bootstrap.php';

// Get user's orders
try {
    $stmt = $pdo->prepare("
        SELECT o.*,
               COUNT(oi.id) as item_count,
               GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ? OR o.customer_email = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$user['id'], $user['email']]);
    $orders = $stmt->fetchAll();
} catch (Exception $e) {
    $orders = [];
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-3">
            <!-- Orders Sidebar -->
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="profile-avatar mb-3">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white" style="width: 80px; height: 80px; font-size: 2rem;">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                    </div>
                    <h5><?php echo h($user['name']); ?></h5>
                    <p class="text-muted small"><?php echo h($user['email']); ?></p>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?php echo url('/profile'); ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-person me-2"></i>Profile Settings
                    </a>
                    <a href="<?php echo url('/orders'); ?>" class="list-group-item list-group-item-action active">
                        <i class="bi bi-receipt me-2"></i>My Orders
                    </a>
                    <a href="<?php echo url('/wishlist'); ?>" class="list-group-item list-group-item-action">
                        <i class="bi bi-heart me-2"></i>Wishlist
                    </a>
                    <a href="<?php echo url('/auth/logout'); ?>" class="list-group-item list-group-item-action text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">My Orders</h4>
                        <small class="text-muted"><?php echo count($orders); ?> orders found</small>
                    </div>
                    <a href="<?php echo url('/shop'); ?>" class="btn btn-primary">
                        <i class="bi bi-shop me-2"></i>Continue Shopping
                    </a>
                </div>

                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-receipt text-muted display-1"></i>
                            <h3 class="mt-3">No orders yet</h3>
                            <p class="text-muted">You haven't placed any orders yet. Start shopping to see your orders here.</p>
                            <a href="<?php echo url('/shop'); ?>" class="btn btn-primary">Start Shopping</a>
                        </div>
                    <?php else: ?>
                        <div class="orders-list">
                            <?php foreach ($orders as $order): ?>
                                <div class="order-card card border mb-3">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h5 class="mb-0 me-3">Order #<?php echo $order['id']; ?></h5>
                                                    <?php
                                                    $status_class = 'secondary';
                                                    switch ($order['status']) {
                                                        case 'pending': $status_class = 'warning'; break;
                                                        case 'processing': $status_class = 'info'; break;
                                                        case 'shipped': $status_class = 'primary'; break;
                                                        case 'delivered': $status_class = 'success'; break;
                                                        case 'cancelled': $status_class = 'danger'; break;
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class; ?>"><?php echo ucfirst($order['status']); ?></span>
                                                </div>

                                                <p class="text-muted small mb-1">
                                                    <i class="bi bi-calendar me-1"></i><?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?>
                                                </p>

                                                <p class="mb-1">
                                                    <strong><?php echo $order['item_count']; ?> item<?php echo $order['item_count'] > 1 ? 's' : ''; ?>:</strong>
                                                    <?php echo h(substr($order['product_names'], 0, 100)); ?>
                                                    <?php if (strlen($order['product_names']) > 100): ?>...<?php endif; ?>
                                                </p>

                                                <p class="mb-0 small text-muted">
                                                    <i class="bi bi-geo-alt me-1"></i><?php echo h($order['shipping_address']); ?>, <?php echo h($order['shipping_city']); ?>
                                                </p>
                                            </div>

                                            <div class="col-md-4 text-end">
                                                <div class="mb-2">
                                                    <div class="fw-bold fs-5 text-primary">Rs. <?php echo format_price($order['total_amount']); ?></div>
                                                </div>

                                                <div class="mb-2">
                                                    <?php if ($order['payment_method'] === 'cod'): ?>
                                                        <small class="text-success"><i class="bi bi-cash me-1"></i>Cash on Delivery</small>
                                                    <?php elseif ($order['payment_method'] === 'esewa'): ?>
                                                        <small class="text-primary"><i class="bi bi-credit-card me-1"></i>eSewa</small>
                                                    <?php endif; ?>
                                                </div>

                                                <div>
                                                    <a href="<?php echo url('/order/' . $order['id']); ?>" class="btn btn-outline-primary btn-sm">
                                                        <i class="bi bi-eye me-1"></i>View Details
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Order Statistics -->
                        <div class="row mt-4">
                            <?php
                            try {
                                $stats = [];

                                // Count orders by status
                                $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM orders WHERE user_id = ? OR customer_email = ? GROUP BY status");
                                $stmt->execute([$user['id'], $user['email']]);
                                $status_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                                $stats['pending'] = $status_counts['pending'] ?? 0;
                                $stats['processing'] = $status_counts['processing'] ?? 0;
                                $stats['shipped'] = $status_counts['shipped'] ?? 0;
                                $stats['delivered'] = $status_counts['delivered'] ?? 0;
                                $stats['cancelled'] = $status_counts['cancelled'] ?? 0;

                                // Total spent
                                $stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE (user_id = ? OR customer_email = ?) AND status != 'cancelled'");
                                $stmt->execute([$user['id'], $user['email']]);
                                $stats['total_spent'] = $stmt->fetch()['total'] ?? 0;

                            } catch (Exception $e) {
                                $stats = ['pending' => 0, 'processing' => 0, 'shipped' => 0, 'delivered' => 0, 'cancelled' => 0, 'total_spent' => 0];
                            }
                            ?>

                            <div class="col-md-3">
                                <div class="card border-0 bg-light text-center">
                                    <div class="card-body">
                                        <div class="fs-4 fw-bold text-warning"><?php echo $stats['pending']; ?></div>
                                        <small class="text-muted">Pending</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 bg-light text-center">
                                    <div class="card-body">
                                        <div class="fs-4 fw-bold text-info"><?php echo $stats['processing']; ?></div>
                                        <small class="text-muted">Processing</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 bg-light text-center">
                                    <div class="card-body">
                                        <div class="fs-4 fw-bold text-primary"><?php echo $stats['shipped']; ?></div>
                                        <small class="text-muted">Shipped</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 bg-light text-center">
                                    <div class="card-body">
                                        <div class="fs-4 fw-bold text-success"><?php echo $stats['delivered']; ?></div>
                                        <small class="text-muted">Delivered</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <div class="text-muted">
                                Total spent: <strong class="text-success">Rs. <?php echo format_price($stats['total_spent']); ?></strong>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.order-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}
</style>

<?php include 'includes/footer-bootstrap.php'; ?>