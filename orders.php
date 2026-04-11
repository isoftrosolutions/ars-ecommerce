<?php
/**
 * User Orders History Page
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
} catch (Exception $e) { $orders = []; }
?>

<style>
/* ═══ Orders Responsiveness ═══ */
.profile-nav-mobile {
    display: none;
    background: white;
    margin-bottom: 20px;
    border-radius: 12px;
    padding: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.profile-nav-mobile a {
    flex: 1;
    text-align: center;
    color: #64748b;
    text-decoration: none;
    font-size: 0.75rem;
    padding: 8px 0;
}
.profile-nav-mobile a.active {
    color: var(--primary-color);
    font-weight: 700;
}
.profile-nav-mobile i {
    display: block;
    font-size: 1.25rem;
    margin-bottom: 2px;
}

@media (max-width: 991px) {
    .profile-sidebar-desktop { display: none; }
    .profile-nav-mobile { display: flex; }
    .order-header { flex-direction: column; align-items: flex-start !important; }
    .order-header .btn { width: 100%; margin-top: 10px; }
}

.order-card {
    border-radius: 12px;
    transition: all 0.2s;
    border: 1px solid #eee !important;
}
.order-card:hover { border-color: var(--primary-color) !important; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }

.status-grid .card { border-radius: 12px; border: none; background: #f8fafc; }
</style>

<div class="container py-4">
    <!-- Mobile Nav -->
    <div class="profile-nav-mobile shadow-sm border">
        <a href="<?php echo url('/profile'); ?>">
            <i class="bi bi-person"></i> Profile
        </a>
        <a href="<?php echo url('/orders'); ?>" class="active">
            <i class="bi bi-bag-check-fill"></i> Orders
        </a>
        <a href="<?php echo url('/wishlist'); ?>">
            <i class="bi bi-heart"></i> Wishlist
        </a>
        <a href="<?php echo url('/backend/logout.php'); ?>" class="text-danger">
            <i class="bi bi-box-arrow-right"></i> Exit
        </a>
    </div>

    <div class="row">
        <!-- Desktop Sidebar -->
        <div class="col-lg-3 profile-sidebar-desktop">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-body text-center py-4 bg-light">
                    <div class="mb-3">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white" style="width: 70px; height: 70px; font-size: 1.8rem; font-weight:700;">
                            <?php echo strtoupper(substr($user['full_name'] ?? 'U', 0, 1)); ?>
                        </div>
                    </div>
                    <h6 class="mb-1 fw-bold"><?php echo h($user['full_name']); ?></h6>
                    <p class="text-muted small mb-0"><?php echo h($user['email']); ?></p>
                </div>
                <div class="list-group list-group-flush small">
                    <a href="<?php echo url('/profile'); ?>" class="list-group-item list-group-item-action py-3 border-0">
                        <i class="bi bi-person me-2"></i> Account Settings
                    </a>
                    <a href="<?php echo url('/orders'); ?>" class="list-group-item list-group-item-action py-3 active border-0">
                        <i class="bi bi-bag-check me-2"></i> Order History
                    </a>
                    <a href="<?php echo url('/wishlist'); ?>" class="list-group-item list-group-item-action py-3 border-0">
                        <i class="bi bi-heart me-2"></i> My Wishlist
                    </a>
                    <a href="<?php echo url('/backend/logout.php'); ?>" class="list-group-item list-group-item-action py-3 border-0 text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Orders Main -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4 order-header">
                <div>
                    <h4 class="fw-bold mb-0">My Orders</h4>
                    <span class="text-muted small">Manage your recent purchases</span>
                </div>
                <a href="<?php echo url('/shop'); ?>" class="btn btn-outline-dark rounded-pill px-4 btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> New Order
                </a>
            </div>

            <?php if (empty($orders)): ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                    <i class="bi bi-bag-x display-1 text-muted opacity-25"></i>
                    <h5 class="mt-3 fw-bold">No orders found</h5>
                    <p class="text-muted mb-4">Looks like you haven't placed any orders yet.</p>
                    <a href="<?php echo url('/shop'); ?>" class="btn btn-primary px-5 rounded-pill">Start Shopping</a>
                </div>
            <?php else: ?>
                <?php foreach($orders as $order): ?>
                    <div class="card order-card mb-3 shadow-none">
                        <div class="card-body">
                            <div class="row align-items-center g-3">
                                <div class="col-6 col-md-3">
                                    <div class="small text-muted mb-1">Order ID</div>
                                    <div class="fw-bold fs-6">#<?php echo $order['id']; ?></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="small text-muted mb-1">Date</div>
                                    <div class="fw-bold small"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <?php
                                        $s = $order['status'];
                                        $c = ($s=='delivered')?'success':(($s=='cancelled')?'danger':'warning');
                                    ?>
                                    <div class="small text-muted mb-1">Status</div>
                                    <span class="badge rounded-pill bg-<?php echo $c; ?>-soft text-<?php echo $c; ?> px-3" style="background:rgba(var(--bs-<?php echo $c; ?>-rgb), 0.1); border:1px solid rgba(var(--bs-<?php echo $c; ?>-rgb), 0.2);">
                                        <?php echo ucfirst($s); ?>
                                    </span>
                                </div>
                                <div class="col-6 col-md-3 text-md-end">
                                    <div class="small text-muted mb-1">Total</div>
                                    <div class="fw-bold fs-5 text-dark">Rs. <?php echo number_format($order['total_amount'], 0); ?></div>
                                </div>
                            </div>
                            <hr class="my-3 opacity-50">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted text-truncate small" style="max-width: 70%;">
                                    <i class="bi bi-box-seam me-1"></i> <?php echo h($order['product_names']); ?>
                                </div>
                                <a href="<?php echo url('/order.php?id=' . $order['id']); ?>" class="btn btn-light btn-sm rounded-pill px-3 fw-bold">Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer-bootstrap.php'; ?>