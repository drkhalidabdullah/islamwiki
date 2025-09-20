<?php
// Enhanced Search API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Get search parameters
$query = $_GET['q'] ?? '';
$content_type = $_GET['type'] ?? 'all';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'relevance';
$limit = min((int)($_GET['limit'] ?? 10), 50); // Max 50 results
$offset = max((int)($_GET['offset'] ?? 0), 0);

// Validate query
if (empty(trim($query))) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Search query is required',
        'results' => [],
        'total' => 0
    ]);
    exit();
}

// Rate limiting (skip for now)
// if (isRateLimited('search', 60, 100)) {
//     http_response_code(429);
//     echo json_encode([
//         'error' => 'Too many search requests. Please try again later.',
//         'results' => [],
//         'total' => 0
//     ]);
//     exit();
// }

// Log search query
logSearchQuery($query, $content_type, $category, $sort);

try {
    // Perform comprehensive search
    $results = performComprehensiveSearch($query, $content_type, $category, $sort, $limit, $offset, null);
    
    // Return results
    echo json_encode($results);
    
} catch (Exception $e) {
    error_log("Search API error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Search failed. Please try again.',
        'results' => [],
        'total' => 0
    ]);
}