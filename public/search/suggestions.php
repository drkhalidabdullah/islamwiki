<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');
header('Cache-Control: public, max-age=300'); // Cache for 5 minutes

$query = sanitize_input($_GET['q'] ?? '');
$type = sanitize_input($_GET['type'] ?? 'all');
$limit = min((int)($_GET['limit'] ?? 10), 20); // Max 20 suggestions

if (strlen($query) < 2) {
    echo json_encode(['suggestions' => [], 'total' => 0]);
    exit;
}

$suggestions = [];
$user_id = $_SESSION['user_id'] ?? null;

try {
    // Get article suggestions with better relevance scoring
    if ($type === 'all' || $type === 'articles') {
        $stmt = $pdo->prepare("
            SELECT 
                'article' as type,
                title as text,
                slug as url,
                'wiki' as category,
                excerpt,
                view_count,
                published_at,
                CASE 
                    WHEN title LIKE ? THEN 100
                    WHEN title LIKE ? THEN 80
                    WHEN content LIKE ? THEN 60
                    ELSE 40
                END as relevance_score
            FROM wiki_articles 
            WHERE status = 'published' 
            AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)
            ORDER BY relevance_score DESC, view_count DESC, published_at DESC
            LIMIT ?
        ");
        $search_term = '%' . $query . '%';
        $title_exact = $query . '%';
        $stmt->execute([$title_exact, $search_term, $search_term, $search_term, $search_term, $search_term, $limit]);
        $article_suggestions = $stmt->fetchAll();
        
        foreach ($article_suggestions as $suggestion) {
            $suggestions[] = [
                'type' => 'article',
                'text' => $suggestion['text'],
                'url' => '/wiki/' . $suggestion['url'],
                'category' => 'Articles',
                'excerpt' => substr($suggestion['excerpt'], 0, 100) . '...',
                'views' => number_format($suggestion['view_count']),
                'date' => date('M j, Y', strtotime($suggestion['published_at'])),
                'icon' => 'fas fa-book',
                'relevance' => $suggestion['relevance_score']
            ];
        }
    }
    
    // Get user suggestions with better matching
    if ($type === 'all' || $type === 'users') {
        $stmt = $pdo->prepare("
            SELECT 
                'user' as type,
                username,
                display_name,
                bio,
                avatar,
                created_at,
                CASE 
                    WHEN username LIKE ? THEN 100
                    WHEN display_name LIKE ? THEN 90
                    WHEN bio LIKE ? THEN 70
                    ELSE 50
                END as relevance_score
            FROM users 
            WHERE is_active = 1 
            AND (username LIKE ? OR display_name LIKE ? OR bio LIKE ?)
            ORDER BY relevance_score DESC, created_at DESC
            LIMIT ?
        ");
        $search_term = '%' . $query . '%';
        $username_exact = $query . '%';
        $display_exact = $query . '%';
        $stmt->execute([$username_exact, $display_exact, $search_term, $search_term, $search_term, $search_term, $limit]);
        $user_suggestions = $stmt->fetchAll();
        
        foreach ($user_suggestions as $suggestion) {
            $display_text = $suggestion['display_name'] ?: $suggestion['username'];
            $suggestions[] = [
                'type' => 'user',
                'text' => $display_text,
                'subtext' => '@' . $suggestion['username'],
                'url' => '/user/' . $suggestion['username'],
                'category' => 'Users',
                'bio' => substr($suggestion['bio'], 0, 80) . '...',
                'avatar' => $suggestion['avatar'] ?: '/assets/images/default-avatar.png',
                'join_date' => date('M Y', strtotime($suggestion['created_at'])),
                'icon' => 'fas fa-user',
                'relevance' => $suggestion['relevance_score']
            ];
        }
    }
    
    // Get category suggestions
    if ($type === 'all' || $type === 'categories') {
        $stmt = $pdo->prepare("
            SELECT 
                'category' as type,
                name as text,
                slug as url,
                description,
                article_count,
                CASE 
                    WHEN name LIKE ? THEN 100
                    WHEN description LIKE ? THEN 70
                    ELSE 50
                END as relevance_score
            FROM content_categories 
            WHERE is_active = 1 
            AND (name LIKE ? OR description LIKE ?)
            ORDER BY relevance_score DESC, article_count DESC, name ASC
            LIMIT ?
        ");
        $search_term = '%' . $query . '%';
        $name_exact = $query . '%';
        $stmt->execute([$name_exact, $search_term, $search_term, $search_term, $limit]);
        $category_suggestions = $stmt->fetchAll();
        
        foreach ($category_suggestions as $suggestion) {
            $suggestions[] = [
                'type' => 'category',
                'text' => $suggestion['text'],
                'url' => '/wiki/category/' . $suggestion['url'],
                'category' => 'Categories',
                'description' => substr($suggestion['description'], 0, 80) . '...',
                'article_count' => $suggestion['article_count'],
                'icon' => 'fas fa-folder',
                'relevance' => $suggestion['relevance_score']
            ];
        }
    }
    
    // Get popular searches if query is short
    if (strlen($query) <= 3 && $type === 'all') {
        $stmt = $pdo->prepare("
            SELECT query, COUNT(*) as search_count
            FROM search_analytics 
            WHERE query LIKE ? AND search_time > DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY query
            ORDER BY search_count DESC
            LIMIT 5
        ");
        $stmt->execute([$query . '%']);
        $popular_searches = $stmt->fetchAll();
        
        foreach ($popular_searches as $popular) {
            $suggestions[] = [
                'type' => 'popular',
                'text' => $popular['query'],
                'url' => '/search?q=' . urlencode($popular['query']),
                'category' => 'Popular Searches',
                'search_count' => $popular['search_count'],
                'icon' => 'fas fa-fire',
                'relevance' => 30
            ];
        }
    }
    
    // Sort all suggestions by relevance
    usort($suggestions, function($a, $b) {
        return $b['relevance'] - $a['relevance'];
    });
    
    // Limit total suggestions
    $suggestions = array_slice($suggestions, 0, $limit);
    
    // Track search for analytics
    if ($user_id) {
        $stmt = $pdo->prepare("
            INSERT INTO search_analytics (query, user_id, content_type, results_count, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $query,
            $user_id,
            $type,
            count($suggestions),
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
    
    echo json_encode([
        'suggestions' => $suggestions,
        'total' => count($suggestions),
        'query' => $query,
        'type' => $type
    ]);
    
} catch (Exception $e) {
    error_log("Search suggestions error: " . $e->getMessage());
    echo json_encode(['suggestions' => [], 'total' => 0, 'error' => 'Search temporarily unavailable']);
}
?>
