<?php

/**
 * Public API Router
 * 
 * This script handles API calls from the public directory
 * and routes them to the main API handler
 * 
 * @author Khalid Abdullah
 * @version: 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get the API path from the request
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Extract the API endpoint
if (preg_match('/\/admin\/api\/(.+)/', $path, $matches)) {
    $api_endpoint = $matches[1];
    
    // Set the path for the main API handler
    $_SERVER['REQUEST_URI'] = '/admin/api/' . $api_endpoint;
    
    // Include the main API handler
    require_once __DIR__ . '/../api.php';
} else {
    // Invalid API path
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Invalid API endpoint']);
} 