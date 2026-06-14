<?php
/**
 * Shop Page
 * Easy Shopping A.R.S
 */
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Accept both 'q' (from header) and 'search' param names
$search = isset($_GET['q']) ? trim($_GET['q']) : (isset($_GET['search']) ? trim($_GET['search']) : null);
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
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
        // Try FULLTEXT + LIKE combined for best results
        $likeTerm = '%' . $search . '%';
        $boolTerm = '+' . implode('* +', explode(' ', $search)) . '*';
        $query .= " AND (
            MATCH(p.name, p.description) AGAINST(? IN BOOLEAN MODE)
            OR p.name LIKE ?
            OR p.description LIKE ?
            OR c.name LIKE ?
        )";
        $params[] = $boolTerm;
        $params[] = $likeTerm;
        $params[] = $likeTerm;
        $params[] = $likeTerm;
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
    $count_query = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
    $count_params = [];

    if ($category_id) {
        $count_query .= " AND p.category_id = ?";
        $count_params[] = $category_id;
    }

    if ($search) {
        $count_query .= " AND (
            MATCH(p.name, p.description) AGAINST(? IN BOOLEAN MODE)
            OR p.name LIKE ?
            OR p.description LIKE ?
            OR c.name LIKE ?
        )";
        $count_params[] = $boolTerm;
        $count_params[] = $likeTerm;
        $count_params[] = $likeTerm;
        $count_params[] = $likeTerm;
    }

    if ($min_price !== null) {
        $count_query .= " AND p.price >= ?";
        $count_params[] = $min_price;
    }

    if ($max_price !== null) {
        $count_query .= " AND p.price <= ?";
        $count_params[] = $max_price;
    }

    if ($in_stock_only) {
        $count_query .= " AND p.stock > 0";
    }

    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($count_params);
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
    // Fallback: retry without FULLTEXT (index might not exist yet)
    try {
        $query = "SELECT p.*, c.name as category_name FROM products p
                  LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
        $params = [];

        if ($category_id) {
            $query .= " AND p.category_id = ?";
            $params[] = $category_id;
        }

        if ($search) {
            $likeTerm = '%' . $search . '%';
            $query .= " AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
            $params[] = $likeTerm; $params[] = $likeTerm; $params[] = $likeTerm;
        }

        if ($min_price !== null) { $query .= " AND p.price >= ?"; $params[] = $min_price; }
        if ($max_price !== null) { $query .= " AND p.price <= ?"; $params[] = $max_price; }
        if ($in_stock_only) { $query .= " AND p.stock > 0"; }

        switch ($sort_by) {
            case 'price_low': $query .= " ORDER BY p.price ASC"; break;
            case 'price_high': $query .= " ORDER BY p.price DESC"; break;
            case 'name': $query .= " ORDER BY p.name ASC"; break;
            default: $query .= " ORDER BY p.created_at DESC"; break;
        }

        $count_query = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
        $count_params = [];

        if ($category_id) { $count_query .= " AND p.category_id = ?"; $count_params[] = $category_id; }
        if ($search) {
            $likeTerm = '%' . $search . '%';
            $count_query .= " AND (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
            $count_params[] = $likeTerm; $count_params[] = $likeTerm; $count_params[] = $likeTerm;
        }
        if ($min_price !== null) { $count_query .= " AND p.price >= ?"; $count_params[] = $min_price; }
        if ($max_price !== null) { $count_query .= " AND p.price <= ?"; $count_params[] = $max_price; }
        if ($in_stock_only) { $count_query .= " AND p.stock > 0"; }

        $count_stmt = $pdo->prepare($count_query);
        $count_stmt->execute($count_params);
        $total_products = $count_stmt->fetchColumn();
        $total_pages = ceil($total_products / $per_page);

        $offset = ($page - 1) * $per_page;
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $per_page; $params[] = $offset;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
    } catch (PDOException $e2) {
        $products = [];
        $error = $e2->getMessage();
        $total_products = 0;
        $total_pages = 0;
    }
}

/**
 * Highlight search terms in text
 */
function highlightSearch($text, $search) {
    if (!$search) return h($text);
    $words = explode(' ', trim($search));
    $pattern = '/(' . implode('|', array_map('preg_quote', $words)) . ')/iu';
    return preg_replace($pattern, '<mark style="background:#fef3c7;color:#92400e;padding:0 2px;border-radius:2px;">$1</mark>', h($text));
}

/**
 * Get related category suggestions for empty results
 */
function getRelatedSuggestions($pdo, $search) {
    try {
        $likeTerm = '%' . $search . '%';
        $stmt = $pdo->prepare("
            SELECT id, name, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
            FROM categories c
            WHERE c.name LIKE ?
            ORDER BY product_count DESC
            LIMIT 4
        ");
        $stmt->execute([$likeTerm]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function getPopularProducts($pdo, $limit = 4) {
    try {
        $stmt = $pdo->query("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.stock > 0 AND p.is_featured = 1
            ORDER BY p.created_at DESC
            LIMIT $limit
        ");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// ── SEO meta ─────────────────────────
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
:root { --gold: #ea6c00; }

.filters-sidebar {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
    height: fit-content;
}

.filter-section { margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #f3f4f6; }
.filter-title { font-weight: 700; color: #111827; margin-bottom: 16px; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; }

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
.product-image img { width: 100%; height: 100%; object-fit: cover; }

@media (max-width: 767px) {
    .product-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; padding: 5px; }
    .product-card { border-radius: 8px; }
    .product-info { padding: 10px !important; }
    .product-category { font-size: 0.65rem !important; }
    .product-title { font-size: 0.85rem !important; margin-bottom: 8px !important; height: 2.8em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
    .product-price { font-size: 0.95rem !important; margin-bottom: 10px !important; }
    .original-price { font-size: 0.75rem !important; }
    .add-to-cart-btn { padding: 8px !important; font-size: 0.8rem !important; border-radius: 6px !important; }
    .mobile-filter-trigger { display: flex; align-items: center; justify-content: center; gap: 8px; background: #fff; border: 1px solid #ddd; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; width: 100%; }
}

.price-range-inputs { display: flex; gap: 8px; align-items: center; }
.price-range-inputs input { flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.85rem; }
.sort-select { padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.9rem; min-width: 180px; }
.discount-badge { position: absolute; top: 10px; right: 10px; background: #ef4444; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; z-index: 1; }

/* Active filter tags */
.active-filters { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
.filter-tag { display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; border: 1px solid #e2e8f0; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; color: #334155; }
.filter-tag a { color: #94a3b8; text-decoration: none; font-weight: 700; margin-left: 4px; }
.filter-tag a:hover { color: #ef4444; }

/* Active category indicator */
.active-category { font-weight: 700 !important; color: var(--gold) !important; position: relative; }
.active-category::after { content: '✓'; margin-left: 6px; font-size: 0.7rem; }
</style>

<div class="container py-4">
    <!-- Breadcrumbs & Stats -->
    <div class="row mb-4 align-items-end">
        <div class="col-8">
            <h1 class="h3 fw-bold mb-1">
                <?php if ($search): ?>
                    Search: "<?php echo h($search); ?>"
                <?php elseif ($_active_cat): ?>
                    <?php echo h($_active_cat); ?>
                <?php else: ?>
                    Shop Collections
                <?php endif; ?>
            </h1>
            <div class="text-muted small"><?php echo $total_products; ?> Items Found</div>
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

    <!-- Active filter tags -->
    <?php if ($search || $category_id || $min_price !== null || $max_price !== null || $in_stock_only): ?>
    <div class="active-filters">
        <?php if ($search): ?>
            <span class="filter-tag">"<?php echo h($search); ?>" <a href="<?php echo url('/shop'); ?>" onclick="event.preventDefault();const u=new URL(window.location);u.searchParams.delete('q');u.searchParams.delete('search');u.searchParams.set('page','1');window.location=u.toString();">&times;</a></span>
        <?php endif; ?>
        <?php if ($_active_cat): ?>
            <span class="filter-tag"><?php echo h($_active_cat); ?> <a href="<?php echo url('/shop'); ?>" onclick="event.preventDefault();const u=new URL(window.location);u.searchParams.delete('category');u.searchParams.set('page','1');window.location=u.toString();">&times;</a></span>
        <?php endif; ?>
        <?php if ($min_price !== null): ?>
            <span class="filter-tag">Min: Rs.<?php echo number_format($min_price,0); ?> <a href="<?php echo url('/shop'); ?>" onclick="event.preventDefault();const u=new URL(window.location);u.searchParams.delete('min_price');u.searchParams.set('page','1');window.location=u.toString();">&times;</a></span>
        <?php endif; ?>
        <?php if ($max_price !== null): ?>
            <span class="filter-tag">Max: Rs.<?php echo number_format($max_price,0); ?> <a href="<?php echo url('/shop'); ?>" onclick="event.preventDefault();const u=new URL(window.location);u.searchParams.delete('max_price');u.searchParams.set('page','1');window.location=u.toString();">&times;</a></span>
        <?php endif; ?>
        <?php if ($in_stock_only): ?>
            <span class="filter-tag">In Stock Only <a href="<?php echo url('/shop'); ?>" onclick="event.preventDefault();const u=new URL(window.location);u.searchParams.delete('in_stock');u.searchParams.set('page','1');window.location=u.toString();">&times;</a></span>
        <?php endif; ?>
        <a href="<?php echo url('/shop'); ?>" class="filter-tag" style="background:#fef2f2;border-color:#fecaca;color:#dc2626;">Clear All</a>
    </div>
    <?php endif; ?>

    <!-- Mobile Filter Trigger -->
    <button class="mobile-filter-trigger d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#shopFilters">
        <i class="bi bi-sliders"></i> Filters & Sorting
    </button>

    <div class="row">
        <div class="col-lg-3 d-none d-lg-block">
            <div class="filters-sidebar">
                <?php render_shop_filters($all_categories, $category_id, $min_price, $max_price, $sort_by, $in_stock_only); ?>
            </div>
        </div>

        <div class="offcanvas offcanvas-bottom" tabindex="-1" id="shopFilters" style="height: 80vh;">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title fw-bold">Filters & Sorting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <div class="mb-4">
                    <label class="fw-bold mb-2 small text-uppercase text-muted">Order By</label>
                    <select class="sort-select w-100" onchange="changeSort(this.value)">
                        <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="price_low" <?php echo $sort_by === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort_by === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </div>
                <?php render_shop_filters($all_categories, $category_id, $min_price, $max_price, $sort_by, $in_stock_only); ?>
            </div>
        </div>

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
                                <div class="product-category text-uppercase small" style="color:#ea6c00; font-weight:700; margin-bottom:4px; font-size:0.7rem;">
                                    <?php echo $search ? highlightSearch($product['category_name'] ?? 'General', $search) : h($product['category_name'] ?? 'General'); ?>
                                </div>
                                <h3 class="product-title small mb-2" style="font-weight:600; line-height:1.3;">
                                    <a href="<?php echo url('/product/' . ($product['slug'] ?? $product['id'])); ?>" class="text-decoration-none text-dark">
                                        <?php echo $search ? highlightSearch($product['name'], $search) : h($product['name']); ?>
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
                    <h4 class="mt-3">No products found matching your search.</h4>
                    <?php if ($search): ?>
                        <p class="text-muted mb-4">Try different keywords, browse categories, or check for typos.</p>

                        <?php $relatedCats = getRelatedSuggestions($pdo, $search); ?>
                        <?php if (!empty($relatedCats)): ?>
                            <div class="mb-4">
                                <p class="fw-bold mb-2">Related Categories:</p>
                                <div class="d-flex flex-wrap justify-content-center gap-2">
                                    <?php foreach ($relatedCats as $cat): ?>
                                        <a href="<?php echo url('/shop?category=' . $cat['id']); ?>" class="btn btn-outline-dark btn-sm rounded-pill">
                                            <?php echo h($cat['name']); ?> (<?php echo $cat['product_count']; ?>)
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mt-3">
                            <a href="<?php echo url('/shop'); ?>" class="btn btn-dark me-2">Browse All Products</a>
                            <a href="<?php echo url('/categories'); ?>" class="btn btn-outline-dark">Browse Categories</a>
                        </div>

                        <!-- Popular products when no results -->
                        <?php $popular = getPopularProducts($pdo, 4); ?>
                        <?php if (!empty($popular)): ?>
                            <hr class="my-5">
                            <h5 class="fw-bold mb-4">Popular Products You Might Like</h5>
                            <div class="row g-3 justify-content-center">
                                <?php foreach ($popular as $rp): ?>
                                    <div class="col-6 col-md-3">
                                        <div class="card h-100 border-0 shadow-sm rounded-3">
                                            <a href="<?php echo url('/product/' . ($rp['slug'] ?? $rp['id'])); ?>">
                                                <img src="<?php echo getProductImage($rp['image']); ?>" class="card-img-top p-2 rounded-4" style="aspect-ratio:1/1; object-fit:contain;">
                                            </a>
                                            <div class="card-body p-2 text-center">
                                                <h6 class="text-truncate mb-1" style="font-size:0.85rem;"><?php echo h($rp['name']); ?></h6>
                                                <div class="fw-bold small">Rs. <?php echo number_format($rp['price'], 0); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <p class="text-muted mb-4">No products found in this category.</p>
                        <a href="<?php echo url('/shop'); ?>" class="btn btn-dark">Browse All Products</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
function render_shop_filters($all_categories, $category_id, $min_price, $max_price, $sort_by, $in_stock_only = false) {
    ?>
    <div class="filter-section">
        <div class="filter-title small text-uppercase text-muted fw-bold">Categories</div>
        <div class="list-group list-group-flush small">
            <a href="<?php echo url('/shop'); ?>" class="list-group-item list-group-item-action border-0 px-0 <?php echo !$category_id ? 'fw-bold text-dark' : 'text-muted'; ?>">All Items</a>
            <?php foreach ($all_categories as $cat): ?>
                <a href="<?php echo url('/shop?category=' . $cat['id'] . ($sort_by !== 'newest' ? '&sort=' . $sort_by : '')); ?>" class="list-group-item list-group-item-action border-0 px-0 <?php echo $category_id == $cat['id'] ? 'active-category fw-bold text-dark' : 'text-muted'; ?>">
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
            <?php if ($search): ?>
            <input type="hidden" name="q" value="<?php echo h($search); ?>">
            <?php endif; ?>
        </form>
    </div>

    <div class="filter-section">
        <div class="filter-title small text-uppercase text-muted fw-bold d-flex align-items-center gap-2">
            <input type="checkbox" id="inStockFilter" onchange="toggleStockFilter()" <?php echo $in_stock_only ? 'checked' : ''; ?> style="width:16px;height:16px;">
            <label for="inStockFilter" class="mb-0 cursor-pointer">In Stock Only</label>
        </div>
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
    // Normalize: prefer 'q' over 'search' for clean URLs
    if (isset($params['search']) && !isset($params['q'])) {
        $params['q'] = $params['search'];
    }
    unset($params['search']);
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

function toggleStockFilter() {
    const url = new URL(window.location);
    if (document.getElementById('inStockFilter').checked) {
        url.searchParams.set('in_stock', '1');
    } else {
        url.searchParams.delete('in_stock');
    }
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
            window.location.href = '<?php echo url("/cart"); ?>';
        } else if (data.require_login) {
            const modal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
            modal.show();
            btn.innerHTML = originalText;
            btn.disabled = false;
        } else {
            showToast('Notice', data.message || 'Could not add item', 'warning');
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
