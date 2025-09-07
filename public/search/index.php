<?php
require_once "../config/config.php";
require_once "../includes/functions.php";

$page_title = "Search";

// Get search parameters
$query = $_GET['q'] ?? '';
$content_type = $_GET['type'] ?? 'all';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'relevance';

// Initialize results
$results = [
    'articles' => [],
    'users' => [],
    'messages' => []
];

// Get categories for filter
$categories = [];
try {
    $stmt = $pdo->prepare("SELECT id, name FROM content_categories ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
}

// Perform search if query is provided
if (!empty($query)) {
    // Search articles
    if ($content_type === 'all' || $content_type === 'articles') {
        $sql = "SELECT a.*, c.name as category_name, u.username as author_username, u.display_name as author_name
                FROM wiki_articles a
                LEFT JOIN content_categories c ON a.category_id = c.id
                LEFT JOIN users u ON a.author_id = u.id
                WHERE a.status = 'published'";
        
        $params = [];
        
        if (!empty($query)) {
            $sql .= " AND (a.title LIKE ? OR a.content LIKE ? OR a.excerpt LIKE ?)";
            $params[] = "%$query%"; $params[] = "%$query%"; $params[] = "%$query%";
        }
        
        if (!empty($category)) {
            $sql .= " AND a.category_id = :category";
            $params[':category'] = $category;
        }
        
        // Add sorting
        switch ($sort) {
            case 'title':
                $sql .= " ORDER BY a.title ASC";
                break;
            case 'date':
                $sql .= " ORDER BY a.published_at DESC";
                break;
            case 'relevance':
            default:
                $sql .= " ORDER BY a.view_count DESC, a.published_at DESC";
                break;
        }
        
        $sql .= " LIMIT 50";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $results['articles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching articles: " . $e->getMessage());
        }
    }
}

// Calculate total results
$total_results = count($results['articles']) + count($results['users']) + count($results['messages']);

include "../includes/header.php";
?>
<?php
?>

<div class="search-page">
    <div class="search-header">
        <h1>Comprehensive Search</h1>
        <p>Discover knowledge across articles, users, and content with our advanced search system</p>
    </div>
    
    <div class="search-form-container">
        <form method="GET" class="search-form">
            <div class="search-input-group">
                <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" 
                       placeholder="Search everything..." required>
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
            
            <div class="search-filters">
                <div class="filter-group">
                    <label>Content Type</label>
                    <select name="type">
                        <option value="all" <?php echo ($content_type === 'all') ? 'selected' : ''; ?>>All Content</option>
                        <option value="articles" <?php echo ($content_type === 'articles') ? 'selected' : ''; ?>>Articles</option>
                        <option value="users" <?php echo ($content_type === 'users') ? 'selected' : ''; ?>>Users</option>
                        <?php if ($is_logged_in): ?>
                        <option value="messages" <?php echo ($content_type === 'messages') ? 'selected' : ''; ?>>My Messages</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($category == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Sort By</label>
                    <select name="sort">
                        <option value="relevance" <?php echo ($sort === 'relevance') ? 'selected' : ''; ?>>Relevance</option>
                        <option value="title" <?php echo ($sort === 'title') ? 'selected' : ''; ?>>Title</option>
                        <option value="date" <?php echo ($sort === 'date') ? 'selected' : ''; ?>>Date</option>
                    </select>
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="/search" class="btn btn-secondary">Clear All</a>
            </div>
        </form>
    </div>
    
    <?php if (!empty($query)): ?>
    <div class="search-results-container">
        <div class="search-results-header">
            <h3>Search Results</h3>
            <div class="results-count"><?php echo $total_results; ?> results</div>
        </div>
        
        <?php if ($total_results > 0): ?>
            <h4><?php echo $total_results; ?> results for "<?php echo htmlspecialchars($query); ?>"</h4>
            
            <?php if (!empty($results['articles'])): ?>
            <div class="results-section">
                <h5>Articles (<?php echo count($results['articles']); ?>)</h5>
                <div class="results-list">
                    <?php foreach ($results['articles'] as $article): ?>
                    <div class="result-item">
                        <div class="result-item-header">
                            <div class="result-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="result-content">
                                <h4 class="result-title">
                                    <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>">
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    </a>
                                </h4>
                                <div class="result-excerpt">
                                    <?php 
                                    $excerpt = $article['excerpt'] ?: strip_tags($article['content']);
                                    echo htmlspecialchars(substr($excerpt, 0, 200)) . (strlen($excerpt) > 200 ? '...' : '');
                                    ?>
                                </div>
                                <div class="result-meta">
                                    <div class="result-category"><?php echo htmlspecialchars($article['category_name'] ?: 'Uncategorized'); ?></div>
                                    <div class="result-meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo date('M j, Y', strtotime($article['published_at'])); ?></span>
                                    </div>
                                    <div class="result-meta-item">
                                        <i class="fas fa-eye"></i>
                                        <span><?php echo number_format($article['view_count']); ?> views</span>
                                    </div>
                                </div>
                                <div class="result-footer">
                                    <div class="result-author">
                                        <div class="result-author-avatar">
                                            <?php echo strtoupper(substr($article['author_name'] ?: $article['author_username'], 0, 1)); ?>
                                        </div>
                                        <span>By <?php echo htmlspecialchars($article['author_name'] ?: $article['author_username']); ?></span>
                                    </div>
                                    <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>" class="result-link">
                                        Read more <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="no-results">
                <div class="no-results-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h4>No results found</h4>
                <p>No results found for "<?php echo htmlspecialchars($query); ?>"</p>
                <p>Try adjusting your search terms or filters.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>
