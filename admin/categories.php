<?php
/**
 * Admin Categories Page
 * Easy Shopping A.R.S
 */
$page_title = "Categories";
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Categories</h1>
    <button class="btn btn-primary" onclick="openModal()">
        <i class="fa-solid fa-plus"></i> Add Category
    </button>
</div>

<!-- Stats Row -->
<div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr);" id="stats-row">
    <div class="kpi-card"><span class="kpi-label">Total Categories</span><div class="kpi-value" id="stat-total">—</div></div>
    <div class="kpi-card"><span class="kpi-label">With Products</span><div class="kpi-value" id="stat-with">—</div></div>
    <div class="kpi-card"><span class="kpi-label">Empty</span><div class="kpi-value" id="stat-empty">—</div></div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <span id="cat-count" style="color:var(--text-secondary); font-size:13px;">Loading...</span>
        <div class="table-actions">
            <input type="text" id="search-input" class="form-control" style="width:200px;" placeholder="Search categories...">
        </div>
    </div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Products</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="categories-tbody">
                <tr class="loading-row"><td colspan="4"><div class="spinner"></div></td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="cat-modal">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add Category</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="cat-id">
            <div class="form-group">
                <label class="form-label">Category Name *</label>
                <input type="text" id="cat-name" class="form-control" placeholder="e.g. Electronics" oninput="autoSlug()">
            </div>
            <div class="form-group">
                <label class="form-label">Slug</label>
                <div style="display:flex; gap:8px;">
                    <input type="text" id="cat-slug" class="form-control" placeholder="auto-generated">
                    <button class="btn btn-ghost btn-sm" onclick="generateSlug()" style="white-space:nowrap;">Generate</button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
            <button class="btn btn-primary" onclick="saveCategory()">Save</button>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="delete-modal">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3 class="modal-title">Delete Category</h3>
            <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Delete this category? This cannot be undone. Categories with assigned products cannot be deleted.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn btn-danger" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>

<script>
let allCategories = [];
let deleteTargetId = null;

async function loadStats() {
    const json = await apiFetch('/api/categories/stats');
    if (json.success) {
        const s = json.data;
        document.getElementById('stat-total').textContent = s.total_categories;
        document.getElementById('stat-with').textContent = s.categories_with_products;
        document.getElementById('stat-empty').textContent = s.empty_categories;
    }
}

async function loadCategories() {
    const json = await apiFetch('/api/categories/list');
    if (!json.success) { Toast.error(json.message); return; }
    allCategories = json.data;
    renderTable(allCategories);
    document.getElementById('cat-count').textContent = `${allCategories.length} categories`;
}

function renderTable(data) {
    const tbody = document.getElementById('categories-tbody');
    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4"><div class="empty-state"><i class="fa-solid fa-layer-group"></i><p>No categories found.</p></div></td></tr>`;
        return;
    }
    tbody.innerHTML = data.map(c => `
        <tr>
            <td style="font-weight:500;">${escHtml(c.name)}</td>
            <td><code style="background:var(--gray-100); padding:2px 8px; border-radius:4px; font-size:12px;">${escHtml(c.slug)}</code></td>
            <td><span class="badge ${c.product_count > 0 ? 'badge-info' : 'badge-warning'}">${c.product_count} products</span></td>
            <td>
                <div class="table-actions">
                    <button class="btn btn-ghost btn-sm" onclick="editCategory(${c.id})"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-ghost btn-sm" onclick="openDeleteModal(${c.id})" ${c.product_count > 0 ? 'disabled title="Has products"' : ''}>
                        <i class="fa-solid fa-trash" style="color:${c.product_count > 0 ? 'var(--gray-300)' : 'var(--danger)'}"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function filterTable() {
    const q = document.getElementById('search-input').value.toLowerCase();
    renderTable(allCategories.filter(c => c.name.toLowerCase().includes(q) || c.slug.toLowerCase().includes(q)));
}

function autoSlug() {
    const name = document.getElementById('cat-name').value;
    document.getElementById('cat-slug').value = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

async function generateSlug() {
    const name = document.getElementById('cat-name').value.trim();
    if (!name) return;
    const json = await apiFetch('/api/categories/generate-slug', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ name })
    });
    if (json.success) document.getElementById('cat-slug').value = json.data.slug;
}

function openModal() {
    document.getElementById('cat-id').value = '';
    document.getElementById('cat-name').value = '';
    document.getElementById('cat-slug').value = '';
    document.getElementById('modal-title').textContent = 'Add Category';
    document.getElementById('cat-modal').classList.add('open');
}

async function editCategory(id) {
    const json = await apiFetch(`/api/categories/detail?id=${id}`);
    if (!json.success) { Toast.error(json.message); return; }
    const c = json.data;
    document.getElementById('cat-id').value = c.id;
    document.getElementById('cat-name').value = c.name;
    document.getElementById('cat-slug').value = c.slug;
    document.getElementById('modal-title').textContent = 'Edit Category';
    document.getElementById('cat-modal').classList.add('open');
}

async function saveCategory() {
    const name = document.getElementById('cat-name').value.trim();
    const slug = document.getElementById('cat-slug').value.trim();
    if (!name || !slug) { Toast.error('Name and Slug are required.'); return; }

    const id = document.getElementById('cat-id').value;
    const action = id ? 'update' : 'create';

    const json = await apiFetch(`/api/categories/${action}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id, name, slug })
    });

    if (json.success) {
        Toast.success(id ? 'Category updated!' : 'Category added!');
        closeModal();
        loadCategories();
        loadStats();
    } else {
        Toast.error(json.message);
    }
}

function openDeleteModal(id) {
    deleteTargetId = id;
    document.getElementById('delete-modal').classList.add('open');
}
function closeDeleteModal() {
    deleteTargetId = null;
    document.getElementById('delete-modal').classList.remove('open');
}
function closeModal() { document.getElementById('cat-modal').classList.remove('open'); }

async function confirmDelete() {
    if (!deleteTargetId) return;
    const json = await apiFetch('/api/categories/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id: deleteTargetId })
    });
    closeDeleteModal();
    if (json.success) { Toast.success('Category deleted.'); loadCategories(); loadStats(); }
    else Toast.error(json.message);
}

function escHtml(str) {
    if (!str) return '';
    return str.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Init
loadStats();
loadCategories();
document.getElementById('search-input').addEventListener('input', filterTable);
document.querySelectorAll('.modal-overlay').forEach(o => o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); }));
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
