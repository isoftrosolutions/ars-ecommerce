<?php
/**
 * Admin Team Members Page
 * Easy Shopping A.R.S
 */
$page_title = "Team Members";
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Team Members</h1>
    <a href="<?php echo url('/admin/team-edit.php'); ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add Team Member
    </a>
</div>

<!-- Filters -->
<div class="card" style="padding: 16px; margin-bottom: 20px;">
    <div class="filters-bar" style="margin-bottom: 0;">
        <input type="text" id="search-input" class="form-control search-input" placeholder="Search by name, email, phone...">
        <select id="role-filter" class="form-control">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="support">Support</option>
            <option value="technical">Technical</option>
            <option value="manager">Manager</option>
        </select>
        <select id="status-filter" class="form-control">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
        <button class="btn btn-primary" onclick="loadTeamMembers(1)">
            <i class="fa-solid fa-search"></i> Search
        </button>
        <button class="btn btn-ghost" onclick="clearFilters()">Clear</button>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <span id="team-count" class="text-sm" style="color: var(--text-secondary);">Loading...</span>
        <div class="table-actions">
            <select id="bulk-action" class="form-control" style="width:auto;">
                <option value="">Bulk Action</option>
                <option value="activate">Activate Selected</option>
                <option value="deactivate">Deactivate Selected</option>
                <option value="delete">Delete Selected</option>
            </select>
            <button class="btn btn-ghost btn-sm" onclick="applyBulkAction()">Apply</button>
        </div>
    </div>
    <div id="team-pagination" style="padding: 12px 16px;"></div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all" onchange="toggleSelectAll(this)"></th>
                    <th>Member</th>
                    <th>Role</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="team-table-body">
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm" role="status"></div>
                        Loading team members...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
// Global variables
let currentPage = 1;
let searchQuery = '';
let roleFilter = '';
let statusFilter = '';

document.addEventListener('DOMContentLoaded', () => loadTeamMembers(1));

function loadTeamMembers(page = 1) {
    currentPage = page;
    searchQuery = document.getElementById('search-input').value;
    roleFilter = document.getElementById('role-filter').value;
    statusFilter = document.getElementById('status-filter').value;

    fetch(`${window.BASE_URL}/admin/api/team-members.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            action: 'list',
            page: page,
            search: searchQuery,
            role: roleFilter,
            status: statusFilter
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderTeamTable(data.members);
            updatePagination(data.pagination);
        } else {
            showToast('Error loading team members', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error loading team members', 'error');
    });
}

function renderTeamTable(members) {
    const tbody = document.getElementById('team-table-body');
    const count = document.getElementById('team-count');

    count.textContent = `Showing ${members.length} team member${members.length !== 1 ? 's' : ''}`;

    if (members.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <i class="fa-solid fa-users text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">No team members found</p>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = members.map(member => `
        <tr>
            <td><input type="checkbox" class="member-checkbox" value="${member.id}"></td>
            <td>
                <div class="d-flex align-items-center">
                    <img src="${member.profile_image || window.BASE_URL + '/public/assets/img/default-avatar.png'}"
                         alt="${member.name}"
                         class="rounded-circle me-3"
                         style="width: 40px; height: 40px; object-fit: cover;"
                         onerror="this.src='${window.BASE_URL}/public/assets/img/default-avatar.png'">
                    <div>
                        <div class="fw-semibold">${member.name}</div>
                        <div class="text-sm text-muted">${member.position}</div>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge ${getRoleBadgeClass(member.role)}">${capitalizeFirst(member.role)}</span>
            </td>
            <td>
                <div class="text-sm">
                    ${member.email ? `<div><i class="fa-solid fa-envelope me-1"></i>${member.email}</div>` : ''}
                    ${member.mobile ? `<div><i class="fa-solid fa-phone me-1"></i>${member.mobile}</div>` : ''}
                    ${member.fb_link ? `<div><i class="fa-brands fa-facebook me-1"></i><a href="${member.fb_link}" target="_blank">Facebook</a></div>` : ''}
                </div>
            </td>
            <td>
                <span class="badge ${member.is_active ? 'bg-success' : 'bg-secondary'}">
                    ${member.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>
                <div class="btn-group">
                    <a href="${window.BASE_URL}/admin/team-edit.php?id=${member.id}"
                       class="btn btn-sm btn-outline-primary" title="Edit">
                        <i class="fa-solid fa-edit"></i>
                    </a>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteTeamMember(${member.id})" title="Delete">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function getRoleBadgeClass(role) {
    const classes = {
        'admin': 'bg-danger',
        'support': 'bg-info',
        'technical': 'bg-warning',
        'manager': 'bg-success'
    };
    return classes[role] || 'bg-secondary';
}

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function deleteTeamMember(id) {
    if (confirm('Are you sure you want to delete this team member?')) {
        fetch(`${window.BASE_URL}/admin/api/team-members.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                action: 'delete',
                id: id
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Team member deleted successfully', 'success');
                loadTeamMembers(currentPage);
            } else {
                showToast('Error deleting team member', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error deleting team member', 'error');
        });
    }
}

function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.member-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

function applyBulkAction() {
    const action = document.getElementById('bulk-action').value;
    const selectedIds = Array.from(document.querySelectorAll('.member-checkbox:checked')).map(cb => cb.value);

    if (!action || selectedIds.length === 0) {
        showToast('Please select an action and team members', 'warning');
        return;
    }

    if (action === 'delete' && !confirm(`Are you sure you want to delete ${selectedIds.length} team member(s)?`)) {
        return;
    }

    fetch(`${window.BASE_URL}/admin/api/team-members.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            action: 'bulk_' + action,
            ids: selectedIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            loadTeamMembers(currentPage);
        } else {
            showToast('Error performing bulk action', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error performing bulk action', 'error');
    });
}

function clearFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('role-filter').value = '';
    document.getElementById('status-filter').value = '';
    loadTeamMembers(1);
}

function updatePagination(pagination) {
    const count = document.getElementById('team-count');
    const total = pagination.total_items ?? 0;
    count.textContent = `${total} team member${total !== 1 ? 's' : ''}`;

    let pagerEl = document.getElementById('team-pagination');
    if (!pagerEl) return;

    if (pagination.total_pages <= 1) {
        pagerEl.innerHTML = '';
        return;
    }

    let html = '<div class="pagination-wrap">';
    if (pagination.current_page > 1) {
        html += `<button class="btn btn-ghost btn-sm" onclick="loadTeamMembers(${pagination.current_page - 1})">‹ Prev</button>`;
    }
    html += `<span style="padding:0 12px;color:var(--text-secondary);font-size:13px;">Page ${pagination.current_page} of ${pagination.total_pages}</span>`;
    if (pagination.current_page < pagination.total_pages) {
        html += `<button class="btn btn-ghost btn-sm" onclick="loadTeamMembers(${pagination.current_page + 1})">Next ›</button>`;
    }
    html += '</div>';
    pagerEl.innerHTML = html;
}

function showToast(message, type = 'info') {
    Toast.show(message, type);
}
</script>

<style>
.filters-bar { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
.filters-bar .form-control { width:200px; }
.search-input { width:250px !important; }

.table { width:100%; border-collapse:collapse; }
.table th, .table td { padding:12px; text-align:left; border-bottom:1px solid var(--border-color); }
.table th { font-weight:600; color:var(--text-secondary); background:var(--bg-secondary); }
.table-actions { display:flex; gap:8px; align-items:center; }

.btn-outline-primary { background:transparent; color:var(--primary); border:1px solid var(--primary); border-radius:6px; padding:6px 10px; cursor:pointer; font-size:13px; display:inline-flex; align-items:center; text-decoration:none; transition:all 0.2s; }
.btn-outline-primary:hover { background:var(--primary); color:white; }
.btn-outline-danger { background:transparent; color:var(--danger); border:1px solid var(--danger); border-radius:6px; padding:6px 10px; cursor:pointer; font-size:13px; display:inline-flex; align-items:center; transition:all 0.2s; }
.btn-outline-danger:hover { background:var(--danger); color:white; }
.btn-group { display:flex; gap:6px; }

.badge { padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
.bg-success { background:rgba(16,185,129,0.12); color:var(--success); }
.bg-secondary { background:var(--gray-100); color:var(--text-secondary); }
.bg-danger { background:rgba(239,68,68,0.12); color:var(--danger); }
.bg-info { background:rgba(59,130,246,0.12); color:var(--info); }
.bg-warning { background:rgba(245,158,11,0.12); color:var(--warning); }

.text-sm { font-size:12px; }
.text-muted { color:var(--text-secondary); }
.fw-semibold { font-weight:600; }
.d-flex { display:flex; }
.align-items-center { align-items:center; }
.me-3 { margin-right:12px; }
.rounded-circle { border-radius:50%; }
.pagination-wrap { display:flex; align-items:center; gap:8px; }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>