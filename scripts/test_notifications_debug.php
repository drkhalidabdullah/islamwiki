<?php
// Debug test for notifications API
require_once '../config/config.php';
require_once '../includes/functions.php';

// Simulate a logged-in user for testing
$_SESSION['user_id'] = 1; // Assuming user ID 1 exists

header('Content-Type: application/json');

try {
    $user_id = $_SESSION['user_id'];
    $limit = 5;
    
    $notifications = [];
    
    // Test basic database query
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_exists = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Debug test successful',
        'user_id' => $user_id,
        'user_exists' => $user_exists['count'] > 0,
        'notifications_count' => count($notifications),
        'database_connected' => isset($pdo)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>

