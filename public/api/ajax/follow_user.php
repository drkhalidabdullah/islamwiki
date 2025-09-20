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
$target_user_id = $input['user_id'] ?? null;
$action = $input['action'] ?? null;

if (!$target_user_id || !in_array($action, ['follow', 'unfollow'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

try {
    $current_user_id = $_SESSION['user_id'];
    
    // Can't follow yourself
    if ($current_user_id == $target_user_id) {
        echo json_encode(['success' => false, 'message' => 'Cannot follow yourself']);
        exit();
    }
    
    // Check if target user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$target_user_id]);
    $target_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$target_user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    // Check if already following
    $stmt = $pdo->prepare("
        SELECT id FROM user_follows 
        WHERE follower_id = ? AND following_id = ?
    ");
    $stmt->execute([$current_user_id, $target_user_id]);
    $existing_follow = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($action === 'follow') {
        if ($existing_follow) {
            echo json_encode(['success' => false, 'message' => 'Already following']);
            exit();
        }
        
        // Add follow relationship
        $stmt = $pdo->prepare("
            INSERT INTO user_follows (follower_id, following_id) 
            VALUES (?, ?)
        ");
        $stmt->execute([$current_user_id, $target_user_id]);
        
        // Update follower/following counts
        $stmt = $pdo->prepare("
            UPDATE users 
            SET followers_count = followers_count + 1 
            WHERE id = ?
        ");
        $stmt->execute([$target_user_id]);
        
        $stmt = $pdo->prepare("
            UPDATE users 
            SET following_count = following_count + 1 
            WHERE id = ?
        ");
        $stmt->execute([$current_user_id]);
        
    } else { // unfollow
        if (!$existing_follow) {
            echo json_encode(['success' => false, 'message' => 'Not following']);
            exit();
        }
        
        // Remove follow relationship
        $stmt = $pdo->prepare("
            DELETE FROM user_follows 
            WHERE follower_id = ? AND following_id = ?
        ");
        $stmt->execute([$current_user_id, $target_user_id]);
        
        // Update follower/following counts
        $stmt = $pdo->prepare("
            UPDATE users 
            SET followers_count = GREATEST(followers_count - 1, 0) 
            WHERE id = ?
        ");
        $stmt->execute([$target_user_id]);
        
        $stmt = $pdo->prepare("
            UPDATE users 
            SET following_count = GREATEST(following_count - 1, 0) 
            WHERE id = ?
        ");
        $stmt->execute([$current_user_id]);
    }
    
    // Get updated counts
    $stmt = $pdo->prepare("
        SELECT followers_count, following_count FROM users WHERE id = ?
    ");
    $stmt->execute([$target_user_id]);
    $updated_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'followers_count' => (int)$updated_user['followers_count'],
        'following_count' => (int)$updated_user['following_count']
    ]);
    
} catch (Exception $e) {
    error_log("Error following user: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>