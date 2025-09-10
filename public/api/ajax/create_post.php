<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$content = trim($input['content'] ?? '');
$post_type = $input['post_type'] ?? 'text';
$is_public = isset($input['is_public']) ? (bool)$input['is_public'] : true;

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Post content cannot be empty']);
    exit();
}

if (strlen($content) > 5000) {
    echo json_encode(['success' => false, 'message' => 'Post content too long (max 5000 characters)']);
    exit();
}

$current_user_id = $_SESSION['user_id'];

try {
    $result = create_user_post($current_user_id, $content, $post_type, null, null, null, null, null, null, $is_public ? 1 : 0);
    
    if ($result) {
        // Get the created post data
        $stmt = $pdo->prepare("
            SELECT up.*, u.username, u.display_name, u.avatar,
                   COALESCE(like_counts.likes_count, 0) as likes_count,
                   COALESCE(comment_counts.comments_count, 0) as comments_count,
                   COALESCE(share_counts.shares_count, 0) as shares_count
            FROM user_posts up
            JOIN users u ON up.user_id = u.id
            LEFT JOIN (
                SELECT post_id, COUNT(*) as likes_count
                FROM post_interactions
                WHERE interaction_type = 'like'
                GROUP BY post_id
            ) like_counts ON up.id = like_counts.post_id
            LEFT JOIN (
                SELECT post_id, COUNT(*) as comments_count
                FROM post_comments
                GROUP BY post_id
            ) comment_counts ON up.id = comment_counts.post_id
            LEFT JOIN (
                SELECT post_id, COUNT(*) as shares_count
                FROM post_interactions
                WHERE interaction_type = 'share'
                GROUP BY post_id
            ) share_counts ON up.id = share_counts.post_id
            WHERE up.id = LAST_INSERT_ID()
        ");
        $stmt->execute();
        $post = $stmt->fetch();
        
        // Log activity
        log_activity('create_post', "Created a new post", $current_user_id);
        
        echo json_encode([
            'success' => true, 
            'post' => $post,
            'message' => 'Post created successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create post']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
