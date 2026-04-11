<?php
/**
 * Admin Header Layout
 * Easy Shopping A.R.S
 */
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

protect_admin_page(); // Guards every admin page
$csrf_token = generate_csrf_token();
$admin_name = $_SESSION['user']['full_name'] ?? 'Admin';
$admin_initial = strtoupper(substr($admin_name, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $csrf_token; ?>">
    <title><?php echo $page_title ?? 'Admin Dashboard'; ?> - Easy Shopping A.R.S</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="<?php echo url('/public/assets/css/admin-style.css'); ?>">
</head>
<body>

<div class="app-container">
    <!-- Sidebar -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="header">
            <div class="header-left">
                <button id="sidebar-toggle" class="btn btn-ghost" aria-label="Toggle sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
            
            <div class="header-right" style="display: flex; align-items: center; gap: 16px;">
                <button id="theme-toggle" class="btn btn-ghost theme-toggle" aria-label="Toggle theme">
                    <i class="fa-solid fa-moon"></i>
                </button>
                
                <div class="user-profile" style="display: flex; align-items: center; gap: 10px;">
                    <span class="user-name" style="font-weight: 500;"><?php echo h($admin_name); ?></span>
                    <div class="avatar" style="width: 36px; height: 36px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                        <?php echo $admin_initial; ?>
                    </div>
                </div>
            </div>
        </header>

        <div class="page-content" style="margin-top: 24px;">
