<?php
/**
 * Admin Coupons Page
 * Easy Shopping A.R.S
 */
$page_title = "Coupons";
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Coupons</h1>
    <button class="btn btn-primary" onclick="openModal()">
        <i class="fa-solid fa-plus"></i> Add Coupon
    </button>
</div>

<!-- Stats -->
<div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="kpi-card"><span class="kpi-label">Total Coupons</span><div class="kpi-value" id="stat-total">—</div></div>
    <div class="kpi-card"><span class="kpi-label">Active</span><div class="kpi-value" id="stat-active" style="color:var(--success);">—</div></div>
    <div class="kpi-card"><span class="kpi-label">Total Discount Given</span><div class="kpi-value" id="stat-discount">—</div></div>
</div>

<!-- Filters -->
<div class="card" style="padding: 16px; margin-bottom: 20px;">
    <div class="filters-bar" style="margin-bottom:0;">
        <input type="text" id="search-input" class="form-control search-input" placeholder="Search by code...">
        <select id="status-filter" class="form-control">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        <select id="type-filter" class="form-control">
            <option value="">All Types</option>
            <option value="fixed">Fixed</option>
            <option value="percentage">Percentage</option>
        </select>
        <button class="btn btn-primary" onclick="loadCoupons(1)"><i class="fa-solid fa-search"></i> Filter</button>
        <button class="btn btn-ghost" onclick="clearFilters()">Clear</button>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <span id="coupon-count" style="color:var(--text-secondary); font-size:13px;">Loading...</span>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Min Cart</th>
                    <th>Times Used</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="coupons-tbody">
                <tr class="loading-row"><td colspan="8"><div class="spinner"></div></td></tr>
            </tbody>
        </table>
    </div>
    <div class="pagination" id="pagination"></div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="coupon-modal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add Coupon</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="coupon-id">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Coupon Code *</label>
                    <input type="text" id="coupon-code" class="form-control" placeholder="e.g. SAVE20" style="text-transform:uppercase;">
                </div>
                <div class="form-group">
                    <label class="form-label">Type *</label>
                    <select id="coupon-type" class="form-control">
                        <option value="fixed">Fixed Amount</option>
                        <option value="percentage">Percentage</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Discount Value *</label>
                    <input type="number" id="coupon-value" class="form-control" placeholder="0" min="0" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label">Min Cart Amount (Rs.)</label>
                    <input type="number" id="coupon-min" class="form-control" placeholder="0" min="0" step="0.01">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Expiry Date</label>
                    <input type="date" id="coupon-expiry" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select id="coupon-status" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="saveCoupon()"><i class="fa-solid fa-save"></i> Save Coupon</button>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="delete-modal">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3 class="modal-title">Delete Coupon</h3>
            <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body"><p>Delete this coupon? Coupons that have been used in orders cannot be deleted.</p></div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn btn-danger" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let deleteTargetId = null;

async function loadStats() {
    const res = await fetch('/backend/coupons.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_stats'
    });
    const json = await res.json();
    if (json.success) {
        const s = json.data;
        document.getElementById('stat-total').textContent = s.total_coupons;
        document.getElementById('stat-active').textContent = s.active_coupons;
        document.getElementById('stat-discount').textContent = 'Rs. ' + parseFloat(s.total_discount_given || 0).toFixed(2);
    }
}

async function loadCoupons(page = 1) {
    currentPage = page;
    document.getElementById('coupons-tbody').innerHTML = '<tr class="loading-row"><td colspan="8"><div class="spinner"></div></td></tr>';

    const params = new URLSearchParams({
        action: 'get_coupons', page, limit: 10,
        search: document.getElementById('search-input').value,
        status: document.getElementById('status-filter').value,
        type: document.getElementById('type-filter').value
    });

    const res = await fetch('/backend/coupons.php', { method: 'POST', body: params });
    const json = await res.json();
    if (!json.success) { Toast.error(json.message); return; }

    const { data, pagination } = json;
    document.getElementById('coupon-count').textContent = `${pagination.total} coupons`;

    if (data.length === 0) {
        document.getElementById('coupons-tbody').innerHTML = `<tr><td colspan="8"><div class="empty-state"><i class="fa-solid fa-ticket"></i><p>No coupons found.</p></div></td></tr>`;
        document.getElementById('pagination').innerHTML = '';
        return;
    }

    document.getElementById('coupons-tbody').innerHTML = data.map(c => `
        <tr>
            <td><code style="background:var(--primary-light); color:var(--primary); padding:3px 10px; border-radius:4px; font-size:13px; font-weight:700;">${escHtml(c.code)}</code></td>
            <td><span class="badge badge-info">${c.type}</span></td>
            <td style="font-weight:600;">${c.type === 'percentage' ? c.value + '%' : 'Rs. ' + parseFloat(c.value).toFixed(2)}</td>
            <td>${parseFloat(c.min_cart_amount || 0) > 0 ? 'Rs. ' + parseFloat(c.min_cart_amount).toFixed(2) : '—'}</td>
            <td>${c.times_used}</td>
            <td style="color:var(--text-secondary); font-size:13px;">${c.expiry_date ? formatDate(c.expiry_date) : 'Never'}</td>
            <td>
                <button class="btn btn-ghost btn-sm" onclick="toggleStatus(${c.id}, '${c.status === 'active' ? 'inactive' : 'active'}')">
                    <span class="badge ${c.status === 'active' ? 'badge-success' : 'badge-danger'}">${c.status}</span>
                </button>
            </td>
            <td>
                <div class="table-actions">
                    <button class="btn btn-ghost btn-sm" onclick="editCoupon(${c.id})"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-ghost btn-sm" onclick="openDeleteModal(${c.id})"><i class="fa-solid fa-trash" style="color:var(--danger)"></i></button>
                </div>
            </td>
        </tr>
    `).join('');

    renderPagination(pagination);
}

function openModal() {
    document.getElementById('coupon-id').value = '';
    document.getElementById('coupon-code').value = '';
    document.getElementById('coupon-type').value = 'fixed';
    document.getElementById('coupon-value').value = '';
    document.getElementById('coupon-min').value = '';
    document.getElementById('coupon-expiry').value = '';
    document.getElementById('coupon-status').value = 'active';
    document.getElementById('modal-title').textContent = 'Add Coupon';
    document.getElementById('coupon-modal').classList.add('open');
}

async function editCoupon(id) {
    const res = await fetch('/backend/coupons.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=get_coupon&id=${id}`
    });
    const json = await res.json();
    if (!json.success) { Toast.error(json.message); return; }
    const c = json.data;
    document.getElementById('coupon-id').value = c.id;
    document.getElementById('coupon-code').value = c.code;
    document.getElementById('coupon-type').value = c.type;
    document.getElementById('coupon-value').value = c.value;
    document.getElementById('coupon-min').value = c.min_cart_amount || '';
    document.getElementById('coupon-expiry').value = c.expiry_date || '';
    document.getElementById('coupon-status').value = c.status;
    document.getElementById('modal-title').textContent = 'Edit Coupon';
    document.getElementById('coupon-modal').classList.add('open');
}

async function saveCoupon() {
    const code = document.getElementById('coupon-code').value.trim();
    const value = document.getElementById('coupon-value').value;
    if (!code || !value) { Toast.error('Code and Value are required.'); return; }

    const params = new URLSearchParams({ action: 'save_coupon' });
    const id = document.getElementById('coupon-id').value;
    if (id) params.append('coupon[id]', id);
    params.append('coupon[code]', code);
    params.append('coupon[type]', document.getElementById('coupon-type').value);
    params.append('coupon[value]', value);
    params.append('coupon[min_cart_amount]', document.getElementById('coupon-min').value || 0);
    params.append('coupon[expiry_date]', document.getElementById('coupon-expiry').value);
    params.append('coupon[status]', document.getElementById('coupon-status').value);

    const res = await fetch('/backend/coupons.php', { method: 'POST', body: params });
    const json = await res.json();
    if (json.success) { Toast.success(id ? 'Coupon updated!' : 'Coupon added!'); closeModal(); loadCoupons(currentPage); loadStats(); }
    else Toast.error(json.message);
}

async function toggleStatus(id, newStatus) {
    const res = await fetch('/backend/coupons.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=toggle_status&id=${id}&status=${newStatus}`
    });
    const json = await res.json();
    if (json.success) { loadCoupons(currentPage); loadStats(); }
    else Toast.error(json.message);
}

function openDeleteModal(id) { deleteTargetId = id; document.getElementById('delete-modal').classList.add('open'); }
function closeDeleteModal() { deleteTargetId = null; document.getElementById('delete-modal').classList.remove('open'); }
function closeModal() { document.getElementById('coupon-modal').classList.remove('open'); }

async function confirmDelete() {
    if (!deleteTargetId) return;
    const res = await fetch('/backend/coupons.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=delete_coupon&id=${deleteTargetId}`
    });
    const json = await res.json();
    closeDeleteModal();
    if (json.success) { Toast.success('Coupon deleted.'); loadCoupons(currentPage); loadStats(); }
    else Toast.error(json.message);
}

function clearFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('type-filter').value = '';
    loadCoupons(1);
}

function renderPagination(p) {
    const el = document.getElementById('pagination');
    if (p.pages <= 1) { el.innerHTML = ''; return; }
    let html = `<span class="pagination-info">Page ${p.page} of ${p.pages}</span>`;
    html += `<button class="btn btn-ghost btn-sm" onclick="loadCoupons(${p.page-1})" ${p.page===1?'disabled':''}><i class="fa-solid fa-chevron-left"></i></button>`;
    for (let i = 1; i <= p.pages; i++) {
        if (i===p.page||i===1||i===p.pages||Math.abs(i-p.page)<=1) html += `<button class="btn btn-ghost btn-sm ${i===p.page?'active':''}" onclick="loadCoupons(${i})">${i}</button>`;
        else if (Math.abs(i-p.page)===2) html += `<span style="padding:0 4px">…</span>`;
    }
    html += `<button class="btn btn-ghost btn-sm" onclick="loadCoupons(${p.page+1})" ${p.page===p.pages?'disabled':''}><i class="fa-solid fa-chevron-right"></i></button>`;
    el.innerHTML = html;
}

function formatDate(d) { return d ? new Date(d).toLocaleDateString('en-US', {year:'numeric',month:'short',day:'numeric'}) : '—'; }
function escHtml(str) { if (!str) return ''; return str.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

loadStats();
loadCoupons(1);
document.getElementById('search-input').addEventListener('keypress', e => { if (e.key==='Enter') loadCoupons(1); });
document.querySelectorAll('.modal-overlay').forEach(o => o.addEventListener('click', e => { if (e.target===o) o.classList.remove('open'); }));
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
