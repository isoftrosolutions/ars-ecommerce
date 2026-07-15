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

            <!-- ═══ Variants Section ═══ -->
            <hr style="margin:20px 0;">
            <div class="form-group">
                <button type="button" class="btn btn-ghost" onclick="toggleVariantsSection()" style="width:100%; text-align:left; display:flex; align-items:center; justify-content:space-between; padding:10px 14px; border:1px solid var(--border-color); border-radius:8px;">
                    <span><i class="fa-solid fa-layer-group"></i> Product Variants</span>
                    <span id="variants-toggle-icon"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
            </div>
            <div id="variants-section" style="display:none; padding-top:10px;">
                <p class="text-sm" style="color:var(--text-secondary); margin-bottom:12px;">
                    Define attributes like Color and Size. Each combination becomes a variant with its own SKU, price, and stock.
                    Upload images per color value so customers see the right image when they pick a color.
                </p>

                <!-- Attribute List -->
                <div id="attributes-container"></div>

                <button type="button" class="btn btn-ghost btn-sm" onclick="addAttribute()" style="margin-bottom:12px;">
                    <i class="fa-solid fa-plus"></i> Add Attribute
                </button>

                <!-- Generate Variants -->
                <div id="variants-generate-area" style="display:none; margin-bottom:12px;">
                    <button type="button" class="btn btn-primary btn-sm" onclick="generateVariants()">
                        <i class="fa-solid fa-table-cells"></i> Generate Variants
                    </button>
                </div>

                <!-- Variant Grid -->
                <div id="variants-grid" style="display:none;">
                    <label class="form-label">Variant Combinations</label>
                    <div style="overflow-x:auto; border:1px solid var(--border-color); border-radius:8px;">
                        <table class="table" style="margin-bottom:0; min-width:500px;">
                            <thead>
                                <tr>
                                    <th style="width:30px;">#</th>
                                    <th>Variant</th>
                                    <th style="width:130px;">SKU</th>
                                    <th style="width:100px;">Price</th>
                                    <th style="width:100px;">Disc. Price</th>
                                    <th style="width:70px;">Stock</th>
                                    <th style="width:50px;">Default</th>
                                </tr>
                            </thead>
                            <tbody id="variants-tbody"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Hidden input for attribute/variant JSON data -->
                <input type="hidden" id="attr-json" name="attr_json" value="">
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
    const json = await apiFetch('/api/categories/list');
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
        page,
        limit: 10,
        search: document.getElementById('search-input').value,
        category_id: document.getElementById('category-filter').value
    });

    const json = await apiFetch('/api/products/list?' + params.toString());

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
                    <img src="${p.image_url || 'https://placehold.co/40x40/e5e7eb/6b7280?text=No+Img'}"
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
                    <i class="fa-solid fa-star" style="color:${p.is_featured == 1 ? 'var(--warning)' : 'var(--gray-300)'}"></i>
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
    resetVariantState();
    document.getElementById('variants-section').style.display = 'none';
    document.getElementById('variants-toggle-icon').innerHTML = '<i class="fa-solid fa-chevron-down"></i>';
    document.getElementById('product-modal').classList.add('open');
}

async function editProduct(id) {
    const json = await apiFetch(`/api/products/detail?id=${id}`);
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
    productImages = (p.images || []).map(img => ({ type: 'url', path: img.full_url, file: null, preview: null }));
    // Fallback: if no images table rows but product has a legacy image
    if (!productImages.length && p.image_url) {
        productImages = [{ type: 'url', path: p.image_url, file: null, preview: null }];
    }
    renderImageList();

    // Load variants if they exist
    resetVariantState();
    if (p.attributes && p.attributes.length > 0) {
        variantState.attributes = p.attributes.map(a => ({
            id: a.id,
            name: a.name,
            values: (a.values || []).map(v => ({
                id: v.id,
                value: v.value,
                image_path: v.image_path || null,
                image_temp_id: null
            }))
        }));
        renderAttributes();
        document.getElementById('variants-section').style.display = 'block';
        document.getElementById('variants-toggle-icon').innerHTML = '<i class="fa-solid fa-chevron-up"></i>';
    }
    if (p.variants && p.variants.length > 0) {
        variantState.variants = p.variants.map(v => ({
            sku: v.sku || '',
            price: v.price || '',
            discount_price: v.discount_price || '',
            stock: v.stock || '0',
            is_default: v.is_default ? 1 : 0,
            value_refs: (v.value_ids || []).map(() => ''), // filled below
            _label: ''
        }));
        // Map value_refs by matching attribute value IDs
        const allValIds = [];
        variantState.attributes.forEach((a, ai) => {
            a.values.forEach((v, vi) => {
                if (v.id) allValIds[ai + ':' + vi] = v.id;
            });
        });
        // Invert: valueId → ref string
        const idToRef = {};
        Object.keys(allValIds).forEach(k => { idToRef[allValIds[k]] = k; });
        p.variants.forEach((v, idx) => {
            if (v.value_ids) {
                variantState.variants[idx].value_refs = v.value_ids.map(id => idToRef[id] || '');
                const parts = [];
                variantState.variants[idx].value_refs.forEach(ref => {
                    const [ai, vi] = ref.split(':');
                    const attr = variantState.attributes[parseInt(ai)];
                    const val = attr ? attr.values[parseInt(vi)] : null;
                    if (attr && val) parts.push(`${attr.name}: ${val.value}`);
                });
                variantState.variants[idx]._label = parts.join(', ');
            }
        });
        renderVariantsGrid();
    }

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

    // Append variant data
    const attrJson = buildAttrJson();
    formData.append('attr_json', attrJson);

    // Append attribute value image files
    variantState.attributes.forEach((attr, ai) => {
        attr.values.forEach((val, vi) => {
            if (val._file && val.image_temp_id) {
                formData.append(`attr_value_img_${val.image_temp_id}`, val._file, val._file.name);
            }
        });
    });

    try {
        const action = id ? 'update' : 'create';
        const json = await apiFetch(`/api/products/${action}`, { method: 'POST', body: formData });
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
    const json = await apiFetch('/api/products/toggle-featured', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id, featured: !!featured })
    });
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
    const json = await apiFetch('/api/products/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id: deleteTargetId })
    });
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

    if (action === 'delete' && await arsConfirm(`Delete ${ids.length} product(s)?`)) {
        const json = await apiFetch('/api/products/bulk-delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ product_ids: ids })
        });
        if (json.success) {
            Toast.success(`${ids.length} product(s) deleted.`);
            loadProducts(currentPage);
        } else {
            Toast.error(json.message);
        }
    }
}

function escHtml(str) {
    if (!str) return '';
    return str.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ═══ Variant Management ═══════════════════════════════════════════

// In-memory state for the variant builder
let variantState = { attributes: [], variants: [] };
let attrImgCounter = 0;

function toggleVariantsSection() {
    const section = document.getElementById('variants-section');
    const icon = document.getElementById('variants-toggle-icon');
    const isOpen = section.style.display !== 'none';
    section.style.display = isOpen ? 'none' : 'block';
    icon.innerHTML = isOpen ? '<i class="fa-solid fa-chevron-down"></i>' : '<i class="fa-solid fa-chevron-up"></i>';
}

function addAttribute(name, values) {
    const container = document.getElementById('attributes-container');
    const idx = variantState.attributes.length;

    variantState.attributes.push({ id: null, name: name || '', values: values || [] });

    const div = document.createElement('div');
    div.className = 'attribute-block';
    div.style.cssText = 'border:1px solid var(--border-color); border-radius:8px; padding:12px; margin-bottom:10px;';
    div.dataset.idx = idx;
    div.innerHTML = `
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
            <input type="text" class="form-control attr-name" value="${escHtml(name || '')}" placeholder="Attribute name (e.g. Color, Size)" style="flex:1; font-weight:600;">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeAttribute(${idx})"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="attr-values"></div>
        <button type="button" class="btn btn-ghost btn-sm" onclick="addValue(${idx})"><i class="fa-solid fa-plus"></i> Add Value</button>
    `;
    container.appendChild(div);

    // Add existing values
    (values || []).forEach(v => addValueRow(idx, v));

    updateGenerateButton();
    updateAttrNameListeners();
}

function removeAttribute(idx) {
    variantState.attributes.splice(idx, 1);
    renderAttributes();
    updateGenerateButton();
}

function addValue(attrIdx) {
    variantState.attributes[attrIdx].values.push({ id: null, value: '', image_path: null, image_temp_id: null });
    addValueRow(attrIdx, { id: null, value: '', image_path: null, image_temp_id: null });
    updateGenerateButton();
}

function addValueRow(attrIdx, val) {
    const container = document.querySelectorAll('.attribute-block')[attrIdx];
    if (!container) return;
    const valuesDiv = container.querySelector('.attr-values');
    const vIdx = container.querySelectorAll('.value-row').length;

    const row = document.createElement('div');
    row.className = 'value-row';
    row.style.cssText = 'display:flex; align-items:center; gap:6px; margin-bottom:6px;';
    row.dataset.vi = vIdx;

    let imgHtml = '';
    const previewSrc = val.image_url || (val.image_path ? window.BASE_URL + '/uploads/products/' + val.image_path : null);
    if (previewSrc) {
        imgHtml = `<img src="${escHtml(previewSrc)}" style="width:32px;height:32px;object-fit:cover;border-radius:4px;border:1px solid var(--border-color);">`;
    }

    row.innerHTML = `
        <input type="text" class="form-control attr-val" value="${escHtml(val.value || '')}" placeholder="Value" style="flex:1;">
        <label class="btn btn-ghost btn-sm" style="cursor:pointer; margin-bottom:0; white-space:nowrap;">
            ${imgHtml || '<i class="fa-solid fa-image"></i>'}
            <input type="file" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="handleAttrValueImage(this, ${attrIdx}, ${vIdx})">
        </label>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeValue(${attrIdx}, ${vIdx})"><i class="fa-solid fa-xmark"></i></button>
    `;
    valuesDiv.appendChild(row);
    const valInput = row.querySelector('.attr-val');
    if (valInput) {
        valInput.addEventListener('input', function() {
            if (variantState.attributes[attrIdx] && variantState.attributes[attrIdx].values[vIdx]) {
                variantState.attributes[attrIdx].values[vIdx].value = this.value;
            }
            updateGenerateButton();
        });
    }
}

function removeValue(attrIdx, vIdx) {
    variantState.attributes[attrIdx].values.splice(vIdx, 1);
    renderAttributes();
    updateGenerateButton();
}

function handleAttrValueImage(input, attrIdx, vIdx) {
    const file = input.files[0];
    if (!file) return;

    const tempId = 'img_' + (attrImgCounter++);
    variantState.attributes[attrIdx].values[vIdx].image_temp_id = tempId;
    variantState.attributes[attrIdx].values[vIdx]._file = file;

    // Show preview
    const reader = new FileReader();
    reader.onload = function(e) {
        const row = document.querySelectorAll('.attribute-block')[attrIdx]
            ?.querySelectorAll('.value-row')[vIdx];
        if (row) {
            const btn = row.querySelector('label.btn');
            if (btn) btn.innerHTML = `<img src="${e.target.result}" style="width:32px;height:32px;object-fit:cover;border-radius:4px;border:1px solid var(--border-color);">`;
        }
    };
    reader.readAsDataURL(file);
}

function updateAttrNameListeners() {
    document.querySelectorAll('.attribute-block').forEach((block, idx) => {
        const input = block.querySelector('.attr-name');
        if (input) {
            input.addEventListener('input', function() {
                variantState.attributes[idx].name = this.value;
                updateGenerateButton();
            });
        }
        block.querySelectorAll('.attr-val').forEach((inp, vi) => {
            inp.addEventListener('input', function() {
                if (variantState.attributes[idx] && variantState.attributes[idx].values[vi]) {
                    variantState.attributes[idx].values[vi].value = this.value;
                }
                updateGenerateButton();
            });
        });
    });
}

function updateGenerateButton() {
    const area = document.getElementById('variants-generate-area');
    const hasAttrs = variantState.attributes.some(a => a.name && a.values.some(v => v.value));
    area.style.display = hasAttrs ? 'block' : 'none';
}

function renderAttributes() {
    const container = document.getElementById('attributes-container');
    container.innerHTML = '';
    variantState.attributes.forEach((attr, idx) => addAttribute(attr.name, attr.values));
    // Re-bind value files
    variantState.attributes.forEach((attr, idx) => {
        attr.values.forEach((val, vi) => {
            if (val._file) {
                const row = document.querySelectorAll('.attribute-block')[idx]
                    ?.querySelectorAll('.value-row')[vi];
                if (row) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const btn = row.querySelector('label.btn');
                        if (btn) btn.innerHTML = `<img src="${e.target.result}" style="width:32px;height:32px;object-fit:cover;border-radius:4px;border:1px solid var(--border-color);">`;
                    };
                    reader.readAsDataURL(val._file);
                }
            }
        });
    });
}

function generateVariants() {
    // Read current attribute values from state
    const attrs = variantState.attributes.filter(a => a.name && a.values.some(v => v.value));
    if (attrs.length === 0) {
        Toast.error('Add at least one attribute with values first.');
        return;
    }

    // Build combinations (cartesian product)
    const valueLists = attrs.map(a => a.values.filter(v => v.value));
    const combos = cartesian(valueLists);

    variantState.variants = combos.map((combo, idx) => {
        const valueRefs = [];
        const labelParts = [];
        combo.forEach((val, ai) => {
            // Find the index of this value in its attribute
            const aIdx = variantState.attributes.indexOf(attrs[ai]);
            const vIdx = variantState.attributes[aIdx].values.indexOf(val);
            valueRefs.push(`${aIdx}:${vIdx}`);
            labelParts.push(`${attrs[ai].name}: ${val.value}`);
        });
        return {
            sku: '',
            price: '',
            discount_price: '',
            stock: '0',
            is_default: idx === 0 ? 1 : 0,
            value_refs: valueRefs,
            _label: labelParts.join(', ')
        };
    });

    renderVariantsGrid();
}

function cartesian(lists) {
    if (lists.length === 0) return [[]];
    const result = [];
    const remaining = cartesian(lists.slice(1));
    for (const item of lists[0]) {
        for (const combo of remaining) {
            result.push([item, ...combo]);
        }
    }
    return result;
}

function renderVariantsGrid() {
    const grid = document.getElementById('variants-grid');
    const tbody = document.getElementById('variants-tbody');
    grid.style.display = 'block';

    tbody.innerHTML = variantState.variants.map((v, idx) => `
        <tr>
            <td style="color:var(--text-secondary);">${idx + 1}</td>
            <td><span class="text-sm">${escHtml(v._label)}</span></td>
            <td><input type="text" class="form-control" value="${escHtml(v.sku || '')}" placeholder="Auto" style="width:120px;" data-vidx="${idx}" onchange="updateVariantField(${idx}, 'sku', this.value)"></td>
            <td><input type="number" step="0.01" class="form-control" value="${v.price || ''}" placeholder="Base" style="width:90px;" data-vidx="${idx}" onchange="updateVariantField(${idx}, 'price', this.value)"></td>
            <td><input type="number" step="0.01" class="form-control" value="${v.discount_price || ''}" placeholder="Base" style="width:90px;" data-vidx="${idx}" onchange="updateVariantField(${idx}, 'discount_price', this.value)"></td>
            <td><input type="number" class="form-control" value="${v.stock}" min="0" style="width:65px;" data-vidx="${idx}" onchange="updateVariantField(${idx}, 'stock', this.value)"></td>
            <td style="text-align:center;">
                <input type="radio" name="default-variant" ${v.is_default ? 'checked' : ''} onclick="setDefaultVariant(${idx})">
            </td>
        </tr>
    `).join('');
}

function updateVariantField(idx, field, value) {
    if (variantState.variants[idx]) {
        variantState.variants[idx][field] = value;
    }
}

function setDefaultVariant(idx) {
    variantState.variants.forEach((v, i) => v.is_default = i === idx ? 1 : 0);
}

function resetVariantState() {
    variantState = { attributes: [], variants: [] };
    attrImgCounter = 0;
    document.getElementById('attributes-container').innerHTML = '';
    document.getElementById('variants-grid').style.display = 'none';
    document.getElementById('variants-generate-area').style.display = 'none';
    document.getElementById('attr-json').value = '';
}

/**
 * Build the attr_json payload from current variantState
 */
function buildAttrJson() {
    // Clean up: only keep attributes with a name
    const attrs = variantState.attributes
        .filter(a => a.name)
        .map(a => ({
            id: a.id || null,
            name: a.name,
            values: a.values
                .filter(v => v.value)
                .map(v => ({
                    id: v.id || null,
                    value: v.value,
                    image_path: v.image_path || null,
                    image_temp_id: v.image_temp_id || null
                }))
        }));

    const variants = variantState.variants.map(v => ({
        sku: v.sku || '',
        price: v.price || '',
        discount_price: v.discount_price || '',
        stock: v.stock || '0',
        is_default: v.is_default ? 1 : 0,
        value_refs: v.value_refs || []
    }));

    return JSON.stringify({ attributes: attrs, variants: variants });
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
