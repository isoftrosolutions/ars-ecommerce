<?php
/**
 * Admin Billing/Invoices Dashboard
 * Easy Shopping A.R.S
 */
$page_title = "Billing & Invoices";
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Billing & Invoices</h1>
    <div style="display: flex; gap: 12px;">
        <button class="btn btn-ghost" onclick="loadBilling()"><i class="fa-solid fa-rotate"></i> Refresh</button>
    </div>
</div>

<!-- Stats -->
<div class="kpi-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="kpi-card"><span class="kpi-label">Total Revenue</span><div class="kpi-value" id="stat-revenue">—</div></div>
    <div class="kpi-card"><span class="kpi-label">This Month</span><div class="kpi-value" id="stat-revenue-month">—</div></div>
    <div class="kpi-card"><span class="kpi-label">Paid Invoices</span><div class="kpi-value" id="stat-paid">—</div></div>
    <div class="kpi-card"><span class="kpi-label">Pending Payment</span><div class="kpi-value" id="stat-pending">—</div></div>
</div>

<!-- Filters -->
<div class="card" style="padding: 16px; margin-bottom: 20px;">
    <div class="filters-bar" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
        <input type="text" id="search-input" class="form-control" style="max-width: 300px;" placeholder="Search by order ID, customer name, email...">
        <select id="filter-status" class="form-control" style="max-width: 180px;">
            <option value="">All Payment Status</option>
            <option value="Pending">Pending</option>
            <option value="Paid">Paid</option>
            <option value="Failed">Failed</option>
            <option value="Refunded">Refunded</option>
        </select>
        <select id="filter-delivery" class="form-control" style="max-width: 180px;">
            <option value="">All Delivery Status</option>
            <option value="Pending">Pending</option>
            <option value="Processing">Processing</option>
            <option value="Shipped">Shipped</option>
            <option value="Delivered">Delivered</option>
            <option value="Cancelled">Cancelled</option>
        </select>
        <input type="date" id="filter-date-from" class="form-control" style="max-width: 160px;" placeholder="From Date">
        <input type="date" id="filter-date-to" class="form-control" style="max-width: 160px;" placeholder="To Date">
        <button class="btn btn-primary" onclick="loadBilling(1)"><i class="fa-solid fa-search"></i> Filter</button>
        <button class="btn btn-ghost" onclick="clearFilters()">Clear</button>
    </div>
</div>

<!-- Invoices Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Invoices</h3>
        <button class="btn btn-sm btn-ghost" onclick="exportBilling()"><i class="fa-solid fa-download"></i> Export CSV</button>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Delivery</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="billing-tbody">
                <tr class="loading-row"><td colspan="8"><div class="spinner"></div></td></tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center;">
        <span id="billing-count" style="color: var(--text-secondary); font-size: 13px;">Loading...</span>
        <div class="pagination" id="billing-pagination"></div>
    </div>
</div>

<script>
let currentPage = 1;
let billingData = [];

async function loadBilling(page = 1) {
    currentPage = page;
    const params = new URLSearchParams({
        page: page,
        limit: 20,
        search: document.getElementById('search-input').value,
        payment_status: document.getElementById('filter-status').value,
        delivery_status: document.getElementById('filter-delivery').value,
        date_from: document.getElementById('filter-date-from').value,
        date_to: document.getElementById('filter-date-to').value
    });

    const json = await apiFetch('/api/billing/list?' + params.toString());
    
    if (!json.success) {
        document.getElementById('billing-tbody').innerHTML = '<tr><td colspan="8" style="text-align:center; padding:40px; color:var(--danger);">' + json.message + '</td></tr>';
        return;
    }

    billingData = json.data || [];
    renderBilling();
    loadBillingStats();
}

function renderBilling() {
    const tbody = document.getElementById('billing-tbody');
    
    if (!billingData.length) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding:40px;">No invoices found</td></tr>';
        document.getElementById('billing-count').textContent = '0 invoices';
        return;
    }

    tbody.innerHTML = billingData.map(o => {
        const badgeClass = getBadgeClass(o.payment_status);
        const deliveryBadge = getDeliveryBadge(o.delivery_status);
        const date = new Date(o.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        
        return `
            <tr>
                <td><strong>INV-${String(o.id).padStart(5, '0')}</strong></td>
                <td><a href="<?php echo url('/admin/orders.php'); ?>?search=${o.id}" class="link">#${o.id}</a></td>
                <td>
                    <div style="font-weight: 500;">${escHtml(o.customer_name)}</div>
                    <div style="font-size: 12px; color: var(--text-secondary);">${escHtml(o.customer_email)}</div>
                </td>
                <td><strong>Rs. ${parseFloat(o.total_amount).toLocaleString()}</strong></td>
                <td><span class="badge ${badgeClass}">${o.payment_status}</span></td>
                <td><span class="badge ${deliveryBadge}">${o.delivery_status}</span></td>
                <td>${date}</td>
                <td>
                    <a href="<?php echo url('/invoice?id='); ?>${o.id}" target="_blank" class="btn btn-sm btn-ghost" title="View Invoice"><i class="fa-solid fa-file-invoice"></i></a>
                    <button class="btn btn-sm btn-ghost" onclick="printInvoice(${o.id})" title="Print"><i class="fa-solid fa-print"></i></button>
                </td>
            </tr>
        `;
    }).join('');

    document.getElementById('billing-count').textContent = '${billingData.length} invoices';
}

function getBadgeClass(status) {
    const map = {
        'Pending': 'bg-warning',
        'Paid': 'bg-success',
        'Failed': 'bg-danger',
        'Refunded': 'bg-secondary'
    };
    return map[status] || 'bg-secondary';
}

function getDeliveryBadge(status) {
    const map = {
        'Pending': 'bg-secondary',
        'Processing': 'bg-info',
        'Shipped': 'bg-primary',
        'Delivered': 'bg-success',
        'Cancelled': 'bg-danger'
    };
    return map[status] || 'bg-secondary';
}

async function loadBillingStats() {
    const json = await apiFetch('/api/billing/stats');
    
    if (!json.success) return;
    
    const stats = json.data;
    document.getElementById('stat-revenue').textContent = 'Rs. ' + parseFloat(stats.total_revenue || 0).toLocaleString();
    document.getElementById('stat-revenue-month').textContent = 'Rs. ' + parseFloat(stats.month_revenue || 0).toLocaleString();
    document.getElementById('stat-paid').textContent = stats.paid_count || 0;
    document.getElementById('stat-pending').textContent = stats.pending_count || 0;
}

function clearFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('filter-status').value = '';
    document.getElementById('filter-delivery').value = '';
    document.getElementById('filter-date-from').value = '';
    document.getElementById('filter-date-to').value = '';
    loadBilling(1);
}

function exportBilling() {
    let csv = 'Invoice #,Order ID,Customer,Email,Amount,Payment Status,Delivery Status,Date\n';
    billingData.forEach(o => {
        csv += `INV-${String(o.id).padStart(5, '0')},${o.id},"${o.customer_name}","${o.customer_email}",${o.total_amount},${o.payment_status},${o.delivery_status},${o.created_at}\n`;
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'billing-export-' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
}

function printInvoice(orderId) {
    window.open('<?php echo url('/invoice?id='); ?>' + orderId, '_blank');
}

// Helper
function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Initial load
loadBilling();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>