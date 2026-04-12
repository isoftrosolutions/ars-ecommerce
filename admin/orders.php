<?php
/**
 * Admin Orders Page
 * Easy Shopping A.R.S
 */
$page_title = "Orders";
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Orders</h1>
</div>

<!-- Filters -->
<div class="card" style="padding: 16px; margin-bottom: 20px;">
    <div class="filters-bar" style="margin-bottom: 0;">
        <input type="text" id="search-input" class="form-control search-input" placeholder="Search by order ID, customer name...">
        <select id="status-filter" class="form-control">
            <option value="">All Delivery Status</option>
            <option>Pending</option>
            <option>Confirmed</option>
            <option>Shipped</option>
            <option>Out for Delivery</option>
            <option>Delivered</option>
            <option>Cancelled</option>
        </select>
        <select id="payment-filter" class="form-control">
            <option value="">All Payment Status</option>
            <option>Pending</option>
            <option>Paid</option>
            <option>Failed</option>
            <option>Refunded</option>
        </select>
        <button class="btn btn-primary" onclick="loadOrders(1)"><i class="fa-solid fa-search"></i> Filter</button>
        <button class="btn btn-ghost" onclick="clearFilters()">Clear</button>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <span id="order-count" style="color:var(--text-secondary); font-size:13px;">Loading...</span>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Delivery</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="orders-tbody">
                <tr class="loading-row"><td colspan="7"><div class="spinner"></div></td></tr>
            </tbody>
        </table>
    </div>
    <div class="pagination" id="pagination"></div>
</div>

<!-- Order Detail Modal -->
<div class="modal-overlay" id="order-modal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 class="modal-title" id="order-modal-title">Order Details</h3>
            <button class="modal-close" onclick="closeOrderModal()">&times;</button>
        </div>
        <div class="modal-body" id="order-modal-body">
            <div class="spinner"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeOrderModal()">Close</button>
            <button class="btn btn-primary" onclick="saveOrderStatus()"><i class="fa-solid fa-save"></i> Update Status</button>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let currentOrderId = null;

async function loadOrders(page = 1) {
    currentPage = page;
    document.getElementById('orders-tbody').innerHTML = '<tr class="loading-row"><td colspan="7"><div class="spinner"></div></td></tr>';

    const params = new URLSearchParams({
        page,
        limit: 10,
        search: document.getElementById('search-input').value,
        status: document.getElementById('status-filter').value,
        payment_status: document.getElementById('payment-filter').value
    });

    const res = await fetch(BASE_URL + '/api/orders/list?' + params.toString());
    const json = await res.json();
    if (!json.success) { Toast.error(json.message); return; }

    const { data, pagination } = json;
    document.getElementById('order-count').textContent = `${pagination.total} orders`;

    if (data.length === 0) {
        document.getElementById('orders-tbody').innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-box-open"></i><p>No orders found.</p></div></td></tr>`;
        document.getElementById('pagination').innerHTML = '';
        return;
    }

    document.getElementById('orders-tbody').innerHTML = data.map(o => `
        <tr>
            <td style="font-weight:600;">#${o.id}</td>
            <td>
                <div style="font-weight:500;">${escHtml(o.full_name || 'Guest')}</div>
                <div style="font-size:12px; color:var(--text-secondary);">${escHtml(o.mobile || '')}</div>
            </td>
            <td style="font-weight:600;">Rs. ${parseFloat(o.total_amount).toFixed(2)}</td>
            <td><span class="badge ${badge(o.payment_status)}">${escHtml(o.payment_status)}</span></td>
            <td><span class="badge ${badge(o.delivery_status)}">${escHtml(o.delivery_status)}</span></td>
            <td style="color:var(--text-secondary); font-size:13px;">${formatDate(o.created_at)}</td>
            <td>
                <button class="btn btn-ghost btn-sm" onclick="viewOrder(${o.id})">
                    <i class="fa-solid fa-eye"></i> View
                </button>
            </td>
        </tr>
    `).join('');

    renderPagination(pagination);
}

async function viewOrder(id) {
    currentOrderId = id;
    document.getElementById('order-modal-title').textContent = `Order #${id}`;
    document.getElementById('order-modal-body').innerHTML = '<div style="text-align:center;padding:40px;"><div class="spinner"></div></div>';
    document.getElementById('order-modal').classList.add('open');

    const res = await fetch(BASE_URL + `/api/orders/detail?id=${id}`);
    const json = await res.json();
    if (!json.success) { document.getElementById('order-modal-body').innerHTML = `<p style="color:var(--danger)">${json.message}</p>`; return; }

    const o = json.data;

    const itemsHtml = (o.items || []).map(item => `
        <tr>
            <td>${escHtml(item.name)}</td>
            <td>${escHtml(item.sku)}</td>
            <td>${item.quantity}</td>
            <td>Rs. ${parseFloat(item.price).toFixed(2)}</td>
            <td>Rs. ${(item.price * item.quantity).toFixed(2)}</td>
        </tr>
    `).join('');

    document.getElementById('order-modal-body').innerHTML = `
        <div class="detail-grid" style="margin-bottom:24px;">
            <div class="detail-item"><label>Customer</label><span>${escHtml(o.full_name || 'Guest')}</span></div>
            <div class="detail-item"><label>Mobile</label><span>${escHtml(o.mobile || '—')}</span></div>
            <div class="detail-item"><label>Email</label><span>${escHtml(o.email || '—')}</span></div>
            <div class="detail-item"><label>Order Date</label><span>${formatDate(o.created_at)}</span></div>
            <div class="detail-item"><label>Shipping Address</label><span>${escHtml(o.address || o.user_address || '—')}</span></div>
            <div class="detail-item"><label>Payment Method</label><span>${escHtml(o.payment_method || '—')}</span></div>
            <div class="detail-item"><label>Transaction ID</label><span>${escHtml(o.transaction_id || '—')}</span></div>
            <div class="detail-item"><label>Coupon</label><span>${o.coupon_code ? escHtml(o.coupon_code) + ' (-Rs. ' + parseFloat(o.discount_amount||0).toFixed(2) + ')' : '—'}</span></div>
            ${o.payment_proof ? `<div class="detail-item" style="grid-column: 1 / -1;"><label>Payment Proof</label><div style="margin-top: 8px;"><a href="${BASE_URL}/${escHtml(o.payment_proof)}" target="_blank" title="Click to view full size"><img src="${BASE_URL}/${escHtml(o.payment_proof)}" style="max-height: 150px; border: 1px solid var(--border-color, #e2e8f0); border-radius: 6px; padding: 4px; background: white; object-fit: contain;"></a></div></div>` : ''}
        </div>

        <div class="table-container" style="margin-bottom:24px;">
            <table class="table">
                <thead><tr><th>Product</th><th>SKU</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
                <tbody>${itemsHtml}</tbody>
                <tfoot>
                    <tr style="background:var(--gray-50);">
                        <td colspan="4" style="text-align:right; font-weight:600; padding:12px 16px;">Grand Total</td>
                        <td style="font-weight:700; padding:12px 16px;">Rs. ${parseFloat(o.total_amount).toFixed(2)}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Delivery Status</label>
                <select id="update-delivery-status" class="form-control">
                    ${['Pending','Confirmed','Shipped','Out for Delivery','Delivered','Cancelled'].map(s =>
                        `<option ${o.delivery_status === s ? 'selected' : ''}>${s}</option>`
                    ).join('')}
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Payment Status</label>
                <select id="update-payment-status" class="form-control">
                    ${['Pending','Paid','Failed','Refunded'].map(s =>
                        `<option ${o.payment_status === s ? 'selected' : ''}>${s}</option>`
                    ).join('')}
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Tracking Location</label>
            <input type="text" id="update-location" class="form-control" value="${escHtml(o.current_location || '')}" placeholder="e.g. Out for delivery in Kathmandu">
        </div>
    `;
}

async function saveOrderStatus() {
    if (!currentOrderId) return;

    const deliveryStatus = document.getElementById('update-delivery-status').value;
    const paymentStatus = document.getElementById('update-payment-status').value;
    const location = document.getElementById('update-location').value;

    const [r1, r2] = await Promise.all([
        fetch(BASE_URL + '/api/orders/update-status', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                order_id: currentOrderId,
                status: deliveryStatus,
                current_location: location
            })
        }),
        fetch(BASE_URL + '/api/orders/update-payment-status', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                order_id: currentOrderId,
                payment_status: paymentStatus
            })
        })
    ]);

    const [j1, j2] = await Promise.all([r1.json(), r2.json()]);

    if (j1.success && j2.success) {
        Toast.success('Order updated!');
        closeOrderModal();
        loadOrders(currentPage);
    } else {
        Toast.error(j1.message || j2.message);
    }
}

function closeOrderModal() { document.getElementById('order-modal').classList.remove('open'); currentOrderId = null; }

function clearFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('payment-filter').value = '';
    loadOrders(1);
}

function renderPagination(p) {
    const el = document.getElementById('pagination');
    if (p.pages <= 1) { el.innerHTML = ''; return; }
    let html = `<span class="pagination-info">Page ${p.page} of ${p.pages}</span>`;
    html += `<button class="btn btn-ghost btn-sm" onclick="loadOrders(${p.page - 1})" ${p.page === 1 ? 'disabled' : ''}><i class="fa-solid fa-chevron-left"></i></button>`;
    for (let i = 1; i <= p.pages; i++) {
        if (i === p.page || i === 1 || i === p.pages || Math.abs(i - p.page) <= 1) {
            html += `<button class="btn btn-ghost btn-sm ${i === p.page ? 'active' : ''}" onclick="loadOrders(${i})">${i}</button>`;
        } else if (Math.abs(i - p.page) === 2) html += `<span style="padding:0 4px">…</span>`;
    }
    html += `<button class="btn btn-ghost btn-sm" onclick="loadOrders(${p.page + 1})" ${p.page === p.pages ? 'disabled' : ''}><i class="fa-solid fa-chevron-right"></i></button>`;
    el.innerHTML = html;
}

function badge(status) {
    const s = (status || '').toLowerCase();
    if (['paid','delivered','approved'].includes(s)) return 'badge-success';
    if (['pending'].includes(s)) return 'badge-warning';
    if (['shipped','confirmed','out for delivery'].includes(s)) return 'badge-info';
    if (['failed','cancelled','rejected'].includes(s)) return 'badge-danger';
    return 'badge-primary';
}

function formatDate(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-US', {year:'numeric', month:'short', day:'numeric'});
}

function escHtml(str) {
    if (!str) return '';
    return str.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Init
loadOrders(1);
document.getElementById('search-input').addEventListener('keypress', e => { if (e.key === 'Enter') loadOrders(1); });
document.querySelectorAll('.modal-overlay').forEach(o => o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); }));
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
