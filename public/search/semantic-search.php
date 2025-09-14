<link rel="stylesheet" href="/skins/bismillah/assets/css/search.css">
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$query = sanitize_input($_GET['q'] ?? '');
$user_id = $_SESSION['user_id'] ?? null;

if (strlen($query) < 2) {
    echo json_encode(['suggestions' => [], 'semantic_results' => []]);
    exit;
}

try {
    // Parse natural language query
    $parsed_query = parseNaturalLanguageQuery($query);
    
    // Generate semantic suggestions
    $semantic_suggestions = generateSemanticSuggestions($parsed_query);
    
    // Get contextual results
    $contextual_results = getContextualResults($parsed_query, $user_id);
    
    // Generate related topics
    $related_topics = generateRelatedTopics($parsed_query);
    
    echo json_encode([
        'success' => true,
        'query' => $query,
        'parsed_query' => $parsed_query,
        'semantic_suggestions' => $semantic_suggestions,
        'contextual_results' => $contextual_results,
        'related_topics' => $related_topics,
        'search_intent' => detectSearchIntent($query)
    ]);
    
} catch (Exception $e) {
    error_log("Semantic search error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Semantic search temporarily unavailable']);
}

function parseNaturalLanguageQuery($query) {
    $parsed = [
        'original' => $query,
        'keywords' => [],
        'intent' => 'search',
        'filters' => [],
        'timeframe' => null,
        'entity_type' => null
    ];
    
    // Extract keywords
    $parsed['keywords'] = extractKeywords($query);
    
    // Detect search intent
    $parsed['intent'] = detectSearchIntent($query);
    
    // Extract time-based filters
    $parsed['timeframe'] = extractTimeframe($query);
    
    // Extract entity types
    $parsed['entity_type'] = extractEntityType($query);
    
    // Extract specific filters
    $parsed['filters'] = extractFilters($query);
    
    return $parsed;
}

function extractKeywords($query) {
    // Remove common stop words
    $stop_words = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'must', 'shall'];
    
    $words = preg_split('/\s+/', strtolower($query));
    $keywords = array_filter($words, function($word) use ($stop_words) {
        return strlen($word) > 2 && !in_array($word, $stop_words);
    });
    
    return array_values($keywords);
}

function detectSearchIntent($query) {
    $intents = [
        'definition' => ['what is', 'define', 'definition', 'meaning', 'explain'],
        'how_to' => ['how to', 'how do', 'how can', 'tutorial', 'guide', 'steps'],
        'comparison' => ['vs', 'versus', 'compare', 'difference', 'better', 'best'],
        'location' => ['where', 'location', 'place', 'find', 'near'],
        'time' => ['when', 'time', 'schedule', 'date', 'calendar'],
        'person' => ['who', 'person', 'author', 'scholar', 'imam'],
        'search' => [] // default
    ];
    
    $query_lower = strtolower($query);
    
    foreach ($intents as $intent => $patterns) {
        foreach ($patterns as $pattern) {
            if (strpos($query_lower, $pattern) !== false) {
                return $intent;
            }
        }
    }
    
    return 'search';
}

function extractTimeframe($query) {
    $timeframes = [
        'today' => ['today', 'this day'],
        'week' => ['this week', 'week', 'weekly'],
        'month' => ['this month', 'month', 'monthly'],
        'year' => ['this year', 'year', 'yearly'],
        'recent' => ['recent', 'latest', 'new', 'newest'],
        'old' => ['old', 'ancient', 'historical', 'past']
    ];
    
    $query_lower = strtolower($query);
    
    foreach ($timeframes as $timeframe => $patterns) {
        foreach ($patterns as $pattern) {
            if (strpos($query_lower, $pattern) !== false) {
                return $timeframe;
            }
        }
    }
    
    return null;
}

function extractEntityType($query) {
    $entity_types = [
        'person' => ['person', 'people', 'scholar', 'imam', 'author', 'writer'],
        'place' => ['place', 'location', 'city', 'country', 'mosque', 'masjid'],
        'concept' => ['concept', 'idea', 'belief', 'principle', 'doctrine'],
        'event' => ['event', 'occasion', 'celebration', 'festival', 'holiday'],
        'book' => ['book', 'text', 'scripture', 'quran', 'hadith', 'sunnah']
    ];
    
    $query_lower = strtolower($query);
    
    foreach ($entity_types as $type => $patterns) {
        foreach ($patterns as $pattern) {
            if (strpos($query_lower, $pattern) !== false) {
                return $type;
            }
        }
    }
    
    return null;
}

function extractFilters($query) {
    $filters = [];
    
    // Extract category filters
    $categories = ['prayer', 'fasting', 'charity', 'pilgrimage', 'belief', 'ethics', 'history', 'law'];
    $query_lower = strtolower($query);
    
    foreach ($categories as $category) {
        if (strpos($query_lower, $category) !== false) {
            $filters['category'] = $category;
            break;
        }
    }
    
    return $filters;
}

function generateSemanticSuggestions($parsed_query) {
    global $pdo;
    
    $suggestions = [];
    $keywords = $parsed_query['keywords'];
    
    if (empty($keywords)) {
        return $suggestions;
    }
    
    // Generate semantic variations
    $semantic_variations = generateSemanticVariations($keywords);
    
    // Get related articles
    foreach ($semantic_variations as $variation) {
        $stmt = $pdo->prepare("
            SELECT 
                'article' as type,
                title,
                slug,
                excerpt,
                view_count,
                published_at
            FROM wiki_articles 
            WHERE status = 'published' 
            AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)
            ORDER BY 
                CASE 
                    WHEN title LIKE ? THEN 100
                    WHEN content LIKE ? THEN 80
                    ELSE 60
                END DESC,
                view_count DESC
            LIMIT 3
        ");
        
        $search_term = '%' . $variation . '%';
        $title_exact = $variation . '%';
        $stmt->execute([$search_term, $search_term, $search_term, $title_exact, $search_term]);
        $articles = $stmt->fetchAll();
        
        foreach ($articles as $article) {
            $suggestions[] = [
                'type' => 'semantic_article',
                'title' => $article['title'],
                'url' => '/wiki/' . $article['slug'],
                'excerpt' => substr($article['excerpt'], 0, 100) . '...',
                'relevance' => 'semantic',
                'variation' => $variation
            ];
        }
    }
    
    return array_slice($suggestions, 0, 10);
}

function generateSemanticVariations($keywords) {
    $variations = [];
    
    // Add original keywords
    $variations = array_merge($variations, $keywords);
    
    // Generate synonyms and related terms
    $synonyms = [
        'prayer' => ['salah', 'namaz', 'worship', 'devotion'],
        'fasting' => ['sawm', 'roza', 'abstinence'],
        'charity' => ['zakat', 'sadaqah', 'donation', 'giving'],
        'pilgrimage' => ['hajj', 'umrah', 'journey', 'visit'],
        'islam' => ['muslim', 'islamic', 'deen', 'religion'],
        'quran' => ['koran', 'holy book', 'scripture'],
        'hadith' => ['sunnah', 'tradition', 'prophetic saying'],
        'mosque' => ['masjid', 'place of worship', 'house of god']
    ];
    
    foreach ($keywords as $keyword) {
        if (isset($synonyms[$keyword])) {
            $variations = array_merge($variations, $synonyms[$keyword]);
        }
    }
    
    return array_unique($variations);
}

function getContextualResults($parsed_query, $user_id) {
    global $pdo;
    
    $contextual_results = [];
    
    // Get user's search history for context
    if ($user_id) {
        $stmt = $pdo->prepare("
            SELECT query, content_type, searched_at
            FROM user_search_history 
            WHERE user_id = ? 
            ORDER BY searched_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $recent_searches = $stmt->fetchAll();
        
        // Find related searches
        foreach ($recent_searches as $search) {
            if (hasSemanticRelation($parsed_query['keywords'], $search['query'])) {
                $contextual_results[] = [
                    'type' => 'related_search',
                    'title' => 'Related to: ' . $search['query'],
                    'url' => '/search?q=' . urlencode($search['query']),
                    'relevance' => 'contextual'
                ];
            }
        }
    }
    
    // Get trending topics related to query
    $stmt = $pdo->prepare("
        SELECT query, COUNT(*) as search_count
        FROM search_analytics 
        WHERE query LIKE ? 
        AND search_time > DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY query
        ORDER BY search_count DESC
        LIMIT 3
    ");
    $stmt->execute(['%' . implode('%', $parsed_query['keywords']) . '%']);
    $trending = $stmt->fetchAll();
    
    foreach ($trending as $trend) {
        $contextual_results[] = [
            'type' => 'trending',
            'title' => 'Trending: ' . $trend['query'],
            'url' => '/search?q=' . urlencode($trend['query']),
            'relevance' => 'trending',
            'search_count' => $trend['search_count']
        ];
    }
    
    return $contextual_results;
}

function hasSemanticRelation($keywords1, $query2) {
    $keywords2 = extractKeywords($query2);
    
    // Check for common keywords
    $common_keywords = array_intersect($keywords1, $keywords2);
    
    return count($common_keywords) > 0;
}

function generateRelatedTopics($parsed_query) {
    global $pdo;
    
    $related_topics = [];
    $keywords = $parsed_query['keywords'];
    
    if (empty($keywords)) {
        return $related_topics;
    }
    
    // Get related categories
    $stmt = $pdo->prepare("
        SELECT 
            c.name,
            c.slug,
            c.description,
            COUNT(wa.id) as article_count
        FROM content_categories c
        LEFT JOIN wiki_articles wa ON c.id = wa.category_id
        WHERE c.is_active = 1
        AND (c.name LIKE ? OR c.description LIKE ?)
        GROUP BY c.id
        ORDER BY article_count DESC
        LIMIT 5
    ");
    
    $search_term = '%' . implode('%', $keywords) . '%';
    $stmt->execute([$search_term, $search_term]);
    $categories = $stmt->fetchAll();
    
    foreach ($categories as $category) {
        $related_topics[] = [
            'type' => 'category',
            'title' => $category['name'],
            'url' => '/wiki/category/' . $category['slug'],
            'description' => $category['description'],
            'article_count' => $category['article_count']
        ];
    }
    
    return $related_topics;
}
?>
<script src="/skins/bismillah/assets/js/search_index.js"></script>
