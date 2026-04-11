<?php
/**
 * Admin Sidebar Layout
 * Easy Shopping A.R.S
 */

// Use REQUEST_URI so active state works for both direct PHP access and clean router URLs
$_req_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$_req_path = trim(str_replace('\\', '/', $_req_path), '/');

function _is_nav_active(string $page): string {
    global $_req_path;
    return (strpos($_req_path, $page) !== false) ? 'active' : '';
}
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <span class="nav-text">Easy Shopping</span>
    </div>
    
    <nav class="sidebar-nav">
        <a href="<?php echo url('/admin/dashboard.php'); ?>" class="nav-item <?php echo _is_nav_active('admin/dashboard'); ?>">
            <span class="nav-icon"><i class="fa-solid fa-gauge-high"></i></span>
            <span class="nav-text">Dashboard</span>
        </a>
        
        <a href="<?php echo url('/admin/products.php'); ?>" class="nav-item <?php echo _is_nav_active('admin/products'); ?>">
            <span class="nav-icon"><i class="fa-solid fa-box"></i></span>
            <span class="nav-text">Products</span>
        </a>
        
        <a href="<?php echo url('/admin/orders.php'); ?>" class="nav-item <?php echo _is_nav_active('admin/orders'); ?>">
            <span class="nav-icon"><i class="fa-solid fa-cart-shopping"></i></span>
            <span class="nav-text">Orders</span>
        </a>
        
        <a href="<?php echo url('/admin/categories.php'); ?>" class="nav-item <?php echo _is_nav_active('admin/categories'); ?>">
            <span class="nav-icon"><i class="fa-solid fa-layer-group"></i></span>
            <span class="nav-text">Categories</span>
        </a>
        
        <a href="<?php echo url('/admin/customers.php'); ?>" class="nav-item <?php echo _is_nav_active('admin/customers'); ?>">
            <span class="nav-icon"><i class="fa-solid fa-users"></i></span>
            <span class="nav-text">Customers</span>
        </a>

        <a href="<?php echo url('/admin/reviews.php'); ?>" class="nav-item <?php echo _is_nav_active('admin/reviews'); ?>">
            <span class="nav-icon"><i class="fa-solid fa-star"></i></span>
            <span class="nav-text">Reviews</span>
        </a>

        <a href="<?php echo url('/admin/coupons.php'); ?>" class="nav-item <?php echo _is_nav_active('admin/coupons'); ?>">
            <span class="nav-icon"><i class="fa-solid fa-ticket"></i></span>
            <span class="nav-text">Coupons</span>
        </a>

        <a href="<?php echo url('/admin/contact.php'); ?>" class="nav-item <?php echo _is_nav_active('admin/contact'); ?>">
            <span class="nav-icon"><i class="fa-solid fa-envelope"></i></span>
            <span class="nav-text">Contact</span>
        </a>

        <div style="margin: 20px 0; border-top: 1px solid var(--border-color);"></div>

        <a href="<?php echo url('/admin/settings.php'); ?>" class="nav-item <?php echo _is_nav_active('admin/settings'); ?>">
            <span class="nav-icon"><i class="fa-solid fa-gear"></i></span>
            <span class="nav-text">Settings</span>
        </a>

        <a href="<?php echo url('/backend/logout'); ?>" class="nav-item">
            <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
            <span class="nav-text">Logout</span>
        </a>
    </nav>
</aside>
