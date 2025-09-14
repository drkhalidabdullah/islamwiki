<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/wiki_functions.php';

// Check maintenance mode
check_maintenance_mode();

$page_title = 'Category';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    redirect('/');
}

// Get category
$stmt = $pdo->prepare("SELECT * FROM content_categories WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$category = $stmt->fetch();

if (!$category) {
    show_message('Category not found.', 'error');
    redirect('/');
}

// Get articles in this category
$stmt = $pdo->prepare("
    SELECT wa.*, u.display_name, u.username 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    WHERE wa.category_id = ? AND wa.status = 'published' 
    ORDER BY wa.published_at DESC
");
$stmt->execute([$category['id']]);
$articles = $stmt->fetchAll();

$page_title = $category['name'];

include "../../includes/header.php";;

?>
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_module_category.css">
<?php
?>

<div class="category-container">
    <div class="category-header">
        <div class="card">
            <h1><?php echo htmlspecialchars($category['name']); ?></h1>
            <?php if ($category['description']): ?>
                <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="category-content">
        <?php if (!empty($articles)): ?>
            <div class="articles-grid">
                <?php foreach ($articles as $article): ?>
                <div class="card">
                    <h3><a href="/wiki/<?php echo $article['slug']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h3>
                    <div class="article-meta">
                        <span class="author">By <?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?></span>
                        <span class="date"><?php echo format_date($article['published_at']); ?></span>
                        <span class="views"><?php echo number_format($article['view_count']); ?> views</span>
                    </div>
                    <p><?php echo truncate_text($article['excerpt'] ?: strip_tags($article['content']), 120); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card">
                <p>No articles found in this category yet.</p>
                <?php if (is_logged_in() && is_editor()): ?>
                    <a href="../create_article.php" class="btn btn-success">Create First Article</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>


<?php include "../../includes/footer.php";; ?>
