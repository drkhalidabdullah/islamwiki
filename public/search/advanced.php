<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";
require_once __DIR__ . "/../includes/wiki_functions.php";

// Require login for all site access
require_login();

$page_title = "Advanced Search";
$is_search_page = true; // Hide header search

// Get search parameters
$query = $_GET['q'] ?? '';
$namespace = $_GET['namespace'] ?? 'all';
$content_type = $_GET['type'] ?? 'all';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'relevance';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$author = $_GET['author'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Get namespaces for filter
$namespaces = get_wiki_namespaces();

// Get categories for filter
$categories = [];
try {
    $stmt = $pdo->prepare("SELECT id, name FROM content_categories ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
}

// Get authors for filter
$authors = [];
try {
    $stmt = $pdo->prepare("
        SELECT DISTINCT u.id, u.username, u.display_name 
        FROM users u 
        JOIN wiki_articles wa ON u.id = wa.author_id 
        ORDER BY u.display_name, u.username
    ");
    $stmt->execute();
    $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching authors: " . $e->getMessage());
}

// Perform search if query is provided
$results = [];
$total_results = 0;
$search_time = 0;

if (!empty($query)) {
    $start_time = microtime(true);
    
    // Build search query
    $where_conditions = [];
    $params = [];
    
    // Basic text search
    $where_conditions[] = "(wa.title LIKE ? OR wa.content LIKE ? OR wa.excerpt LIKE ?)";
    $search_term = "%$query%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    
    // Namespace filter
    if ($namespace !== 'all') {
        $where_conditions[] = "wn.name = ?";
        $params[] = $namespace;
    }
    
    // Content type filter
    if ($content_type !== 'all') {
        if ($content_type === 'articles') {
            $where_conditions[] = "wa.status = 'published'";
        } elseif ($content_type === 'drafts') {
            $where_conditions[] = "wa.status = 'draft'";
        }
    }
    
    // Category filter
    if (!empty($category)) {
        $where_conditions[] = "wa.category_id = ?";
        $params[] = $category;
    }
    
    // Date range filter
    if (!empty($date_from)) {
        $where_conditions[] = "DATE(wa.created_at) >= ?";
        $params[] = $date_from;
    }
    if (!empty($date_to)) {
        $where_conditions[] = "DATE(wa.created_at) <= ?";
        $params[] = $date_to;
    }
    
    // Author filter
    if (!empty($author)) {
        $where_conditions[] = "wa.author_id = ?";
        $params[] = $author;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Build sort clause
    $order_clause = '';
    switch ($sort) {
        case 'relevance':
            $order_clause = "ORDER BY 
                CASE 
                    WHEN wa.title LIKE ? THEN 1
                    WHEN wa.excerpt LIKE ? THEN 2
                    ELSE 3
                END,
                wa.view_count DESC,
                wa.updated_at DESC";
            $params[] = "%$query%";
            $params[] = "%$query%";
            break;
        case 'title':
            $order_clause = "ORDER BY wa.title ASC";
            break;
        case 'date_newest':
            $order_clause = "ORDER BY wa.created_at DESC";
            break;
        case 'date_oldest':
            $order_clause = "ORDER BY wa.created_at ASC";
            break;
        case 'views':
            $order_clause = "ORDER BY wa.view_count DESC";
            break;
        case 'updated':
            $order_clause = "ORDER BY wa.updated_at DESC";
            break;
    }
    
    try {
        // Get total count
        $count_sql = "
            SELECT COUNT(*) 
            FROM wiki_articles wa
            LEFT JOIN wiki_namespaces wn ON wa.namespace_id = wn.id
            WHERE $where_clause
        ";
        $stmt = $pdo->prepare($count_sql);
        $stmt->execute($params);
        $total_results = $stmt->fetchColumn();
        
        // Get results
        $sql = "
            SELECT wa.*, u.username, u.display_name, cc.name as category_name, wn.name as namespace_name
            FROM wiki_articles wa
            LEFT JOIN users u ON wa.author_id = u.id
            LEFT JOIN content_categories cc ON wa.category_id = cc.id
            LEFT JOIN wiki_namespaces wn ON wa.namespace_id = wn.id
            WHERE $where_clause
            $order_clause
            LIMIT $limit OFFSET $offset
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $search_time = round((microtime(true) - $start_time) * 1000, 2);
        
    } catch (PDOException $e) {
        error_log("Search error: " . $e->getMessage());
        $results = [];
    }
}

$total_pages = ceil($total_results / $limit);

include "../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/search_advanced.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/search_advanced.css">
<?php
?>

<div class="advanced-search-page">
    <div class="search-header">
        <h1>Advanced Search</h1>
        <p>Search with advanced filters and options</p>
    </div>

    <!-- Search Form -->
    <div class="search-form-container">
        <form method="GET" class="advanced-search-form">
            <div class="search-main">
                <div class="search-input-group">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" 
                           placeholder="Enter your search query..." class="search-input" required>
                    <button type="submit" class="search-button">
                        <i class="iw iw-search"></i>
                        Search
                    </button>
                </div>
            </div>
            
            <div class="search-filters">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="namespace">Namespace:</label>
                        <select name="namespace" id="namespace">
                            <option value="all" <?php echo $namespace === 'all' ? 'selected' : ''; ?>>All Namespaces</option>
                            <?php foreach ($namespaces as $ns): ?>
                                <option value="<?php echo htmlspecialchars($ns['name']); ?>" 
                                        <?php echo $namespace === $ns['name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ns['display_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="type">Content Type:</label>
                        <select name="type" id="type">
                            <option value="all" <?php echo $content_type === 'all' ? 'selected' : ''; ?>>All Content</option>
                            <option value="articles" <?php echo $content_type === 'articles' ? 'selected' : ''; ?>>Published Articles</option>
                            <option value="drafts" <?php echo $content_type === 'drafts' ? 'selected' : ''; ?>>Draft Articles</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="category">Category:</label>
                        <select name="category" id="category">
                            <option value="" <?php echo empty($category) ? 'selected' : ''; ?>>All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="author">Author:</label>
                        <select name="author" id="author">
                            <option value="" <?php echo empty($author) ? 'selected' : ''; ?>>All Authors</option>
                            <?php foreach ($authors as $auth): ?>
                                <option value="<?php echo $auth['id']; ?>" 
                                        <?php echo $author == $auth['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($auth['display_name'] ?: $auth['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_from">From Date:</label>
                        <input type="date" name="date_from" id="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_to">To Date:</label>
                        <input type="date" name="date_to" id="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                </div>
                
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="sort">Sort By:</label>
                        <select name="sort" id="sort">
                            <option value="relevance" <?php echo $sort === 'relevance' ? 'selected' : ''; ?>>Relevance</option>
                            <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>Title (A-Z)</option>
                            <option value="date_newest" <?php echo $sort === 'date_newest' ? 'selected' : ''; ?>>Date (Newest)</option>
                            <option value="date_oldest" <?php echo $sort === 'date_oldest' ? 'selected' : ''; ?>>Date (Oldest)</option>
                            <option value="views" <?php echo $sort === 'views' ? 'selected' : ''; ?>>Most Viewed</option>
                            <option value="updated" <?php echo $sort === 'updated' ? 'selected' : ''; ?>>Recently Updated</option>
                        </select>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="button" onclick="clearFilters()" class="btn btn-secondary">Clear Filters</button>
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Search Results -->
    <?php if (!empty($query)): ?>
        <div class="search-results">
            <div class="results-header">
                <h2>Search Results</h2>
                <div class="results-info">
                    <span class="results-count">
                        <?php echo number_format($total_results); ?> result<?php echo $total_results !== 1 ? 's' : ''; ?>
                    </span>
                    <span class="search-time">(<?php echo $search_time; ?>ms)</span>
                </div>
            </div>
            
            <?php if (empty($results)): ?>
                <div class="no-results">
                    <p>No results found for your search criteria.</p>
                    <p>Try adjusting your search terms or filters.</p>
                </div>
            <?php else: ?>
                <div class="results-list">
                    <?php foreach ($results as $result): ?>
                        <div class="result-item">
                            <div class="result-header">
                                <h3>
                                    <a href="/wiki/<?php echo $result['slug']; ?>">
                                        <?php echo htmlspecialchars($result['title']); ?>
                                    </a>
                                </h3>
                                <div class="result-meta">
                                    <?php if ($result['namespace_name']): ?>
                                        <span class="namespace"><?php echo htmlspecialchars($result['namespace_name']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($result['category_name']): ?>
                                        <span class="category"><?php echo htmlspecialchars($result['category_name']); ?></span>
                                    <?php endif; ?>
                                    <span class="author">by <?php echo htmlspecialchars($result['display_name'] ?: $result['username']); ?></span>
                                    <span class="date"><?php echo format_date($result['created_at']); ?></span>
                                </div>
                            </div>
                            
                            <div class="result-content">
                                <?php if ($result['excerpt']): ?>
                                    <p><?php echo htmlspecialchars($result['excerpt']); ?></p>
                                <?php else: ?>
                                    <p><?php echo htmlspecialchars(substr(strip_tags($result['content']), 0, 200)); ?>...</p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="result-footer">
                                <div class="result-stats">
                                    <span><i class="iw iw-eye"></i> <?php echo number_format($result['view_count']); ?> views</span>
                                    <span><i class="iw iw-edit"></i> <?php echo format_date($result['updated_at']); ?></span>
                                </div>
                                <div class="result-actions">
                                    <a href="/wiki/<?php echo $result['slug']; ?>" class="btn btn-sm btn-primary">Read</a>
                                    <a href="/wiki/<?php echo $result['slug']; ?>/history" class="btn btn-sm btn-secondary">History</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                               class="btn btn-secondary">Previous</a>
                        <?php endif; ?>
                        
                        <span class="page-info">
                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                        </span>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                               class="btn btn-secondary">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>



<?php include "../includes/footer.php"; ?>
