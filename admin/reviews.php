<?php
/**
 * Admin Reviews Page
 * Easy Shopping A.R.S
 */
$page_title = "Reviews";
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Product Reviews</h1>
</div>

<!-- Stats -->
<div class="kpi-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="kpi-card"><span class="kpi-label">Total Reviews</span><div class="kpi-value" id="stat-total">—</div></div>
    <div class="kpi-card"><span class="kpi-label">Pending</span><div class="kpi-value" id="stat-pending" style="color:var(--warning);">—</div></div>
    <div class="kpi-card"><span class="kpi-label">Approved</span><div class="kpi-value" id="stat-approved" style="color:var(--success);">—</div></div>
    <div class="kpi-card"><span class="kpi-label">Avg Rating</span><div class="kpi-value" id="stat-avg">—</div></div>
</div>

<!-- Filters -->
<div class="card" style="padding: 16px; margin-bottom: 20px;">
    <div class="filters-bar" style="margin-bottom:0;">
        <input type="text" id="search-input" class="form-control search-input" placeholder="Search by product, reviewer, comment...">
        <select id="status-filter" class="form-control">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
        </select>
        <select id="rating-filter" class="form-control">
            <option value="">All Ratings</option>
            <option value="5">5 Stars</option>
            <option value="4">4 Stars</option>
            <option value="3">3 Stars</option>
            <option value="2">2 Stars</option>
            <option value="1">1 Star</option>
        </select>
        <button class="btn btn-primary" onclick="loadReviews(1)"><i class="fa-solid fa-search"></i> Filter</button>
        <button class="btn btn-ghost" onclick="clearFilters()">Clear</button>
    </div>
</div>

<!-- Bulk Actions -->
<div class="card" style="padding:12px 16px; margin-bottom: 16px;">
    <div style="display:flex; gap:10px; align-items:center;">
        <select id="bulk-action" class="form-control" style="width:auto;">
            <option value="">Bulk Action</option>
            <option value="approved">Approve Selected</option>
            <option value="rejected">Reject Selected</option>
            <option value="delete">Delete Selected</option>
        </select>
        <button class="btn btn-ghost btn-sm" onclick="applyBulkAction()">Apply</button>
        <span id="review-count" style="margin-left:auto; color:var(--text-secondary); font-size:13px;">Loading...</span>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all" onchange="toggleSelectAll(this)"></th>
                    <th>Product</th>
                    <th>Reviewer</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="reviews-tbody">
                <tr class="loading-row"><td colspan="8"><div class="spinner"></div></td></tr>
            </tbody>
        </table>
    </div>
    <div class="pagination" id="pagination"></div>
</div>

<script>
let currentPage = 1;

async function loadStats() {
    const res = await fetch(BASE_URL + '/api/reviews/stats');
    const json = await res.json();
    if (json.success) {
        const s = json.data;
        document.getElementById('stat-total').textContent = s.total;
        document.getElementById('stat-pending').textContent = s.pending;
        document.getElementById('stat-avg').textContent = s.average_rating ? s.average_rating + ' ★' : '—';
    }
}

async function loadReviews(page = 1) {
    currentPage = page;
    document.getElementById('reviews-tbody').innerHTML = '<tr class="loading-row"><td colspan="8"><div class="spinner"></div></td></tr>';

    const params = new URLSearchParams({
        page, limit: 10,
        search: document.getElementById('search-input').value,
        status: document.getElementById('status-filter').value,
        rating: document.getElementById('rating-filter').value
    });

    const res = await fetch(BASE_URL + '/api/reviews/list?' + params.toString());
    const json = await res.json();
    if (!json.success) { Toast.error(json.message); return; }

    const { data, pagination } = json;
    document.getElementById('review-count').textContent = `${pagination.total} reviews`;

    if (data.length === 0) {
        document.getElementById('reviews-tbody').innerHTML = `<tr><td colspan="8"><div class="empty-state"><i class="fa-solid fa-star"></i><p>No reviews found.</p></div></td></tr>`;
        document.getElementById('pagination').innerHTML = '';
        return;
    }

    document.getElementById('reviews-tbody').innerHTML = data.map(r => `
        <tr>
            <td><input type="checkbox" class="row-check" value="${r.id}"></td>
            <td style="font-weight:500; max-width:160px;">${escHtml(r.product_name)}</td>
            <td>
                <div style="font-weight:500;">${escHtml(r.user_name || 'Anonymous')}</div>
                <div style="font-size:12px; color:var(--text-secondary);">${escHtml(r.user_email || '')}</div>
            </td>
            <td><span class="stars">${'★'.repeat(r.rating)}${'☆'.repeat(5 - r.rating)}</span></td>
            <td style="max-width:200px; color:var(--text-secondary); font-size:13px;">${escHtml((r.comment || '').substring(0, 80))}${r.comment && r.comment.length > 80 ? '…' : ''}</td>
            <td>
                <span class="badge ${r.status === 'approved' ? 'badge-success' : r.status === 'rejected' ? 'badge-danger' : 'badge-warning'}">
                    ${r.status}
                </span>
            </td>
            <td style="color:var(--text-secondary); font-size:13px;">${formatDate(r.created_at)}</td>
            <td>
                <div class="table-actions">
                    ${r.status !== 'approved' ? `<button class="btn btn-ghost btn-sm" onclick="updateStatus(${r.id},'approved')" title="Approve"><i class="fa-solid fa-check" style="color:var(--success)"></i></button>` : ''}
                    ${r.status !== 'rejected' ? `<button class="btn btn-ghost btn-sm" onclick="updateStatus(${r.id},'rejected')" title="Reject"><i class="fa-solid fa-xmark" style="color:var(--danger)"></i></button>` : ''}
                    <button class="btn btn-ghost btn-sm" onclick="deleteReview(${r.id})" title="Delete"><i class="fa-solid fa-trash" style="color:var(--danger)"></i></button>
                </div>
            </td>
        </tr>
    `).join('');

    renderPagination(pagination);
}

async function updateStatus(id, status) {
    const res = await fetch(BASE_URL + '/api/reviews/update-status', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id, status })
    });
    const json = await res.json();
    if (json.success) { Toast.success(`Review ${status}!`); loadReviews(currentPage); loadStats(); }
    else Toast.error(json.message);
}

async function deleteReview(id) {
    if (!confirm('Delete this review?')) return;
    const res = await fetch(BASE_URL + '/api/reviews/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id })
    });
    const json = await res.json();
    if (json.success) { Toast.success('Review deleted.'); loadReviews(currentPage); loadStats(); }
    else Toast.error(json.message);
}

async function applyBulkAction() {
    const action = document.getElementById('bulk-action').value;
    if (!action) return;
    const ids = [...document.querySelectorAll('.row-check:checked')].map(c => c.value);
    if (!ids.length) { Toast.error('Select at least one review.'); return; }

    if (action === 'delete') {
        if (!confirm(`Delete ${ids.length} review(s)?`)) return;
        const res = await fetch(BASE_URL + '/api/reviews/bulk-delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ ids })
        });
        const json = await res.json();
        if (json.success) { Toast.success(`${json.data.deleted} review(s) deleted.`); loadReviews(currentPage); loadStats(); }
        else Toast.error(json.message);
    } else {
        const res = await fetch(BASE_URL + '/api/reviews/bulk-update-status', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ ids, status: action })
        });
        const json = await res.json();
        if (json.success) { Toast.success(`${json.data.updated} review(s) updated.`); loadReviews(currentPage); loadStats(); }
        else Toast.error(json.message);
    }
}

function clearFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('rating-filter').value = '';
    loadReviews(1);
}

function toggleSelectAll(cb) { document.querySelectorAll('.row-check').forEach(c => c.checked = cb.checked); }

function renderPagination(p) {
    const el = document.getElementById('pagination');
    if (p.pages <= 1) { el.innerHTML = ''; return; }
    let html = `<span class="pagination-info">Page ${p.page} of ${p.pages}</span>`;
    html += `<button class="btn btn-ghost btn-sm" onclick="loadReviews(${p.page-1})" ${p.page===1?'disabled':''}><i class="fa-solid fa-chevron-left"></i></button>`;
    for (let i = 1; i <= p.pages; i++) {
        if (i===p.page||i===1||i===p.pages||Math.abs(i-p.page)<=1) html += `<button class="btn btn-ghost btn-sm ${i===p.page?'active':''}" onclick="loadReviews(${i})">${i}</button>`;
        else if (Math.abs(i-p.page)===2) html += `<span style="padding:0 4px">…</span>`;
    }
    html += `<button class="btn btn-ghost btn-sm" onclick="loadReviews(${p.page+1})" ${p.page===p.pages?'disabled':''}><i class="fa-solid fa-chevron-right"></i></button>`;
    el.innerHTML = html;
}

function formatDate(d) { return d ? new Date(d).toLocaleDateString('en-US', {year:'numeric',month:'short',day:'numeric'}) : '—'; }
function escHtml(str) { if (!str) return ''; return str.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

loadStats();
loadReviews(1);
document.getElementById('search-input').addEventListener('keypress', e => { if (e.key==='Enter') loadReviews(1); });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
