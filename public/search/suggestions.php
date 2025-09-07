<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$query = sanitize_input($_GET['q'] ?? '');
$limit = (int)($_GET['limit'] ?? 10);

if (strlen($query) < 2) {
    echo json_encode(['suggestions' => []]);
    exit;
}

$suggestions = [];

try {
    // Get article suggestions
    $stmt = $pdo->prepare("
        SELECT 'article' as type, title as text, slug as url, 'wiki' as category
        FROM wiki_articles 
        WHERE status = 'published' AND title LIKE ?
        ORDER BY view_count DESC, published_at DESC
        LIMIT ?
    ");
    $stmt->execute(['%' . $query . '%', $limit]);
    $article_suggestions = $stmt->fetchAll();
    
    // Get user suggestions
    $stmt = $pdo->prepare("
        SELECT 'user' as type, CONCAT(COALESCE(display_name, username), ' (@', username, ')') as text, username as url, 'users' as category
        FROM users 
        WHERE is_active = 1 AND (username LIKE ? OR display_name LIKE ?)
        ORDER BY created_at DESC
        LIMIT ?
    ");
    $stmt->execute(['%' . $query . '%', '%' . $query . '%', $limit]);
    $user_suggestions = $stmt->fetchAll();
    
    // Get category suggestions
    $stmt = $pdo->prepare("
        SELECT 'category' as type, name as text, slug as url, 'categories' as category
        FROM content_categories 
        WHERE is_active = 1 AND name LIKE ?
        ORDER BY sort_order ASC, name ASC
        LIMIT ?
    ");
    $stmt->execute(['%' . $query . '%', $limit]);
    $category_suggestions = $stmt->fetchAll();
    
    // Combine and limit suggestions
    $all_suggestions = array_merge($article_suggestions, $user_suggestions, $category_suggestions);
    $suggestions = array_slice($all_suggestions, 0, $limit);
    
} catch (Exception $e) {
    error_log("Search suggestions error: " . $e->getMessage());
}

echo json_encode(['suggestions' => $suggestions]);
?>
