<?php
/**
 * Shop Page
 * Easy Shopping A.R.S
 */
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Enhanced Filtering and Pagination Logic
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$in_stock_only = isset($_GET['in_stock']) && $_GET['in_stock'] === '1';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 12;

// Get all categories for filter dropdown
try {
    $categories_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $all_categories = $categories_stmt->fetchAll();
} catch (PDOException $e) {
    $all_categories = [];
}

// Build query with filters
try {
    $query = "SELECT p.*, c.name as category_name FROM products p
              LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
    $params = [];

    if ($category_id) {
        $query .= " AND p.category_id = ?";
        $params[] = $category_id;
    }

    if ($search) {
        $query .= " AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($min_price !== null) {
        $query .= " AND p.price >= ?";
        $params[] = $min_price;
    }

    if ($max_price !== null) {
        $query .= " AND p.price <= ?";
        $params[] = $max_price;
    }

    if ($in_stock_only) {
        $query .= " AND p.stock > 0";
    }

    // Sorting
    switch ($sort_by) {
        case 'price_low':
            $query .= " ORDER BY p.price ASC";
            break;
        case 'price_high':
            $query .= " ORDER BY p.price DESC";
            break;
        case 'name':
            $query .= " ORDER BY p.name ASC";
            break;
        case 'newest':
        default:
            $query .= " ORDER BY p.created_at DESC";
            break;
    }

    // Get total count for pagination
    $count_query = str_replace("SELECT p.*, c.name as category_name", "SELECT COUNT(*)", $query);
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $total_products = $count_stmt->fetchColumn();
    $total_pages = ceil($total_products / $per_page);

    // Add pagination
    $offset = ($page - 1) * $per_page;
    $query .= " LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

} catch (PDOException $e) {
    $products = [];
    $error = $e->getMessage();
    $total_products = 0;
    $total_pages = 0;
}

// ── SEO meta — set BEFORE header include ─────────────────────
$_active_cat = null;
if ($category_id) {
    foreach ($all_categories as $c) {
        if ($c['id'] == $category_id) { $_active_cat = $c['name']; break; }
    }
}

if ($search) {
    $page_title     = 'Search Results for "' . $search . '" — ARS Nepal';
    $page_meta_desc = 'Shop ' . $total_products . ' results for "' . $search . '" at Easy Shopping A.R.S — Nepal\'s trusted online store.';
} elseif ($_active_cat) {
    $page_title     = 'Buy ' . $_active_cat . ' Online in Nepal | Easy Shopping A.R.S';
    $page_meta_desc = 'Shop the best ' . $_active_cat . ' products online at Easy Shopping A.R.S Nepal. Fast delivery, eSewa & COD accepted.';
} else {
    $page_title     = 'Online Shop Nepal — Electronics, Fashion & More | Easy Shopping A.R.S';
    $page_meta_desc = 'Browse ' . $total_products . '+ products at Easy Shopping A.R.S. Shop electronics, fashion, home goods with fast delivery across Nepal.';
}

include 'includes/header-bootstrap.php';
?>

<style>
/* ═══ Enhanced Shop & Mobile Responsiveness ═══ */
:root {
    --gold: #ea6c00;
}

/* Sidebar Styling (Desktop Default) */
.filters-sidebar {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
    height: fit-content;
}

.filter-section {
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f3f4f6;
}

.filter-title {
    font-weight: 700;
    color: #111827;
    margin-bottom: 16px;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Product Grid Adjustments */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
}

.product-card {
    background: white;
    border: 1px solid #f1f5f9;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.08);
}

.product-image {
    position: relative;
    aspect-ratio: 1/1;
    overflow: hidden;
    background: #f8fafc;
}
.product-image img {
    width: 100%; height: 100%; object-fit: cover;
}

/* 📱 MOBILE SPECIFIC OVERRIDES */
@media (max-width: 767px) {
    .product-grid {
        grid-template-columns: repeat(2, 1fr); /* 2 items per row */
        gap: 12px;
        padding: 5px;
    }

    .product-card { border-radius: 8px; }
    .product-info { padding: 10px !important; }
    .product-category { font-size: 0.65rem !important; }
    .product-title { 
        font-size: 0.85rem !important; 
        margin-bottom: 8px !important;
        height: 2.8em;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    .product-price { font-size: 0.95rem !important; margin-bottom: 10px !important; }
    .original-price { font-size: 0.75rem !important; }
    .add-to-cart-btn { 
        padding: 8px !important; 
        font-size: 0.8rem !important;
        border-radius: 6px !important;
    }
    
    .mobile-filter-trigger {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: #fff;
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 600;
        width: 100%;
    }
}

.price-range-inputs {
    display: flex;
    gap: 8px;
    align-items: center;
}

.price-range-inputs input {
    flex: 1;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.85rem;
}

.sort-select {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 0.9rem;
    min-width: 180px;
}

.discount-badge {
    position: absolute;
    top: 10px; right: 10px;
    background: #ef4444;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    z-index: 1;
}
</style>

<div class="container py-4">
    <!-- Breadcrumbs & Stats -->
    <div class="row mb-4 align-items-end">
        <div class="col-8">
            <h1 class="h3 fw-bold mb-1">Shop Collections</h1>
            <div class="text-muted small">
                <?php echo $total_products; ?> Items Found
            </div>
        </div>
        <div class="col-4 d-none d-lg-block text-end">
            <label class="me-2 text-muted small">Sort:</label>
            <select class="sort-select" onchange="changeSort(this.value)">
                <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                <option value="price_low" <?php echo $sort_by === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_high" <?php echo $sort_by === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Alphabetical</option>
            </select>
        </div>
    </div>

    <!-- Mobile Filter Trigger -->
    <button class="mobile-filter-trigger d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#shopFilters">
        <i class="bi bi-sliders"></i> Filters & Sorting
    </button>

    <div class="row">
        <!-- 💻 Desktop Filters Sidebar -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="filters-sidebar">
                <?php render_shop_filters($all_categories, $category_id, $min_price, $max_price, $sort_by); ?>
            </div>
        </div>

        <!-- 📱 Mobile Filters Offcanvas -->
        <div class="offcanvas offcanvas-bottom" tabindex="-1" id="shopFilters" style="height: 80vh;">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title fw-bold">Filters & Sorting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <!-- Mobile Sort -->
                <div class="mb-4">
                    <label class="fw-bold mb-2 small text-uppercase text-muted">Order By</label>
                    <select class="sort-select w-100" onchange="changeSort(this.value)">
                        <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="price_low" <?php echo $sort_by === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort_by === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </div>
                <?php render_shop_filters($all_categories, $category_id, $min_price, $max_price, $sort_by); ?>
            </div>
        </div>

        <!-- Products Column -->
        <div class="col-lg-9">
            <?php if (count($products) > 0): ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <a href="<?php echo url('/product/' . ($product['slug'] ?? $product['id'])); ?>">
                                    <img src="<?php echo getProductImage($product['image'] ?? ''); ?>" alt="<?php echo h($product['name']); ?>" loading="lazy">
                                </a>
                                <?php if ($product['discount_price']): ?>
                                    <div class="discount-badge">
                                        -<?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>%
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info p-3 flex-grow-1 d-flex flex-column">
                                <div class="product-category text-uppercase small" style="color:#ea6c00; font-weight:700; margin-bottom:4px; font-size:0.7rem;"><?php echo h($product['category_name'] ?? 'General'); ?></div>
                                <h3 class="product-title small mb-2" style="font-weight:600; line-height:1.3;">
                                    <a href="<?php echo url('/product/' . ($product['slug'] ?? $product['id'])); ?>" class="text-decoration-none text-dark">
                                        <?php echo h($product['name']); ?>
                                    </a>
                                </h3>
                                <div class="product-price mb-2" style="font-weight:700; color:#111;">
                                    <?php if ($product['discount_price']): ?>
                                        Rs. <?php echo number_format($product['discount_price'], 0); ?>
                                        <span class="original-price text-muted text-decoration-line-through ms-1 small" style="font-weight:400; font-size:0.8rem;">Rs. <?php echo number_format($product['price'], 0); ?></span>
                                    <?php else: ?>
                                        Rs. <?php echo number_format($product['price'], 0); ?>
                                    <?php endif; ?>
                                </div>
                                <button class="add-to-cart-btn w-100 py-2 border-0 bg-dark text-white rounded-3 mt-auto" onclick="addToCart(<?php echo $product['id']; ?>, this)">
                                    <i class="bi bi-cart-plus me-1"></i> Add
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav class="mt-5">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo buildPageUrl($i); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-emoji-frown display-1 text-muted"></i>
                    <p class="mt-3">No products found matching your search.</p>
                    <a href="<?php echo url('/shop'); ?>" class="btn btn-outline-dark">Browse All Products</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
/**
 * Helper to render filter sidebar (used in desktop and mobile offcanvas)
 */
function render_shop_filters($all_categories, $category_id, $min_price, $max_price, $sort_by) {
    ?>
    <div class="filter-section">
        <div class="filter-title small text-uppercase text-muted fw-bold">Categories</div>
        <div class="list-group list-group-flush small">
            <a href="<?php echo url('/shop'); ?>" class="list-group-item list-group-item-action border-0 px-0 <?php echo !$category_id ? 'fw-bold text-dark' : 'text-muted'; ?>">All Items</a>
            <?php foreach ($all_categories as $cat): ?>
                <a href="<?php echo url('/shop?category=' . $cat['id']); ?>" class="list-group-item list-group-item-action border-0 px-0 <?php echo $category_id == $cat['id'] ? 'fw-bold text-dark' : 'text-muted'; ?>">
                    <?php echo h($cat['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="filter-section">
        <div class="filter-title small text-uppercase text-muted fw-bold">Price Filter</div>
        <form method="GET" action="<?php echo url('/shop'); ?>">
            <div class="price-range-inputs mb-3">
                <input type="number" name="min_price" placeholder="Min" value="<?php echo $min_price; ?>">
                <input type="number" name="max_price" placeholder="Max" value="<?php echo $max_price; ?>">
            </div>
            <button type="submit" class="btn btn-dark btn-sm w-100 mb-2">Apply Filter</button>
            <input type="hidden" name="category" value="<?php echo $category_id; ?>">
            <input type="hidden" name="sort" value="<?php echo $sort_by; ?>">
        </form>
    </div>

    <div class="filter-section border-0">
        <button class="btn btn-link btn-sm text-danger p-0 text-decoration-none" onclick="window.location.href='<?php echo url('/shop'); ?>'">
            <i class="bi bi-x-circle me-1"></i> Clear All Filters
        </button>
    </div>
    <?php
}

function buildPageUrl($page_num) {
    $params = $_GET;
    $params['page'] = $page_num;
    return url('/shop') . '?' . http_build_query($params);
}
?>

<script>
function changeSort(val) {
    const url = new URL(window.location);
    url.searchParams.set('sort', val);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}

function addToCart(productId, btn) {
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '...';

    fetch('<?php echo url("/cart-action"); ?>?action=add&id=' + productId)
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Redirect to cart page with product details
            window.location.href = '<?php echo url("/cart"); ?>';
        } else if (data.require_login) {
            const modal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
            modal.show();
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('bg-dark');
            btn.disabled = false;
        }, 1500);
    });
}
</script>

<?php include 'includes/footer-bootstrap.php'; ?>
