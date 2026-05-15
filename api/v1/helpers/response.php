<?php
/**
 * Response Helpers
 * Consistent JSON response formatting for the mobile API.
 */

function json_success($data = null, $message = '', $statusCode = 200)
{
    http_response_code($statusCode);
    $response = ['success' => true];
    if ($message) {
        $response['message'] = $message;
    }
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_error($message = 'An error occurred', $statusCode = 400, $errors = null)
{
    http_response_code($statusCode);
    $response = ['success' => false, 'message' => $message];
    if ($errors !== null) {
        $response['errors'] = $errors;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_paginated($data, $total, $page, $perPage)
{
    $lastPage = (int)ceil($total / $perPage);
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $data,
        'pagination' => [
            'total' => (int)$total,
            'page' => (int)$page,
            'last_page' => $lastPage,
            'per_page' => (int)$perPage,
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
