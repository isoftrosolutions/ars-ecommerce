<?php
/**
 * Offline Fallback Page
 * Easy Shopping A.R.S
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = "You're Offline";
require_once __DIR__ . '/includes/header-bootstrap.php';
?>

<div class="container my-5 py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="offline-icon mb-4">
                <i class="bi bi-cloud-slash text-muted" style="font-size: 5rem;"></i>
            </div>
            <h1 class="fw-bold mb-3">Connection Lost</h1>
            <p class="lead text-muted mb-5">
                It looks like you're currently offline. This page was served because you don't have an active internet connection.
            </p>
            
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-light mb-4">
                <h5 class="fw-bold mb-3">What can you do?</h5>
                <ul class="list-unstyled text-start d-inline-block">
                    <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i> Check your Wi-Fi or cellular data.</li>
                    <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i> You can still view some pages you've visited recently.</li>
                    <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i> We'll automatically bring you back once you're online.</li>
                </ul>
            </div>

            <button onclick="window.location.reload()" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm" style="background: #ea6c00; border: none;">
                <i class="bi bi-arrow-clockwise me-2"></i> Try Refreshing
            </button>
            
            <div class="mt-4">
                <a href="<?= url('/') ?>" class="text-decoration-none text-primary fw-semibold">
                    <i class="bi bi-house-door me-1"></i> Return to Homepage
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    body { background-color: #f8fafc; }
    .offline-icon i {
        animation: pulse 2s infinite ease-in-out;
    }
    @keyframes pulse {
        0% { transform: scale(1); opacity: 0.6; }
        50% { transform: scale(1.05); opacity: 1; }
        100% { transform: scale(1); opacity: 0.6; }
    }
</style>

<?php require_once __DIR__ . '/includes/footer-bootstrap.php'; ?>
