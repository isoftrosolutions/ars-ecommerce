<?php
/**
 * Refactored Public Header
 * Easy Shopping A.R.S
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? "Nepal's Trusted Online Store"; ?> | Easy Shopping A.R.S</title>
    <meta name="description" content="<?php echo $page_meta_desc ?? 'Shop electronics, fashion, and home goods with fast delivery in Nepal.'; ?>">
    
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
                <span>ARS</span> Shopping
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
                    <span>ARS</span> Shopping
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

                    <!-- Cart Link -->
                    <a href="<?php echo url('/cart.php'); ?>" class="meta-link ms-2">
                        <div class="cart-wrapper">
                            <i class="bi bi-cart3"></i>
                            <span class="cart-badge cart-count"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                        </div>
                        <div class="meta-text">
                            <span class="meta-label">Shopping</span>
                            <span class="meta-value">Cart</span>
                        </div>
                    </a>
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
                    <a href="<?php echo url('/deals'); ?>" class="nav-link-custom d-none d-md-inline">Today's Deals</a>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
        .then(response => response.json())
        .then(data => {
            if (data.logged_in !== currentLoginStatus) {
                currentLoginStatus = data.logged_in;
                localStorage.setItem('loginStatus', JSON.stringify(data));
                window.location.reload(); // Safer for complex structure changes
            }
        });
    }

    // Storage listener
    window.addEventListener('storage', (e) => {
        if (e.key === 'loginStatus') window.location.reload();
    });

    setInterval(checkLoginStatus, 8000);
})();
</script>
