<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/analytics.php';

header('Content-Type: application/json');

// Check if analytics is enabled
$enable_analytics = get_system_setting('enable_analytics', true);
if (!$enable_analytics) {
    echo json_encode(['success' => false, 'message' => 'Analytics is currently disabled']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['query']) || !isset($input['results_count'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$query = trim($input['query']);
$results_count = (int)$input['results_count'];
$user_id = is_logged_in() ? $_SESSION['user_id'] : null;

// Track the search
$success = track_search($query, $results_count, $user_id);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to track search']);
}
?>
