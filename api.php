<?php

/**
 * Simple API Endpoint for Database Dashboard
 * 
 * This file provides a simple API endpoint that the React frontend can call
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'vendor/autoload.php';

use IslamWiki\Core\Database\DatabaseManager;
use IslamWiki\Core\Database\MigrationManager;
use IslamWiki\Admin\DatabaseController;

try {
    // Create database connection
    $database = new DatabaseManager([
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'islamwiki',
        'username' => 'root',
        'password' => '',
        'timezone' => 'UTC'
    ]);
    
    $migrationManager = new MigrationManager($database, 'database/migrations/');
    $dbController = new DatabaseController($database, $migrationManager);
    
    // Parse the request
    $requestUri = $_SERVER['REQUEST_URI'];
    $path = parse_url($requestUri, PHP_URL_PATH);
    
    // Route the request
    switch ($path) {
        case '/admin/api/database/overview':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $result = $dbController->overview();
                echo json_encode($result);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        case '/admin/api/database/health':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $result = $dbController->health();
                echo json_encode($result);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        case '/admin/api/database/migrations/status':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $result = $dbController->migrationStatus();
                echo json_encode($result);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        case '/admin/api/database/migrations/run':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $dbController->runMigrations();
                echo json_encode($result);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        case '/admin/api/database/migrations/rollback':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $dbController->rollbackMigrations();
                echo json_encode($result);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        case '/admin/api/database/query':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $result = $dbController->executeQuery($input);
                echo json_encode($result);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        case '/admin/api/database/query-log':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $result = $dbController->getQueryLog();
                echo json_encode($result);
            } else {
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Internal server error: ' . $e->getMessage()
    ]);
} 