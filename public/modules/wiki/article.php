<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Article';

$article_id = $_GET['id'] ?? 0;

if (!$article_id) {
    redirect('/');
}

// Get article
$stmt = $pdo->prepare("
    SELECT a.*, u.username, u.full_name 
    FROM articles a 
    JOIN users u ON a.author_id = u.id 
    WHERE a.id = ? AND a.status = 'published'
");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    show_message('Article not found.', 'error');
    redirect('/');
}

// Get article categories
$stmt = $pdo->prepare("
    SELECT c.name 
    FROM categories c 
    JOIN article_categories ac ON c.id = ac.category_id 
    WHERE ac.article_id = ?
");
$stmt->execute([$article_id]);
$article_categories = $stmt->fetchAll();

$page_title = $article['title'];

include "../../includes/header.php";;
?>

<div class="article-container">
    <article class="article">
        <header class="article-header">
            <h1><?php echo htmlspecialchars($article['title']); ?></h1>
            
            <div class="article-meta">
                <p>
                    By <strong><?php echo htmlspecialchars($article['full_name'] ?: $article['username']); ?></strong>
                    | Published on <?php echo date('F j, Y', strtotime($article['created_at'])); ?>
                </p>
                
                <?php if (!empty($article_categories)): ?>
                <div class="article-categories">
                    <strong>Categories:</strong>
                    <?php foreach ($article_categories as $category): ?>
                        <span class="category-tag"><?php echo htmlspecialchars($category['name']); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </header>
        
        <div class="article-content">
            <?php echo nl2br(htmlspecialchars($article['content'])); ?>
        </div>
        
        <?php if (is_logged_in() && ($_SESSION['role'] === 'admin' || $article['author_id'] == $_SESSION['user_id'])): ?>
        <div class="article-actions">
            <a href="edit_article.php?id=<?php echo $article['id']; ?>" class="btn">Edit Article</a>
            <a href="delete_article.php?id=<?php echo $article['id']; ?>" 
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

.article {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

<?php include "../../includes/footer.php";; ?>
