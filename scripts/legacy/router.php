<?php

/**
 * PHP Development Server Router
 * 
 * This script handles routing for the PHP development server
 * Routes API calls to api.php and everything else to the React SPA
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Route API calls to api.php in root
if (strpos($path, '/admin/api/') === 0) {
    // Include the API handler from root directory
    require __DIR__ . '/api.php';
    return true;
}

// Route all other requests to the React SPA
if (is_file(__DIR__ . '/public' . $path)) {
    // Serve static files directly
    return false;
} else {
    // Serve the React app for all other routes
    require __DIR__ . '/public/index.html';
    return true;
} 