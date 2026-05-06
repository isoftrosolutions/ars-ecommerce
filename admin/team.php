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
    <button class="btn btn-primary" onclick="openTeamModal()">
        <i class="fa-solid fa-plus"></i> Add Team Member
    </button>
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

<!-- Team Member Modal -->
<div id="teamModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">Add Team Member</h3>
            <button class="modal-close" onclick="closeTeamModal()">&times;</button>
        </div>
        <form id="teamForm" enctype="multipart/form-data">
            <input type="hidden" id="member_id" name="member_id">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div style="padding: 0 24px 8px;">

            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control">
                </div>
                <div class="form-group">
                    <label for="mobile">Mobile Number</label>
                    <input type="text" id="mobile" name="mobile" class="form-control" pattern="[0-9]{10}" title="10 digit mobile number">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="support">Support</option>
                        <option value="admin">Admin</option>
                        <option value="technical">Technical</option>
                        <option value="manager">Manager</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="position">Position *</label>
                    <input type="text" id="position" name="position" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label for="fb_link">Facebook Link</label>
                <input type="url" id="fb_link" name="fb_link" class="form-control" placeholder="https://facebook.com/...">
            </div>

            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" class="form-control" rows="3" placeholder="Brief description about the team member"></textarea>
            </div>

            <div class="form-group">
                <label for="profile_image">Profile Image</label>
                <input type="file" id="profile_image" name="profile_image" class="form-control" accept="image/*">
                <small class="form-text">Recommended: Square image, max 2MB, JPG/PNG format</small>
                <div id="current-image" style="display: none; margin-top: 10px;">
                    <p style="font-size:12px;color:var(--text-secondary);margin-bottom:6px;">Current photo:</p>
                    <img id="current-image-preview" src="" alt="Current image" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);">
                </div>
                <div id="new-image-preview" style="display: none; margin-top: 10px;">
                    <p style="font-size:12px;color:var(--text-secondary);margin-bottom:6px;">New photo preview:</p>
                    <img id="new-image-preview-img" src="" alt="Preview" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="display_order">Display Order</label>
                    <input type="number" id="display_order" name="display_order" class="form-control" min="0" value="0">
                </div>
                <div class="form-group">
                    <label for="is_active">Status</label>
                    <select id="is_active" name="is_active" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            </div><!-- /form padding wrapper -->

            <div class="modal-actions">
                <button type="button" class="btn btn-ghost" onclick="closeTeamModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Team Member</button>
            </div>
        </form>
    </div>
</div>

<script>
// Global variables
let currentPage = 1;
let searchQuery = '';
let roleFilter = '';
let statusFilter = '';

document.addEventListener('DOMContentLoaded', function() {
    loadTeamMembers(1);

    document.getElementById('teamForm').addEventListener('submit', handleFormSubmit);

    // Live preview when a new image file is selected
    document.getElementById('profile_image').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('new-image-preview-img').src = e.target.result;
                document.getElementById('new-image-preview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            document.getElementById('new-image-preview').style.display = 'none';
        }
    });
});

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
                    <button class="btn btn-sm btn-outline-primary" onclick="editTeamMember(${member.id})">
                        <i class="fa-solid fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteTeamMember(${member.id})">
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

function openTeamModal(memberId = null) {
    const modal = document.getElementById('teamModal');
    const form = document.getElementById('teamForm');
    const title = document.getElementById('modal-title');

    form.reset();
    document.getElementById('current-image').style.display = 'none';
    document.getElementById('new-image-preview').style.display = 'none';

    if (memberId) {
        title.textContent = 'Edit Team Member';
        loadTeamMember(memberId);
    } else {
        title.textContent = 'Add Team Member';
        document.getElementById('member_id').value = '';
    }

    modal.style.display = 'block';
}

function closeTeamModal() {
    document.getElementById('teamModal').style.display = 'none';
}

function loadTeamMember(id) {
    fetch(`${window.BASE_URL}/admin/api/team-members.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            action: 'get',
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            populateForm(data.member);
        } else {
            showToast('Error loading team member', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error loading team member', 'error');
    });
}

function populateForm(member) {
    document.getElementById('member_id').value = member.id;
    document.getElementById('name').value = member.name;
    document.getElementById('email').value = member.email || '';
    document.getElementById('mobile').value = member.mobile || '';
    document.getElementById('role').value = member.role;
    document.getElementById('position').value = member.position;
    document.getElementById('fb_link').value = member.fb_link || '';
    document.getElementById('bio').value = member.bio || '';
    document.getElementById('display_order').value = member.display_order;
    document.getElementById('is_active').value = member.is_active;

    document.getElementById('new-image-preview').style.display = 'none';
    if (member.profile_image) {
        const base = window.BASE_URL || '';
        document.getElementById('current-image').style.display = 'block';
        document.getElementById('current-image-preview').src = base + member.profile_image;
    } else {
        document.getElementById('current-image').style.display = 'none';
    }
}

function handleFormSubmit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const memberId = document.getElementById('member_id').value;
    formData.append('action', memberId ? 'update' : 'create');

    const submitBtn = e.target.querySelector('[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    fetch(`${window.BASE_URL}/admin/api/team-members.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeTeamModal();
            loadTeamMembers(currentPage);
        } else {
            showToast(data.message || 'Error saving team member', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error saving team member', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Save Team Member';
    });
}

function editTeamMember(id) {
    openTeamModal(id);
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
.form-row {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
}

.form-row .form-group {
    flex: 1;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    margin-bottom: 4px;
    font-weight: 500;
    color: var(--text-primary);
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(234, 108, 0, 0.1);
}

.filters-bar {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.filters-bar .form-control {
    width: 200px;
}

.search-input {
    width: 250px !important;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.table th {
    font-weight: 600;
    color: var(--text-secondary);
    background: var(--bg-light);
}

.table-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-hover);
}

.btn-ghost {
    background: transparent;
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
}

.btn-ghost:hover {
    background: var(--bg-light);
}

.btn-outline-primary {
    background: transparent;
    color: var(--primary-color);
    border: 1px solid var(--primary-color);
}

.btn-outline-primary:hover {
    background: var(--primary-color);
    color: white;
}

.btn-outline-danger {
    background: transparent;
    color: #dc3545;
    border: 1px solid #dc3545;
}

.btn-outline-danger:hover {
    background: #dc3545;
    color: white;
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.bg-success { background: #28a745; color: white; }
.bg-secondary { background: #6c757d; color: white; }
.bg-danger { background: #dc3545; color: white; }
.bg-info { background: #17a2b8; color: white; }
.bg-warning { background: #ffc107; color: black; }

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--text-secondary);
}

.modal-actions {
    padding: 20px 24px;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.text-sm { font-size: 12px; }
.text-muted { color: var(--text-secondary); }
.fw-semibold { font-weight: 600; }
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>