<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../includes/functions.php";

// Check maintenance mode
check_maintenance_mode();

// Enforce rate limiting for search queries
enforce_rate_limit('search_queries');

// Include analytics
require_once __DIR__ . '/../includes/analytics.php';

$page_title = "Comprehensive Search";
$is_search_page = true; // Hide header search

// Get search parameters
$query = $_GET['q'] ?? '';
$content_type = $_GET['type'] ?? 'all';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'relevance';
$page = max(1, (int)($_GET['page'] ?? 1));

// Get categories for filter
$categories = [];
try {
    $stmt = $pdo->prepare("SELECT id, name FROM content_categories ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
}

// Get search suggestions for empty query
$suggestions = [];
$trending = [];
if (empty($query)) {
    try {
        $stmt = $pdo->prepare("SELECT suggestion, suggestion_type, content_type, search_count 
                               FROM search_suggestions 
                               WHERE is_active = 1 
                               ORDER BY search_count DESC 
                               LIMIT 10");
        $stmt->execute();
        $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("SELECT suggestion, search_count, content_type
                               FROM search_suggestions 
                               WHERE is_active = 1 AND suggestion_type = 'trending'
                               ORDER BY search_count DESC 
                               LIMIT 5");
        $stmt->execute();
        $trending = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching suggestions: " . $e->getMessage());
    }
}

include __DIR__ . "/../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/search_index.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/search_index.css">
<?php
?>

<div class="search-page-container">
    <div class="search-layout">
        <!-- Left Sidebar Filters -->
        <div class="search-sidebar">
            <div class="sidebar-section">
                <h3>Search Filters</h3>
                
                <!-- Content Type Filter -->
                <div class="filter-group">
                    <h4>Content Type</h4>
                    <div class="filter-options">
                        <a href="?q=<?php echo urlencode($query); ?>&type=all" class="filter-link <?php echo ($content_type === 'all') ? 'active' : ''; ?>">
                            <i class="fas fa-search"></i>
                            All Content
                        </a>
                        <a href="?q=<?php echo urlencode($query); ?>&type=articles" class="filter-link <?php echo ($content_type === 'articles') ? 'active' : ''; ?>">
                            <i class="fas fa-book"></i>
                            Wiki Pages
                        </a>
                        <a href="?q=<?php echo urlencode($query); ?>&type=posts" class="filter-link <?php echo ($content_type === 'posts') ? 'active' : ''; ?>">
                            <i class="fas fa-comment"></i>
                            Posts
                        </a>
                        <a href="?q=<?php echo urlencode($query); ?>&type=people" class="filter-link <?php echo ($content_type === 'people') ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i>
                            People
                        </a>
                        <a href="?q=<?php echo urlencode($query); ?>&type=groups" class="filter-link <?php echo ($content_type === 'groups') ? 'active' : ''; ?>">
                            <i class="fas fa-layer-group"></i>
                            Groups
                        </a>
                        <a href="?q=<?php echo urlencode($query); ?>&type=events" class="filter-link <?php echo ($content_type === 'events') ? 'active' : ''; ?>">
                            <i class="fas fa-calendar"></i>
                            Events
                        </a>
                        <a href="?q=<?php echo urlencode($query); ?>&type=ummah" class="filter-link <?php echo ($content_type === 'ummah') ? 'active' : ''; ?>">
                            <i class="fas fa-mosque"></i>
                            Ummah
                        </a>
                    </div>
                </div>

                <!-- Category Filter (for articles) -->
                <?php if ($content_type === 'all' || $content_type === 'articles'): ?>
                <div class="filter-group">
                    <h4>Category</h4>
                    <select name="category" class="filter-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($category == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Sort Options -->
                <div class="filter-group">
                    <h4>Sort By</h4>
                    <select name="sort" class="filter-select">
                        <option value="relevance" <?php echo ($sort === 'relevance') ? 'selected' : ''; ?>>Relevance</option>
                        <option value="date" <?php echo ($sort === 'date') ? 'selected' : ''; ?>>Date</option>
                        <option value="title" <?php echo ($sort === 'title') ? 'selected' : ''; ?>>Title</option>
                        <option value="popularity" <?php echo ($sort === 'popularity') ? 'selected' : ''; ?>>Popularity</option>
                    </select>
                </div>

                <!-- Clear Filters -->
                <div class="filter-actions">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                </div>
            </div>

            <!-- Search Suggestions -->
            <?php if (!empty($suggestions)): ?>
            <div class="sidebar-section">
                <h3>Popular Searches</h3>
                <div class="suggestions-list">
                    <?php foreach ($suggestions as $suggestion): ?>
                    <a href="?q=<?php echo urlencode($suggestion['suggestion']); ?>&type=<?php echo $suggestion['content_type'] ?? 'all'; ?>" 
                       class="suggestion-item">
                        <span class="suggestion-text"><?php echo htmlspecialchars($suggestion['suggestion']); ?></span>
                        <span class="suggestion-count"><?php echo $suggestion['search_count']; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Trending Topics -->
            <?php if (!empty($trending)): ?>
            <div class="sidebar-section">
                <h3>Trending</h3>
                <div class="trending-list">
                    <?php foreach ($trending as $topic): ?>
                    <a href="?q=<?php echo urlencode($topic['suggestion']); ?>&type=<?php echo $topic['content_type'] ?? 'all'; ?>" 
                       class="trending-item">
                        <span class="trending-text"><?php echo htmlspecialchars($topic['suggestion']); ?></span>
                        <span class="trending-badge">Trending</span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Main Search Content -->
        <div class="search-main">
            <!-- Search Header -->
            <div class="search-header">
                <h1>Comprehensive Search</h1>
                <p>Discover knowledge across articles, users, groups, events, and community content</p>
            </div>

            <!-- Search Form -->
            <div class="search-form-container">
                <form method="GET" class="search-form" id="searchForm">
                    <input type="hidden" name="type" value="<?php echo htmlspecialchars($content_type); ?>">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                    
                    <div class="search-input-group">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" 
                                   placeholder="Search everything..." class="search-input" id="searchInput" required>
                            <button type="button" class="clear-search" id="clearSearch" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary search-btn">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                    <div class="search-options">
                        <a href="/search/advanced" class="btn btn-secondary">
                            <i class="fas fa-cog"></i> Advanced Search
                        </a>
                    </div>
                </form>
            </div>

            <!-- Search Results -->
            <div class="search-results-container" id="searchResults">
                <?php if (!empty($query)): ?>
                    <div class="search-loading" id="searchLoading">
                        <div class="spinner"></div>
                        <span>Searching...</span>
                    </div>
                    <div class="search-results" id="searchResultsContent" style="display: none;">
                        <!-- Results will be loaded via AJAX -->
                    </div>
                <?php else: ?>
                    <div class="search-welcome">
                        <div class="welcome-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Start Your Search</h3>
                        <p>Enter a search term above to discover content across our platform</p>
                        
                        <?php if (!empty($suggestions)): ?>
                        <div class="quick-suggestions">
                            <h4>Try searching for:</h4>
                            <div class="suggestion-tags">
                                <?php foreach (array_slice($suggestions, 0, 6) as $suggestion): ?>
                                <a href="?q=<?php echo urlencode($suggestion['suggestion']); ?>&type=<?php echo $suggestion['content_type'] ?? 'all'; ?>" 
                                   class="suggestion-tag">
                                    <?php echo htmlspecialchars($suggestion['suggestion']); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Search JavaScript -->


<?php include __DIR__ . "/../includes/footer.php"; ?>
