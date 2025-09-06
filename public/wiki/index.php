<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Wiki';

// Get categories
$stmt = $pdo->query("SELECT * FROM content_categories WHERE is_active = 1 ORDER BY sort_order");
$categories = $stmt->fetchAll();

// Get featured articles
$stmt = $pdo->query("
    SELECT wa.*, u.display_name, u.username, cc.name as category_name 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id 
    WHERE wa.status = 'published' AND wa.is_featured = 1 
    ORDER BY wa.published_at DESC 
    LIMIT 6
");
$featured_articles = $stmt->fetchAll();

// Get recent articles
$stmt = $pdo->query("
    SELECT wa.*, u.display_name, u.username, cc.name as category_name 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id 
    WHERE wa.status = 'published' 
    ORDER BY wa.published_at DESC 
    LIMIT 12
");
$recent_articles = $stmt->fetchAll();

// Get popular articles (most viewed)
$stmt = $pdo->query("
    SELECT wa.*, u.display_name, u.username, cc.name as category_name 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id 
    WHERE wa.status = 'published' 
    ORDER BY wa.view_count DESC 
    LIMIT 5
");
$popular_articles = $stmt->fetchAll();

include 'header.php';
?>

<div class="wiki-homepage">
    <div class="hero-section">
        <div class="card">
            <h1>IslamWiki Knowledge Base</h1>
            <p>Explore comprehensive Islamic knowledge, articles, and resources with our enhanced wiki system.</p>
            
            <div class="search-box">
                <form action="search.php" method="GET">
                    <input type="text" name="q" placeholder="Search articles..." required>
                    <button type="submit" class="btn">Search</button>
                </form>
            </div>
            
            <div class="wiki-features">
                <div class="feature-item">
                    <span class="feature-icon">📝</span>
                    <span>Markdown Editor</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">🔗</span>
                    <span>Wiki Links</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">👁️</span>
                    <span>Live Preview</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">🔍</span>
                    <span>Advanced Search</span>
                </div>
            </div>
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
                </div>
                <h3><a href="article.php?slug=<?php echo $article['slug']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h3>
                <p><?php echo truncate_text($article['excerpt'] ?: strip_tags($article['content']), 120); ?></p>
                <div class="article-footer">
                    <span class="author">By <?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?></span>
                    <span class="views"><?php echo number_format($article['view_count']); ?> views</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <div class="wiki-content">
        <div class="categories-sidebar">
            <div class="card">
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
            </div>
            
            <?php if (!empty($popular_articles)): ?>
            <div class="card">
                <h3>Popular Articles</h3>
                <ul class="popular-list">
                    <?php foreach ($popular_articles as $article): ?>
                    <li>
                        <a href="article.php?slug=<?php echo $article['slug']; ?>">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </a>
                        <span class="views"><?php echo number_format($article['view_count']); ?> views</span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="recent-articles">
            <h2>Recent Articles</h2>
            <div class="articles-list">
                <?php foreach ($recent_articles as $article): ?>
                <div class="card article-item">
                    <h4><a href="article.php?slug=<?php echo $article['slug']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h4>
                    <div class="article-meta">
                        <span class="category"><?php echo htmlspecialchars($article['category_name']); ?></span>
                        <span class="author">By <?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?></span>
                        <span class="date"><?php echo format_date($article['published_at']); ?></span>
                        <span class="views"><?php echo number_format($article['view_count']); ?> views</span>
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

.search-box {
    margin: 2rem 0;
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
}

.wiki-features {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-top: 2rem;
    flex-wrap: wrap;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
    font-size: 0.9rem;
}

.feature-icon {
    font-size: 1.2rem;
}

.featured-articles {
    margin-bottom: 3rem;
}

.featured-articles h2 {
    text-align: center;
    margin-bottom: 2rem;
    color: #2c3e50;
}

.articles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.article-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.article-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.article-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
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

.article-footer {
    display: flex;
    justify-content: space-between;
    margin-top: 1rem;
    font-size: 0.9rem;
    color: #666;
}

.wiki-content {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
}

.categories-sidebar .card {
    height: fit-content;
    margin-bottom: 1.5rem;
}

.categories-sidebar h3 {
    margin-bottom: 1rem;
    color: #2c3e50;
}

.category-list,
.popular-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.category-list li,
.popular-list li {
    margin-bottom: 0.5rem;
}

.category-list a,
.popular-list a {
    color: #666;
    text-decoration: none;
    padding: 0.5rem;
    display: block;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.category-list a:hover,
.popular-list a:hover {
    background: #f8f9fa;
    color: #3498db;
}

.popular-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.popular-list .views {
    font-size: 0.8rem;
    color: #999;
}

.recent-articles h2 {
    margin-bottom: 2rem;
    color: #2c3e50;
}

.articles-list {
    display: grid;
    gap: 1rem;
    margin-bottom: 2rem;
}

.article-item h4 {
    margin-bottom: 0.5rem;
}

.article-item h4 a {
    color: #2c3e50;
    text-decoration: none;
}

.article-item h4 a:hover {
    color: #3498db;
}

.wiki-actions {
    text-align: center;
    padding: 2rem 0;
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
    
    .hero-section h1 {
        font-size: 2rem;
    }
    
    .articles-grid {
        grid-template-columns: 1fr;
    }
    
    .search-box form {
        flex-direction: column;
    }
    
    .wiki-features {
        gap: 1rem;
    }
    
    .feature-item {
        font-size: 0.8rem;
    }
}
</style>

<?php include 'footer.php'; ?>
