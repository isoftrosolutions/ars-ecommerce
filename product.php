<?php
/**
 * Individual Product Detail Page
 * Easy Shopping A.R.S
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get product slug from URL
$slug = $_GET['slug'] ?? '';
if (!$slug) {
    header('Location: ' . url('/shop'));
    exit;
}

try {
    // Fetch product with category info — BEFORE header so SEO tags are set
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name,
               (SELECT AVG(rating) FROM product_reviews WHERE product_id = p.id AND status = 'approved') as avg_rating,
               (SELECT COUNT(*) FROM product_reviews WHERE product_id = p.id AND status = 'approved') as review_count
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.slug = ? AND p.id IS NOT NULL
    ");
    $stmt->execute([$slug]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: ' . url('/shop'));
        exit;
    }

    // Related products
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND stock > 0 LIMIT 4");
    $stmt->execute([$product['category_id'], $product['id']]);
    $related_products = $stmt->fetchAll();

    // Reviews
    $stmt = $pdo->prepare("SELECT r.*, u.full_name FROM product_reviews r LEFT JOIN users u ON r.user_id = u.id WHERE r.product_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC");
    $stmt->execute([$product['id']]);
    $reviews = $stmt->fetchAll();

    // Gallery Images
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC");
    $stmt->execute([$product['id']]);
    $gallery_images = $stmt->fetchAll();

} catch (PDOException $e) { $error = $e->getMessage(); $product = null; }

// ── SEO meta — set BEFORE header include ─────────────────────
$page_title     = $product ? $product['name'] . ' — Buy Online in Nepal' : 'Product Not Found';
$page_meta_desc = $product
    ? 'Buy ' . $product['name'] . ' online at Easy Shopping A.R.S Nepal. '
      . 'Price: Rs. ' . number_format($product['discount_price'] ?: $product['price'], 2)
      . '. Fast delivery across Nepal. eSewa & COD accepted.'
    : 'Product not found at Easy Shopping A.R.S.';
$page_og_type   = 'product';
$page_og_image  = $product ? getProductImage($product['image']) : null;
$page_canonical = $product ? $base_url . '/product/' . $product['slug'] : null;

include 'includes/header-bootstrap.php';

if ($product):
$_schema_price  = number_format($product['discount_price'] ?: $product['price'], 2, '.', '');
$_schema_avail  = $product['stock'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock';
$_schema_image  = getProductImage($product['image']);
$_schema_rating = $product['avg_rating'] ? round((float)$product['avg_rating'], 1) : null;
$_schema_rcount = (int)($product['review_count'] ?? 0);
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": <?php echo json_encode($product['name']); ?>,
  "description": <?php echo json_encode($product['description'] ?? ''); ?>,
  "image": <?php echo json_encode($_schema_image); ?>,
  "sku": "ARS-<?php echo $product['id']; ?>",
  "brand": {
    "@type": "Brand",
    "name": "Easy Shopping A.R.S"
  },
  "offers": {
    "@type": "Offer",
    "url": <?php echo json_encode($base_url . '/product/' . $product['slug']); ?>,
    "priceCurrency": "NPR",
    "price": <?php echo json_encode($_schema_price); ?>,
    "availability": "<?php echo $_schema_avail; ?>",
    "seller": {
      "@type": "Organization",
      "name": "Easy Shopping A.R.S"
    }
  }<?php if ($_schema_rating && $_schema_rcount > 0): ?>
,
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": <?php echo $_schema_rating; ?>,
    "reviewCount": <?php echo $_schema_rcount; ?>
  }<?php endif; ?>
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {"@type": "ListItem", "position": 1, "name": "Home",  "item": <?php echo json_encode($base_url); ?>},
    {"@type": "ListItem", "position": 2, "name": "Shop",  "item": <?php echo json_encode($base_url . '/shop'); ?>},
    {"@type": "ListItem", "position": 3, "name": <?php echo json_encode($product['name']); ?>, "item": <?php echo json_encode($base_url . '/product/' . $product['slug']); ?>}
  ]
}
</script>
<?php endif; ?>

<style>
/* ═══ Amazon Styled Gallery ═══ */
.product-thumbnails {
    scrollbar-width: none; /* Firefox */
}
.product-thumbnails::-webkit-scrollbar {
    display: none; /* Safari and Chrome */
}
.thumbnail-item {
    width: 65px;
    height: 65px;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 3px;
    cursor: pointer;
    background: #fff;
    transition: all 0.2s ease-in-out;
}
.thumbnail-item img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.thumbnail-item.active, .thumbnail-item:hover {
    border-color: #007185; /* Amazon blue */
    box-shadow: 0 0 5px rgba(0,113,133,0.3);
}

.product-main-img {
    width: 100%;
    aspect-ratio: 1/1;
    object-fit: contain;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #eee;
    transition: opacity 0.2s ease;
}
.product-title {
    font-size: 2.25rem;
    font-weight: 800;
    line-height: 1.2;
    color: #111;
}
.price-tag {
    font-size: 2rem;
    font-weight: 800;
    color: var(--primary-color);
}
.qty-btn {
    width: 36px; height: 36px;
    border: none;
    background: #fff;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    transition: all 0.2s;
}
.qty-btn:hover {
    background: #f1f1f1;
    transform: scale(1.05);
}

/* 📱 MOBILE STICKY ACTIONS */
.sticky-mobile-actions {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    background: white;
    padding: 12px 15px;
    display: none;
    align-items: center;
    gap: 12px;
    box-shadow: 0 -5px 15px rgba(0,0,0,0.1);
    z-index: 1000;
}

@media (max-width: 991px) {
    .product-title { font-size: 1.5rem; }
    .price-tag { font-size: 1.5rem; }
    .sticky-mobile-actions { display: flex; }
    .desktop-actions { display: none; }
    body { padding-bottom: 80px !important; }
}

.review-item {
    border-bottom: 1px solid #eee;
    padding: 15px 0;
}
.review-item:last-child { border-bottom: none; }

/* Related Grid Mobile */
@media (max-width: 767px) {
    .related-row .col-6 { padding: 5px; }
}
</style>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4 d-none d-md-block">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="<?php echo url('/'); ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo url('/shop'); ?>">Shop</a></li>
            <li class="breadcrumb-item active text-truncate" style="max-width:200px;"><?php echo h($product['name']); ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Gallery (Amazon Style) -->
        <div class="col-lg-6">
            <div class="d-flex flex-column flex-md-row gap-3">
                <!-- Thumbnails (Left on desktop, Bottom on mobile) -->
                <?php 
                $all_images = [];
                // Add primary image
                if ($product['image']) $all_images[] = $product['image'];
                // Add gallery images (avoid duplicates)
                foreach($gallery_images as $g_img) {
                    if ($g_img['image_path'] != $product['image']) {
                        $all_images[] = $g_img['image_path'];
                    }
                }
                ?>
                
                <?php if(count($all_images) > 1): ?>
                <div class="product-thumbnails d-flex flex-md-column order-2 order-md-1 overflow-auto pe-md-2" style="max-height: 500px; gap: 10px; flex-shrink: 0;">
                    <?php foreach($all_images as $index => $img): ?>
                        <div class="thumbnail-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                             onmouseover="changeMainImage('<?php echo getProductImage($img); ?>', this)" 
                             onclick="changeMainImage('<?php echo getProductImage($img); ?>', this)">
                            <img src="<?php echo getProductImage($img); ?>" alt="Thumbnail <?php echo $index + 1; ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Main Image -->
                <div class="position-relative order-1 order-md-2 flex-grow-1">
                    <img src="<?php echo getProductImage($product['image']); ?>" id="mainProductImage" class="product-main-img shadow-sm" alt="<?php echo h($product['name']); ?>">
                    <?php if ($product['discount_price']): ?>
                        <span class="badge bg-danger position-absolute top-0 start-0 m-3 px-3 py-2" style="z-index: 1;">-<?php echo round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>%</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Info -->
        <div class="col-lg-6">
            <div class="ps-lg-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="text-uppercase small fw-bold text-primary"><?php echo h($product['category_name']); ?></div>
                    <?php if ($product['stock'] > 0): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill"><i class="bi bi-check-circle me-1"></i>In Stock (<?php echo $product['stock']; ?>)</span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill"><i class="bi bi-x-circle me-1"></i>Out of Stock</span>
                    <?php endif; ?>
                </div>
                
                <h1 class="product-title mb-2"><?php echo h($product['name']); ?></h1>
                
                <?php if ($product['sku']): ?>
                    <div class="small text-muted mb-3 fw-medium">SKU: <?php echo h($product['sku']); ?></div>
                <?php endif; ?>

                <div class="d-flex align-items-center mb-4 p-3 rounded-3" style="background-color: #f8fafc; border-left: 4px solid var(--primary-color);">
                    <div class="price-tag me-3" style="font-size: 2.2rem; color: #b12704;">
                        Rs. <?php echo number_format($product['discount_price'] ?: $product['price'], 0); ?>
                    </div>
                    <?php if ($product['discount_price']): ?>
                        <div class="text-muted text-decoration-line-through fs-6">Rs. <?php echo number_format($product['price'], 0); ?></div>
                    <?php endif; ?>
                </div>

                <div class="product-description text-secondary mb-4" style="line-height: 1.7; font-size: 0.95rem;">
                    <?php echo nl2br(h($product['description'])); ?>
                </div>

                <!-- Desktop Only Actions -->
                <div class="desktop-actions p-4 rounded-4" style="background-color: #fff; border: 1px solid #eaeaea; box-shadow: 0 8px 24px rgba(0,0,0,0.04);">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="d-flex align-items-center border rounded-pill px-2 py-1" style="background-color: #f8f9fa;">
                            <button class="qty-btn" onclick="updateQty(-1)"><i class="bi bi-dash"></i></button>
                            <input type="number" id="qty" class="form-control border-0 bg-transparent text-center" value="1" min="1" max="<?php echo $product['stock']; ?>" style="width: 60px; font-weight:700;">
                            <button class="qty-btn" onclick="updateQty(1)"><i class="bi bi-plus"></i></button>
                        </div>
                        <button class="btn flex-grow-1 py-3 fw-bold rounded-pill text-white shadow-sm" 
                                style="background: linear-gradient(135deg, var(--primary-color), #ff8533); transition: transform 0.2s, box-shadow 0.2s; border: none;" 
                                onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 6px 15px rgba(234,108,0,0.3)';" 
                                onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';" 
                                onclick="doAddToCart()" 
                                <?php echo $product['stock'] < 1 ? 'disabled' : ''; ?>>
                            <i class="bi bi-cart-plus me-2"></i> <?php echo $product['stock'] < 1 ? 'Out of Stock' : 'Add to Cart'; ?>
                        </button>
                    </div>
                    <button class="btn btn-outline-secondary w-100 py-2 rounded-pill fw-medium" 
                            style="border-color: #ddd; transition: all 0.2s; color: #444;" 
                            onmouseover="this.style.backgroundColor='#f8f9fa'; this.style.borderColor='#ccc'" 
                            onmouseout="this.style.backgroundColor='transparent'; this.style.borderColor='#ddd'" 
                            onclick="toggleWish(<?php echo $product['id']; ?>)">
                        <i class="bi bi-heart me-2 text-danger"></i> Add to Wishlist
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <div class="mt-5 pt-4">
        <h5 class="fw-bold mb-4">You May Also Like</h5>
        <div class="row related-row g-3">
            <?php foreach($related_products as $rp): ?>
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
    </div>
</div>

<!-- 📱 Sticky Bottom Action Bar for Mobile -->
<div class="sticky-mobile-actions">
    <div class="d-flex align-items-center border rounded-pill px-2 py-1 bg-light" style="width:120px;">
        <button class="qty-btn" onclick="updateQty(-1)"><i class="bi bi-dash"></i></button>
        <span id="qty-mobile" class="mx-auto fw-bold">1</span>
        <button class="qty-btn" onclick="updateQty(1)"><i class="bi bi-plus"></i></button>
    </div>
    <button class="btn btn-primary flex-grow-1 py-2 fw-bold rounded-pill shadow" onclick="doAddToCart()">
        Add to Cart
    </button>
</div>

<script>
function changeMainImage(src, element) {
    const mainImg = document.getElementById('mainProductImage');
    mainImg.style.opacity = '0.7'; // Small flash effect
    setTimeout(() => {
        mainImg.src = src;
        mainImg.style.opacity = '1';
    }, 50);
    
    document.querySelectorAll('.thumbnail-item').forEach(item => item.classList.remove('active'));
    element.classList.add('active');
}

function updateQty(delta) {
    const input = document.getElementById('qty');
    const mobileSpan = document.getElementById('qty-mobile');
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    if (val > <?php echo $product['stock']; ?>) val = <?php echo $product['stock']; ?>;
    input.value = val;
    mobileSpan.innerText = val;
}

function doAddToCart() {
    const qty = document.getElementById('qty').value;
    const btn = event.target;
    btn.disabled = true;
    
    fetch('<?php echo url("/cart-action"); ?>?action=add&id=<?php echo $product["id"]; ?>&quantity=' + qty)
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.dispatchEvent(new Event('cartUpdated'));
            window.location.href = '<?php echo url("/cart"); ?>';
        } else if (data.require_login) {
            const modal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
            modal.show();
        }
        btn.disabled = false;
    });
}

function toggleWish(id) {
    fetch('<?php echo url("/wishlist"); ?>?action=add&product_id=' + id)
    .then(() => alert('Added to wishlist!'));
}
</script>

<?php include 'includes/footer-bootstrap.php'; ?>