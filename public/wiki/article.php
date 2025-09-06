<?php
// Fix path issues for web server access
$config_path = file_exists('../config/config.php') ? '../config/config.php' : 'config/config.php';
$functions_path = file_exists('../includes/functions.php') ? '../includes/functions.php' : 'includes/functions.php';

require_once $config_path;
require_once $functions_path;

$page_title = 'Article';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    redirect('index.php');
}

// Get article
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name, cc.name as category_name 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id 
    WHERE wa.slug = ? AND wa.status = 'published'
");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    show_message('Article not found.', 'error');
    redirect('index.php');
}

// Increment view count
$stmt = $pdo->prepare("UPDATE wiki_articles SET view_count = view_count + 1 WHERE id = ?");
$stmt->execute([$article['id']]);

$page_title = $article['title'];

include 'header.php';
?>

<div class="article-container">
    <article class="card">
        <header class="article-header">
            <h1><?php echo htmlspecialchars($article['title']); ?></h1>
            
            <div class="article-meta">
                <p>
                    By <strong><?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?></strong>
                    | Published on <?php echo format_date($article['published_at']); ?>
                    | <?php echo number_format($article['view_count']); ?> views
                </p>
                
                <?php if ($article['category_name']): ?>
                <div class="article-categories">
                    <span class="category-tag"><?php echo htmlspecialchars($article['category_name']); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </header>
        
        <div class="article-content">
            <?php echo nl2br(htmlspecialchars($article['content'])); ?>
        </div>
        
        <?php if (is_logged_in() && (is_admin() || $article['author_id'] == $_SESSION['user_id'])): ?>
        <div class="article-actions">
            <a href="../edit_article.php?id=<?php echo $article['id']; ?>" class="btn">Edit Article</a>
            <a href="../delete_article.php?id=<?php echo $article['id']; ?>" 
               class="btn btn-danger" 
               onclick="return confirm('Are you sure you want to delete this article?')">Delete Article</a>
        </div>
        <?php endif; ?>
    </article>
</div>

<style>
.article-container {
    max-width: 800px;
    margin: 0 auto;
}

.article-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #ecf0f1;
}

.article-header h1 {
    color: #2c3e50;
    margin-bottom: 1rem;
    font-size: 2.5rem;
    line-height: 1.2;
}

.article-meta {
    color: #666;
    font-size: 0.9rem;
}

.article-meta p {
    margin-bottom: 1rem;
}

.article-categories {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}

.category-tag {
    background-color: #3498db;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
}

.article-content {
    line-height: 1.8;
    font-size: 1.1rem;
    margin-bottom: 2rem;
}

.article-actions {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #ecf0f1;
    text-align: center;
}

.article-actions .btn {
    margin: 0 0.5rem;
}
</style>

<?php include 'footer.php'; ?>
