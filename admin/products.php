<?php
/**
 * Admin Products Page
 * Easy Shopping A.R.S
 */
$page_title = "Products";
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Products</h1>
    <button class="btn btn-primary" onclick="openProductModal()">
        <i class="fa-solid fa-plus"></i> Add Product
    </button>
</div>

<!-- Filters -->
<div class="card" style="padding: 16px; margin-bottom: 20px;">
    <div class="filters-bar" style="margin-bottom: 0;">
        <input type="text" id="search-input" class="form-control search-input" placeholder="Search by name, SKU...">
        <select id="category-filter" class="form-control">
            <option value="">All Categories</option>
        </select>
        <button class="btn btn-primary" onclick="loadProducts(1)">
            <i class="fa-solid fa-search"></i> Search
        </button>
        <button class="btn btn-ghost" onclick="clearFilters()">Clear</button>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <span id="product-count" class="text-sm" style="color: var(--text-secondary);">Loading...</span>
        <div class="table-actions">
            <select id="bulk-action" class="form-control" style="width:auto;">
                <option value="">Bulk Action</option>
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
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="products-tbody">
                <tr class="loading-row"><td colspan="8"><div class="spinner"></div></td></tr>
            </tbody>
        </table>
    </div>
    <div class="pagination" id="pagination"></div>
</div>

<!-- Product Modal -->
<div class="modal-overlay" id="product-modal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add Product</h3>
            <button class="modal-close" onclick="closeProductModal()">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="product-id">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Product Name *</label>
                    <input type="text" id="product-name" class="form-control" placeholder="Enter product name" oninput="generateSlug()">
                </div>
                <div class="form-group">
                    <label class="form-label">SKU *</label>
                    <input type="text" id="product-sku" class="form-control" placeholder="e.g. ARS-001">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Slug</label>
                <input type="text" id="product-slug" class="form-control" placeholder="auto-generated">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea id="product-description" class="form-control" rows="4" placeholder="Product description..."></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Price (Rs.) *</label>
                    <input type="number" id="product-price" class="form-control" placeholder="0.00" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Discount Price (Rs.)</label>
                    <input type="number" id="product-discount-price" class="form-control" placeholder="0.00" step="0.01" min="0">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select id="product-category" class="form-control">
                        <option value="">— None —</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Stock</label>
                    <input type="number" id="product-stock" class="form-control" placeholder="0" min="0">
                </div>
            </div>

            <!-- Image Management -->
            <div class="form-group">
                <label class="form-label">Product Images</label>
                <div id="image-list" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:10px;"></div>

                <!-- Upload files -->
                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:8px;">
                    <label class="btn btn-ghost btn-sm" style="cursor:pointer;">
                        <i class="fa-solid fa-upload"></i> Upload Image(s)
                        <input type="file" id="image-file-input" accept="image/jpeg,image/png,image/webp,image/gif" multiple style="display:none;" onchange="handleFileSelect(this)">
                    </label>

                    <!-- OR paste URL -->
                    <input type="text" id="new-image-url" class="form-control" style="flex:1; min-width:200px;" placeholder="Paste image URL and press Add">
                    <button class="btn btn-ghost btn-sm" onclick="addImageFromUrl()">
                        <i class="fa-solid fa-link"></i> Add URL
                    </button>
                </div>
                <small style="color:var(--text-secondary);">First image is the primary/thumbnail. Drag items to reorder (coming soon).</small>
            </div>

            <div class="form-group" style="display:flex; align-items:center; gap:10px;">
                <input type="checkbox" id="product-featured" style="width:16px; height:16px;">
                <label class="form-label" style="margin-bottom:0;">Mark as Featured</label>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeProductModal()">Cancel</button>
            <button class="btn btn-primary" id="save-product-btn" onclick="saveProduct()">
                <i class="fa-solid fa-save"></i> Save Product
            </button>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal-overlay" id="delete-modal">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3 class="modal-title">Confirm Delete</h3>
            <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this product? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn btn-danger" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let totalPages = 1;
let deleteTargetId = null;
// Each item: { type: 'url'|'file', path: '...', file: File|null, preview: dataUrl|null }
let productImages = [];

// Load categories for dropdowns
async function loadCategories() {
    const res = await fetch('/backend/products.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=get_categories'
    });
    const json = await res.json();
    if (json.success) {
        const opts = json.data.map(c => `<option value="${c.id}">${escHtml(c.name)}</option>`).join('');
        document.getElementById('category-filter').innerHTML += opts;
        document.getElementById('product-category').innerHTML += opts;
    }
}

async function loadProducts(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('products-tbody');
    tbody.innerHTML = '<tr class="loading-row"><td colspan="8"><div class="spinner"></div></td></tr>';

    const params = new URLSearchParams({
        action: 'get_products',
        page,
        limit: 10,
        search: document.getElementById('search-input').value,
        category_id: document.getElementById('category-filter').value
    });

    const res = await fetch('/backend/products.php', { method: 'POST', body: params });
    const json = await res.json();

    if (!json.success) { Toast.error(json.message); return; }

    const { data, pagination } = json;
    totalPages = pagination.pages;

    document.getElementById('product-count').textContent =
        `Showing ${data.length} of ${pagination.total} products`;

    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8"><div class="empty-state"><i class="fa-solid fa-box-open"></i><p>No products found.</p></div></td></tr>`;
        document.getElementById('pagination').innerHTML = '';
        return;
    }

    tbody.innerHTML = data.map(p => `
        <tr>
            <td><input type="checkbox" class="row-check" value="${p.id}"></td>
            <td>
                <div style="display:flex; align-items:center; gap:10px;">
                    <img src="${p.image ? '/uploads/products/' + escHtml(p.image) : 'https://placehold.co/40x40/e5e7eb/6b7280?text=No+Img'}"
                         alt="" style="width:40px; height:40px; object-fit:cover; border-radius:6px;"
                         onerror="this.src='https://placehold.co/40x40/e5e7eb/6b7280?text=Err'">
                    <span style="font-weight:500;">${escHtml(p.name)}</span>
                </div>
            </td>
            <td style="color:var(--text-secondary);">${escHtml(p.sku || '—')}</td>
            <td>${escHtml(p.category_name || '—')}</td>
            <td>
                ${p.discount_price
                    ? `<span style="font-weight:600;">Rs. ${parseFloat(p.discount_price).toFixed(2)}</span> <span style="text-decoration:line-through; color:var(--text-secondary); font-size:12px;">Rs. ${parseFloat(p.price).toFixed(2)}</span>`
                    : `<span style="font-weight:600;">Rs. ${parseFloat(p.price).toFixed(2)}</span>`}
            </td>
            <td>
                <span class="badge ${p.stock < 10 ? 'badge-danger' : 'badge-success'}">${p.stock}</span>
            </td>
            <td>
                <button class="btn btn-ghost btn-sm" onclick="toggleFeatured(${p.id}, ${p.is_featured ? 0 : 1})">
                    <i class="fa-solid fa-star" style="color:${p.is_featured ? 'var(--warning)' : 'var(--gray-300)'}"></i>
                </button>
            </td>
            <td>
                <div class="table-actions">
                    <button class="btn btn-ghost btn-sm" onclick="editProduct(${p.id})"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-ghost btn-sm" onclick="openDeleteModal(${p.id})"><i class="fa-solid fa-trash" style="color:var(--danger)"></i></button>
                </div>
            </td>
        </tr>
    `).join('');

    renderPagination(pagination);
}

function renderPagination(p) {
    const el = document.getElementById('pagination');
    if (p.pages <= 1) { el.innerHTML = ''; return; }
    let html = `<span class="pagination-info">Page ${p.page} of ${p.pages}</span>`;
    html += `<button class="btn btn-ghost btn-sm" onclick="loadProducts(${p.page - 1})" ${p.page === 1 ? 'disabled' : ''}><i class="fa-solid fa-chevron-left"></i></button>`;
    for (let i = 1; i <= p.pages; i++) {
        if (i === p.page || i === 1 || i === p.pages || Math.abs(i - p.page) <= 1)
            html += `<button class="btn btn-ghost btn-sm ${i === p.page ? 'active' : ''}" onclick="loadProducts(${i})">${i}</button>`;
        else if (Math.abs(i - p.page) === 2)
            html += `<span style="padding:0 4px">…</span>`;
    }
    html += `<button class="btn btn-ghost btn-sm" onclick="loadProducts(${p.page + 1})" ${p.page === p.pages ? 'disabled' : ''}><i class="fa-solid fa-chevron-right"></i></button>`;
    el.innerHTML = html;
}

function generateSlug() {
    const name = document.getElementById('product-name').value;
    document.getElementById('product-slug').value = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

// Handle file selection from file picker
function handleFileSelect(input) {
    const files = Array.from(input.files);
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            productImages.push({ type: 'file', file: file, preview: e.target.result, path: null });
            renderImageList();
        };
        reader.readAsDataURL(file);
    });
    input.value = ''; // Reset so same file can be picked again
}

// Handle URL entry
function addImageFromUrl() {
    const url = document.getElementById('new-image-url').value.trim();
    if (!url) return;
    productImages.push({ type: 'url', path: url, file: null, preview: null });
    document.getElementById('new-image-url').value = '';
    renderImageList();
}

function renderImageList() {
    const container = document.getElementById('image-list');
    container.innerHTML = productImages.map((img, i) => {
        const src = img.preview || img.path;
        const label = i === 0 ? '<span style="position:absolute;top:2px;left:2px;background:var(--primary);color:white;font-size:9px;padding:1px 4px;border-radius:3px;">Primary</span>' : '';
        const badge = img.type === 'file' ? '<span style="position:absolute;bottom:2px;left:2px;background:var(--success);color:white;font-size:8px;padding:1px 4px;border-radius:3px;">Upload</span>' : '';
        return `
            <div style="position:relative; display:inline-block;">
                <img src="${escHtml(src)}" style="width:80px; height:80px; object-fit:cover; border-radius:6px; border:2px solid ${i===0 ? 'var(--primary)' : 'var(--border-color)'};" onerror="this.src='https://placehold.co/80x80/e5e7eb/6b7280?text=Err'">
                ${label}${badge}
                <button onclick="removeImage(${i})" style="position:absolute;top:-6px;right:-6px;background:var(--danger);color:white;border:none;border-radius:50%;width:18px;height:18px;cursor:pointer;font-size:12px;line-height:1;">&times;</button>
            </div>
        `;
    }).join('');
}

function removeImage(i) {
    productImages.splice(i, 1);
    renderImageList();
}

function openProductModal() {
    productImages = [];
    document.getElementById('product-id').value = '';
    document.getElementById('product-name').value = '';
    document.getElementById('product-sku').value = '';
    document.getElementById('product-slug').value = '';
    document.getElementById('product-description').value = '';
    document.getElementById('product-price').value = '';
    document.getElementById('product-discount-price').value = '';
    document.getElementById('product-stock').value = '';
    document.getElementById('product-featured').checked = false;
    document.getElementById('product-category').value = '';
    document.getElementById('modal-title').textContent = 'Add Product';
    renderImageList();
    document.getElementById('product-modal').classList.add('open');
}

async function editProduct(id) {
    const res = await fetch('/backend/products.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=get_product&id=${id}`
    });
    const json = await res.json();
    if (!json.success) { Toast.error(json.message); return; }
    const p = json.data;

    document.getElementById('product-id').value = p.id;
    document.getElementById('product-name').value = p.name;
    document.getElementById('product-sku').value = p.sku || '';
    document.getElementById('product-slug').value = p.slug;
    document.getElementById('product-description').value = p.description || '';
    document.getElementById('product-price').value = p.price;
    document.getElementById('product-discount-price').value = p.discount_price || '';
    document.getElementById('product-stock').value = p.stock;
    document.getElementById('product-featured').checked = p.is_featured == 1;
    document.getElementById('product-category').value = p.category_id || '';
    document.getElementById('modal-title').textContent = 'Edit Product';

    // Load existing images as URL type (already stored paths)
    productImages = (p.images || []).map(img => ({ type: 'url', path: img.image_path, file: null, preview: null }));
    // Fallback: if no images table rows but product has a legacy image
    if (!productImages.length && p.image) {
        productImages = [{ type: 'url', path: p.image, file: null, preview: null }];
    }
    renderImageList();
    document.getElementById('product-modal').classList.add('open');
}

async function saveProduct() {
    const id = document.getElementById('product-id').value;
    const name = document.getElementById('product-name').value.trim();
    const price = document.getElementById('product-price').value;

    if (!name || !price) { Toast.error('Name and Price are required.'); return; }

    const saveBtn = document.getElementById('save-product-btn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<div class="spinner" style="width:16px;height:16px;"></div> Saving...';

    // Use FormData to support file uploads
    const formData = new FormData();
    formData.append('action', 'save_product');
    formData.append('product[id]', id);
    formData.append('product[name]', name);
    formData.append('product[sku]', document.getElementById('product-sku').value);
    formData.append('product[slug]', document.getElementById('product-slug').value);
    formData.append('product[description]', document.getElementById('product-description').value);
    formData.append('product[price]', price);
    formData.append('product[discount_price]', document.getElementById('product-discount-price').value);
    formData.append('product[category_id]', document.getElementById('product-category').value);
    formData.append('product[stock]', document.getElementById('product-stock').value || 0);
    if (document.getElementById('product-featured').checked) formData.append('product[is_featured]', '1');

    // Append images in order — files and URLs separately but with order index
    productImages.forEach((img, i) => {
        if (img.type === 'file' && img.file) {
            formData.append('img_order[]', `file:${i}`);
            formData.append(`img_file_${i}`, img.file, img.file.name);
        } else {
            formData.append('img_order[]', `url:${i}`);
            formData.append(`img_url_${i}`, img.path);
        }
    });

    try {
        const res = await fetch('/backend/products.php', { method: 'POST', body: formData });
        const json = await res.json();
        if (json.success) {
            Toast.success(id ? 'Product updated!' : 'Product added!');
            closeProductModal();
            loadProducts(currentPage);
        } else {
            Toast.error(json.message || 'Failed to save product.');
        }
    } catch(e) {
        Toast.error('Network error. Please try again.');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fa-solid fa-save"></i> Save Product';
    }
}

async function toggleFeatured(id, featured) {
    const res = await fetch('/backend/products.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=toggle_featured&id=${id}&featured=${featured}`
    });
    const json = await res.json();
    if (json.success) loadProducts(currentPage);
    else Toast.error(json.message);
}

function openDeleteModal(id) {
    deleteTargetId = id;
    document.getElementById('delete-modal').classList.add('open');
}
function closeDeleteModal() {
    deleteTargetId = null;
    document.getElementById('delete-modal').classList.remove('open');
}

async function confirmDelete() {
    if (!deleteTargetId) return;
    const res = await fetch('/backend/products.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=delete_product&id=${deleteTargetId}`
    });
    const json = await res.json();
    closeDeleteModal();
    if (json.success) { Toast.success('Product deleted.'); loadProducts(currentPage); }
    else Toast.error(json.message);
}

function closeProductModal() {
    document.getElementById('product-modal').classList.remove('open');
}

function clearFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('category-filter').value = '';
    loadProducts(1);
}

function toggleSelectAll(cb) {
    document.querySelectorAll('.row-check').forEach(c => c.checked = cb.checked);
}

async function applyBulkAction() {
    const action = document.getElementById('bulk-action').value;
    if (!action) return;
    const ids = [...document.querySelectorAll('.row-check:checked')].map(c => c.value);
    if (!ids.length) { Toast.error('Select at least one product.'); return; }

    if (action === 'delete' && confirm(`Delete ${ids.length} product(s)?`)) {
        for (const id of ids) {
            await fetch('/backend/products.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=delete_product&id=${id}`
            });
        }
        Toast.success(`${ids.length} product(s) deleted.`);
        loadProducts(currentPage);
    }
}

function escHtml(str) {
    if (!str) return '';
    return str.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Init
loadCategories();
loadProducts(1);
document.getElementById('search-input').addEventListener('keypress', e => { if (e.key === 'Enter') loadProducts(1); });
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('open'); });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
