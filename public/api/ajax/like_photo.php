<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$photo_id = $input['photo_id'] ?? null;
$action = $input['action'] ?? null;

if (!$photo_id || !in_array($action, ['like', 'unlike'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Check if photo exists and belongs to a user the current user can see
    $stmt = $pdo->prepare("
        SELECT up.id, up.user_id, up.likes_count
        FROM user_posts up
        WHERE up.id = ? AND up.post_type = 'image'
    ");
    $stmt->execute([$photo_id]);
    $photo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$photo) {
        echo json_encode(['success' => false, 'message' => 'Photo not found']);
        exit();
    }
    
    // Check if user can view this photo
    if (!can_view_profile($user_id, $photo['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit();
    }
    
    // Check if user has already liked this photo
    $stmt = $pdo->prepare("
        SELECT id FROM post_interactions 
        WHERE post_id = ? AND user_id = ? AND interaction_type = 'like'
    ");
    $stmt->execute([$photo_id, $user_id]);
    $existing_like = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($action === 'like') {
        if ($existing_like) {
            echo json_encode(['success' => false, 'message' => 'Already liked']);
            exit();
        }
        
        // Add like
        $stmt = $pdo->prepare("
            INSERT INTO post_interactions (post_id, user_id, interaction_type) 
            VALUES (?, ?, 'like')
        ");
        $stmt->execute([$photo_id, $user_id]);
        
        // Update likes count
        $stmt = $pdo->prepare("
            UPDATE user_posts 
            SET likes_count = likes_count + 1 
            WHERE id = ?
        ");
        $stmt->execute([$photo_id]);
        
    } else { // unlike
        if (!$existing_like) {
            echo json_encode(['success' => false, 'message' => 'Not liked']);
            exit();
        }
        
        // Remove like
        $stmt = $pdo->prepare("
            DELETE FROM post_interactions 
            WHERE post_id = ? AND user_id = ? AND interaction_type = 'like'
        ");
        $stmt->execute([$photo_id, $user_id]);
        
        // Update likes count
        $stmt = $pdo->prepare("
            UPDATE user_posts 
            SET likes_count = GREATEST(likes_count - 1, 0) 
            WHERE id = ?
        ");
        $stmt->execute([$photo_id]);
    }
    
    // Get updated likes count
    $stmt = $pdo->prepare("
        SELECT likes_count FROM user_posts WHERE id = ?
    ");
    $stmt->execute([$photo_id]);
    $updated_photo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'likes_count' => (int)$updated_photo['likes_count']
    ]);
    
} catch (Exception $e) {
    error_log("Error liking photo: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
