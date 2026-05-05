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

    <link rel="icon" href="<?php echo url('/public/assets/img/logo.jpg'); ?>">
    
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
            background: linear-gradient(135deg, var(--secondary-color) 0%, #334155 50%, var(--secondary-color) 100%);
            position: fixed;
            top: 0; width: 100%; z-index: 1050;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
        }

        .navbar-brand {
            font-weight: 800; font-size: 1.8rem;
            color: white !important; letter-spacing: -1px;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .navbar-brand:hover {
            transform: translateY(-1px);
        }

        .navbar-brand span {
            color: var(--primary-color);
            position: relative;
        }

        .navbar-brand span::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-color), #ff8533);
            transition: width 0.3s ease;
        }

        .navbar-brand:hover span::after {
            width: 100%;
        }

        /* 🔍 Seamless Premium Search Bar */
        .search-container {
            display: flex;
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.98));
            border-radius: 16px;
            overflow: hidden;
            height: 48px;
            max-width: 800px;
            flex-grow: 1;
            margin: 0 40px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .search-container:focus-within {
            box-shadow: 0 12px 40px rgba(234,108,0,0.15);
            border-color: rgba(234,108,0,0.3);
            transform: translateY(-1px);
        }

        .search-cat {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border: none;
            border-right: 1px solid rgba(226,232,240,0.8);
            padding: 0 16px;
            font-size: 0.85rem;
            color: #475569;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            position: relative;
        }

        .search-cat:hover {
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            color: #334155;
        }

        .search-cat::after {
            content: '';
            width: 0;
            height: 0;
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 4px solid #64748b;
            margin-left: 4px;
            transition: transform 0.2s ease;
        }

        .search-input {
            border: none !important;
            padding: 0 20px;
            flex-grow: 1;
            font-size: 0.95rem;
            box-shadow: none !important;
            background: transparent;
            color: #1e293b;
            font-weight: 500;
        }

        .search-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .search-input:focus {
            outline: none;
            background: rgba(234,108,0,0.02);
        }

        .search-btn {
            background: linear-gradient(135deg, var(--primary-color), #ff8533);
            border: none;
            width: 64px;
            color: white;
            font-size: 1.1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .search-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .search-btn:hover::before {
            left: 100%;
        }

        .search-btn:hover {
            background: linear-gradient(135deg, #ff7d2b, #ff9a4d);
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(234,108,0,0.3);
        }

        /* 👤 Account & Cart Meta-info */
        .meta-actions {
            display: flex; align-items: center; gap: 12px;
        }

        .meta-link {
            display: flex; align-items: center; gap: 12px;
            color: white; text-decoration: none;
            padding: 10px 16px; border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .meta-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s ease;
        }

        .meta-link:hover::before {
            left: 100%;
        }

        .meta-link:hover {
            background: rgba(255,255,255,0.08);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: rgba(255,255,255,0.15);
        }

        .meta-link .meta-icon {
            font-size: 1.4rem;
            color: #e2e8f0;
            transition: all 0.3s ease;
        }

        .meta-link:hover .meta-icon {
            color: var(--primary-color);
            transform: scale(1.1);
        }

        .meta-label {
            font-size: 0.75rem;
            color: #94a3b8;
            display: block;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .meta-value {
            font-size: 0.9rem;
            font-weight: 700;
            display: block;
            color: white;
            margin-top: 1px;
        }

        .cart-wrapper {
            position: relative;
            font-size: 1.5rem;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }

        .cart-wrapper:hover {
            transform: scale(1.1);
            color: #ff8533;
        }

        .cart-badge {
            position: absolute; top: -6px; right: -12px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            font-size: 0.7rem; font-weight: 700;
            width: 22px; height: 22px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid var(--secondary-color);
            box-shadow: 0 4px 12px rgba(239,68,68,0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 4px 12px rgba(239,68,68,0.3); }
            50% { box-shadow: 0 4px 12px rgba(239,68,68,0.6); }
            100% { box-shadow: 0 4px 12px rgba(239,68,68,0.3); }
        }

        /* 🧭 Bottom Nav 🧭 */
        .header-bottom {
            background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #1e293b 100%);
            padding: 10px 0;
            position: fixed;
            top: 68px; width: 100%; z-index: 1040;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .nav-link-custom {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 8px 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 8px;
            position: relative;
            margin: 0 2px;
            letter-spacing: 0.25px;
        }

        .nav-link-custom::before {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-color), #ff8533);
            transition: all 0.3s ease;
            border-radius: 1px;
            transform: translateX(-50%);
        }

        .nav-link-custom:hover {
            color: white;
            background: rgba(255,255,255,0.05);
            transform: translateY(-1px);
        }

        .nav-link-custom:hover::before {
            width: 80%;
        }

        .nav-link-custom.active {
            color: var(--primary-color);
            background: rgba(234,108,0,0.1);
        }

        .nav-link-custom.active::before {
            width: 100%;
            background: linear-gradient(90deg, var(--primary-color), #ff8533);
        }

        .nav-link-custom.all-menu {
            font-weight: 700;
            background: linear-gradient(135deg, rgba(234,108,0,0.1), rgba(255,133,51,0.1));
            border: 1px solid rgba(234,108,0,0.2);
        }

        .nav-link-custom.all-menu:hover {
            background: linear-gradient(135deg, rgba(234,108,0,0.15), rgba(255,133,51,0.15));
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(234,108,0,0.2);
        }

        /* ═══ Mobile UI ═══ */
        .mobile-header {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #334155 50%, var(--secondary-color) 100%);
            padding: 12px 0;
            position: fixed;
            top: 0; width: 100%;
            z-index: 1100;
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .mobile-top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            margin-bottom: 12px;
        }

        .mobile-search-row {
            padding: 0 16px;
        }

        .mobile-menu-btn {
            color: white;
            font-size: 1.4rem;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            padding: 10px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
        }

        .mobile-menu-btn:hover {
            background: rgba(255,255,255,0.15);
            transform: scale(1.05);
        }

        .mobile-brand {
            font-size: 1.3rem;
            font-weight: 800;
            color: white !important;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .mobile-brand:hover {
            transform: translateY(-1px);
        }

        .mobile-brand span {
            color: var(--primary-color);
            position: relative;
        }

        .mobile-brand span::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-color), #ff8533);
            transition: width 0.3s ease;
        }

        .mobile-brand:hover span::after {
            width: 100%;
        }

        .mobile-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .mobile-signin {
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 8px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            transition: all 0.3s ease;
        }

        .mobile-signin:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-1px);
        }

        .mobile-whatsapp {
            color: #25D366;
            font-size: 1.4rem;
            transition: all 0.3s ease;
        }

        .mobile-whatsapp:hover {
            transform: scale(1.1);
        }

        .mobile-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.98), rgba(255,255,255,0.95));
            backdrop-filter: blur(20px);
            display: none;
            justify-content: space-around;
            padding: 12px 0;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
            border-top: 1px solid rgba(0,0,0,0.05);
            z-index: 2000;
        }

        .mobile-nav-item {
            text-align: center;
            text-decoration: none;
            color: #64748b;
            font-size: 0.7rem;
            flex: 1;
            padding: 6px 8px;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            font-weight: 600;
            margin: 0 2px;
        }

        .mobile-nav-item::before {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), #ff8533);
            border-radius: 2px;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .mobile-nav-item:hover {
            background: rgba(234,108,0,0.1);
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .mobile-nav-item.active {
            color: var(--primary-color);
            background: rgba(234,108,0,0.1);
        }

        .mobile-nav-item.active::before {
            width: 80%;
        }

        .mobile-nav-item i {
            font-size: 1.4rem;
            display: block;
            margin-bottom: 4px;
            transition: all 0.3s ease;
        }

        .mobile-nav-item.active i,
        .mobile-nav-item:hover i {
            transform: scale(1.1);
        }

        @media (max-width: 991px) {
            .header-bottom { display: none; }
            .search-container {
                order: 3;
                max-width: 100%;
                margin: 8px 0 0;
                box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            }
            .header-main {
                height: auto;
                padding-bottom: 12px;
                background: linear-gradient(135deg, var(--secondary-color) 0%, #334155 100%);
            }
            .navbar-brand {
                margin-right: 0;
                font-size: 1.3rem;
            }
            .meta-item.d-lg-inline-block { display: none; }
            .mobile-bottom-nav { display: flex; }
        }

        @media (max-width: 767px) {
            .mobile-header {
                padding: 8px 0;
            }
            .mobile-top-row {
                padding: 0 12px;
                margin-bottom: 8px;
            }
            .mobile-search-row {
                padding: 0 12px;
            }
            .search-container {
                height: 44px;
                border-radius: 12px;
                margin: 4px 0 0;
            }
            .meta-actions {
                gap: 8px;
            }
            .meta-link {
                padding: 8px 12px;
                gap: 8px;
            }
            .mobile-nav-item {
                font-size: 0.65rem;
                padding: 4px 6px;
            }
            .mobile-nav-item i {
                font-size: 1.2rem;
                margin-bottom: 2px;
            }
        }

        @media (max-width: 575px) {
            .mobile-brand {
                font-size: 1.1rem;
            }
            .mobile-signin {
                font-size: 0.8rem;
                padding: 6px 10px;
            }
            .search-input {
                font-size: 0.85rem;
                padding: 0 12px;
            }
            .search-cat {
                padding: 0 12px;
                font-size: 0.75rem;
            }
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
        <div class="offcanvas-header" style="background: linear-gradient(135deg, var(--secondary-color) 0%, #334155 100%); color: white; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <h5 class="offcanvas-title" id="mobileSidebarLabel">
                <div class="d-flex align-items-center gap-2">
                    <div class="sidebar-user-icon" style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary-color), #ff8533); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-person-circle text-white fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Hello, <?php echo is_logged_in() ? explode(' ', $_SESSION['user']['full_name'])[0] : 'Sign in'; ?></div>
                        <div class="small opacity-75"><?php echo is_logged_in() ? 'Welcome back!' : 'Access your account'; ?></div>
                    </div>
                </div>
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div class="list-group list-group-flush">
                <div class="list-group-item fw-bold py-3 px-4" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); color: #334155; border-bottom: 1px solid #e2e8f0;">
                    <i class="bi bi-grid me-2 text-primary"></i>Shop by Department
                </div>
                <?php
                    $cats = get_categories();
                    foreach($cats as $c):
                ?>
                    <a href="<?php echo url('/shop?category=' . $c['id']); ?>" class="list-group-item list-group-item-action py-3 px-4 sidebar-nav-item">
                        <div class="d-flex align-items-center gap-3">
                            <div class="category-icon" style="width: 32px; height: 32px; background: linear-gradient(135deg, rgba(234,108,0,0.1), rgba(255,133,51,0.1)); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-tag text-primary"></i>
                            </div>
                            <span class="fw-medium"><?php echo h($c['name']); ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>

                <div class="list-group-item fw-bold py-3 px-4 mt-3" style="background: linear-gradient(135deg, #f8fafc, #f1f5f9); color: #334155; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                    <i class="bi bi-gear me-2 text-primary"></i>Help & Settings
                </div>
                <a href="<?php echo url('/profile'); ?>" class="list-group-item list-group-item-action py-3 px-4 sidebar-nav-item">
                    <div class="d-flex align-items-center gap-3">
                        <div class="nav-icon" style="width: 32px; height: 32px; background: linear-gradient(135deg, rgba(59,130,246,0.1), rgba(96,165,250,0.1)); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-person text-primary"></i>
                        </div>
                        <span>Your Account</span>
                    </div>
                </a>
                <a href="<?php echo url('/orders'); ?>" class="list-group-item list-group-item-action py-3 px-4 sidebar-nav-item">
                    <div class="d-flex align-items-center gap-3">
                        <div class="nav-icon" style="width: 32px; height: 32px; background: linear-gradient(135deg, rgba(34,197,94,0.1), rgba(74,222,128,0.1)); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-receipt text-success"></i>
                        </div>
                        <span>Your Orders</span>
                    </div>
                </a>
                <a href="<?php echo url('/queries'); ?>" class="list-group-item list-group-item-action py-3 px-4 sidebar-nav-item">
                    <div class="d-flex align-items-center gap-3">
                        <div class="nav-icon" style="width: 32px; height: 32px; background: linear-gradient(135deg, rgba(168,85,247,0.1), rgba(196,181,253,0.1)); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-chat-dots text-purple"></i>
                        </div>
                        <span>My Queries</span>
                    </div>
                </a>
                <a href="<?php echo url('/contact'); ?>" class="list-group-item list-group-item-action py-3 px-4 sidebar-nav-item">
                    <div class="d-flex align-items-center gap-3">
                        <div class="nav-icon" style="width: 32px; height: 32px; background: linear-gradient(135deg, rgba(251,146,60,0.1), rgba(253,186,116,0.1)); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-headset text-warning"></i>
                        </div>
                        <span>Customer Service</span>
                    </div>
                </a>
                <?php if (is_logged_in()): ?>
                    <a href="<?php echo url('/backend/logout.php'); ?>" class="list-group-item list-group-item-action py-3 px-4 sidebar-nav-item text-danger">
                        <div class="d-flex align-items-center gap-3">
                            <div class="nav-icon" style="width: 32px; height: 32px; background: linear-gradient(135deg, rgba(239,68,68,0.1), rgba(252,165,165,0.1)); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-box-arrow-right text-danger"></i>
                            </div>
                            <span>Sign Out</span>
                        </div>
                    </a>
                <?php else: ?>
                    <a href="<?php echo url('/auth/login.php'); ?>" class="list-group-item list-group-item-action py-3 px-4 sidebar-nav-item text-primary fw-bold">
                        <div class="d-flex align-items-center gap-3">
                            <div class="nav-icon" style="width: 32px; height: 32px; background: linear-gradient(135deg, rgba(59,130,246,0.1), rgba(96,165,250,0.1)); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-box-arrow-in-right text-primary"></i>
                            </div>
                            <span>Sign In</span>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        .sidebar-nav-item {
            transition: all 0.3s ease;
            border: none;
            border-radius: 0;
        }

        .sidebar-nav-item:hover {
            background: linear-gradient(135deg, rgba(234,108,0,0.05), rgba(255,133,51,0.05));
            transform: translateX(4px);
        }

        .sidebar-nav-item:hover .nav-icon,
        .sidebar-nav-item:hover .category-icon {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .offcanvas {
            box-shadow: 0 0 40px rgba(0,0,0,0.1);
        }
    </style>

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
                .catch(() => {});
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
