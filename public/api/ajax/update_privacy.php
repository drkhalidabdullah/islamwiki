<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$section = $_POST['section'] ?? '';
$visibility = $_POST['visibility'] ?? '';

if (empty($section) || empty($visibility)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// Validate visibility value
$allowed_visibility = ['public', 'community', 'followers', 'private'];
if (!in_array($visibility, $allowed_visibility)) {
    echo json_encode(['success' => false, 'message' => 'Invalid visibility setting']);
    exit();
}

try {
    $success = update_privacy_setting($user_id, $section, $visibility);
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Privacy setting updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update privacy setting']);
    }
    
} catch (Exception $e) {
    error_log("Error updating privacy setting: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating privacy setting']);
}
?>
