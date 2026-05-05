<?php
/**
 * Dynamic PWA Manifest — paths adapt to the current environment.
 * Works on localhost/ars/ (dev) and easyshoppingars.com/ (production).
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/manifest+json');
header('Cache-Control: public, max-age=3600');

$base = rtrim(url(''), '/'); // e.g. "http://localhost/ars" or "https://easyshoppingars.com"
$icon192 = $base . '/public/assets/img/pwa-icon-192.png';
$icon512 = $base . '/public/assets/img/pwa-icon-512.png';
$scope   = $base . '/';
$start   = $base . '/';

$manifest = [
    'name'         => 'Easy Shopping A.R.S - Online Shopping in Nepal',
    'short_name'   => 'ARS Shop',
    'description'  => "Nepal's trusted online store. Shop electronics, fashion, home goods with fast delivery. eSewa & COD accepted.",
    'start_url'    => $start,
    'scope'        => $scope,
    'display'      => 'standalone',
    'display_override' => ['standalone', 'minimal-ui'],
    'background_color' => '#1e293b',
    'theme_color'  => '#ea6c00',
    'orientation'  => 'portrait-primary',
    'lang'         => 'en-NP',
    'dir'          => 'ltr',
    'categories'   => ['shopping', 'e-commerce'],
    'prefer_related_applications' => false,
    'icons' => [
        ['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => $icon512, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => $icon512, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
    ],
    'screenshots' => [
        ['src' => $base . '/public/assets/img/screenshot-desktop.png', 'sizes' => '1280x720', 'type' => 'image/png', 'form_factor' => 'wide',   'label' => 'Desktop View'],
        ['src' => $base . '/public/assets/img/screenshot-mobile.png',  'sizes' => '390x844',  'type' => 'image/png', 'form_factor' => 'narrow', 'label' => 'Mobile View'],
    ],
    'shortcuts' => [
        ['name' => 'Shop Now',  'short_name' => 'Shop',   'description' => 'Browse our product catalog', 'url' => $base . '/shop.php',   'icons' => [['src' => $icon192, 'sizes' => '192x192']]],
        ['name' => 'My Cart',   'short_name' => 'Cart',   'description' => 'View your shopping cart',   'url' => $base . '/cart.php',   'icons' => [['src' => $icon192, 'sizes' => '192x192']]],
        ['name' => 'My Orders', 'short_name' => 'Orders', 'description' => 'Track your orders',         'url' => $base . '/orders.php', 'icons' => [['src' => $icon192, 'sizes' => '192x192']]],
    ],
    'related_applications' => [],
    'handle_links'   => 'preferred',
    'launch_handler' => ['client_mode' => 'navigate-existing'],
    'edge_side_panel' => ['preferred_width' => 480],
];

echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
