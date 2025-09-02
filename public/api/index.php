<?php
/**
 * IslamWiki Framework - API Entry Point
 * Author: Khalid Abdullah
 * Version: 0.0.5
 * Date: 2025-01-27
 * License: AGPL-3.0
 */

// Set content type to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include the autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

try {
    // Get the request method and path
    $method = $_SERVER['REQUEST_METHOD'];
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    
    // Check if this is an admin API request
    $isAdminApi = isset($_SERVER['IS_ADMIN_API']) || strpos($_SERVER['REQUEST_URI'], '/admin/api/') === 0;
    
    if ($isAdminApi) {
        // Remove 'admin/api' prefix from path
        $path = str_replace('admin/api/', '', $path);
        error_log("Admin API Request - Method: $method, Path: '$path', Raw URI: " . $_SERVER['REQUEST_URI']);
    } else {
        // Remove 'api' prefix from path
        $path = str_replace('api/', '', $path);
        error_log("API Request - Method: $method, Path: '$path', Raw URI: " . $_SERVER['REQUEST_URI']);
    }
    
    // Get request data
    $data = [];
    if ($method === 'POST' || $method === 'PUT') {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true) ?: [];
    }
    
    // For now, return a simple response to test the API
    if (empty($path)) {
        error_log("Empty path detected, returning API info");
        echo json_encode([
            'message' => 'IslamWiki API is working!',
            'endpoints' => [
                'system/health' => 'GET - System health check',
                'system/stats' => 'GET - System statistics',
                'wiki/overview' => 'GET - Wiki overview'
            ],
            'code' => 200
        ], JSON_PRETTY_PRINT);
        exit();
    }
    
    // Initialize database connection
    $config = [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'database' => $_ENV['DB_DATABASE'] ?? 'islamwiki',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4'
    ];
    
    $database = new \IslamWiki\Core\Database\DatabaseManager($config);
    
    // Initialize API controller
    $apiController = new \IslamWiki\Controllers\ApiController($database);
    
    // Handle the request
    $response = $apiController->handleRequest($method, $path, $data);
    
    // Set appropriate HTTP status code
    $statusCode = $response['code'] ?? 200;
    http_response_code($statusCode);
    
    // Return JSON response
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // Handle errors
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => $e->getMessage(),
        'code' => 500
    ], JSON_PRETTY_PRINT);
} 