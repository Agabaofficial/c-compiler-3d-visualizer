<?php
/**
 * api/middleware/cors.php
 * Simple CORS handler: set Access-Control headers and respond to OPTIONS preflight.
 *
 * Include this file at the top of any API entry point that needs CORS handling
 * before the full middleware stack is loaded.
 */

$allowedOrigins = getenv('CORS_ALLOWED_ORIGINS') ?: '*';
$requestOrigin  = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($allowedOrigins === '*') {
    header('Access-Control-Allow-Origin: *');
} elseif ($requestOrigin !== '') {
    $originList = array_map('trim', explode(',', $allowedOrigins));
    if (in_array($requestOrigin, $originList, true)) {
        header("Access-Control-Allow-Origin: {$requestOrigin}");
        header('Vary: Origin');
    }
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');

// Respond immediately to preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
