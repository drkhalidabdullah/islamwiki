<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode([
        'success' => true,
        'notifications' => [],
        'unread_count' => 0,
        'total' => 0,
        'message' => 'User not logged in'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$limit = $_GET['limit'] ?? 10;

try {
    $notifications = [];
    
    // Simple test - just return basic info
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => 0,
        'total' => 0,
        'user_id' => $user_id,
        'message' => 'Notifications API working'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

