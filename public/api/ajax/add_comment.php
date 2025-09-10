<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$post_id = $input['post_id'] ?? null;
$content = trim($input['content'] ?? '');
$parent_id = $input['parent_id'] ?? null;

if (!$post_id || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Post ID and content are required']);
    exit();
}

if (strlen($content) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Comment too long (max 1000 characters)']);
    exit();
}

$current_user_id = $_SESSION['user_id'];

try {
    $result = add_comment($post_id, $current_user_id, $content, $parent_id);
    
    if ($result) {
        // Get the new comment data
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT pc.*, u.username, u.display_name, u.avatar
            FROM post_comments pc
            JOIN users u ON pc.user_id = u.id
            WHERE pc.id = LAST_INSERT_ID()
        ");
        $stmt->execute();
        $comment = $stmt->fetch();
        
        // Log activity
        log_activity('add_comment', "Added comment to post ID: $post_id", $current_user_id);
        
        echo json_encode([
            'success' => true, 
            'comment' => $comment,
            'message' => 'Comment added successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
