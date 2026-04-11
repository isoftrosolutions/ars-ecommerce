<?php
/**
 * Admin Contact Submissions Page
 * Easy Shopping A.R.S
 */
$page_title = "Contact Submissions";
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Contact Submissions</h1>
</div>

<!-- Stats -->
<div class="kpi-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="kpi-card"><span class="kpi-label">Total</span><div class="kpi-value" id="stat-total">—</div></div>
    <div class="kpi-card"><span class="kpi-label">New</span><div class="kpi-value" id="stat-new" style="color:var(--danger);">—</div></div>
    <div class="kpi-card"><span class="kpi-label">Read</span><div class="kpi-value" id="stat-read" style="color:var(--warning);">—</div></div>
    <div class="kpi-card"><span class="kpi-label">Replied</span><div class="kpi-value" id="stat-replied" style="color:var(--success);">—</div></div>
</div>

<!-- Filters -->
<div class="card" style="padding: 16px; margin-bottom: 20px;">
    <div class="filters-bar" style="margin-bottom:0;">
        <input type="text" id="search-input" class="form-control search-input" placeholder="Search by name, email, subject...">
        <select id="status-filter" class="form-control">
            <option value="">All Statuses</option>
            <option value="new">New</option>
            <option value="read">Read</option>
            <option value="replied">Replied</option>
        </select>
        <button class="btn btn-primary" onclick="loadSubmissions(1)"><i class="fa-solid fa-search"></i> Filter</button>
        <button class="btn btn-ghost" onclick="clearFilters()">Clear</button>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <span id="sub-count" style="color:var(--text-secondary); font-size:13px;">Loading...</span>
        <div class="table-actions">
            <select id="bulk-action" class="form-control" style="width:auto;">
                <option value="">Bulk Action</option>
                <option value="read">Mark as Read</option>
                <option value="replied">Mark as Replied</option>
                <option value="delete">Delete Selected</option>
            </select>
            <button class="btn btn-ghost btn-sm" onclick="applyBulkAction()">Apply</button>
        </div>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all" onchange="toggleSelectAll(this)"></th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="subs-tbody">
                <tr class="loading-row"><td colspan="7"><div class="spinner"></div></td></tr>
            </tbody>
        </table>
    </div>
    <div class="pagination" id="pagination"></div>
</div>

<!-- View Submission Modal -->
<div class="modal-overlay" id="view-modal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="view-modal-title">Submission</h3>
            <button class="modal-close" onclick="closeViewModal()">&times;</button>
        </div>
        <div class="modal-body" id="view-modal-body"></div>
        <!-- Reply Composer -->
        <div style="padding: 16px 24px; border-top: 1px solid var(--border-color);">
            <label class="form-label">Reply to Customer</label>
            <textarea id="reply-textarea" class="form-control" rows="4"
                placeholder="Type your reply here. Email sending requires SMTP setup in Settings."></textarea>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeViewModal()">Close</button>
            <button class="btn btn-success" onclick="sendReply()">
                <i class="fa-solid fa-paper-plane"></i> Send Reply
            </button>
        </div>
    </div>
</div>


<script>
let currentPage = 1;
let currentSubId = null;

async function loadStats() {
    const res = await fetch(BASE_URL + '/backend/contact.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_stats'
    });
    const json = await res.json();
    if (json.success) {
        const s = json.data;
        document.getElementById('stat-total').textContent = s.total_submissions;
        document.getElementById('stat-new').textContent = s.status_counts['new'] || 0;
        document.getElementById('stat-read').textContent = s.status_counts['read'] || 0;
        document.getElementById('stat-replied').textContent = s.status_counts['replied'] || 0;
    }
}

async function loadSubmissions(page = 1) {
    currentPage = page;
    document.getElementById('subs-tbody').innerHTML = '<tr class="loading-row"><td colspan="7"><div class="spinner"></div></td></tr>';

    const params = new URLSearchParams({
        action: 'get_submissions', page, limit: 10,
        search: document.getElementById('search-input').value,
        status: document.getElementById('status-filter').value
    });

    const res = await fetch(BASE_URL + '/backend/contact.php', { method: 'POST', body: params });
    const json = await res.json();
    if (!json.success) { Toast.error(json.message); return; }

    const { data, pagination } = json;
    document.getElementById('sub-count').textContent = `${pagination.total} submissions`;

    if (data.length === 0) {
        document.getElementById('subs-tbody').innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-envelope"></i><p>No submissions found.</p></div></td></tr>`;
        document.getElementById('pagination').innerHTML = '';
        return;
    }

    document.getElementById('subs-tbody').innerHTML = data.map(s => `
        <tr style="${s.status === 'new' ? 'font-weight:600;' : ''}">
            <td><input type="checkbox" class="row-check" value="${s.id}"></td>
            <td>${escHtml(s.name)}</td>
            <td style="color:var(--text-secondary);">${escHtml(s.email)}</td>
            <td>${escHtml((s.subject || '').substring(0, 50))}${s.subject && s.subject.length > 50 ? '…' : ''}</td>
            <td>
                <span class="badge ${s.status === 'replied' ? 'badge-success' : s.status === 'read' ? 'badge-warning' : 'badge-danger'}">
                    ${s.status}
                </span>
            </td>
            <td style="color:var(--text-secondary); font-size:13px;">${formatDate(s.created_at)}</td>
            <td>
                <div class="table-actions">
                    <button class="btn btn-ghost btn-sm" onclick="viewSubmission(${s.id})"><i class="fa-solid fa-eye"></i></button>
                    <button class="btn btn-ghost btn-sm" onclick="deleteSubmission(${s.id})"><i class="fa-solid fa-trash" style="color:var(--danger)"></i></button>
                </div>
            </td>
        </tr>
    `).join('');

    renderPagination(pagination);
}

async function viewSubmission(id) {
    currentSubId = id;
    document.getElementById('view-modal-body').innerHTML = '<div style="text-align:center;padding:40px;"><div class="spinner"></div></div>';
    document.getElementById('view-modal').classList.add('open');

    const res = await fetch(BASE_URL + '/backend/contact.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=get_submission&id=${id}`
    });
    const json = await res.json();
    if (!json.success) { document.getElementById('view-modal-body').innerHTML = `<p style="color:var(--danger)">${json.message}</p>`; return; }
    const s = json.data;

    document.getElementById('view-modal-title').textContent = escHtml(s.subject || 'Submission');

    document.getElementById('view-modal-body').innerHTML = `
        <div class="detail-grid" style="margin-bottom:20px;">
            <div class="detail-item"><label>Name</label><span>${escHtml(s.name)}</span></div>
            <div class="detail-item"><label>Email</label><span>${escHtml(s.email)}</span></div>
            <div class="detail-item"><label>Date</label><span>${formatDate(s.created_at)}</span></div>
            <div class="detail-item"><label>Status</label><span class="badge ${s.status === 'replied' ? 'badge-success' : s.status === 'read' ? 'badge-warning' : 'badge-danger'}">${s.status}</span></div>
        </div>
        <div class="form-group">
            <label class="form-label">Subject</label>
            <div style="padding:10px 12px; background:var(--gray-50); border-radius:6px; border:1px solid var(--border-color);">${escHtml(s.subject || '\u2014')}</div>
        </div>
        <div class="form-group">
            <label class="form-label">Message</label>
            <div style="padding:12px; background:var(--gray-50); border-radius:6px; border:1px solid var(--border-color); white-space:pre-wrap; line-height:1.7;">${escHtml(s.message)}</div>
        </div>
        ${s.admin_reply ? `<div class="form-group"><label class="form-label" style="color:var(--success);">Your Previous Reply</label><div style="padding:12px; background:rgba(16,185,129,0.05); border:1px solid var(--success); border-radius:6px; white-space:pre-wrap; line-height:1.7;">${escHtml(s.admin_reply)}</div></div>` : ''}
    `;
}

async function sendReply() {
    if (!currentSubId) return;
    const msg = document.getElementById('reply-textarea').value.trim();
    if (!msg) { Toast.error('Reply message cannot be empty.'); return; }

    const params = new URLSearchParams({ action: 'send_reply', submission_id: currentSubId, reply_message: msg });
    const res  = await fetch(BASE_URL + '/backend/contact.php', { method: 'POST', body: params });
    const json = await res.json();

    if (json.success) {
        Toast.success('Reply saved!');
        document.getElementById('reply-textarea').value = '';
        closeViewModal();
        loadSubmissions(currentPage);
        loadStats();
    } else {
        Toast.error(json.message);
    }
}


async function markCurrentReplied() {
    if (!currentSubId) return;
    await fetch(BASE_URL + '/backend/contact.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=update_status&id=${currentSubId}&status=replied`
    });
    Toast.success('Marked as replied.');
    closeViewModal();
    loadSubmissions(currentPage);
    loadStats();
}

async function deleteSubmission(id) {
    if (!confirm('Delete this submission?')) return;
    const res = await fetch(BASE_URL + '/backend/contact.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=delete_submission&id=${id}`
    });
    const json = await res.json();
    if (json.success) { Toast.success('Deleted.'); loadSubmissions(currentPage); loadStats(); }
    else Toast.error(json.message);
}

async function applyBulkAction() {
    const action = document.getElementById('bulk-action').value;
    if (!action) return;
    const ids = [...document.querySelectorAll('.row-check:checked')].map(c => c.value);
    if (!ids.length) { Toast.error('Select at least one submission.'); return; }

    if (action === 'delete') {
        if (!confirm(`Delete ${ids.length} submission(s)?`)) return;
        const params = new URLSearchParams({ action: 'bulk_delete' });
        ids.forEach(id => params.append('submission_ids[]', id));
        const res = await fetch(BASE_URL + '/backend/contact.php', { method: 'POST', body: params });
        const json = await res.json();
        if (json.success) { Toast.success(`${json.deleted} deleted.`); loadSubmissions(currentPage); loadStats(); }
        else Toast.error(json.message);
    } else {
        const params = new URLSearchParams({ action: 'bulk_update_status', status: action });
        ids.forEach(id => params.append('submission_ids[]', id));
        const res = await fetch(BASE_URL + '/backend/contact.php', { method: 'POST', body: params });
        const json = await res.json();
        if (json.success) { Toast.success(`${json.updated} updated.`); loadSubmissions(currentPage); loadStats(); }
        else Toast.error(json.message);
    }
}

function closeViewModal() { document.getElementById('view-modal').classList.remove('open'); currentSubId = null; }
function clearFilters() { document.getElementById('search-input').value = ''; document.getElementById('status-filter').value = ''; loadSubmissions(1); }
function toggleSelectAll(cb) { document.querySelectorAll('.row-check').forEach(c => c.checked = cb.checked); }

function renderPagination(p) {
    const el = document.getElementById('pagination');
    if (p.pages <= 1) { el.innerHTML = ''; return; }
    let html = `<span class="pagination-info">Page ${p.page} of ${p.pages}</span>`;
    html += `<button class="btn btn-ghost btn-sm" onclick="loadSubmissions(${p.page-1})" ${p.page===1?'disabled':''}><i class="fa-solid fa-chevron-left"></i></button>`;
    for (let i = 1; i <= p.pages; i++) {
        if (i===p.page||i===1||i===p.pages||Math.abs(i-p.page)<=1) html += `<button class="btn btn-ghost btn-sm ${i===p.page?'active':''}" onclick="loadSubmissions(${i})">${i}</button>`;
        else if (Math.abs(i-p.page)===2) html += `<span style="padding:0 4px">…</span>`;
    }
    html += `<button class="btn btn-ghost btn-sm" onclick="loadSubmissions(${p.page+1})" ${p.page===p.pages?'disabled':''}><i class="fa-solid fa-chevron-right"></i></button>`;
    el.innerHTML = html;
}

function formatDate(d) { return d ? new Date(d).toLocaleDateString('en-US', {year:'numeric',month:'short',day:'numeric'}) : '—'; }
function escHtml(str) { if (!str) return ''; return str.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

loadStats();
loadSubmissions(1);
document.getElementById('search-input').addEventListener('keypress', e => { if (e.key==='Enter') loadSubmissions(1); });
document.querySelectorAll('.modal-overlay').forEach(o => o.addEventListener('click', e => { if (e.target===o) o.classList.remove('open'); }));
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
