<?php
require_once 'public/config/config.php';
require_once 'public/includes/functions.php';

// Mock session for testing
session_start();
$_SESSION['user_id'] = 1;

// Get user's following list for personalized feed
$stmt = $pdo->prepare("
    SELECT uf.following_id, u.username, u.display_name, u.avatar
    FROM user_follows uf
    JOIN users u ON uf.following_id = u.id
    WHERE uf.follower_id = ?
    ORDER BY uf.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$following = $stmt->fetchAll();

echo "Following count: " . count($following) . "\n";

// Get personalized feed content
$feed_items = [];

// Get recent posts from followed users
if (!empty($following)) {
    $following_ids = array_column($following, 'following_id');
    $placeholders = str_repeat('?,', count($following_ids) - 1) . '?';
    
    // Get posts from followed users only
    $stmt = $pdo->prepare("
        SELECT up.*, u.username, u.display_name, u.avatar, 'post' as content_type,
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
        WHERE up.user_id IN ($placeholders) AND up.is_public = 1
        ORDER BY up.created_at DESC
        LIMIT 50
    ");
    $stmt->execute($following_ids);
    $following_posts = $stmt->fetchAll();
    
    // Get articles from followed users only
    $stmt = $pdo->prepare("
        SELECT wa.*, u.username, u.display_name, u.avatar, 'article' as content_type
        FROM wiki_articles wa
        JOIN users u ON wa.author_id = u.id
        WHERE wa.author_id IN ($placeholders) AND wa.status = 'published'
        ORDER BY wa.published_at DESC
        LIMIT 50
    ");
    $stmt->execute($following_ids);
    $following_articles = $stmt->fetchAll();
    
    $feed_items = array_merge($following_posts, $following_articles);
}

echo "Following posts count: " . count($following_posts ?? []) . "\n";
echo "Following articles count: " . count($following_articles ?? []) . "\n";

// If no following content, get recent public posts
if (empty($feed_items)) {
    $stmt = $pdo->prepare("
        SELECT up.*, u.username, u.display_name, u.avatar, 'post' as content_type,
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
        WHERE up.is_public = 1
        ORDER BY up.created_at DESC
        LIMIT 50
    ");
    $stmt->execute();
    $feed_items = $stmt->fetchAll();
}

echo "Total feed items before filtering: " . count($feed_items) . "\n";

// Filter out articles with unparsed template syntax
$feed_items = array_filter($feed_items, function($item) {
    if ($item['content_type'] === 'article') {
        // Check if article contains unparsed template syntax (both {{ and }} must be present)
        return !(strpos($item['content'], '{{') !== false && strpos($item['content'], '}}') !== false);
    }
    return true;
});

echo "Total feed items after filtering: " . count($feed_items) . "\n";

foreach ($feed_items as $item) {
    echo "- " . $item['content_type'] . ": " . $item['title'] . "\n";
}
?>
