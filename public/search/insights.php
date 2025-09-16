<link rel="stylesheet" href="/skins/bismillah/assets/css/search.css">
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$query = sanitize_input($_GET['q'] ?? '');
$user_id = $_SESSION['user_id'] ?? null;

if (strlen($query) < 2) {
    echo json_encode(['insights' => [], 'recommendations' => []]);
    exit;
}

try {
    // Generate search insights
    $insights = generateSearchInsights($query, $user_id);
    
    // Generate recommendations
    $recommendations = generateSearchRecommendations($query, $user_id);
    
    // Get related searches
    $related_searches = getRelatedSearches($query);
    
    // Get search trends
    $search_trends = getSearchTrends($query);
    
    echo json_encode([
        'success' => true,
        'query' => $query,
        'insights' => $insights,
        'recommendations' => $recommendations,
        'related_searches' => $related_searches,
        'search_trends' => $search_trends
    ]);
    
} catch (Exception $e) {
    error_log("Search insights error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Insights temporarily unavailable']);
}

function generateSearchInsights($query, $user_id) {
    global $pdo;
    
    $insights = [];
    
    // Get search statistics
    $search_stats = getSearchStatistics($query);
    
    // Generate content insights
    $content_insights = generateContentInsights($query);
    $insights = array_merge($insights, $content_insights);
    
    // Generate user behavior insights
    if ($user_id) {
        $behavior_insights = generateBehaviorInsights($query, $user_id);
        $insights = array_merge($insights, $behavior_insights);
    }
    
    // Generate trend insights
    $trend_insights = generateTrendInsights($query, $search_stats);
    $insights = array_merge($insights, $trend_insights);
    
    // Generate quality insights
    $quality_insights = generateQualityInsights($query, $search_stats);
    $insights = array_merge($insights, $quality_insights);
    
    return $insights;
}

function getSearchStatistics($query) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_searches,
            COUNT(DISTINCT user_id) as unique_searchers,
            AVG(results_count) as avg_results,
            MIN(search_time) as first_search,
            MAX(search_time) as last_search
        FROM search_analytics 
        WHERE query = ?
    ");
    $stmt->execute([$query]);
    $stats = $stmt->fetch();
    
    // Get recent trend
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as recent_searches
        FROM search_analytics 
        WHERE query = ? AND search_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stmt->execute([$query]);
    $recent_stats = $stmt->fetch();
    
    $stats['recent_searches'] = $recent_stats['recent_searches'];
    
    return $stats;
}

function generateContentInsights($query) {
    global $pdo;
    
    $insights = [];
    
    // Check content coverage
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as article_count,
            COUNT(DISTINCT category_id) as category_count,
            COUNT(DISTINCT author_id) as author_count
        FROM wiki_articles 
        WHERE status = 'published'
        AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)
    ");
    $search_term = '%' . $query . '%';
    $stmt->execute([$search_term, $search_term, $search_term]);
    $content_stats = $stmt->fetch();
    
    if ($content_stats['article_count'] > 0) {
        $insights[] = [
            'type' => 'content_coverage',
            'title' => 'Content Coverage',
            'description' => "Found {$content_stats['article_count']} articles across {$content_stats['category_count']} categories by {$content_stats['author_count']} authors",
            'icon' => 'iw iw-book',
            'priority' => 'high'
        ];
        
        if ($content_stats['article_count'] > 10) {
            $insights[] = [
                'type' => 'rich_content',
                'title' => 'Rich Content Available',
                'description' => 'This topic has extensive coverage with multiple articles and perspectives',
                'icon' => 'iw iw-star',
                'priority' => 'medium'
            ];
        }
    } else {
        $insights[] = [
            'type' => 'content_gap',
            'title' => 'Content Gap Identified',
            'description' => 'No articles found for this topic. Consider creating content to fill this gap',
            'icon' => 'iw iw-exclamation-triangle',
            'priority' => 'high'
        ];
    }
    
    return $insights;
}

function generateBehaviorInsights($query, $user_id) {
    global $pdo;
    
    $insights = [];
    
    // Check user's search history for this query
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as search_count, MAX(searched_at) as last_search
        FROM user_search_history 
        WHERE user_id = ? AND query = ?
    ");
    $stmt->execute([$user_id, $query]);
    $user_stats = $stmt->fetch();
    
    if ($user_stats['search_count'] > 1) {
        $insights[] = [
            'type' => 'recurring_search',
            'title' => 'Recurring Interest',
            'description' => "You've searched for this topic {$user_stats['search_count']} times, indicating strong interest",
            'icon' => 'iw iw-repeat',
            'priority' => 'medium'
        ];
    }
    
    // Check for related searches
    $stmt = $pdo->prepare("
        SELECT query, COUNT(*) as count
        FROM user_search_history 
        WHERE user_id = ? AND query != ?
        AND (query LIKE ? OR ? LIKE CONCAT('%', query, '%'))
        GROUP BY query
        ORDER BY count DESC
        LIMIT 3
    ");
    $search_term = '%' . $query . '%';
    $stmt->execute([$user_id, $query, $search_term, $query]);
    $related_searches = $stmt->fetchAll();
    
    if (!empty($related_searches)) {
        $related_list = implode(', ', array_column($related_searches, 'query'));
        $insights[] = [
            'type' => 'related_interest',
            'title' => 'Related Interests',
            'description' => "You've also searched for: {$related_list}",
            'icon' => 'iw iw-link',
            'priority' => 'low'
        ];
    }
    
    return $insights;
}

function generateTrendInsights($query, $search_stats) {
    $insights = [];
    
    if ($search_stats['total_searches'] > 0) {
        // Calculate trend
        $recent_ratio = $search_stats['recent_searches'] / $search_stats['total_searches'];
        
        if ($recent_ratio > 0.5) {
            $insights[] = [
                'type' => 'trending_topic',
                'title' => 'Trending Topic',
                'description' => 'This topic has been searched frequently in the last week, indicating growing interest',
                'icon' => 'iw iw-fire',
                'priority' => 'high'
            ];
        } elseif ($recent_ratio < 0.1) {
            $insights[] = [
                'type' => 'classic_topic',
                'title' => 'Classic Topic',
                'description' => 'This is a well-established topic with consistent search interest over time',
                'icon' => 'iw iw-bookmark',
                'priority' => 'medium'
            ];
        }
        
        // Popularity insight
        if ($search_stats['total_searches'] > 100) {
            $insights[] = [
                'type' => 'popular_topic',
                'title' => 'Popular Topic',
                'description' => "This topic has been searched {$search_stats['total_searches']} times, making it one of the most popular topics",
                'icon' => 'iw iw-chart-line',
                'priority' => 'high'
            ];
        }
    }
    
    return $insights;
}

function generateQualityInsights($query, $search_stats) {
    $insights = [];
    
    if ($search_stats['avg_results'] > 0) {
        if ($search_stats['avg_results'] > 20) {
            $insights[] = [
                'type' => 'high_quality_results',
                'title' => 'High Quality Results',
                'description' => 'This search typically returns many relevant results, indicating good content coverage',
                'icon' => 'iw iw-check-circle',
                'priority' => 'medium'
            ];
        } elseif ($search_stats['avg_results'] < 5) {
            $insights[] = [
                'type' => 'limited_results',
                'title' => 'Limited Results',
                'description' => 'This search typically returns few results. Consider broadening your search terms',
                'icon' => 'iw iw-info-circle',
                'priority' => 'medium'
            ];
        }
    }
    
    return $insights;
}

function generateSearchRecommendations($query, $user_id) {
    global $pdo;
    
    $recommendations = [];
    
    // Get similar successful searches
    $stmt = $pdo->prepare("
        SELECT query, AVG(results_count) as avg_results, COUNT(*) as search_count
        FROM search_analytics 
        WHERE query != ? AND results_count > 5
        AND (query LIKE ? OR ? LIKE CONCAT('%', query, '%'))
        GROUP BY query
        ORDER BY avg_results DESC, search_count DESC
        LIMIT 5
    ");
    $search_term = '%' . $query . '%';
    $stmt->execute([$query, $search_term, $query]);
    $similar_searches = $stmt->fetchAll();
    
    foreach ($similar_searches as $search) {
        $recommendations[] = [
            'type' => 'similar_search',
            'title' => $search['query'],
            'description' => "Similar search that typically returns {$search['avg_results']} results",
            'url' => '/search?q=' . urlencode($search['query']),
            'icon' => 'iw iw-search'
        ];
    }
    
    // Get related categories
    $stmt = $pdo->prepare("
        SELECT c.name, c.slug, COUNT(wa.id) as article_count
        FROM content_categories c
        LEFT JOIN wiki_articles wa ON c.id = wa.category_id
        WHERE c.is_active = 1
        AND (c.name LIKE ? OR c.description LIKE ?)
        GROUP BY c.id
        ORDER BY article_count DESC
        LIMIT 3
    ");
    $search_term = '%' . $query . '%';
    $stmt->execute([$search_term, $search_term]);
    $related_categories = $stmt->fetchAll();
    
    foreach ($related_categories as $category) {
        $recommendations[] = [
            'type' => 'related_category',
            'title' => $category['name'],
            'description' => "Browse {$category['article_count']} articles in this category",
            'url' => '/wiki/category/' . $category['slug'],
            'icon' => 'iw iw-folder'
        ];
    }
    
    // Get popular articles on similar topics
    $stmt = $pdo->prepare("
        SELECT wa.title, wa.slug, wa.view_count, wa.excerpt
        FROM wiki_articles wa
        WHERE wa.status = 'published'
        AND (wa.title LIKE ? OR wa.content LIKE ? OR wa.excerpt LIKE ?)
        ORDER BY wa.view_count DESC
        LIMIT 3
    ");
    $search_term = '%' . $query . '%';
    $stmt->execute([$search_term, $search_term, $search_term]);
    $popular_articles = $stmt->fetchAll();
    
    foreach ($popular_articles as $article) {
        $recommendations[] = [
            'type' => 'popular_article',
            'title' => $article['title'],
            'description' => "Popular article with {$article['view_count']} views",
            'url' => '/wiki/' . $article['slug'],
            'icon' => 'iw iw-book'
        ];
    }
    
    return $recommendations;
}

function getRelatedSearches($query) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT query, COUNT(*) as search_count
        FROM search_analytics 
        WHERE query != ? AND search_time > DATE_SUB(NOW(), INTERVAL 30 DAY)
        AND (query LIKE ? OR ? LIKE CONCAT('%', query, '%'))
        GROUP BY query
        ORDER BY search_count DESC
        LIMIT 8
    ");
    $search_term = '%' . $query . '%';
    $stmt->execute([$query, $search_term, $query]);
    
    return $stmt->fetchAll();
}

function getSearchTrends($query) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            DATE(search_time) as date,
            COUNT(*) as searches
        FROM search_analytics 
        WHERE query = ? AND search_time > DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(search_time)
        ORDER BY date DESC
        LIMIT 7
    ");
    $stmt->execute([$query]);
    
    return $stmt->fetchAll();
}
?>
<script src="/skins/bismillah/assets/js/search_index.js"></script>
