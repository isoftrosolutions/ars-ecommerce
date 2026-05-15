<?php
/**
 * Image URL Helper
 * Converts relative image paths to absolute URLs.
 */

function absolute_image_url($relativePath)
{
    if (empty($relativePath)) {
        return null;
    }

    $baseUrl = env('APP_BASE_URL', 'https://easyshoppingars.com');

    if (strpos($relativePath, 'http://') === 0 || strpos($relativePath, 'https://') === 0) {
        return $relativePath;
    }

    $relativePath = ltrim($relativePath, '/');
    return rtrim($baseUrl, '/') . '/' . $relativePath;
}

function product_image_url($image)
{
    if (empty($image)) {
        return null;
    }
    return absolute_image_url('uploads/products/' . ltrim($image, '/'));
}

function banner_image_url($image)
{
    if (empty($image)) {
        return null;
    }
    return absolute_image_url('uploads/banners/' . ltrim($image, '/'));
}
