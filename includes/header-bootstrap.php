<?php
/**
 * Public Header (Bootstrap Based)
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
            --primary-color: #ea6c00;
            --secondary-color: #0f172a;
        }
        .navbar {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.9) !important;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 15px 0;
            z-index: 1000;
        }
        .navbar-brand {
            font-family: 'Fraunces', serif;
            font-weight: 900;
            font-size: 1.5rem;
            color: var(--secondary-color) !important;
        }
        .nav-link {
            font-weight: 500;
            font-size: 0.95rem;
            color: var(--secondary-color) !important;
            margin: 0 10px;
        }
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        .btn-cart {
            position: relative;
            padding: 8px 15px;
            border-radius: 50px;
            border: 1px solid rgba(0,0,0,0.1);
            background: white;
            color: var(--secondary-color);
            transition: all 0.2s;
        }
        .btn-cart:hover {
            background: var(--secondary-color);
            color: white;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo url('/'); ?>">
            ARS <span style="color: var(--primary-color);">Shopping</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="<?php echo url('/'); ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo url('/shop'); ?>">Shop</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo url('/categories'); ?>">Categories</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo url('/about'); ?>">About Us</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <a href="<?php echo url('/cart'); ?>" class="btn-cart text-decoration-none">
                    <i class="bi bi-cart3"></i>
                    <span class="ms-1">Cart (<span class="cart-count"><?php echo get_cart_count(); ?></span>)</span>
                </a>
                <?php if (is_logged_in()): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-2"></i><?php echo h($_SESSION['user']['full_name']); ?>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo url('/profile'); ?>"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo url('/orders'); ?>"><i class="bi bi-receipt me-2"></i>My Orders</a></li>
                            <li><a class="dropdown-item" href="<?php echo url('/wishlist'); ?>"><i class="bi bi-heart me-2"></i>Wishlist</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo url('/backend/logout'); ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo url('/auth/login'); ?>" class="btn text-dark fw-medium">Login</a>
                    <a href="<?php echo url('/auth/signup'); ?>" class="btn btn-dark px-4" style="background: var(--secondary-color); border-radius: 50px;">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
