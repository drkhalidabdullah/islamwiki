<?php
// Fix path issues for web server access
$config_path = file_exists('../config/config.php') ? '../config/config.php' : 'config/config.php';
$functions_path = file_exists('../includes/functions.php') ? '../includes/functions.php' : 'includes/functions.php';

require_once $config_path;
require_once $functions_path;

$page_title = 'Search';

$query = sanitize_input($_GET['q'] ?? '');
$articles = [];

if (!empty($query)) {
    $stmt = $pdo->prepare("
        SELECT wa.*, u.display_name, u.username, cc.name as category_name 
        FROM wiki_articles wa 
        JOIN users u ON wa.author_id = u.id 
        LEFT JOIN content_categories cc ON wa.category_id = cc.id 
        WHERE wa.status = 'published' 
        AND (wa.title LIKE ? OR wa.content LIKE ? OR wa.excerpt LIKE ?)
        ORDER BY wa.published_at DESC
    ");
    $search_term = "%$query%";
    $stmt->execute([$search_term, $search_term, $search_term]);
    $articles = $stmt->fetchAll();
}

include 'header.php';
?>

<div class="search-container">
    <div class="search-header">
        <h1>Search Articles</h1>
        
        <div class="search-box">
            <form method="GET">
                <input type="text" name="q" placeholder="Search articles..." value="<?php echo htmlspecialchars($query); ?>" required>
                <button type="submit" class="btn">Search</button>
            </form>
        </div>
    </div>
    
    <?php if (!empty($query)): ?>
    <div class="search-results">
        <h2>Search Results for "<?php echo htmlspecialchars($query); ?>"</h2>
        
        <?php if (!empty($articles)): ?>
            <p class="results-count">Found <?php echo count($articles); ?> article(s)</p>
            
            <div class="articles-list">
                <?php foreach ($articles as $article): ?>
                <div class="card">
                    <h3><a href="article.php?slug=<?php echo $article['slug']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h3>
                    <div class="article-meta">
                        <span class="category"><?php echo htmlspecialchars($article['category_name']); ?></span>
                        <span class="author">By <?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?></span>
                        <span class="date"><?php echo format_date($article['published_at']); ?></span>
                        <span class="views"><?php echo number_format($article['view_count']); ?> views</span>
                    </div>
                    <p><?php echo truncate_text($article['excerpt'] ?: strip_tags($article['content']), 150); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card">
                <p>No articles found matching your search.</p>
                <a href="index.php" class="btn">Browse All Articles</a>
            </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="card">
        <p>Enter a search term to find articles.</p>
    </div>
    <?php endif; ?>
</div>

<style>
.search-container {
    max-width: 800px;
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

.search-box {
    max-width: 500px;
    margin: 0 auto;
}

.search-box form {
    display: flex;
    gap: 0.5rem;
}

.search-box input {
    flex: 1;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.search-box button {
    padding: 0.75rem 1.5rem;
}

.search-results h2 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.results-count {
    color: #666;
    margin-bottom: 2rem;
}

.articles-list {
    display: grid;
    gap: 1rem;
}

.article-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    color: #666;
}

.article-meta .category {
    background: #3498db;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
}

@media (max-width: 768px) {
    .search-box form {
        flex-direction: column;
    }
    
    .article-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<?php include 'footer.php'; ?>
