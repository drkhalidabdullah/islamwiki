<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/markdown/MarkdownParser.php';

$page_title = 'Article';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    redirect('index.php');
}

// Get article
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name, cc.name as category_name, cc.slug as category_slug 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id 
    WHERE (wa.slug = ? OR wa.slug = ?) AND wa.status = 'published'
");
$stmt->execute([$slug, ucfirst($slug)]);
$article = $stmt->fetch();

if (!$article) {
    redirect("not_found.php?slug=" . urlencode($slug) . "&title=" . urlencode(ucfirst(str_replace('-', ' ', $slug))));
}

// Increment view count
$stmt = $pdo->prepare("UPDATE wiki_articles SET view_count = view_count + 1 WHERE id = ?");
$stmt->execute([$article['id']]);

$page_title = $article['title'];

// Parse markdown content
$parser = new MarkdownParser('');
$parsed_content = $parser->parse($article['content']);

include '../../includes/header.php';
?>

<div class="article-container">
    <article class="card">
        <header class="article-header">
            <div class="article-actions-top">
                <a href="history.php?slug=<?php echo urlencode($article['slug']); ?>" class="btn-icon" title="View History">
                    <i class="fas fa-history"></i>
                </a>
                <a href="../edit_article.php?id=<?php echo $article['id']; ?>" class="btn-icon" title="Edit Article">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="../delete_article.php?id=<?php echo $article['id']; ?>" class="btn-icon btn-danger" title="Delete Article" onclick="return confirm('Are you sure you want to delete this article?')">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
            <h1><?php echo htmlspecialchars($article['title']); ?></h1>
            
            <div class="article-meta">
                <p>
                    Published on <?php echo format_date($article['published_at']); ?>
                    | <?php echo number_format($article['view_count']); ?> views
                </p>
                
                <?php if ($article['category_name']): ?>
                <div class="article-categories">
                    <a href="category.php?slug=<?php echo $article['category_slug']; ?>" class="category-tag"><?php echo htmlspecialchars($article['category_name']); ?></a>
                </div>
                <?php endif; ?>
            </div>
        </header>
        
        <div class="article-content">
            <?php echo $parsed_content; ?>
        </div>
        
    </article>
    
    <!-- Related Articles -->
    <?php
    // Get related articles (same category, excluding current)
    $stmt = $pdo->prepare("
        SELECT wa.*, u.display_name, u.username 
        FROM wiki_articles wa 
        JOIN users u ON wa.author_id = u.id 
        WHERE wa.category_id = ? AND wa.id != ? AND wa.status = 'published' 
        ORDER BY wa.published_at DESC 
        LIMIT 3
    ");
    $stmt->execute([$article['category_id'], $article['id']]);
    $related_articles = $stmt->fetchAll();
    
    if (!empty($related_articles)):
    ?>
    <div class="related-articles">
        <h3>Related Articles</h3>
        <div class="related-grid">
            <?php foreach ($related_articles as $related): ?>
            <div class="related-item">
                <h4><a href="<?php echo ucfirst($related['slug']); ?>"><?php echo htmlspecialchars($related['title']); ?></a></h4>
                <p class="related-meta">
                    By <?php echo htmlspecialchars($related['display_name'] ?: $related['username']); ?>
                    | <?php echo format_date($related['published_at']); ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>


<?php include '../../includes/footer.php'; ?>
