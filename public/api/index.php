<?php
/**
 * API Entry Point for IslamWiki
 * Handles all API requests including admin API endpoints
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to JSON
header('Content-Type: application/json');

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Debug: Log the request
    error_log("API Request - Method: " . $_SERVER['REQUEST_METHOD'] . ", URI: " . $_SERVER['REQUEST_URI']);
    
    // Load environment variables
    if (file_exists(__DIR__ . '/../../.env')) {
        $envFile = file_get_contents(__DIR__ . '/../../.env');
        foreach (explode("\n", $envFile) as $line) {
            if (strpos($line, '=') !== false && !str_starts_with(trim($line), '#')) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }

    // Load Composer autoloader
    if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
        require_once __DIR__ . '/../../vendor/autoload.php';
    } else {
        throw new Exception('Composer autoloader not found');
    }

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

    // Initialize database manager
    $databaseManager = new \IslamWiki\Core\Database\DatabaseManager([
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? 3306,
        'database' => $_ENV['DB_DATABASE'] ?? 'islamwiki',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4'
    ]);

    // Initialize API controller
    $apiController = new \IslamWiki\Controllers\ApiController($databaseManager);

    // Process the request
    $response = $apiController->handleRequest($method, $path, $data);

    // Return JSON response
    http_response_code($response['code'] ?? 200);
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error: ' . $e->getMessage(),
        'code' => 500
    ]);
} 