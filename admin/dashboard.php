<?php
/**
 * Admin Dashboard
 * Easy Shopping A.R.S
 */
$page_title = "Dashboard";
include __DIR__ . '/includes/header.php';
?>

<div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1>Overview</h1>
    <div style="display: flex; gap: 12px;">
        <button class="btn btn-ghost" onclick="fetchStats()" title="Refresh Data">
            <i class="fa-solid fa-rotate"></i>
        </button>
        <a href="<?php echo url('/admin/products.php'); ?>" class="btn btn-primary">+ Add New Product</a>
    </div>
</div>

<!-- KPI Grid -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <span class="kpi-label">Total Revenue</span>
            <i class="fa-solid fa-money-bill-trend-up" style="color: var(--success); font-size: 20px;"></i>
        </div>
        <div class="kpi-value" id="stat-revenue"><div class="spinner spinner-sm"></div></div>
        <div class="kpi-change" id="stat-revenue-trend">—</div>
    </div>
    
    <div class="kpi-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <span class="kpi-label">Orders</span>
            <i class="fa-solid fa-cart-shopping" style="color: var(--primary); font-size: 20px;"></i>
        </div>
        <div class="kpi-value" id="stat-orders"><div class="spinner spinner-sm"></div></div>
        <div class="kpi-change" id="stat-orders-trend">—</div>
    </div>
    
    <div class="kpi-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <span class="kpi-label">Products</span>
            <i class="fa-solid fa-box" style="color: #7C3AED; font-size: 20px;"></i>
        </div>
        <div class="kpi-value" id="stat-products"><div class="spinner spinner-sm"></div></div>
        <div class="kpi-change">Active in shop</div>
    </div>
    
    <div class="kpi-card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <span class="kpi-label">Customers</span>
            <i class="fa-solid fa-users" style="color: var(--warning); font-size: 20px;"></i>
        </div>
        <div class="kpi-value" id="stat-customers"><div class="spinner spinner-sm"></div></div>
        <div class="kpi-change" id="stat-customers-trend">—</div>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Orders</h3>
            <a href="<?php echo url('/admin/orders.php'); ?>" class="btn btn-ghost btn-sm">View All</a>
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
                <tbody id="recent-orders-tbody">
                    <tr><td colspan="5" style="text-align:center; padding:40px;"><div class="spinner"></div></td></tr>
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
                <span>Delivery Service</span>
                <span class="badge badge-success">Online</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>COD Payment</span>
                <span class="badge badge-success">Enabled</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Digital Payment</span>
                <span class="badge badge-info" id="payment-status">eSewa/QR Active</span>
            </div>
            <hr style="border: none; border-top: 1px solid var(--border-color);">
            <div id="order-status-breakdown" style="display:flex; flex-direction:column; gap:8px;">
                <!-- Filled via JS -->
            </div>
        </div>
    </div>
</div>

<script>
async function fetchStats() {
    try {
        const json = await apiFetch('/api/dashboard/stats');
        
        if (!json.success) {
            Toast.error(json.message);
            return;
        }
        
        const s = json.data;
        
        // Update KPIs
        document.getElementById('stat-revenue').textContent = formatPrice(s.total_revenue);
        renderTrend('stat-revenue-trend', s.revenue_trend.change_percent);
        
        document.getElementById('stat-orders').textContent = s.total_orders;
        renderTrend('stat-orders-trend', s.orders_trend.change_percent);
        
        document.getElementById('stat-products').textContent = s.total_products;
        document.getElementById('stat-customers').textContent = s.total_customers;
        
        const newCust = s.new_customers_week;
        document.getElementById('stat-customers-trend').innerHTML = newCust > 0 
            ? `<span class="positive"><i class="fa-solid fa-caret-up"></i> ${newCust} new this week</span>`
            : `<span style="color:var(--text-secondary)">No new this week</span>`;
            
        // Recent Orders
        const tbody = document.getElementById('recent-orders-tbody');
        if (s.recent_orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:40px; color:var(--text-secondary);">No recent orders.</td></tr>';
        } else {
            tbody.innerHTML = s.recent_orders.map(o => `
                <tr>
                    <td style="font-weight:600;">#${o.id}</td>
                    <td>${escHtml(o.full_name || 'Guest')}</td>
                    <td style="font-weight:600;">${formatPrice(o.total_amount)}</td>
                    <td><span class="badge ${getBadgeClass(o.delivery_status)}">${o.delivery_status}</span></td>
                    <td style="color:var(--text-secondary); font-size:13px;">${formatDate(o.created_at)}</td>
                </tr>
            `).join('');
        }
        
        // Status Breakdown
        const breakdown = document.getElementById('order-status-breakdown');
        breakdown.innerHTML = Object.entries(s.order_status_breakdown).map(([status, count]) => `
            <div style="display:flex; justify-content:space-between; font-size:13px;">
                <span style="color:var(--text-secondary)">${status}</span>
                <span style="font-weight:600;">${count}</span>
            </div>
        `).join('');
        
    } catch (e) {
        console.error(e);
        Toast.error('Failed to load dashboard statistics');
    }
}

function renderTrend(id, pct) {
    const el = document.getElementById(id);
    const cls = pct >= 0 ? 'positive' : 'negative';
    const icon = pct >= 0 ? 'fa-caret-up' : 'fa-caret-down';
    el.className = 'kpi-change ' + cls;
    el.innerHTML = `<i class="fa-solid ${icon}"></i> ${Math.abs(pct)}% ${pct >= 0 ? 'up' : 'down'} from last month`;
}

function getBadgeClass(status) {
    const s = (status || '').toLowerCase();
    if (['paid','delivered','approved'].includes(s)) return 'badge-success';
    if (['pending'].includes(s)) return 'badge-warning';
    if (['shipped','confirmed','out for delivery'].includes(s)) return 'badge-info';
    if (['failed','cancelled','rejected'].includes(s)) return 'badge-danger';
    return 'badge-primary';
}

function formatPrice(val) {
    return 'Rs. ' + parseFloat(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function formatDate(d) {
    return new Date(d).toLocaleDateString('en-US', {month:'short', day:'numeric'});
}

function escHtml(str) {
    if (!str) return '';
    return str.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Initialize
fetchStats();
</script>

<style>
.spinner-sm { width: 14px; height: 14px; border-width: 2px; }
.positive { color: var(--success); font-weight: 500; }
.negative { color: var(--danger); font-weight: 500; }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
