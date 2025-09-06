<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Search';

$query = sanitize_input($_GET['q'] ?? '');
$category = (int)($_GET['category'] ?? 0);
$sort = sanitize_input($_GET['sort'] ?? 'relevance');

$results = [];
$total_results = 0;
$suggestions = [];

if (!empty($query)) {
    // Build search query
    $where_conditions = ["wa.status = 'published'"];
    $params = [];
    
    if ($category > 0) {
        $where_conditions[] = "wa.category_id = ?";
        $params[] = $category;
    }
    
    // Full-text search
    $search_terms = explode(' ', $query);
    $search_conditions = [];
    foreach ($search_terms as $term) {
        $search_conditions[] = "(wa.title LIKE ? OR wa.content LIKE ? OR wa.excerpt LIKE ?)";
        $term_param = '%' . $term . '%';
        $params[] = $term_param;
        $params[] = $term_param;
        $params[] = $term_param;
    }
    
    if (!empty($search_conditions)) {
        $where_conditions[] = '(' . implode(' AND ', $search_conditions) . ')';
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Determine sort order
    $order_by = 'wa.published_at DESC';
    switch ($sort) {
        case 'title':
            $order_by = 'wa.title ASC';
            break;
        case 'date':
            $order_by = 'wa.published_at DESC';
            break;
        case 'views':
            $order_by = 'wa.view_count DESC';
            break;
        case 'relevance':
        default:
            $order_by = 'wa.view_count DESC, wa.published_at DESC';
            break;
    }
    
    // Get results
    $sql = "
        SELECT wa.*, u.username, u.display_name, cc.name as category_name,
               MATCH(wa.title, wa.content, wa.excerpt) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance
        FROM wiki_articles wa 
        JOIN users u ON wa.author_id = u.id 
        LEFT JOIN content_categories cc ON wa.category_id = cc.id 
        WHERE $where_clause
        ORDER BY $order_by
        LIMIT 50
    ";
    
    $search_params = array_merge([$query], $params);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($search_params);
    $results = $stmt->fetchAll();
    
    // Get total count
    $count_sql = "
        SELECT COUNT(*) 
        FROM wiki_articles wa 
        WHERE $where_clause
    ";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_results = $stmt->fetchColumn();
    
    // Get search suggestions if no results
    if (empty($results)) {
        $stmt = $pdo->prepare("
            SELECT title, slug 
            FROM wiki_articles 
            WHERE status = 'published' 
            ORDER BY view_count DESC 
            LIMIT 10
        ");
        $stmt->execute();
        $suggestions = $stmt->fetchAll();
    }
}

// Get categories for filter
$stmt = $pdo->query("SELECT * FROM content_categories WHERE is_active = 1 ORDER BY name");
$categories = $stmt->fetchAll();

include 'header.php';
?>

<div class="search-page">
    <div class="search-header">
        <h1>Search Articles</h1>
        
        <form method="GET" class="search-form">
            <div class="search-input-group">
                <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" 
                       placeholder="Search articles..." required>
                <button type="submit" class="btn">Search</button>
            </div>
            
            <div class="search-filters">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                <?php echo ($category == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="sort">
                    <option value="relevance" <?php echo ($sort === 'relevance') ? 'selected' : ''; ?>>Relevance</option>
                    <option value="title" <?php echo ($sort === 'title') ? 'selected' : ''; ?>>Title</option>
                    <option value="date" <?php echo ($sort === 'date') ? 'selected' : ''; ?>>Date</option>
                    <option value="views" <?php echo ($sort === 'views') ? 'selected' : ''; ?>>Views</option>
                </select>
            </div>
        </form>
    </div>
    
    <?php if (!empty($query)): ?>
        <div class="search-results">
            <div class="results-header">
                <h2>
                    <?php if ($total_results > 0): ?>
                        <?php echo number_format($total_results); ?> result<?php echo $total_results !== 1 ? 's' : ''; ?> for "<?php echo htmlspecialchars($query); ?>"
                    <?php else: ?>
                        No results found for "<?php echo htmlspecialchars($query); ?>"
                    <?php endif; ?>
                </h2>
            </div>
            
            <?php if (!empty($results)): ?>
                <div class="results-list">
                    <?php foreach ($results as $article): ?>
                    <div class="card result-item">
                        <div class="result-meta">
                            <span class="category"><?php echo htmlspecialchars($article['category_name']); ?></span>
                            <span class="date"><?php echo format_date($article['published_at']); ?></span>
                            <span class="views"><?php echo number_format($article['view_count']); ?> views</span>
                        </div>
                        
                        <h3><a href="article.php?slug=<?php echo $article['slug']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h3>
                        
                        <p class="result-excerpt">
                            <?php 
                            $excerpt = $article['excerpt'] ?: strip_tags($article['content']);
                            $excerpt = truncate_text($excerpt, 200);
                            
                            // Highlight search terms
                            foreach (explode(' ', $query) as $term) {
                                $excerpt = preg_replace('/(' . preg_quote($term, '/') . ')/i', '<mark>$1</mark>', $excerpt);
                            }
                            
                            echo $excerpt;
                            ?>
                        </p>
                        
                        <div class="result-footer">
                            <span class="author">By <?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?></span>
                            <a href="article.php?slug=<?php echo $article['slug']; ?>" class="read-more">Read more →</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif (!empty($suggestions)): ?>
                <div class="no-results">
                    <h3>No articles found. Try these popular articles:</h3>
                    <ul class="suggestions">
                        <?php foreach ($suggestions as $suggestion): ?>
                        <li><a href="article.php?slug=<?php echo $suggestion['slug']; ?>"><?php echo htmlspecialchars($suggestion['title']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="search-intro">
            <div class="card">
                <h2>Search Tips</h2>
                <ul>
                    <li>Use specific keywords for better results</li>
                    <li>Try different spellings or synonyms</li>
                    <li>Use the category filter to narrow results</li>
                    <li>Check out our <a href="index.php">featured articles</a> for popular content</li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.search-page {
    max-width: 1000px;
    margin: 0 auto;
}

.search-header {
    text-align: center;
    margin-bottom: 3rem;
}

.search-header h1 {
    color: #2c3e50;
    margin-bottom: 2rem;
}

.search-form {
    max-width: 600px;
    margin: 0 auto;
}

.search-input-group {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.search-input-group input {
    flex: 1;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.search-filters {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.search-filters select {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
}

.search-results {
    margin-top: 2rem;
}

.results-header h2 {
    color: #2c3e50;
    margin-bottom: 2rem;
}

.results-list {
    display: grid;
    gap: 1.5rem;
}

.result-item {
    transition: transform 0.2s, box-shadow 0.2s;
}

.result-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.result-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: #666;
}

.result-meta .category {
    background: #3498db;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
}

.result-item h3 {
    margin-bottom: 1rem;
}

.result-item h3 a {
    color: #2c3e50;
    text-decoration: none;
}

.result-item h3 a:hover {
    color: #3498db;
}

.result-excerpt {
    margin-bottom: 1rem;
    line-height: 1.6;
}

.result-excerpt mark {
    background: #ffeb3b;
    padding: 0.125rem 0.25rem;
    border-radius: 2px;
}

.result-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
    color: #666;
}

.read-more {
    color: #3498db;
    text-decoration: none;
    font-weight: 500;
}

.read-more:hover {
    text-decoration: underline;
}

.no-results {
    text-align: center;
    padding: 3rem 0;
}

.suggestions {
    list-style: none;
    padding: 0;
    margin: 2rem 0;
    display: grid;
    gap: 0.5rem;
}

.suggestions li {
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 4px;
    border-left: 4px solid #3498db;
}

.suggestions a {
    color: #2c3e50;
    text-decoration: none;
}

.suggestions a:hover {
    color: #3498db;
}

.search-intro {
    margin-top: 3rem;
}

.search-intro .card {
    max-width: 600px;
    margin: 0 auto;
    text-align: left;
}

.search-intro h2 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.search-intro ul {
    margin: 0;
    padding-left: 1.5rem;
}

.search-intro li {
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .search-input-group {
        flex-direction: column;
    }
    
    .search-filters {
        flex-direction: column;
        align-items: center;
    }
    
    .result-meta {
        flex-wrap: wrap;
    }
    
    .result-footer {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }
}
</style>

<?php include 'footer.php'; ?>
