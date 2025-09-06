<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$post_id = $input['post_id'] ?? null;
$action = $input['action'] ?? null;

if (!$post_id || !$action) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

$current_user_id = $_SESSION['user_id'];

try {
    if ($action === 'like') {
        $result = like_post($current_user_id, $post_id);
        if ($result) {
            log_activity('like_post', "Liked post ID: $post_id", $current_user_id);
        }
    } elseif ($action === 'unlike') {
        $result = unlike_post($current_user_id, $post_id);
        if ($result) {
            log_activity('unlike_post', "Unliked post ID: $post_id", $current_user_id);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit();
    }
    
    echo json_encode(['success' => $result]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
