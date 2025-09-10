<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/moderation_functions.php';
require_once '../../includes/rate_limiter.php';

header('Content-Type: application/json');

// Check if user is logged in (optional for reporting)
$reporter_id = is_logged_in() ? $_SESSION['user_id'] : null;

// Get POST data
$content_type = $_POST['content_type'] ?? '';
$content_id = (int)($_POST['content_id'] ?? 0);
$reason = $_POST['reason'] ?? '';
$description = $_POST['description'] ?? '';

// Validate input
if (empty($content_type) || !$content_id || empty($reason)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields.'
    ]);
    exit;
}

// Validate content type
$valid_types = ['wiki_article', 'user_post', 'comment', 'user_profile'];
if (!in_array($content_type, $valid_types)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid content type.'
    ]);
    exit;
}

// Validate reason
$valid_reasons = ['spam', 'inappropriate', 'harassment', 'copyright', 'other'];
if (!in_array($reason, $valid_reasons)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid report reason.'
    ]);
    exit;
}

// Check if content exists
$content = get_content_for_moderation($content_type, $content_id);
if (!$content) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Content not found.'
    ]);
    exit;
}

// Submit report
$result = report_content($content_type, $content_id, $reason, $description, $reporter_id);

if ($result['success']) {
    echo json_encode([
        'success' => true,
        'message' => $result['message']
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $result['message']
    ]);
}
?>
