<?php

require_once __DIR__ . '/../src/Core/Database/DatabaseManager.php';
require_once __DIR__ . '/../src/Controllers/TranslationController.php';

use IslamWiki\Core\Database\DatabaseManager;
use IslamWiki\Controllers\TranslationController;

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Initialize database
    $config = [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'dbname' => $_ENV['DB_NAME'] ?? 'islamwiki',
        'username' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4'
    ];
    
    $database = new DatabaseManager($config);
    $controller = new TranslationController($database);
    
    // Get request method and endpoint
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $endpoint = basename($path);
    
    // Get request data
    $data = [];
    if ($method === 'GET') {
        $data = $_GET;
    } else {
        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $data = json_decode($input, true) ?? [];
        }
        $data = array_merge($data, $_POST);
    }
    
    // Handle the request
    $response = $controller->handleRequest($method, $endpoint, $data);
    
    // Send response
    http_response_code($response['success'] ? 200 : 400);
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
