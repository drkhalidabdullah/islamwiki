<link rel="stylesheet" href="/skins/bismillah/assets/css/search.css">
<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$query = sanitize_input($_GET['q'] ?? '');
$content_type = sanitize_input($_GET['type'] ?? 'all');
$limit = min((int)($_GET['limit'] ?? 50), 100);

if (strlen($query) < 2) {
    echo json_encode(['clusters' => [], 'total_results' => 0]);
    exit;
}

try {
    // Get search results
    $search_results = getSearchResults($query, $content_type, $limit * 2);
    
    // Cluster the results
    $clusters = clusterSearchResults($search_results, $query);
    
    // Enhance clusters with metadata
    $enhanced_clusters = enhanceClusters($clusters);
    
    echo json_encode([
        'success' => true,
        'query' => $query,
        'clusters' => $enhanced_clusters,
        'total_results' => count($search_results),
        'cluster_count' => count($enhanced_clusters)
    ]);
    
} catch (Exception $e) {
    error_log("Search clustering error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Clustering temporarily unavailable']);
}

function getSearchResults($query, $content_type, $limit) {
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
                wa.content,
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
                'content' => $article['content'],
                'view_count' => $article['view_count'],
                'published_at' => $article['published_at'],
                'category_id' => $article['category_id'],
                'category_name' => $article['category_name'],
                'author_id' => $article['author_id'],
                'author_name' => $article['author_name'],
                'keywords' => extractKeywords($article['title'] . ' ' . $article['excerpt'])
            ];
        }
    }
    
    // Search users
    if ($content_type === 'all' || $content_type === 'users') {
        $stmt = $pdo->prepare("
            SELECT 
                'user' as type,
                u.id,
                u.username,
                u.display_name,
                u.bio,
                u.created_at,
                u.avatar
            FROM users u
            WHERE u.is_active = 1
            AND (u.username LIKE ? OR u.display_name LIKE ? OR u.bio LIKE ?)
            ORDER BY u.created_at DESC
            LIMIT ?
        ");
        $search_term = '%' . $query . '%';
        $stmt->execute([$search_term, $search_term, $search_term, $limit]);
        $users = $stmt->fetchAll();
        
        foreach ($users as $user) {
            $results[] = [
                'type' => 'user',
                'id' => $user['id'],
                'title' => $user['display_name'] ?: $user['username'],
                'url' => '/user/' . $user['username'],
                'excerpt' => $user['bio'],
                'content' => $user['bio'],
                'created_at' => $user['created_at'],
                'username' => $user['username'],
                'avatar' => $user['avatar'],
                'keywords' => extractKeywords($user['display_name'] . ' ' . $user['bio'])
            ];
        }
    }
    
    return $results;
}

function extractKeywords($text) {
    // Simple keyword extraction
    $words = preg_split('/\s+/', strtolower($text));
    $stop_words = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'must', 'shall'];
    
    $keywords = array_filter($words, function($word) use ($stop_words) {
        return strlen($word) > 2 && !in_array($word, $stop_words);
    });
    
    return array_values($keywords);
}

function clusterSearchResults($results, $query) {
    $clusters = [];
    
    // Group by content type first
    $type_clusters = [];
    foreach ($results as $result) {
        $type = $result['type'];
        if (!isset($type_clusters[$type])) {
            $type_clusters[$type] = [];
        }
        $type_clusters[$type][] = $result;
    }
    
    // Further cluster each type
    foreach ($type_clusters as $type => $type_results) {
        switch ($type) {
            case 'article':
                $clusters = array_merge($clusters, clusterArticles($type_results, $query));
                break;
            case 'user':
                $clusters = array_merge($clusters, clusterUsers($type_results, $query));
                break;
        }
    }
    
    // Sort clusters by relevance and size
    usort($clusters, function($a, $b) {
        $score_a = $a['relevance_score'] * count($a['results']);
        $score_b = $b['relevance_score'] * count($b['results']);
        return $score_b - $score_a;
    });
    
    return $clusters;
}

function clusterArticles($articles, $query) {
    $clusters = [];
    
    // Cluster by category
    $category_clusters = [];
    foreach ($articles as $article) {
        $category = $article['category_name'] ?: 'Uncategorized';
        if (!isset($category_clusters[$category])) {
            $category_clusters[$category] = [];
        }
        $category_clusters[$category][] = $article;
    }
    
    foreach ($category_clusters as $category => $category_articles) {
        if (count($category_articles) > 0) {
            $clusters[] = [
                'type' => 'category',
                'title' => $category,
                'description' => "Articles in {$category} category",
                'results' => $category_articles,
                'relevance_score' => calculateCategoryRelevance($category, $query),
                'cluster_size' => count($category_articles),
                'icon' => 'fas fa-folder'
            ];
        }
    }
    
    // Cluster by author
    $author_clusters = [];
    foreach ($articles as $article) {
        $author = $article['author_name'] ?: 'Unknown Author';
        if (!isset($author_clusters[$author])) {
            $author_clusters[$author] = [];
        }
        $author_clusters[$author][] = $article;
    }
    
    foreach ($author_clusters as $author => $author_articles) {
        if (count($author_articles) > 1) { // Only cluster if multiple articles
            $clusters[] = [
                'type' => 'author',
                'title' => "By {$author}",
                'description' => "Articles written by {$author}",
                'results' => $author_articles,
                'relevance_score' => calculateAuthorRelevance($author, $query),
                'cluster_size' => count($author_articles),
                'icon' => 'fas fa-user'
            ];
        }
    }
    
    // Cluster by time period
    $time_clusters = clusterByTimePeriod($articles);
    $clusters = array_merge($clusters, $time_clusters);
    
    // Cluster by topic similarity
    $topic_clusters = clusterByTopicSimilarity($articles, $query);
    $clusters = array_merge($clusters, $topic_clusters);
    
    return $clusters;
}

function clusterUsers($users, $query) {
    $clusters = [];
    
    // Cluster by activity level (based on join date)
    $activity_clusters = [];
    foreach ($users as $user) {
        $join_date = strtotime($user['created_at']);
        $days_since_join = (time() - $join_date) / (24 * 60 * 60);
        
        if ($days_since_join < 30) {
            $activity = 'New Users';
        } elseif ($days_since_join < 365) {
            $activity = 'Recent Users';
        } else {
            $activity = 'Established Users';
        }
        
        if (!isset($activity_clusters[$activity])) {
            $activity_clusters[$activity] = [];
        }
        $activity_clusters[$activity][] = $user;
    }
    
    foreach ($activity_clusters as $activity => $activity_users) {
        if (count($activity_users) > 0) {
            $clusters[] = [
                'type' => 'activity',
                'title' => $activity,
                'description' => "Users who joined {$activity}",
                'results' => $activity_users,
                'relevance_score' => 0.5,
                'cluster_size' => count($activity_users),
                'icon' => 'fas fa-clock'
            ];
        }
    }
    
    return $clusters;
}

function clusterByTimePeriod($articles) {
    $clusters = [];
    $time_clusters = [
        'Recent' => [],
        'This Month' => [],
        'This Year' => [],
        'Older' => []
    ];
    
    foreach ($articles as $article) {
        $published_date = strtotime($article['published_at']);
        $days_ago = (time() - $published_date) / (24 * 60 * 60);
        
        if ($days_ago < 7) {
            $time_clusters['Recent'][] = $article;
        } elseif ($days_ago < 30) {
            $time_clusters['This Month'][] = $article;
        } elseif ($days_ago < 365) {
            $time_clusters['This Year'][] = $article;
        } else {
            $time_clusters['Older'][] = $article;
        }
    }
    
    foreach ($time_clusters as $period => $period_articles) {
        if (count($period_articles) > 0) {
            $clusters[] = [
                'type' => 'time',
                'title' => $period,
                'description' => "Articles published {$period}",
                'results' => $period_articles,
                'relevance_score' => $period === 'Recent' ? 1.0 : ($period === 'This Month' ? 0.8 : 0.6),
                'cluster_size' => count($period_articles),
                'icon' => 'fas fa-calendar'
            ];
        }
    }
    
    return $clusters;
}

function clusterByTopicSimilarity($articles, $query) {
    $clusters = [];
    $topic_groups = [];
    
    // Group articles by similar keywords
    foreach ($articles as $article) {
        $keywords = $article['keywords'];
        $matched_topic = null;
        
        // Find existing topic group with similar keywords
        foreach ($topic_groups as $topic => $topic_articles) {
            $topic_keywords = [];
            foreach ($topic_articles as $topic_article) {
                $topic_keywords = array_merge($topic_keywords, $topic_article['keywords']);
            }
            $topic_keywords = array_unique($topic_keywords);
            
            // Calculate keyword overlap
            $overlap = count(array_intersect($keywords, $topic_keywords));
            if ($overlap > 0 && $overlap / max(count($keywords), count($topic_keywords)) > 0.3) {
                $matched_topic = $topic;
                break;
            }
        }
        
        if ($matched_topic) {
            $topic_groups[$matched_topic][] = $article;
        } else {
            // Create new topic group
            $topic_name = implode(' ', array_slice($keywords, 0, 3));
            $topic_groups[$topic_name] = [$article];
        }
    }
    
    // Convert topic groups to clusters
    foreach ($topic_groups as $topic => $topic_articles) {
        if (count($topic_articles) > 1) { // Only cluster if multiple articles
            $clusters[] = [
                'type' => 'topic',
                'title' => $topic,
                'description' => "Articles about {$topic}",
                'results' => $topic_articles,
                'relevance_score' => calculateTopicRelevance($topic, $query),
                'cluster_size' => count($topic_articles),
                'icon' => 'fas fa-tags'
            ];
        }
    }
    
    return $clusters;
}

function calculateCategoryRelevance($category, $query) {
    $query_lower = strtolower($query);
    $category_lower = strtolower($category);
    
    if (strpos($category_lower, $query_lower) !== false) {
        return 1.0;
    }
    
    // Check for partial matches
    $query_words = explode(' ', $query_lower);
    $category_words = explode(' ', $category_lower);
    
    $matches = 0;
    foreach ($query_words as $query_word) {
        foreach ($category_words as $category_word) {
            if (strpos($category_word, $query_word) !== false || strpos($query_word, $category_word) !== false) {
                $matches++;
                break;
            }
        }
    }
    
    return $matches / count($query_words);
}

function calculateAuthorRelevance($author, $query) {
    $query_lower = strtolower($query);
    $author_lower = strtolower($author);
    
    if (strpos($author_lower, $query_lower) !== false) {
        return 1.0;
    }
    
    return 0.3; // Default relevance for author clusters
}

function calculateTopicRelevance($topic, $query) {
    $query_lower = strtolower($query);
    $topic_lower = strtolower($topic);
    
    $query_words = explode(' ', $query_lower);
    $topic_words = explode(' ', $topic_lower);
    
    $matches = 0;
    foreach ($query_words as $query_word) {
        foreach ($topic_words as $topic_word) {
            if (strpos($topic_word, $query_word) !== false || strpos($query_word, $topic_word) !== false) {
                $matches++;
                break;
            }
        }
    }
    
    return $matches / count($query_words);
}

function enhanceClusters($clusters) {
    foreach ($clusters as &$cluster) {
        // Add cluster metadata
        $cluster['total_views'] = array_sum(array_column($cluster['results'], 'view_count'));
        $cluster['avg_views'] = $cluster['total_views'] / count($cluster['results']);
        
        // Add top result
        if (!empty($cluster['results'])) {
            usort($cluster['results'], function($a, $b) {
                return ($b['view_count'] ?? 0) - ($a['view_count'] ?? 0);
            });
            $cluster['top_result'] = $cluster['results'][0];
        }
        
        // Add cluster summary
        $cluster['summary'] = generateClusterSummary($cluster);
    }
    
    return $clusters;
}

function generateClusterSummary($cluster) {
    $size = count($cluster['results']);
    $type = $cluster['type'];
    $title = $cluster['title'];
    
    switch ($type) {
        case 'category':
            return "{$size} articles in the {$title} category";
        case 'author':
            return "{$size} articles by {$title}";
        case 'time':
            return "{$size} articles published {$title}";
        case 'topic':
            return "{$size} articles about {$title}";
        case 'activity':
            return "{$size} users who joined {$title}";
        default:
            return "{$size} results in {$title}";
    }
}
?>
<script src="/skins/bismillah/assets/js/search_index.js"></script>
