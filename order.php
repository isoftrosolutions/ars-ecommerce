<?php
/**
 * Order Details Page
 * Easy Shopping A.R.S
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: ' . url('/auth/login.php'));
    exit;
}

$user = $_SESSION['user'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$order_id) {
    header('Location: ' . url('/orders.php'));
    exit;
}

$page_title = "Order Details";
include 'includes/header-bootstrap.php';

// Get order details
try {
    $stmt = $pdo->prepare("
        SELECT o.*, oi.quantity, oi.price as unit_price, p.name as prod_name, p.image as prod_img, p.slug as prod_slug
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.id = ? AND (o.user_id = ? OR o.customer_email = ?)
    ");
    $stmt->execute([$order_id, $user['id'], $user['email']]);
    $items = $stmt->fetchAll();

    if (empty($items)) {
        $order = null;
    } else {
        $order = $items[0];
    }
} catch (Exception $e) { $order = null; }
?>

<style>
/* ═══ Order Details Responsiveness ═══ */
.order-detail-header { border-radius: 12px; }
.timeline-dot { width: 12px; height: 12px; border-radius: 50%; position: absolute; left: -6px; top: 10px; border: 2px solid white; box-shadow: 0 0 0 2px #eee; }
.timeline-item { position: relative; padding-left: 20px; border-left: 2px solid #eee; padding-bottom: 25px; margin-left: 10px; }
.timeline-item:last-child { border-left: transparent; }
.timeline-item.active { border-left-color: var(--primary-color); }
.timeline-item.active .timeline-dot { background: var(--primary-color); box-shadow: 0 0 0 2px var(--primary-color); }

@media (max-width: 767px) {
    .order-summary-card { border: none !important; background: #f8fafc; border-radius: 15px; }
    .item-row { flex-direction: column; text-align: left; }
    .item-image { width: 60px !important; height: 60px !important; margin-bottom: 10px; }
}
</style>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav class="mb-4 d-none d-md-block">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?php echo url('/orders.php'); ?>">My Orders</a></li>
            <li class="breadcrumb-item active">Order #<?php echo $order_id; ?></li>
        </ol>
    </nav>

    <?php if (!$order): ?>
        <div class="text-center py-5">
            <h4 class="fw-bold">Order not found.</h4>
            <a href="<?php echo url('/orders.php'); ?>" class="btn btn-primary mt-3">Back to Orders</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Left: Order Items & Tracking -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4 order-detail-header">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h4 class="fw-bold mb-1">Order #<?php echo $order['id']; ?></h4>
                                <div class="text-muted small">Placed on <?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                            </div>
                            <?php
                                $s = $order['status'];
                                $c = ($s=='delivered')?'success':(($s=='cancelled')?'danger':'warning');
                            ?>
                            <span class="badge rounded-pill bg-<?php echo $c; ?> px-3 py-2"><?php echo ucfirst($s); ?></span>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold mb-3">Items Purchased</h6>
                        <?php foreach($items as $i): ?>
                            <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                                <img src="<?php echo getProductImage($i['prod_img']); ?>" class="rounded item-image" style="width:70px; height:70px; object-fit:contain; background:#f8f9fa;">
                                <div class="flex-grow-1">
                                    <div class="fw-bold small mb-1"><?php echo h($i['prod_name']); ?></div>
                                    <div class="text-muted small">Qty: <?php echo $i['quantity']; ?></div>
                                </div>
                                <div class="fw-bold fs-6">Rs. <?php echo number_format($i['unit_price'] * $i['quantity'], 0); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tracking Timeline -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-4">Track Order</h6>
                        <div class="timeline-item active">
                            <span class="timeline-dot"></span>
                            <div class="fw-bold small">Order Placed</div>
                            <div class="text-muted x-small"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                        </div>
                        <div class="timeline-item <?php echo in_array($order['status'], ['processing','shipped','delivered'])?'active':''; ?>">
                            <span class="timeline-dot"></span>
                            <div class="fw-bold small">Processing</div>
                            <div class="text-muted x-small">Your order is being prepared.</div>
                        </div>
                        <div class="timeline-item <?php echo in_array($order['status'], ['shipped','delivered'])?'active':''; ?>">
                            <span class="timeline-dot"></span>
                            <div class="fw-bold small">Shipped</div>
                            <div class="text-muted x-small">Package has left our facility.</div>
                        </div>
                        <div class="timeline-item <?php echo ($order['status']=='delivered')?'active':''; ?>">
                            <span class="timeline-dot"></span>
                            <div class="fw-bold small">Delivered</div>
                            <div class="text-muted x-small">Successfully reached your doorstep.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Summary & Address -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4 rounded-4 order-summary-card">
                    <div class="card-body p-4 text-center">
                        <h6 class="fw-bold mb-3">Order Total</h6>
                        <div class="display-5 fw-bold text-dark mb-4">Rs. <?php echo number_format($order['total_amount'], 0); ?></div>
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted">Payment:</span>
                            <span class="fw-bold px-2 py-1 rounded bg-light"><?php echo strtoupper($order['payment_method']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Delivery Information</h6>
                        <div class="d-flex gap-3 mb-3">
                            <i class="bi bi-geo-alt-fill text-muted mt-1"></i>
                            <div class="small">
                                <div class="fw-bold"><?php echo h($order['customer_name']); ?></div>
                                <div class="text-muted">
                                    <?php echo h($order['shipping_address']); ?><br>
                                    <?php echo h($order['shipping_city']); ?>, Nepal
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-3 mb-3">
                            <i class="bi bi-telephone-fill text-muted mt-1"></i>
                            <div class="small fw-bold"><?php echo h($order['customer_phone']); ?></div>
                        </div>
                        <div class="d-flex gap-3">
                            <i class="bi bi-envelope-fill text-muted mt-1"></i>
                            <div class="small text-muted text-truncate"><?php echo h($order['customer_email']); ?></div>
                        </div>
                    </div>
                </div>
                
                <a href="<?php echo url('/shop'); ?>" class="btn btn-dark w-100 py-3 rounded-pill mt-4 fw-bold shadow-sm">
                    Back to Shopping
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer-bootstrap.php'; ?>