<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Wiki';

// Get categories
$stmt = $pdo->query("SELECT * FROM content_categories WHERE is_active = 1 ORDER BY sort_order");
$categories = $stmt->fetchAll();

// Get featured articles with proper permissions
$user_id = $_SESSION['user_id'] ?? null;
$is_logged_in = is_logged_in();
$is_editor = is_editor();

$where_conditions = ["wa.is_featured = 1"];
$params = [];

if (!$is_logged_in) {
    $where_conditions[] = "wa.status = 'published'";
} elseif (!$is_editor) {
    $where_conditions[] = "(wa.status = 'published' OR (wa.status = 'draft' AND wa.author_id = ?))";
    $params[] = $user_id;
} else {
    $where_conditions[] = "(wa.status = 'published' OR wa.status = 'draft')";
}

$stmt = $pdo->prepare("
    SELECT wa.*, u.display_name, u.username, cc.name as category_name 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id 
    WHERE " . implode(' AND ', $where_conditions) . "
    ORDER BY wa.published_at DESC 
    LIMIT 6
");
$stmt->execute($params);
$featured_articles = $stmt->fetchAll();

// Get recent articles with proper permissions
$where_conditions = [];
$params = [];

if (!$is_logged_in) {
    $where_conditions[] = "wa.status = 'published'";
} elseif (!$is_editor) {
    $where_conditions[] = "(wa.status = 'published' OR (wa.status = 'draft' AND wa.author_id = ?))";
    $params[] = $user_id;
} else {
    $where_conditions[] = "(wa.status = 'published' OR wa.status = 'draft')";
}

$stmt = $pdo->prepare("
    SELECT wa.*, u.display_name, u.username, cc.name as category_name 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id
    WHERE " . implode(' AND ', $where_conditions) . "
    ORDER BY wa.published_at DESC 
    LIMIT 12
");
$stmt->execute($params);
$recent_articles = $stmt->fetchAll();

// Get popular articles (most viewed) with proper permissions
$where_conditions = [];
$params = [];

if (!$is_logged_in) {
    $where_conditions[] = "wa.status = 'published'";
} elseif (!$is_editor) {
    $where_conditions[] = "(wa.status = 'published' OR (wa.status = 'draft' AND wa.author_id = ?))";
    $params[] = $user_id;
} else {
    $where_conditions[] = "(wa.status = 'published' OR wa.status = 'draft')";
}

$stmt = $pdo->prepare("
    SELECT wa.*, u.display_name, u.username, cc.name as category_name 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id
    WHERE " . implode(' AND ', $where_conditions) . "
    ORDER BY wa.view_count DESC 
    LIMIT 5
");
$stmt->execute($params);
$popular_articles = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="wiki-homepage">
    <div class="hero-section">
        <div class="card">
            <h1>Islamic Knowledge Wiki</h1>
            <p>Explore comprehensive articles about Islam, Islamic history, and Islamic teachings.</p>
            
        </div>
    </div>
    
    <?php if (!empty($featured_articles)): ?>
    <section class="featured-articles">
        <h2>Featured Articles</h2>
        <div class="articles-grid">
            <?php foreach ($featured_articles as $article): ?>
            <div class="card article-card">
                <div class="article-meta">
                    <span class="category"><?php echo htmlspecialchars($article['category_name']); ?></span>
                    <span class="date"><?php echo format_date($article['published_at']); ?></span>
                    <?php if ($article['status'] === 'draft'): ?>
                        <span class="draft-indicator">📝 Draft</span>
                    <?php endif; ?>
                </div>
                <h3><a href="<?php echo ucfirst($article['slug']); ?>"><?php echo htmlspecialchars($article['title']); ?></a></h3>
                <p><?php echo truncate_text($article['excerpt'] ?: strip_tags($article['content']), 120); ?></p>
                <div class="article-footer">
                    <span class="author">By <?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?></span>
                    <?php if ($article['status'] === 'published'): ?>
                        <span class="views"><?php echo number_format($article['view_count']); ?> views</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <div class="wiki-content">
        <div class="categories-sidebar">
            <h3>Categories</h3>
            <ul class="category-list">
                <?php foreach ($categories as $category): ?>
                <li>
                    <a href="category.php?slug=<?php echo $category['slug']; ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            
            <div class="popular-articles">
                <h3>Popular Articles</h3>
                <ul>
                    <?php foreach ($popular_articles as $article): ?>
                    <li>
                        <a href="<?php echo ucfirst($article['slug']); ?>">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </a>
                        <?php if ($article['status'] === 'published'): ?>
                            <span class="views">(<?php echo number_format($article['view_count']); ?> views)</span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <div class="recent-articles">
            <h2>Recent Articles</h2>
            <div class="articles-list">
                <?php foreach ($recent_articles as $article): ?>
                <div class="card article-item">
                    <h4><a href="<?php echo ucfirst($article['slug']); ?>"><?php echo htmlspecialchars($article['title']); ?></a></h4>
                    <div class="article-meta">
                        <span class="category"><?php echo htmlspecialchars($article['category_name']); ?></span>
                        <span class="author">By <?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?></span>
                        <span class="date"><?php echo format_date($article['published_at']); ?></span>
                        <?php if ($article['status'] === 'published'): ?>
                            <span class="views"><?php echo number_format($article['view_count']); ?> views</span>
                        <?php else: ?>
                            <span class="draft-indicator">📝 Draft</span>
                        <?php endif; ?>
                    </div>
                    <p><?php echo truncate_text($article['excerpt'] ?: strip_tags($article['content']), 100); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="wiki-actions">
                <?php if (is_logged_in() && is_editor()): ?>
                    <a href="../create_article.php" class="btn btn-success">Create New Article</a>
                <?php endif; ?>
                <a href="search.php" class="btn">Browse All Articles</a>
            </div>
        </div>
    </div>
</div>

<style>
.wiki-homepage {
    max-width: 1200px;
    margin: 0 auto;
}

.hero-section {
    text-align: center;
    margin-bottom: 3rem;
}

.hero-section .card {
    max-width: 800px;
    margin: 0 auto;
}

.hero-section h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: #2c3e50;
}

.hero-section p {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    color: #666;
}


.search-box form {
    display: flex;
    gap: 0.5rem;
    max-width: 500px;
    margin: 0 auto;
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
    background: #3498db;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
}

.search-box button:hover {
    background: #2980b9;
}

.wiki-content {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.categories-sidebar {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    height: fit-content;
}

.categories-sidebar h3 {
    margin-top: 0;
    color: #2c3e50;
}

.category-list {
    list-style: none;
    padding: 0;
}

.category-list li {
    margin: 0.5rem 0;
}

.category-list a {
    color: #3498db;
    text-decoration: none;
}

.category-list a:hover {
    text-decoration: underline;
}

.popular-articles {
    margin-top: 2rem;
}

.popular-articles ul {
    list-style: none;
    padding: 0;
}

.popular-articles li {
    margin: 0.5rem 0;
    font-size: 0.9rem;
}

.popular-articles a {
    color: #2c3e50;
    text-decoration: none;
}

.popular-articles a:hover {
    color: #3498db;
}

.views {
    color: #666;
    font-size: 0.8rem;
}

.draft-indicator {
    color: #f39c12;
    font-size: 0.8rem;
    font-weight: 500;
}

.articles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.article-card {
    transition: transform 0.2s ease;
}

.article-card:hover {
    transform: translateY(-2px);
}

.article-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: #666;
}

.article-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
    font-size: 0.9rem;
    color: #666;
}

.articles-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.article-item {
    padding: 1.5rem;
}

.article-item h4 {
    margin-top: 0;
}

.article-item .article-meta {
    margin: 0.5rem 0;
}

.wiki-actions {
    margin-top: 2rem;
    text-align: center;
}

.wiki-actions .btn {
    margin: 0 0.5rem;
}

@media (max-width: 768px) {
    .wiki-content {
        grid-template-columns: 1fr;
    }
    
    .categories-sidebar {
        order: 2;
    }
    
    .articles-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
