<?php
/**
 * Admin Customers Page
 * Easy Shopping A.R.S
 */
$page_title = "Customers";
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Customers</h1>
</div>

<!-- Stats -->
<div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="kpi-card"><span class="kpi-label">Total Customers</span><div class="kpi-value" id="stat-total">—</div></div>
    <div class="kpi-card"><span class="kpi-label">New This Month</span><div class="kpi-value" id="stat-new">—</div></div>
    <div class="kpi-card"><span class="kpi-label">Active (30 days)</span><div class="kpi-value" id="stat-active">—</div></div>
</div>

<!-- Filters -->
<div class="card" style="padding: 16px; margin-bottom: 20px;">
    <div class="filters-bar" style="margin-bottom:0;">
        <input type="text" id="search-input" class="form-control search-input" placeholder="Search by name, email, mobile...">
        <button class="btn btn-primary" onclick="loadCustomers(1)"><i class="fa-solid fa-search"></i> Search</button>
        <button class="btn btn-ghost" onclick="clearFilters()">Clear</button>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <span id="cust-count" style="color:var(--text-secondary); font-size:13px;">Loading...</span>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Mobile</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Last Order</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="customers-tbody">
                <tr class="loading-row"><td colspan="7"><div class="spinner"></div></td></tr>
            </tbody>
        </table>
    </div>
    <div class="pagination" id="pagination"></div>
</div>

<!-- Customer Detail Modal -->
<div class="modal-overlay" id="cust-modal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 class="modal-title" id="cust-modal-title">Customer Details</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="cust-modal-body">
            <div class="spinner"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<script>
let currentPage = 1;

async function loadStats() {
    const res = await fetch('/backend/customers.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_customer_stats'
    });
    const json = await res.json();
    if (json.success) {
        const s = json.data;
        document.getElementById('stat-total').textContent = s.total_customers;
        document.getElementById('stat-new').textContent = s.new_this_month;
        document.getElementById('stat-active').textContent = s.active_customers;
    }
}

async function loadCustomers(page = 1) {
    currentPage = page;
    document.getElementById('customers-tbody').innerHTML = '<tr class="loading-row"><td colspan="7"><div class="spinner"></div></td></tr>';

    const params = new URLSearchParams({
        action: 'get_customers',
        page,
        limit: 10,
        search: document.getElementById('search-input').value
    });

    const res = await fetch('/backend/customers.php', { method: 'POST', body: params });
    const json = await res.json();
    if (!json.success) { Toast.error(json.message); return; }

    const { data, pagination } = json;
    document.getElementById('cust-count').textContent = `${pagination.total} customers`;

    if (data.length === 0) {
        document.getElementById('customers-tbody').innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-users"></i><p>No customers found.</p></div></td></tr>`;
        document.getElementById('pagination').innerHTML = '';
        return;
    }

    document.getElementById('customers-tbody').innerHTML = data.map(c => `
        <tr>
            <td>
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px; height:36px; border-radius:50%; background:var(--primary); color:white; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; flex-shrink:0;">
                        ${escHtml(c.full_name ? c.full_name[0].toUpperCase() : '?')}
                    </div>
                    <div>
                        <div style="font-weight:500;">${escHtml(c.full_name)}</div>
                        <div style="font-size:12px; color:var(--text-secondary);">${escHtml(c.email)}</div>
                    </div>
                </div>
            </td>
            <td>${escHtml(c.mobile || '—')}</td>
            <td><span class="badge badge-info">${c.total_orders}</span></td>
            <td style="font-weight:600;">Rs. ${parseFloat(c.total_spent || 0).toFixed(2)}</td>
            <td style="color:var(--text-secondary); font-size:13px;">${c.last_order_date ? formatDate(c.last_order_date) : '—'}</td>
            <td style="color:var(--text-secondary); font-size:13px;">${formatDate(c.created_at)}</td>
            <td>
                <button class="btn btn-ghost btn-sm" onclick="viewCustomer(${c.id})">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');

    renderPagination(pagination);
}

async function viewCustomer(id) {
    document.getElementById('cust-modal-title').textContent = 'Customer Details';
    document.getElementById('cust-modal-body').innerHTML = '<div style="text-align:center;padding:40px;"><div class="spinner"></div></div>';
    document.getElementById('cust-modal').classList.add('open');

    const res = await fetch('/backend/customers.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=get_customer_details&customer_id=${id}`
    });
    const json = await res.json();
    if (!json.success) { document.getElementById('cust-modal-body').innerHTML = `<p style="color:var(--danger)">${json.message}</p>`; return; }

    const c = json.data;
    document.getElementById('cust-modal-title').textContent = escHtml(c.full_name);

    const ordersHtml = (c.recent_orders || []).length > 0
        ? c.recent_orders.map(o => `
            <tr>
                <td>#${o.id}</td>
                <td>${o.item_count} items</td>
                <td>Rs. ${parseFloat(o.total_amount).toFixed(2)}</td>
                <td><span class="badge ${badge(o.delivery_status)}">${escHtml(o.delivery_status)}</span></td>
                <td>${formatDate(o.created_at)}</td>
            </tr>`).join('')
        : '<tr><td colspan="5" style="text-align:center; color:var(--text-secondary);">No orders yet.</td></tr>';

    document.getElementById('cust-modal-body').innerHTML = `
        <div class="detail-grid" style="margin-bottom:24px;">
            <div class="detail-item"><label>Full Name</label><span>${escHtml(c.full_name)}</span></div>
            <div class="detail-item"><label>Email</label><span>${escHtml(c.email)}</span></div>
            <div class="detail-item"><label>Mobile</label><span>${escHtml(c.mobile)}</span></div>
            <div class="detail-item"><label>Joined</label><span>${formatDate(c.created_at)}</span></div>
            <div class="detail-item" style="grid-column:1/-1;"><label>Address</label><span>${escHtml(c.address || '—')}</span></div>
        </div>

        <h3 style="margin-bottom:12px;">Recent Orders</h3>
        <div class="table-container">
            <table class="table">
                <thead><tr><th>ID</th><th>Items</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>${ordersHtml}</tbody>
            </table>
        </div>

        ${(c.wishlist || []).length > 0 ? `
        <h3 style="margin:20px 0 12px;">Wishlist</h3>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            ${c.wishlist.map(w => `<div style="font-size:13px; padding:6px 12px; background:var(--gray-100); border-radius:6px;">${escHtml(w.name)}</div>`).join('')}
        </div>` : ''}
    `;
}

function closeModal() { document.getElementById('cust-modal').classList.remove('open'); }

function clearFilters() {
    document.getElementById('search-input').value = '';
    loadCustomers(1);
}

function renderPagination(p) {
    const el = document.getElementById('pagination');
    if (p.pages <= 1) { el.innerHTML = ''; return; }
    let html = `<span class="pagination-info">Page ${p.page} of ${p.pages}</span>`;
    html += `<button class="btn btn-ghost btn-sm" onclick="loadCustomers(${p.page - 1})" ${p.page === 1 ? 'disabled' : ''}><i class="fa-solid fa-chevron-left"></i></button>`;
    for (let i = 1; i <= p.pages; i++) {
        if (i === p.page || i === 1 || i === p.pages || Math.abs(i - p.page) <= 1)
            html += `<button class="btn btn-ghost btn-sm ${i === p.page ? 'active' : ''}" onclick="loadCustomers(${i})">${i}</button>`;
        else if (Math.abs(i - p.page) === 2) html += `<span style="padding:0 4px">…</span>`;
    }
    html += `<button class="btn btn-ghost btn-sm" onclick="loadCustomers(${p.page + 1})" ${p.page === p.pages ? 'disabled' : ''}><i class="fa-solid fa-chevron-right"></i></button>`;
    el.innerHTML = html;
}

function badge(status) {
    const s = (status || '').toLowerCase();
    if (['paid','delivered'].includes(s)) return 'badge-success';
    if (s === 'pending') return 'badge-warning';
    if (['shipped','confirmed'].includes(s)) return 'badge-info';
    if (['failed','cancelled'].includes(s)) return 'badge-danger';
    return 'badge-primary';
}

function formatDate(d) { return d ? new Date(d).toLocaleDateString('en-US', {year:'numeric', month:'short', day:'numeric'}) : '—'; }
function escHtml(str) { if (!str) return ''; return str.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

loadStats();
loadCustomers(1);
document.getElementById('search-input').addEventListener('keypress', e => { if (e.key === 'Enter') loadCustomers(1); });
document.querySelectorAll('.modal-overlay').forEach(o => o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); }));
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
