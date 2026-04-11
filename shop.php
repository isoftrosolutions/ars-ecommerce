<?php
/**
 * Shop Page
 * Easy Shopping A.R.S
 */
$page_title = "Shop All Products";
include 'includes/header-bootstrap.php';

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

// Get price range for filters
try {
    $price_range_stmt = $pdo->query("SELECT MIN(price) as min_price, MAX(price) as max_price FROM products WHERE stock > 0");
    $price_range = $price_range_stmt->fetch();
} catch (PDOException $e) {
    $price_range = ['min_price' => 0, 'max_price' => 10000];
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
        case 'rating':
            $query .= " ORDER BY (SELECT AVG(rating) FROM product_reviews WHERE product_id = p.id AND status = 'approved') DESC";
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
?>

<style>
/* Enhanced Shop Styles */
.filters-sidebar {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 25px;
    position: sticky;
    top: 20px;
}

.filter-section {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}

.filter-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.filter-title {
    font-weight: 600;
    color: #374151;
    margin-bottom: 15px;
    font-size: 1rem;
}

.price-range-inputs {
    display: flex;
    gap: 10px;
    align-items: center;
}

.price-range-inputs input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.9rem;
}

.sort-select {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    background: white;
    font-size: 0.9rem;
}

.filter-checkbox {
    margin-right: 8px;
}

.clear-filters {
    background: #6b7280;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.2s;
}

.clear-filters:hover {
    background: #4b5563;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.product-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.product-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.discount-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #ef4444;
    color: white;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
}

.product-info {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-category {
    color: var(--ember);
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.product-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 12px;
    line-height: 1.4;
}

.product-price {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--gold);
    margin-bottom: 15px;
}

.original-price {
    text-decoration: line-through;
    color: #9ca3af;
    font-size: 1rem;
    margin-left: 8px;
}

.add-to-cart-btn {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, var(--ember) 0%, var(--gold) 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.add-to-cart-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(249, 115, 22, 0.3);
}

.pagination-custom {
    margin-top: 40px;
}

.pagination-custom .page-link {
    color: var(--ember);
    border-color: #e9ecef;
    padding: 10px 15px;
    margin: 0 2px;
    border-radius: 8px;
}

.pagination-custom .page-link:hover {
    background: var(--ember);
    color: white;
    border-color: var(--ember);
}

.pagination-custom .page-item.active .page-link {
    background: var(--ember);
    border-color: var(--ember);
}

.results-info {
    color: #6b7280;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .filters-sidebar {
        position: static;
        margin-bottom: 30px;
    }

    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
    }
}
</style>

<div class="container py-5">
    <!-- Header with Search -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold mb-0">Shop All Products</h2>
            <p class="text-muted">Discover our curated collection</p>
        </div>
        <div class="col-md-4">
            <form action="<?php echo url('/shop'); ?>" method="GET" class="d-flex" id="searchForm">
                <input type="text" name="search" class="form-control me-2" placeholder="Search products..." value="<?php echo h($search); ?>" id="searchInput">
                <button type="submit" class="btn btn-dark">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="filters-sidebar">
                <!-- Categories Filter -->
                <div class="filter-section">
                    <div class="filter-title">Categories</div>
                    <div class="mb-2">
                        <a href="<?php echo url('/shop'); ?>" class="text-decoration-none d-block py-2 <?php echo !$category_id ? 'fw-bold text-primary' : 'text-muted'; ?>">
                            All Categories
                        </a>
                    </div>
                    <?php foreach ($all_categories as $cat): ?>
                        <div class="mb-2">
                            <a href="<?php echo url('/shop?category=' . $cat['id']); ?>" class="text-decoration-none d-block py-2 <?php echo $category_id == $cat['id'] ? 'fw-bold text-primary' : 'text-muted'; ?>">
                                <?php echo h($cat['name']); ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Price Range Filter -->
                <div class="filter-section">
                    <div class="filter-title">Price Range</div>
                    <form method="GET" action="<?php echo url('/shop'); ?>" id="priceForm">
                        <div class="price-range-inputs mb-3">
                            <input type="number" name="min_price" placeholder="Min" value="<?php echo $min_price !== null ? $min_price : ''; ?>" min="0" step="100">
                            <span>-</span>
                            <input type="number" name="max_price" placeholder="Max" value="<?php echo $max_price !== null ? $max_price : ''; ?>" min="0" step="100">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">Apply Filter</button>
                        <input type="hidden" name="search" value="<?php echo h($search); ?>">
                        <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                        <input type="hidden" name="sort" value="<?php echo $sort_by; ?>">
                        <?php if ($in_stock_only): ?>
                            <input type="hidden" name="in_stock" value="1">
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Availability Filter -->
                <div class="filter-section">
                    <div class="filter-title">Availability</div>
                    <label class="d-flex align-items-center">
                        <input type="checkbox" class="filter-checkbox" id="inStockOnly" <?php echo $in_stock_only ? 'checked' : ''; ?>>
                        In Stock Only
                    </label>
                </div>

                <!-- Clear Filters -->
                <div class="filter-section">
                    <button class="clear-filters w-100" onclick="clearAllFilters()">
                        <i class="bi bi-x-circle me-2"></i>Clear All Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="col-lg-9">
            <!-- Sorting and Results Info -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="results-info">
                    <?php if ($total_products > 0): ?>
                        Showing <?php echo (($page - 1) * $per_page) + 1; ?> - <?php echo min($page * $per_page, $total_products); ?> of <?php echo $total_products; ?> products
                    <?php else: ?>
                        No products found
                    <?php endif; ?>
                </div>
                <div class="d-flex align-items-center">
                    <label class="me-2 text-muted">Sort by:</label>
                    <select class="sort-select" onchange="changeSort(this.value)">
                        <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest</option>
                        <option value="price_low" <?php echo $sort_by === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort_by === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Name A-Z</option>
                        <option value="rating" <?php echo $sort_by === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                    </select>
                </div>
            </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo h($error); ?></div>
    <?php endif; ?>

            <!-- Products Grid -->
            <?php if (count($products) > 0): ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <a href="<?php echo url('/product/' . ($product['slug'] ?? $product['id'])); ?>">
                                    <img src="<?php echo getProductImage($product['image'] ?? ''); ?>" alt="<?php echo h($product['name']); ?>">
                                </a>
                                <?php if ($product['discount_price']): ?>
                                    <div class="discount-badge">
                                        -<?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>%
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <div class="product-category"><?php echo h($product['category_name'] ?? 'Uncategorized'); ?></div>
                                <h3 class="product-title">
                                    <a href="<?php echo url('/product/' . ($product['slug'] ?? $product['id'])); ?>" class="text-decoration-none text-dark">
                                        <?php echo h($product['name']); ?>
                                    </a>
                                </h3>
                                <div class="product-price">
                                    <?php if ($product['discount_price']): ?>
                                        Rs. <?php echo number_format($product['discount_price'], 2); ?>
                                        <span class="original-price">Rs. <?php echo number_format($product['price'], 2); ?></span>
                                    <?php else: ?>
                                        Rs. <?php echo number_format($product['price'], 2); ?>
                                    <?php endif; ?>
                                </div>
                                <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>, this)">
                                    <i class="bi bi-cart-plus me-2"></i>Add to Cart
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav class="pagination-custom">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo buildPageUrl($page - 1); ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);

                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo buildPageUrl(1); ?>">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="<?php echo buildPageUrl($i); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo buildPageUrl($total_pages); ?>"><?php echo $total_pages; ?></a>
                                </li>
                            <?php endif; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?php echo buildPageUrl($page + 1); ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-search text-muted display-1"></i>
                    <p class="mt-3 fs-5">No products found matching your criteria.</p>
                    <p class="text-muted">Try adjusting your filters or search terms.</p>
                    <a href="<?php echo url('/shop'); ?>" class="btn btn-primary mt-2">Clear Filters & Browse All</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Helper function to build pagination URLs
function buildPageUrl($page_num) {
    $params = $_GET;
    $params['page'] = $page_num;
    return url('/shop') . '?' . http_build_query($params);
}
?>

<script>
// Add to cart function
function addToCart(productId, button) {
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Adding...';

    fetch('<?php echo url("/cart-action"); ?>?action=add&id=' + productId)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count
            const cartCounter = document.querySelector('.cart-count');
            if (cartCounter && data.cart_count !== undefined) {
                cartCounter.textContent = data.cart_count;
            }

            button.innerHTML = '<i class="bi bi-check-circle me-2"></i>Added!';
            button.classList.add('btn-success');

            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = originalText;
                button.classList.remove('btn-success');
            }, 2000);
        } else {
            alert(data.message || 'Failed to add item to cart');
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the item to cart');
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Sort change handler
function changeSort(sortValue) {
    const url = new URL(window.location);
    url.searchParams.set('sort', sortValue);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
}

// In stock filter handler
document.getElementById('inStockOnly').addEventListener('change', function() {
    const url = new URL(window.location);
    if (this.checked) {
        url.searchParams.set('in_stock', '1');
    } else {
        url.searchParams.delete('in_stock');
    }
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
});

// Clear all filters
function clearAllFilters() {
    window.location.href = '<?php echo url("/shop"); ?>';
}

// Auto-submit price filter on enter
document.querySelectorAll('.price-range-inputs input').forEach(input => {
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('priceForm').submit();
        }
    });
});
</script>
</div>

<script>
// Add to cart function
function addToCart(productId, button) {
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Adding...';

    fetch('<?php echo url("/cart-action"); ?>?action=add&id=' + productId)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update cart count if there's a cart counter in the header
            const cartCounter = document.querySelector('.cart-count');
            if (cartCounter && data.cart_count !== undefined) {
                cartCounter.textContent = data.cart_count;
            }

            // Show success feedback
            button.classList.remove('btn-outline-dark');
            button.classList.add('btn-success');
            button.innerHTML = '<i class="bi bi-check-circle me-1"></i>Added!';

            // Reset button after 2 seconds
            setTimeout(() => {
                button.disabled = false;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-dark');
                button.innerHTML = originalText;
            }, 2000);
        } else {
            alert(data.message || 'Failed to add item to cart');
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the item to cart');
        button.disabled = false;
        button.innerHTML = originalText;
    });
}
</script>

<?php include 'includes/footer-bootstrap.php'; ?>
