<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/config.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get user's current cover photo
    $stmt = $pdo->prepare("
        SELECT cover_photo 
        FROM user_profiles 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $current_cover = $stmt->fetchColumn();
    
    $photos = [];
    
    // Add current cover photo if it exists
    if ($current_cover) {
        $photos[] = [
            'url' => $current_cover,
            'is_current' => true
        ];
    }
    
    // For now, we'll just return the current cover photo
    // In a full implementation, you might want to store a history of cover photos
    echo json_encode([
        'success' => true,
        'photos' => $photos
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load cover photos: ' . $e->getMessage()
    ]);
}
?>
