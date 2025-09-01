<?php

/**
 * Database Overview API Endpoint
 * 
 * This script handles the /admin/api/database/overview endpoint
 * and routes it to the main API handler
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
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set the path for the main API handler
$_SERVER['REQUEST_URI'] = '/admin/api/database/overview';

// Include the main API handler
require_once __DIR__ . '/../../../../api.php'; 