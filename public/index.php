<?php
/**
 * IslamWiki Framework - SPA Router
 * Author: Khalid Abdullah
 * Version: 0.0.5
 * Date: 2025-01-27
 * License: AGPL-3.0
 */

// Handle API requests
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    // Route API requests to the API handler
    $apiPath = str_replace('/api/', '', $_SERVER['REQUEST_URI']);
    include __DIR__ . '/api/index.php';
    exit();
}

// Handle Admin API requests
if (strpos($_SERVER['REQUEST_URI'], '/admin/api/') === 0) {
    // Debug: Log admin API request
    error_log("Main Router: Admin API request detected: " . $_SERVER['REQUEST_URI']);
    
    // Route admin API requests to the API handler
    $adminApiPath = str_replace('/admin/api/', '', $_SERVER['REQUEST_URI']);
    // Set a flag to indicate this is an admin API request
    $_SERVER['IS_ADMIN_API'] = true;
    include __DIR__ . '/api/index.php';
    exit();
}

// Don't process static files - let Apache serve them directly
$staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg', 'woff', 'woff2', 'ttf', 'eot'];
$pathInfo = pathinfo($_SERVER['REQUEST_URI']);
if (isset($pathInfo['extension']) && in_array(strtolower($pathInfo['extension']), $staticExtensions)) {
    // This is a static file, let Apache handle it
    return false;
}

// For all other requests, serve the React app
$htmlFile = __DIR__ . '/index.html';
if (file_exists($htmlFile)) {
    // Read the HTML file
    $html = file_get_contents($htmlFile);
    
    // Set proper content type
    header('Content-Type: text/html; charset=utf-8');
    
    // Output the HTML
    echo $html;
} else {
    // Fallback if index.html doesn't exist
    http_response_code(404);
    echo '<h1>404 - File Not Found</h1>';
    echo '<p>The requested resource was not found.</p>';
} 