<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'get_personalized_results';
$query = sanitize_input($_GET['q'] ?? '');
$content_type = sanitize_input($_GET['type'] ?? 'all');
$limit = min((int)($_GET['limit'] ?? 20), 50);

try {
    switch ($action) {
        case 'get_personalized_results':
            $personalized_results = getPersonalizedSearchResults($user_id, $query, $content_type, $limit);
            echo json_encode([
                'success' => true,
                'results' => $personalized_results,
                'personalization_factors' => getPersonalizationFactors($user_id)
            ]);
            break;
            
        case 'update_preferences':
            $preferences = json_decode(file_get_contents('php://input'), true);
            updateUserSearchPreferences($user_id, $preferences);
            echo json_encode(['success' => true, 'message' => 'Preferences updated']);
            break;
            
        case 'get_recommendations':
            $recommendations = getUserSearchRecommendations($user_id, $limit);
            echo json_encode([
                'success' => true,
                'recommendations' => $recommendations
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    error_log("Search personalization error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

function getPersonalizedSearchResults($user_id, $query, $content_type, $limit) {
    global $pdo;
    
    // Get user's search history and preferences
    $user_profile = buildUserSearchProfile($user_id);
    
    // Get base search results
    $base_results = getBaseSearchResults($query, $content_type, $limit * 2);
    
    // Apply personalization scoring
    $personalized_results = applyPersonalizationScoring($base_results, $user_profile);
    
    // Sort by personalized score
    usort($personalized_results, function($a, $b) {
        return $b['personalized_score'] - $a['personalized_score'];
    });
    
    return array_slice($personalized_results, 0, $limit);
}

function buildUserSearchProfile($user_id) {
    global $pdo;
    
    $profile = [
        'interests' => [],
        'preferred_categories' => [],
        'preferred_authors' => [],
        'search_patterns' => [],
        'reading_behavior' => []
    ];
    
    // Get user's search history
    $stmt = $pdo->prepare("
        SELECT query, content_type, results_count, searched_at
        FROM user_search_history 
        WHERE user_id = ? 
        ORDER BY searched_at DESC 
        LIMIT 100
    ");
    $stmt->execute([$user_id]);
    $search_history = $stmt->fetchAll();
    
    // Analyze search patterns
    $profile['search_patterns'] = analyzeSearchPatterns($search_history);
    
    // Get user's reading behavior (articles viewed)
    $stmt = $pdo->prepare("
        SELECT wa.category_id, wa.author_id, wa.view_count, c.name as category_name, u.username as author_name
        FROM wiki_articles wa
        LEFT JOIN content_categories c ON wa.category_id = c.id
        LEFT JOIN users u ON wa.author_id = u.id
        WHERE wa.id IN (
            SELECT DISTINCT article_id 
            FROM article_views 
            WHERE user_id = ? 
            ORDER BY viewed_at DESC 
            LIMIT 50
        )
    ");
    $stmt->execute([$user_id]);
    $reading_behavior = $stmt->fetchAll();
    
    // Analyze reading preferences
    $profile['preferred_categories'] = analyzeCategoryPreferences($reading_behavior);
    $profile['preferred_authors'] = analyzeAuthorPreferences($reading_behavior);
    
    // Extract interests from search queries
    $profile['interests'] = extractInterestsFromQueries($search_history);
    
    return $profile;
}

function analyzeSearchPatterns($search_history) {
    $patterns = [
        'common_queries' => [],
        'query_length' => 0,
        'search_frequency' => 0,
        'preferred_content_types' => []
    ];
    
    if (empty($search_history)) {
        return $patterns;
    }
    
    // Analyze common queries
    $query_counts = [];
    foreach ($search_history as $search) {
        $query = strtolower($search['query']);
        $query_counts[$query] = ($query_counts[$query] ?? 0) + 1;
    }
    
    arsort($query_counts);
    $patterns['common_queries'] = array_slice(array_keys($query_counts), 0, 10);
    
    // Calculate average query length
    $total_length = 0;
    foreach ($search_history as $search) {
        $total_length += strlen($search['query']);
    }
    $patterns['query_length'] = $total_length / count($search_history);
    
    // Calculate search frequency (searches per day)
    $days = (strtotime($search_history[0]['searched_at']) - strtotime($search_history[count($search_history) - 1]['searched_at'])) / (24 * 60 * 60);
    $patterns['search_frequency'] = count($search_history) / max($days, 1);
    
    // Analyze preferred content types
    $content_type_counts = [];
    foreach ($search_history as $search) {
        $type = $search['content_type'];
        $content_type_counts[$type] = ($content_type_counts[$type] ?? 0) + 1;
    }
    arsort($content_type_counts);
    $patterns['preferred_content_types'] = array_keys($content_type_counts);
    
    return $patterns;
}

function analyzeCategoryPreferences($reading_behavior) {
    $category_counts = [];
    
    foreach ($reading_behavior as $behavior) {
        if ($behavior['category_id']) {
            $category_id = $behavior['category_id'];
            $category_counts[$category_id] = [
                'id' => $category_id,
                'name' => $behavior['category_name'],
                'count' => ($category_counts[$category_id]['count'] ?? 0) + 1
            ];
        }
    }
    
    // Sort by count and return top categories
    usort($category_counts, function($a, $b) {
        return $b['count'] - $a['count'];
    });
    
    return array_slice($category_counts, 0, 5);
}

function analyzeAuthorPreferences($reading_behavior) {
    $author_counts = [];
    
    foreach ($reading_behavior as $behavior) {
        if ($behavior['author_id']) {
            $author_id = $behavior['author_id'];
            $author_counts[$author_id] = [
                'id' => $author_id,
                'username' => $behavior['author_name'],
                'count' => ($author_counts[$author_id]['count'] ?? 0) + 1
            ];
        }
    }
    
    // Sort by count and return top authors
    usort($author_counts, function($a, $b) {
        return $b['count'] - $a['count'];
    });
    
    return array_slice($author_counts, 0, 5);
}

function extractInterestsFromQueries($search_history) {
    $interests = [];
    $interest_keywords = [
        'prayer' => ['prayer', 'salah', 'namaz', 'worship'],
        'fasting' => ['fasting', 'sawm', 'roza', 'ramadan'],
        'charity' => ['charity', 'zakat', 'sadaqah', 'donation'],
        'pilgrimage' => ['hajj', 'umrah', 'pilgrimage', 'mecca'],
        'quran' => ['quran', 'koran', 'scripture', 'verses'],
        'hadith' => ['hadith', 'sunnah', 'prophet', 'tradition'],
        'islamic_law' => ['fiqh', 'law', 'jurisprudence', 'ruling'],
        'history' => ['history', 'caliph', 'companion', 'battle'],
        'spirituality' => ['spirituality', 'sufism', 'dhikr', 'meditation']
    ];
    
    foreach ($search_history as $search) {
        $query = strtolower($search['query']);
        
        foreach ($interest_keywords as $interest => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($query, $keyword) !== false) {
                    $interests[$interest] = ($interests[$interest] ?? 0) + 1;
                    break;
                }
            }
        }
    }
    
    // Sort by frequency and return top interests
    arsort($interests);
    return array_slice(array_keys($interests), 0, 5);
}

function getBaseSearchResults($query, $content_type, $limit) {
    global $pdo;
    
    $results = [];
    
    // Search articles
    if ($content_type === 'all' || $content_type === 'articles') {
        $stmt = $pdo->prepare("
            SELECT 
                'article' as type,
                wa.id,
                wa.title,
                wa.slug,
                wa.excerpt,
                wa.view_count,
                wa.published_at,
                wa.category_id,
                wa.author_id,
                c.name as category_name,
                u.username as author_name
            FROM wiki_articles wa
            LEFT JOIN content_categories c ON wa.category_id = c.id
            LEFT JOIN users u ON wa.author_id = u.id
            WHERE wa.status = 'published'
            AND (wa.title LIKE ? OR wa.content LIKE ? OR wa.excerpt LIKE ?)
            ORDER BY wa.view_count DESC, wa.published_at DESC
            LIMIT ?
        ");
        $search_term = '%' . $query . '%';
        $stmt->execute([$search_term, $search_term, $search_term, $limit]);
        $articles = $stmt->fetchAll();
        
        foreach ($articles as $article) {
            $results[] = [
                'type' => 'article',
                'id' => $article['id'],
                'title' => $article['title'],
                'url' => '/wiki/' . $article['slug'],
                'excerpt' => $article['excerpt'],
                'view_count' => $article['view_count'],
                'published_at' => $article['published_at'],
                'category_id' => $article['category_id'],
                'category_name' => $article['category_name'],
                'author_id' => $article['author_id'],
                'author_name' => $article['author_name'],
                'base_score' => $article['view_count']
            ];
        }
    }
    
    return $results;
}

function applyPersonalizationScoring($results, $user_profile) {
    foreach ($results as &$result) {
        $personalized_score = $result['base_score'];
        
        // Category preference boost
        if (isset($result['category_id'])) {
            foreach ($user_profile['preferred_categories'] as $pref_category) {
                if ($pref_category['id'] == $result['category_id']) {
                    $personalized_score *= 1.5; // 50% boost for preferred categories
                    break;
                }
            }
        }
        
        // Author preference boost
        if (isset($result['author_id'])) {
            foreach ($user_profile['preferred_authors'] as $pref_author) {
                if ($pref_author['id'] == $result['author_id']) {
                    $personalized_score *= 1.3; // 30% boost for preferred authors
                    break;
                }
            }
        }
        
        // Interest-based boost
        if (isset($result['category_name'])) {
            $category_name = strtolower($result['category_name']);
            foreach ($user_profile['interests'] as $interest) {
                if (strpos($category_name, $interest) !== false) {
                    $personalized_score *= 1.2; // 20% boost for interest-related content
                    break;
                }
            }
        }
        
        // Recency boost for active users
        if ($user_profile['search_patterns']['search_frequency'] > 2) {
            $days_old = (time() - strtotime($result['published_at'])) / (24 * 60 * 60);
            if ($days_old < 30) {
                $personalized_score *= 1.1; // 10% boost for recent content
            }
        }
        
        $result['personalized_score'] = $personalized_score;
    }
    
    return $results;
}

function getPersonalizationFactors($user_id) {
    global $pdo;
    
    $profile = buildUserSearchProfile($user_id);
    
    return [
        'interests' => $profile['interests'],
        'preferred_categories' => $profile['preferred_categories'],
        'preferred_authors' => $profile['preferred_authors'],
        'search_frequency' => $profile['search_patterns']['search_frequency'],
        'common_queries' => $profile['search_patterns']['common_queries']
    ];
}

function updateUserSearchPreferences($user_id, $preferences) {
    global $pdo;
    
    // Store user preferences in database
    $stmt = $pdo->prepare("
        INSERT INTO user_search_preferences (user_id, preferences, updated_at)
        VALUES (?, ?, NOW())
        ON DUPLICATE KEY UPDATE
        preferences = VALUES(preferences),
        updated_at = VALUES(updated_at)
    ");
    
    $stmt->execute([$user_id, json_encode($preferences)]);
}

function getUserSearchRecommendations($user_id, $limit) {
    global $pdo;
    
    $profile = buildUserSearchProfile($user_id);
    $recommendations = [];
    
    // Get recommendations based on interests
    foreach ($profile['interests'] as $interest) {
        $stmt = $pdo->prepare("
            SELECT 
                wa.id,
                wa.title,
                wa.slug,
                wa.excerpt,
                wa.view_count,
                c.name as category_name
            FROM wiki_articles wa
            LEFT JOIN content_categories c ON wa.category_id = c.id
            WHERE wa.status = 'published'
            AND (wa.title LIKE ? OR wa.content LIKE ? OR c.name LIKE ?)
            ORDER BY wa.view_count DESC
            LIMIT 3
        ");
        
        $search_term = '%' . $interest . '%';
        $stmt->execute([$search_term, $search_term, $search_term]);
        $articles = $stmt->fetchAll();
        
        foreach ($articles as $article) {
            $recommendations[] = [
                'type' => 'recommendation',
                'title' => $article['title'],
                'url' => '/wiki/' . $article['slug'],
                'excerpt' => $article['excerpt'],
                'reason' => "Based on your interest in {$interest}",
                'category' => $article['category_name']
            ];
        }
    }
    
    // Remove duplicates and limit results
    $unique_recommendations = [];
    $seen_ids = [];
    
    foreach ($recommendations as $rec) {
        if (!in_array($rec['url'], $seen_ids)) {
            $unique_recommendations[] = $rec;
            $seen_ids[] = $rec['url'];
        }
    }
    
    return array_slice($unique_recommendations, 0, $limit);
}
?>
