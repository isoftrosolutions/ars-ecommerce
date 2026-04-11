<?php
/**
 * Admin Dashboard
 * Easy Shopping A.R.S
 */
$page_title = "Dashboard";
include __DIR__ . '/includes/header.php';

// Fetch Statistics
try {
    // Total Products
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $total_products = $stmt->fetchColumn();

    // Total Orders
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $total_orders = $stmt->fetchColumn();

    // Total Revenue (Paid Orders)
    $stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'Paid'");
    $total_revenue = $stmt->fetchColumn() ?: 0;

    // Total Customers
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
    $total_customers = $stmt->fetchColumn();

    // --- Revenue Trend: this month vs last month ---
    $stmt = $pdo->query("
        SELECT
            COALESCE(SUM(CASE WHEN DATE_FORMAT(created_at,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m') THEN total_amount ELSE 0 END), 0) AS this_month,
            COALESCE(SUM(CASE WHEN DATE_FORMAT(created_at,'%Y-%m') = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH),'%Y-%m') THEN total_amount ELSE 0 END), 0) AS last_month
        FROM orders WHERE payment_status = 'Paid'
    ");
    $rev = $stmt->fetch();
    $revenue_pct = $rev['last_month'] > 0
        ? round(($rev['this_month'] - $rev['last_month']) / $rev['last_month'] * 100)
        : ($rev['this_month'] > 0 ? 100 : 0);

    // --- Orders Trend: this month vs last month ---
    $stmt = $pdo->query("
        SELECT
            SUM(CASE WHEN DATE_FORMAT(created_at,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m') THEN 1 ELSE 0 END) AS this_month,
            SUM(CASE WHEN DATE_FORMAT(created_at,'%Y-%m') = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH),'%Y-%m') THEN 1 ELSE 0 END) AS last_month
        FROM orders
    ");
    $ord = $stmt->fetch();
    $orders_pct = $ord['last_month'] > 0
        ? round(($ord['this_month'] - $ord['last_month']) / $ord['last_month'] * 100)
        : ($ord['this_month'] > 0 ? 100 : 0);

    // --- New customers this week ---
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'customer' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->execute();
    $new_customers_week = (int)$stmt->fetchColumn();

    // Recent Orders
    $stmt = $pdo->query("SELECT o.*, u.full_name FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC LIMIT 5");
    $recent_orders = $stmt->fetchAll();

} catch (PDOException $e) {
    $total_products = 0;
    $total_orders = 0;
    $total_revenue = 0;
    $total_customers = 0;
    $recent_orders = [];
    $revenue_pct = 0;
    $orders_pct = 0;
    $new_customers_week = 0;
    error_log('[ARS] Dashboard query error: ' . $e->getMessage());
    $db_error = 'Database query failed. Please ensure db.sql is imported.';
}

// Helpers for trend display
function trend_class(int $pct): string { return $pct >= 0 ? 'positive' : 'negative'; }
function trend_icon(int $pct): string  { return $pct >= 0 ? 'fa-caret-up' : 'fa-caret-down'; }
function trend_label(int $pct, string $context = 'from last month'): string {
    return abs($pct) . '% ' . ($pct >= 0 ? 'up' : 'down') . ' ' . $context;
}
?>

<div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1>Overview</h1>
    <a href="/admin/products" class="btn btn-primary">+ Add New Product</a>
</div>

<?php if (isset($db_error)): ?>
    <div class="card" style="border-left: 4px solid var(--danger);">
        <p style="color: var(--danger);"><strong>Note:</strong> <?php echo h($db_error); ?></p>
    </div>
<?php endif; ?>

<!-- KPI Grid -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <span class="kpi-label">Total Revenue</span>
            <i class="fa-solid fa-money-bill-trend-up" style="color: var(--success); font-size: 20px;"></i>
        </div>
        <div class="kpi-value"><?php echo format_price($total_revenue); ?></div>
        <div class="kpi-change <?php echo trend_class($revenue_pct); ?>">
            <i class="fa-solid <?php echo trend_icon($revenue_pct); ?>"></i>
            <?php echo trend_label($revenue_pct); ?>
        </div>
    </div>
    
    <div class="kpi-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <span class="kpi-label">Orders</span>
            <i class="fa-solid fa-cart-shopping" style="color: var(--primary); font-size: 20px;"></i>
        </div>
        <div class="kpi-value"><?php echo $total_orders; ?></div>
        <div class="kpi-change <?php echo trend_class($orders_pct); ?>">
            <i class="fa-solid <?php echo trend_icon($orders_pct); ?>"></i>
            <?php echo trend_label($orders_pct); ?>
        </div>
    </div>
    
    <div class="kpi-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <span class="kpi-label">Products</span>
            <i class="fa-solid fa-box" style="color: #7C3AED; font-size: 20px;"></i>
        </div>
        <div class="kpi-value"><?php echo $total_products; ?></div>
        <div class="kpi-change">Active in shop</div>
    </div>
    
    <div class="kpi-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <span class="kpi-label">Customers</span>
            <i class="fa-solid fa-users" style="color: var(--warning); font-size: 20px;"></i>
        </div>
        <div class="kpi-value"><?php echo $total_customers; ?></div>
        <div class="kpi-change <?php echo $new_customers_week > 0 ? 'positive' : ''; ?>">
            <?php if ($new_customers_week > 0): ?>
                <i class="fa-solid fa-caret-up"></i>
            <?php endif; ?>
            <?php echo $new_customers_week > 0 ? $new_customers_week . ' new this week' : 'No new this week'; ?>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Orders</h3>
            <a href="/admin/orders" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recent_orders) > 0): ?>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo h($order['full_name'] ?? 'Guest'); ?></td>
                                <td><?php echo format_price($order['total_amount']); ?></td>
                                <td><span class="badge <?php echo get_status_badge($order['delivery_status']); ?>"><?php echo $order['delivery_status']; ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 40px;">No recent orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Shop Status -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Shop Status</h3>
        </div>
        <div class="status-summary" style="display: flex; flex-direction: column; gap: 16px;">
            <div style="display: flex; justify-content: space-between;">
                <span>Delivery Status</span>
                <span class="badge badge-success">Online</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>COD Payment</span>
                <span class="badge badge-success">Enabled</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Digital Payment</span>
                <span class="badge badge-info">eSewa/QR Active</span>
            </div>
            <hr style="border: none; border-top: 1px solid var(--border-color);">
            <p class="text-sm" style="color: var(--text-secondary);">
                Welcome to your command center. From here you can manage all aspects of <strong>Easy Shopping A.R.S</strong>.
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
