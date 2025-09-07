<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

$query = sanitize_input($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min((int)($_GET['limit'] ?? 20), 50);
$content_type = sanitize_input($_GET['type'] ?? 'all');
$category = (int)($_GET['category'] ?? 0);
$sort = sanitize_input($_GET['sort'] ?? 'relevance');
$date_range = sanitize_input($_GET['date_range'] ?? '');
$author = sanitize_input($_GET['author'] ?? '');

$offset = ($page - 1) * $limit;
$results = [];

try {
    // Build date filter
    $date_filter = '';
    $date_params = [];
    if ($date_range) {
        switch ($date_range) {
            case 'today':
                $date_filter = ' AND DATE(published_at) = CURDATE()';
                break;
            case 'week':
                $date_filter = ' AND published_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
                break;
            case 'month':
                $date_filter = ' AND published_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
                break;
            case 'year':
                $date_filter = ' AND published_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
                break;
        }
    }
    
    // Build sort clause
    $sort_clause = '';
    switch ($sort) {
        case 'title':
            $sort_clause = 'ORDER BY title ASC';
            break;
        case 'date':
            $sort_clause = 'ORDER BY published_at DESC';
            break;
        case 'views':
            $sort_clause = 'ORDER BY view_count DESC';
            break;
        case 'popularity':
            $sort_clause = 'ORDER BY (view_count + likes_count) DESC';
            break;
        default:
            $sort_clause = 'ORDER BY 
                CASE 
                    WHEN title LIKE ? THEN 100
                    WHEN content LIKE ? THEN 80
                    ELSE 60
                END DESC, view_count DESC';
    }
    
    // Search articles
    if ($content_type === 'all' || $content_type === 'articles') {
        $sql = "
            SELECT 
                'article' as type,
                title as text,
                title,
                slug as url,
                excerpt as content,
                excerpt,
                view_count as views,
                published_at,
                created_at,
                u.username as author,
                u.display_name as author_name,
                c.name as category_name
            FROM wiki_articles wa
            LEFT JOIN users u ON wa.author_id = u.id
            LEFT JOIN content_categories c ON wa.category_id = c.id
            WHERE wa.status = 'published'
        ";
        
        $params = [];
        
        if ($query) {
            $sql .= " AND (wa.title LIKE ? OR wa.content LIKE ? OR wa.excerpt LIKE ?)";
            $search_term = '%' . $query . '%';
            $params = array_merge($params, [$search_term, $search_term, $search_term]);
        }
        
        if ($category) {
            $sql .= " AND wa.category_id = ?";
            $params[] = $category;
        }
        
        if ($author) {
            $sql .= " AND (u.username LIKE ? OR u.display_name LIKE ?)";
            $author_term = '%' . $author . '%';
            $params[] = $author_term;
            $params[] = $author_term;
        }
        
        $sql .= $date_filter;
        
        if ($sort === 'relevance' && $query) {
            $sql .= " " . $sort_clause;
            $params[] = $query . '%';
            $params[] = '%' . $query . '%';
        } else {
            $sql .= " " . $sort_clause;
        }
        
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $article_results = $stmt->fetchAll();
        
        foreach ($article_results as $result) {
            $results[] = [
                'type' => 'article',
                'title' => $result['title'],
                'text' => $result['text'],
                'url' => '/wiki/' . $result['url'],
                'content' => $result['content'],
                'excerpt' => $result['excerpt'],
                'views' => number_format($result['views']),
                'published_at' => $result['published_at'],
                'created_at' => $result['created_at'],
                'author' => $result['author_name'] ?: $result['author'],
                'category' => $result['category_name']
            ];
        }
    }
    
    // Search users
    if ($content_type === 'all' || $content_type === 'users') {
        $sql = "
            SELECT 
                'user' as type,
                username,
                display_name,
                bio as content,
                bio as excerpt,
                created_at,
                avatar
            FROM users
            WHERE is_active = 1
        ";
        
        $params = [];
        
        if ($query) {
            $sql .= " AND (username LIKE ? OR display_name LIKE ? OR bio LIKE ?)";
            $search_term = '%' . $query . '%';
            $params = array_merge($params, [$search_term, $search_term, $search_term]);
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $user_results = $stmt->fetchAll();
        
        foreach ($user_results as $result) {
            $results[] = [
                'type' => 'user',
                'title' => $result['display_name'] ?: $result['username'],
                'text' => $result['display_name'] ?: $result['username'],
                'url' => '/user/' . $result['username'],
                'content' => $result['content'],
                'excerpt' => $result['excerpt'],
                'created_at' => $result['created_at'],
                'avatar' => $result['avatar']
            ];
        }
    }
    
    // Search messages (only for logged-in users)
    if (($content_type === 'all' || $content_type === 'messages') && is_logged_in()) {
        $user_id = $_SESSION['user_id'];
        
        $sql = "
            SELECT 
                'message' as type,
                CONCAT('Message from ', u.display_name) as title,
                m.content as text,
                m.content as content,
                m.content as excerpt,
                m.created_at,
                u.username as author,
                u.display_name as author_name
            FROM messages m
            LEFT JOIN users u ON m.sender_id = u.id
            WHERE (m.sender_id = ? OR m.recipient_id = ?)
        ";
        
        $params = [$user_id, $user_id];
        
        if ($query) {
            $sql .= " AND m.content LIKE ?";
            $params[] = '%' . $query . '%';
        }
        
        $sql .= " ORDER BY m.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $message_results = $stmt->fetchAll();
        
        foreach ($message_results as $result) {
            $results[] = [
                'type' => 'message',
                'title' => $result['title'],
                'text' => $result['text'],
                'url' => '/messages',
                'content' => $result['content'],
                'excerpt' => $result['excerpt'],
                'created_at' => $result['created_at'],
                'author' => $result['author_name'] ?: $result['author']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'results' => $results,
        'page' => $page,
        'limit' => $limit,
        'total' => count($results)
    ]);
    
} catch (Exception $e) {
    error_log("Load more search results error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load more results',
        'results' => []
    ]);
}
?>
