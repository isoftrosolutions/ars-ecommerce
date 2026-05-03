<?php
/**
 * Refactored Public Header
 * Easy Shopping A.R.S
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/maintenance.php';
?>
<?php
// ── SEO meta resolution ───────────────────────────────────────
$_seo_title    = isset($page_title)    ? h($page_title) . ' | Easy Shopping A.R.S' : "Nepal's Trusted Online Store | Easy Shopping A.R.S";
$_seo_desc     = isset($page_meta_desc) ? h($page_meta_desc) : 'Shop electronics, fashion, and home goods with fast delivery in Nepal. eSewa & COD accepted.';
$_seo_image    = isset($page_og_image)  ? $page_og_image : $base_url . '/public/assets/img/og-default.jpg';
$_seo_canonical= isset($page_canonical) ? $page_canonical : $base_url . '/' . ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
// Strip the project prefix from canonical so it's clean
$_seo_canonical = rtrim($_seo_canonical, '?&');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Primary SEO -->
    <title><?php echo $_seo_title; ?></title>
    <meta name="description" content="<?php echo $_seo_desc; ?>">
    <link rel="canonical" href="<?php echo h($_seo_canonical); ?>">

    <!-- PWA Settings -->
    <link rel="manifest" href="<?php echo $base_url; ?>/manifest.json">
    <meta name="theme-color" content="#ea6c00">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ARS Shop">
    <link rel="apple-touch-icon" href="<?php echo url('/public/assets/img/pwa-icon-192.png'); ?>">
    <link rel="apple-touch-icon" sizes="192x192" href="<?php echo url('/public/assets/img/pwa-icon-192.png'); ?>">
    <link rel="apple-touch-icon" sizes="512x512" href="<?php echo url('/public/assets/img/pwa-icon-512.png'); ?>">
    
    <!-- iOS Splash Screens -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- Windows Tile -->
    <meta name="msapplication-TileImage" content="<?php echo url('/public/assets/img/pwa-icon-192.png'); ?>">
    <meta name="msapplication-TileColor" content="#ea6c00">
    <meta name="application-name" content="ARS Shop">
    
    <!-- Theme & Color -->
    <meta name="format-detection" content="telephone=no">
    <meta name="HandheldFriendly" content="true">
    <meta name="color-scheme" content="light dark">

    <!-- Open Graph (Facebook, WhatsApp, LinkedIn) -->
    <meta property="og:type"        content="<?php echo isset($page_og_type) ? h($page_og_type) : 'website'; ?>">
    <meta property="og:title"       content="<?php echo $_seo_title; ?>">
    <meta property="og:description" content="<?php echo $_seo_desc; ?>">
    <meta property="og:url"         content="<?php echo h($_seo_canonical); ?>">
    <meta property="og:image"       content="<?php echo h($_seo_image); ?>">
    <meta property="og:site_name"   content="Easy Shopping A.R.S">
    <meta property="og:locale"      content="ne_NP">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?php echo $_seo_title; ?>">
    <meta name="twitter:description" content="<?php echo $_seo_desc; ?>">
    <meta name="twitter:image"       content="<?php echo h($_seo_image); ?>">

    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛒</text></svg>">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #ea6c00; /* Original ARS Orange */
            --secondary-color: #0f172a; /* Original ARS Deep Blue */
            --nav-dark: #1e293b; /* Nav Background */
            --text-light: #ffffff;
        }

        body { padding-top: 110px; font-family: 'DM Sans', sans-serif; }
        @media (max-width: 991px) { body { padding-top: 70px; padding-bottom: 60px; } }

        /* ═══ Header Foundation ═══ */
        .header-main {
            background-color: var(--secondary-color);
            position: fixed;
            top: 0; width: 100%; z-index: 1050;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 800; font-size: 1.8rem;
            color: white !important; letter-spacing: -1px;
        }
        .navbar-brand span { color: var(--primary-color); }

        /* 🔍 Seamless Premium Search Bar */
        .search-container {
            display: flex;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            height: 44px;
            max-width: 800px;
            flex-grow: 1;
            margin: 0 40px;
        }

        .search-cat {
            background: #f1f5f9;
            border: none;
            border-right: 1px solid #e2e8f0;
            padding: 0 15px;
            font-size: 0.8rem;
            color: #475569;
            font-weight: 600;
            cursor: pointer;
        }

        .search-input {
            border: none !important;
            padding: 0 20px;
            flex-grow: 1;
            font-size: 0.9rem;
            box-shadow: none !important;
        }

        .search-btn {
            background: var(--primary-color);
            border: none;
            width: 60px;
            color: white;
            font-size: 1.2rem;
            transition: all 0.2s;
        }
        .search-btn:hover { background: #ff7d2b; }

        /* 👤 Account & Cart Meta-info */
        .meta-actions {
            display: flex; align-items: center; gap: 8px;
        }
        .meta-link {
            display: flex; align-items: center; gap: 10px;
            color: white; text-decoration: none;
            padding: 6px 12px; border-radius: 8px;
            transition: background 0.2s;
        }
        .meta-link:hover { background: rgba(255,255,255,0.05); color: white; }

        .meta-label { font-size: 0.7rem; color: #94a3b8; display: block; }
        .meta-value { font-size: 0.85rem; font-weight: 700; display: block; }

        .cart-wrapper { position: relative; font-size: 1.5rem; color: var(--primary-color); }
        .cart-badge {
            position: absolute; top: -5px; right: -10px;
            background: #ef4444; color: white;
            font-size: 0.65rem; font-weight: 700;
            width: 20px; height: 20px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid var(--secondary-color);
        }

        /* 🧭 Bottom Nav 🧭 */
        .header-bottom {
            background-color: #1e293b;
            padding: 8px 0;
            position: fixed;
            top: 68px; width: 100%; z-index: 1040;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .nav-link-custom {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 4px 12px;
            transition: color 0.2s;
        }
        .nav-link-custom:hover { color: white; }
        .nav-link-custom.active { color: var(--primary-color); }
        .nav-link-custom:hover { outline: 1px solid white; }
        .nav-link-custom.all-menu { font-weight: 800; }

        /* ═══ Mobile UI ═══ */
        .mobile-header {
            background-color: var(--secondary-color);
            padding: 10px 0;
            position: fixed;
            top: 0; width: 100%;
            z-index: 1100;
        }
        .mobile-top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 15px;
            margin-bottom: 10px;
        }
        .mobile-search-row { padding: 0 15px; }
        .mobile-menu-btn { color: white; font-size: 1.5rem; background: none; border: none; padding: 0; }
        .mobile-brand { font-size: 1.25rem; font-weight: 800; color: white !important; text-decoration: none; }
        .mobile-brand span { color: var(--primary-color); }
        .mobile-actions { display: flex; align-items: center; gap: 15px; color: white; }
        .mobile-signin { color: white; text-decoration: none; font-size: 0.9rem; font-weight: 600; }
        .mobile-whatsapp { color: #25D366; font-size: 1.4rem; }

        .mobile-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            display: none;
            justify-content: space-around;
            padding: 10px 0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 2000;
        }
        .mobile-nav-item {
            text-align: center;
            text-decoration: none;
            color: #64748b;
            font-size: 0.65rem;
            flex: 1;
        }
        .mobile-nav-item.active { color: var(--primary-color); }
        .mobile-nav-item i { font-size: 1.3rem; display: block; margin-bottom: 2px; }

        @media (max-width: 991px) {
            .header-bottom { display: none; }
            .search-container { order: 3; max-width: 100%; margin: 8px 0 0; }
            .header-main { height: auto; padding-bottom: 12px; }
            .navbar-brand { margin-right: 0; font-size: 1.3rem; }
            .meta-item.d-lg-inline-block { display: none; }
            .mobile-bottom-nav { display: flex; }
        }
    </style>
</head>
<body>

<header>
    <!-- 📱 Mobile Header (Visible only on < 992px) -->
    <div class="mobile-header d-lg-none">
        <div class="mobile-top-row">
            <button class="mobile-menu-btn" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                <i class="bi bi-list"></i>
            </button>
            <a href="<?php echo url('/'); ?>" class="mobile-brand">
                <div style="width: 40px; height: 40px; overflow: hidden; border-radius: 8px;">
                    <img src="<?php echo url('/public/assets/img/logo.jpg'); ?>" alt="ARS Shop Logo" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
            </a>
            <div class="mobile-actions">
                <a href="<?php echo is_logged_in() ? url('/profile') : url('/auth/login.php'); ?>" class="mobile-signin">
                    <?php echo is_logged_in() ? 'Profile' : 'Sign In'; ?> <i class="bi bi-chevron-right ms-1"></i>
                </a>
            </div>
        </div>
        <div class="mobile-search-row">
            <form action="<?php echo url('/shop'); ?>" method="GET">
                <div class="search-container">
                    <input type="text" name="q" class="search-input" placeholder="Search ARS Shopping..." value="<?php echo h($_GET['q'] ?? ''); ?>" required>
                    <button type="submit" class="search-btn">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 📱 Mobile Sidebar (Offcanvas) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
        <div class="offcanvas-header bg-dark text-white">
            <h5 class="offcanvas-title" id="mobileSidebarLabel">
                <i class="bi bi-person-circle me-2"></i> Hello, <?php echo is_logged_in() ? explode(' ', $_SESSION['user']['full_name'])[0] : 'Sign in'; ?>
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div class="list-group list-group-flush">
                <div class="list-group-item bg-light fw-bold">Shop by Department</div>
                <?php 
                    $cats = get_categories();
                    foreach($cats as $c): 
                ?>
                    <a href="<?php echo url('/shop?category=' . $c['id']); ?>" class="list-group-item list-group-item-action py-3">
                        <?php echo h($c['name']); ?>
                    </a>
                <?php endforeach; ?>

                <div class="list-group-item bg-light fw-bold">Help & Settings</div>
                <a href="<?php echo url('/profile'); ?>" class="list-group-item list-group-item-action py-3">Your Account</a>
                <a href="<?php echo url('/orders'); ?>" class="list-group-item list-group-item-action py-3">Your Orders</a>
                <a href="<?php echo url('/queries'); ?>" class="list-group-item list-group-item-action py-3">My Queries</a>
                <a href="<?php echo url('/contact'); ?>" class="list-group-item list-group-item-action py-3">Customer Service</a>
                <?php if (is_logged_in()): ?>
                    <a href="<?php echo url('/backend/logout.php'); ?>" class="list-group-item list-group-item-action py-3 text-danger">Sign Out</a>
                <?php else: ?>
                    <a href="<?php echo url('/auth/login.php'); ?>" class="list-group-item list-group-item-action py-3 text-primary font-weight-bold">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 💻 Desktop Header (Visible only on >= 992px) -->
    <div class="d-none d-lg-block">
        <!-- Top Primary Header -->
        <div class="header-main">
            <div class="container d-flex align-items-center">
                <!-- Brand -->
                <a class="navbar-brand me-4" href="<?php echo url('/'); ?>">
                    <div style="width: 45px; height: 45px; overflow: hidden; border-radius: 10px; display: inline-block; vertical-align: middle; margin-right: 10px;">
                        <img src="<?php echo url('/public/assets/img/logo.jpg'); ?>" alt="ARS Shop Logo" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <span style="font-weight: 800; font-size: 1.5rem; vertical-align: middle;">ARS <span style="color: var(--primary-color);">Shopping</span></span>
                </a>

                <!-- 🔍 Seamless Search Engine -->
                <form action="<?php echo url('/shop'); ?>" method="GET" class="search-container">
                    <select class="search-cat" name="category">
                        <option value="">All Categories</option>
                        <?php 
                            $cats = get_categories();
                            foreach($cats as $c): 
                        ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $c['id']) ? 'selected' : ''; ?>>
                                <?php echo h($c['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="q" class="search-input" placeholder="Search for products, brands and more..." value="<?php echo h($_GET['q'] ?? ''); ?>" required>
                    <button type="submit" class="search-btn">
                        <i class="bi bi-search"></i>
                    </button>
                </form>

                <!-- Meta Actions -->
                <div class="meta-actions ms-auto">
                    <!-- Account Link -->
                    <a href="<?php echo is_logged_in() ? url('/profile') : url('/auth/login.php'); ?>" class="meta-link">
                        <div class="meta-icon"><i class="bi bi-person-circle fs-4"></i></div>
                        <div class="meta-text">
                            <span class="meta-label">Hello, <?php echo is_logged_in() ? explode(' ', $_SESSION['user']['full_name'])[0] : 'Sign In'; ?></span>
                            <span class="meta-value">Account & Lists</span>
                        </div>
                    </a>

                    <?php if (is_admin()): ?>
                    <!-- Admin Dashboard Link -->
                    <a href="<?php echo url('/admin/dashboard'); ?>" class="meta-link ms-2">
                        <div class="cart-wrapper">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <div class="meta-text">
                            <span class="meta-label">Admin</span>
                            <span class="meta-value">Dashboard</span>
                        </div>
                    </a>
                    <?php else: ?>
                    <!-- Cart Link -->
                    <a href="#" class="meta-link ms-2" onclick="event.preventDefault(); loadMiniCart(); bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('miniCartDrawer')).show();">
                        <div class="cart-wrapper">
                            <i class="bi bi-cart3"></i>
                            <span class="cart-badge cart-count"><?php echo get_cart_count(); ?></span>
                        </div>
                        <div class="meta-text">
                            <span class="meta-label">Shopping</span>
                            <span class="meta-value">Cart</span>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Bottom Secondary Header -->
        <div class="header-bottom">
            <div class="container d-flex align-items-center">
                <button class="nav-link-custom all-menu border-0 bg-transparent me-2" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                    <i class="bi bi-list me-1"></i> All
                </button>
                <nav class="d-flex align-items-center">
                    <a href="<?php echo url('/'); ?>" class="nav-link-custom">Home</a>
                    <a href="<?php echo url('/shop'); ?>" class="nav-link-custom active">Shop</a>
                    <a href="<?php echo url('/categories'); ?>" class="nav-link-custom">Categories</a>
                    <a href="<?php echo url('/orders'); ?>" class="nav-link-custom">My Orders</a>
                    <a href="<?php echo url('/queries'); ?>" class="nav-link-custom">My Queries</a>
                    <a href="<?php echo url('/todays-deal'); ?>" class="nav-link-custom d-none d-md-inline">Today's Deal</a>
                    <a href="<?php echo url('/support'); ?>" class="nav-link-custom d-none d-lg-inline">Service</a>
                </nav>
            </div>
        </div>
    </div>
    </div>
</header>

<!-- Mobile Bottom Navigation -->
<div class="mobile-bottom-nav">
    <a href="<?php echo url('/'); ?>" class="mobile-nav-item <?php echo $_SERVER['REQUEST_URI'] == url('/') ? 'active' : ''; ?>">
        <i class="bi bi-house"></i>Home
    </a>
    <a href="<?php echo url('/shop'); ?>" class="mobile-nav-item <?php echo strpos($_SERVER['REQUEST_URI'], '/shop') !== false ? 'active' : ''; ?>">
        <i class="bi bi-shop"></i>Shop
    </a>
    <a href="<?php echo url('/cart'); ?>" class="mobile-nav-item">
        <i class="bi bi-cart3"></i>Cart
    </a>
    <a href="<?php echo url('/profile'); ?>" class="mobile-nav-item">
        <i class="bi bi-person"></i>Profile
    </a>
</div>

<!-- Mini Cart Drawer -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="miniCartDrawer" aria-labelledby="miniCartLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="miniCartLabel">
            <i class="bi bi-cart3 me-2"></i>Your Cart
            <span class="badge bg-warning text-dark ms-2" id="miniCartCount">0</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0" id="miniCartBody">
        <div class="text-center py-5 text-muted">
            <i class="bi bi-cart-x" style="font-size: 3rem; opacity: 0.3;"></i>
            <p class="mt-3">Your cart is empty</p>
            <a href="<?php echo url('/shop'); ?>" class="btn btn-outline-primary btn-sm">Continue Shopping</a>
        </div>
    </div>
    <div class="offcanvas-footer border-top p-3" id="miniCartFooter" style="display: none;">
        <div class="d-flex justify-content-between mb-3">
            <span class="fw-bold">Total:</span>
            <span class="fw-bold fs-5" id="miniCartTotal">Rs. 0</span>
        </div>
        <a href="<?php echo url('/checkout'); ?>" class="btn btn-warning w-100 fw-bold">
            <i class="bi bi-bag-check me-2"></i>Proceed to Checkout
        </a>
        <a href="<?php echo url('/cart'); ?>" class="btn btn-outline-secondary w-100 mt-2">
            View Full Cart
        </a>
    </div>
</div>

<style>
/* Mini Cart Drawer Styles */
#miniCartDrawer .offcanvas {
    width: 400px;
    max-width: 90vw;
}
.mini-cart-item {
    display: flex;
    gap: 12px;
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s;
}
.mini-cart-item:hover {
    background: #fafafa;
}
.mini-cart-item img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    background: #f8fafc;
}
.mini-cart-item-details {
    flex: 1;
    min-width: 0;
}
.mini-cart-item-name {
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.mini-cart-item-price {
    color: var(--primary-color);
    font-weight: 700;
}
.mini-cart-item-qty {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
}
.mini-cart-qty-btn {
    width: 24px;
    height: 24px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}
.mini-cart-qty-btn:hover {
    background: #f0f0f0;
}
.mini-cart-remove {
    color: #dc3545;
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
}
.mini-cart-remove:hover {
    color: #bb2d3b;
}
</style>

<!-- Toast Container -->
<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 3000;"></div>

<!-- Login Required Modal -->
<div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-labelledby="loginRequiredLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="mb-4">
                    <i class="bi bi-person-circle text-primary" style="font-size: 4rem;"></i>
                </div>
                <h4 class="fw-bold mb-2" id="loginRequiredLabel">Login Required</h4>
                <p class="text-muted mb-4">Please log in or create a free account to add items to your cart.</p>
                <div class="d-grid gap-2">
                    <a href="<?php echo url('/auth/login.php'); ?>" class="btn btn-primary btn-lg fw-bold py-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Log In
                    </a>
                    <a href="<?php echo url('/auth/signup.php'); ?>" class="btn btn-outline-secondary fw-bold py-3">
                        <i class="bi bi-person-plus me-2"></i>Create Free Account
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.BASE_URL = '<?php echo rtrim(url(''), '/'); ?>';
</script>
<script>
// Dynamic login status synchronization logic
(function() {
    let currentLoginStatus = <?php echo is_logged_in() ? 'true' : 'false'; ?>;
    
    function updateHeaderUI(userData) {
        const accountValue = document.querySelector('.meta-value');
        const accountLabel = document.querySelector('.meta-label');
        if (!accountLabel) return;

        if (userData.logged_in) {
            accountLabel.textContent = `Hello, ${userData.full_name.split(' ')[0]}`;
            // We'd typically refresh the dropdown content here via AJAX or reload
        } else {
            accountLabel.textContent = "Hello, Sign in";
        }
    }

    function checkLoginStatus() {
        fetch('<?php echo url('/backend/check-login'); ?>', {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) return null;
            const ct = response.headers.get('content-type') || '';
            if (!ct.includes('application/json')) return null;
            return response.json();
        })
        .then(data => {
            if (!data) return;
            if (data.logged_in !== currentLoginStatus) {
                currentLoginStatus = data.logged_in;
                localStorage.setItem('loginStatus', JSON.stringify(data));
                window.location.reload();
            }
        })
        .catch(() => { /* silently ignore transient network/parse errors */ });
    }

    // Storage listener
    window.addEventListener('storage', (e) => {
        if (e.key === 'loginStatus') window.location.reload();
    });

    setInterval(checkLoginStatus, 8000);
})();

// Service Worker Registration
(function() {
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/ars/sw.js')
                .then(registration => {
                    console.log('PWA Service Worker registered:', registration.scope);
                    
                    // Check for updates periodically
                    setInterval(() => {
                        registration.update();
                    }, 60 * 60 * 1000); // Check every hour
                    
                    // Handle update found
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                // New service worker available
                                window.dispatchEvent(new CustomEvent('sw_updated', {
                                    detail: { version: '2.0.0' }
                                }));
                            }
                        });
                    });
                })
                .catch(error => {
                    console.error('PWA Service Worker registration failed:', error);
                });
        });
        
        // Handle controller change (when new SW takes over)
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            window.dispatchEvent(new Event('sw_controllerchange'));
        });
    }
})();

/**
 * Global Cart Function
 */
async function addToCart(productId, quantity = 1) {
    const btn = event?.currentTarget;
    const originalContent = btn ? btn.innerHTML : null;
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }

    try {
        const response = await fetch(`${window.BASE_URL}/cart-action?action=add&id=${productId}&quantity=${quantity}`);
        const data = await response.json();

        if (data.success) {
            // Redirect to cart page with product details
            window.location.href = `${window.BASE_URL}/cart`;
        } else if (data.require_login) {
            // Show login required modal
            const modal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
            modal.show();
        } else {
            showToast('Notice', data.message || 'Could not add item', 'warning');
        }
    } catch (error) {
        console.error('Cart Error:', error);
        showToast('Error', 'Something went wrong. Please try again.', 'danger');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    }
}

/**
 * Load Mini Cart Contents
 */
async function loadMiniCart() {
    try {
        const response = await fetch(`${window.BASE_URL}/cart-action?action=get`);
        const data = await response.json();
        
        const body = document.getElementById('miniCartBody');
        const footer = document.getElementById('miniCartFooter');
        const countBadge = document.getElementById('miniCartCount');
        const totalEl = document.getElementById('miniCartTotal');
        
        countBadge.textContent = data.cart_count || 0;
        
        if (!data.items || data.items.length === 0) {
            body.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-cart-x" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="mt-3">Your cart is empty</p>
                    <a href="${window.BASE_URL}/shop" class="btn btn-outline-primary btn-sm">Continue Shopping</a>
                </div>`;
            footer.style.display = 'none';
            return;
        }
        
        let itemsHtml = '';
        let total = 0;
        
        data.items.forEach(item => {
            const price = item.discount_price || item.price;
            total += price * item.quantity;
            const imageUrl = item.image ? (item.image.startsWith('http') ? item.image : window.BASE_URL + '/uploads/products/' + item.image) 
                : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&q=80&w=100';
            
            itemsHtml += `
                <div class="mini-cart-item">
                    <img src="${imageUrl}" alt="${item.name}" onerror="this.src='https://placehold.co/60x60/f1f5f9/6b7280?text=No+Img'">
                    <div class="mini-cart-item-details">
                        <div class="mini-cart-item-name">${item.name}</div>
                        <div class="mini-cart-item-price">Rs. ${price.toLocaleString()}</div>
                        <div class="mini-cart-item-qty">
                            <button class="mini-cart-qty-btn" onclick="updateMiniCartQty(${item.product_id}, ${item.quantity - 1})">-</button>
                            <span>${item.quantity}</span>
                            <button class="mini-cart-qty-btn" onclick="updateMiniCartQty(${item.product_id}, ${item.quantity + 1})">+</button>
                            <button class="mini-cart-remove ms-auto" onclick="removeMiniCartItem(${item.product_id})" title="Remove">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>`;
        });
        
        body.innerHTML = itemsHtml;
        totalEl.textContent = 'Rs. ' + total.toLocaleString();
        footer.style.display = 'block';
        
    } catch (error) {
        console.error('Error loading mini cart:', error);
    }
}

/**
 * Update Mini Cart Quantity
 */
async function updateMiniCartQty(productId, quantity) {
    if (quantity < 1) {
        await removeMiniCartItem(productId);
        return;
    }
    
    try {
        const response = await fetch(`${window.BASE_URL}/cart-action?action=update&id=${productId}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'quantity=' + quantity
        });
        const data = await response.json();
        
        if (data.success) {
            await loadMiniCart();
            // Update header count
            document.querySelectorAll('.cart-count').forEach(el => {
                el.textContent = data.cart_count;
            });
        }
    } catch (error) {
        console.error('Error updating quantity:', error);
    }
}

/**
 * Remove Item from Mini Cart
 */
async function removeMiniCartItem(productId) {
    try {
        const response = await fetch(`${window.BASE_URL}/cart-action?action=remove&id=${productId}`);
        const data = await response.json();
        
        if (data.success) {
            await loadMiniCart();
            document.querySelectorAll('.cart-count').forEach(el => {
                el.textContent = data.cart_count;
            });
        }
    } catch (error) {
        console.error('Error removing item:', error);
    }
}

/**
 * Simple Toast/Notification Helper
 */
function showToast(title, message, type = 'success') {
    // Using simple alert if no custom toast system is present, 
    // but we can easily add a Bootstrap Toast container here
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '3000';
        document.body.appendChild(container);
    }
    
    const id = 'toast-' + Date.now();
    const html = `
        <div id="${id}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}:</strong> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    document.getElementById('toast-container').insertAdjacentHTML('beforeend', html);
    const toastEl = document.getElementById(id);
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
    
    // Auto remove from DOM after hidden
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}
</script>

<style>
/* Cart Bump Animation */
@keyframes bump {
    0% { transform: scale(1); }
    50% { transform: scale(1.4); }
    100% { transform: scale(1); }
}
.cart-count.bump {
    animation: bump 0.3s ease-out;
}

/* PWA Install Banner */
.pwa-install-banner {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: white;
    padding: 16px 20px;
    display: none;
    z-index: 9999;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.2);
}
.pwa-install-banner.show {
    display: block;
    animation: slideUp 0.3s ease-out;
}
@keyframes slideUp {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}
.pwa-install-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 1200px;
    margin: 0 auto;
    gap: 16px;
    flex-wrap: wrap;
}
.pwa-install-text {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}
.pwa-install-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    object-fit: cover;
}
.pwa-install-info h5 {
    margin: 0 0 2px 0;
    font-weight: 700;
    font-size: 0.95rem;
}
.pwa-install-info p {
    margin: 0;
    font-size: 0.8rem;
    opacity: 0.8;
}
.pwa-install-actions {
    display: flex;
    gap: 10px;
}
.pwa-install-btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}
.pwa-install-btn.primary {
    background: #ea6c00;
    color: white;
}
.pwa-install-btn.primary:hover {
    background: #ff7d2b;
}
.pwa-install-btn.secondary {
    background: transparent;
    color: rgba(255,255,255,0.7);
    border: 1px solid rgba(255,255,255,0.3);
}
.pwa-install-btn.secondary:hover {
    background: rgba(255,255,255,0.1);
    color: white;
}

/* PWA Update Banner */
.pwa-update-banner {
    position: fixed;
    top: 70px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    color: white;
    padding: 12px 20px;
    border-radius: 12px;
    display: none;
    z-index: 9998;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    max-width: 90%;
}
.pwa-update-banner.show {
    display: flex;
    align-items: center;
    gap: 16px;
    animation: slideDown 0.3s ease-out;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateX(-50%) translateY(-20px); }
    to { opacity: 1; transform: translateX(-50%) translateY(0); }
}
.pwa-update-text {
    display: flex;
    align-items: center;
    gap: 12px;
}
.pwa-update-text i {
    font-size: 1.5rem;
}
.pwa-update-text span {
    font-weight: 600;
    font-size: 0.9rem;
}
.pwa-update-actions {
    display: flex;
    gap: 8px;
}
.pwa-update-btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.8rem;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}
.pwa-update-btn.refresh {
    background: white;
    color: #059669;
}
.pwa-update-btn.refresh:hover {
    background: #f0fdf4;
}
.pwa-update-btn.later {
    background: transparent;
    color: rgba(255,255,255,0.8);
}
.pwa-update-btn.later:hover {
    background: rgba(255,255,255,0.1);
    color: white;
}

@media (max-width: 767px) {
    .pwa-install-content {
        flex-direction: column;
        text-align: center;
    }
    .pwa-install-text {
        flex-direction: column;
    }
    .pwa-install-actions {
        width: 100%;
    }
    .pwa-install-btn {
        flex: 1;
    }
    .pwa-update-banner {
        top: auto;
        bottom: 80px;
        left: 10px;
        right: 10px;
        transform: none;
        flex-direction: column;
    }
}
</style>

<!-- PWA Install Banner -->
<div class="pwa-install-banner" id="pwaInstallBanner">
    <div class="pwa-install-content">
        <div class="pwa-install-text">
            <img src="<?php echo url('/public/assets/img/pwa-icon-192.png'); ?>" alt="ARS Shop" class="pwa-install-icon">
            <div class="pwa-install-info">
                <h5>Install ARS Shop App</h5>
                <p>Get the best shopping experience with our app</p>
            </div>
        </div>
        <div class="pwa-install-actions">
            <button class="pwa-install-btn primary" onclick="installPWA()">
                <i class="bi bi-download me-1"></i> Install
            </button>
            <button class="pwa-install-btn secondary" onclick="dismissInstallBanner()">
                Not now
            </button>
        </div>
    </div>
</div>

<!-- PWA Update Banner -->
<div class="pwa-update-banner" id="pwaUpdateBanner">
    <div class="pwa-update-text">
        <i class="bi bi-arrow-repeat"></i>
        <span>A new version is available!</span>
    </div>
    <div class="pwa-update-actions">
        <button class="pwa-update-btn refresh" onclick="refreshForUpdate()">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
        </button>
        <button class="pwa-update-btn later" onclick="dismissUpdateBanner()">
            Later
        </button>
    </div>
</div>

<script>
// PWA Install Prompt Variables
let deferredPrompt = null;
const PWA_INSTALL_KEY = 'pwa_install_dismissed';
const PWA_UPDATE_KEY = 'pwa_update_dismissed';

// PWA Install Prompt Handler
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    
    // Check if user already dismissed
    if (!localStorage.getItem(PWA_INSTALL_KEY)) {
        setTimeout(() => {
            document.getElementById('pwaInstallBanner').classList.add('show');
        }, 3000);
    }
});

// PWA Install Function
async function installPWA() {
    if (!deferredPrompt) {
        showToast('Info', 'Install feature not available. Try adding to home screen manually.', 'info');
        return;
    }
    
    deferredPrompt.prompt();
    
    const { outcome } = await deferredPrompt.userChoice;
    console.log('PWA Install:', outcome);
    
    if (outcome === 'accepted') {
        showToast('Success', 'ARS Shop app installed successfully!', 'success');
    }
    
    deferredPrompt = null;
    dismissInstallBanner();
}

// Dismiss Install Banner
function dismissInstallBanner() {
    document.getElementById('pwaInstallBanner').classList.remove('show');
    localStorage.setItem(PWA_INSTALL_KEY, Date.now());
}

// PWA Update Handler
window.addEventListener('sw_updated', (e) => {
    if (e.detail && !localStorage.getItem(PWA_UPDATE_KEY + '_' + e.detail.version)) {
        document.getElementById('pwaUpdateBanner').classList.add('show');
    }
});

// Refresh for Update
function refreshForUpdate() {
    localStorage.removeItem(PWA_UPDATE_KEY);
    window.location.reload();
}

// Dismiss Update Banner
function dismissUpdateBanner() {
    document.getElementById('pwaUpdateBanner').classList.remove('show');
    const version = document.getElementById('pwaUpdateBanner').dataset.version || 'current';
    localStorage.setItem(PWA_UPDATE_KEY + '_' + version, Date.now());
}

// Service Worker Message Listener
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', (event) => {
        console.log('SW Message:', event.data);
        
        if (event.data.type === 'SW_ACTIVATED') {
            document.getElementById('pwaUpdateBanner').dataset.version = event.data.version;
            
            // Only show update banner if it's a real update (not first install)
            if (navigator.serviceWorker.controller) {
                const dismissedKey = PWA_UPDATE_KEY + '_' + event.data.version;
                if (!localStorage.getItem(dismissedKey)) {
                    setTimeout(() => {
                        document.getElementById('pwaUpdateBanner').classList.add('show');
                    }, 1000);
                }
            }
        }
        
        if (event.data.type === 'CART_SYNC') {
            window.dispatchEvent(new Event('cartUpdated'));
        }
    });
}

// Clear dismissed install banner after 7 days
(function() {
    const dismissed = localStorage.getItem(PWA_INSTALL_KEY);
    if (dismissed) {
        const daysSinceDismissed = (Date.now() - parseInt(dismissed)) / (1000 * 60 * 60 * 24);
        if (daysSinceDismissed > 7) {
            localStorage.removeItem(PWA_INSTALL_KEY);
        }
    }
})();
</script>
