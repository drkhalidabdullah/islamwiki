<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/markdown/MarkdownParser.php';

$page_title = 'Article';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    redirect('index.php');
}

// Enhanced article query - handles drafts based on user permissions
$user_id = $_SESSION['user_id'] ?? null;
$is_logged_in = is_logged_in();
$is_editor = is_editor();
$is_admin = is_admin();

// Build the query based on user permissions
$where_conditions = ["wa.slug = ?"];
$params = [$slug];

if (!$is_logged_in) {
    // Guest users can only see published articles
    $where_conditions[] = "wa.status = 'published'";
} elseif (!$is_editor) {
    // Regular users can see published articles and their own drafts
    $where_conditions[] = "(wa.status = 'published' OR (wa.status = 'draft' AND wa.author_id = ?))";
    $params[] = $user_id;
} else {
    // Editors can see published articles and drafts they have access to
    $where_conditions[] = "(wa.status = 'published' OR wa.status = 'draft')";
}

$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name, cc.name as category_name, cc.slug as category_slug,
           u2.username as last_editor_username, u2.display_name as last_editor_display_name,
           u3.username as verifier_username, u3.display_name as verifier_display_name
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id 
    LEFT JOIN users u2 ON wa.last_edited_by = u2.id
    LEFT JOIN users u3 ON wa.verified_by = u3.id
    WHERE " . implode(' AND ', $where_conditions)
);
$stmt->execute($params);
$article = $stmt->fetch();

if (!$article) {
    // Check if there's a draft that the user can't access
    $stmt = $pdo->prepare("SELECT id, title, status, author_id FROM wiki_articles WHERE slug = ?");
    $stmt->execute([$slug]);
    $draft_check = $stmt->fetch();
    
    if ($draft_check) {
        // Article exists but user doesn't have permission to view it
        if ($draft_check['status'] === 'draft') {
            if (!$is_logged_in) {
                redirect("not_found.php?slug=" . urlencode($slug) . "&title=" . urlencode(ucfirst(str_replace('-', ' ', $slug))) . "&reason=login_required");
            } else {
                redirect("not_found.php?slug=" . urlencode($slug) . "&title=" . urlencode(ucfirst(str_replace('-', ' ', $slug))) . "&reason=draft_no_access");
            }
        }
    }
    
    redirect("not_found.php?slug=" . urlencode($slug) . "&title=" . urlencode(ucfirst(str_replace('-', ' ', $slug))));
}

// Check if user can view this draft
if ($article['status'] === 'draft') {
    if (!can_view_draft($article, $user_id)) {
        redirect("not_found.php?slug=" . urlencode($slug) . "&title=" . urlencode(ucfirst(str_replace('-', ' ', $slug))) . "&reason=draft_no_access");
    }
}

// Increment view count only for published articles
if ($article['status'] === 'published') {
    $stmt = $pdo->prepare("UPDATE wiki_articles SET view_count = view_count + 1 WHERE id = ?");
    $stmt->execute([$article['id']]);
}

$page_title = $article['title'];

// Parse markdown content
$parser = new MarkdownParser('');
$parsed_content = $parser->parse($article['content']);

include 'header.php';
?>

<div class="article-container">
    <article class="card">
        <header>
            <h1><?php echo htmlspecialchars($article['title']); ?></h1>
            
            <?php if ($article['status'] === 'draft'): ?>
            <div class="draft-banner">
                <span class="draft-badge">📝 Draft</span>
                <?php if ($article['is_scholar_verified']): ?>
                    <span class="verified-badge">✅ Scholar Verified</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="article-meta">
                <p>
                    By <strong><?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?></strong>
                    <?php if ($article['status'] === 'published'): ?>
                        | Published on <?php echo format_date($article['published_at']); ?>
                    <?php else: ?>
                        | Created on <?php echo format_date($article['created_at']); ?>
                        <?php if ($article['last_edited_at']): ?>
                            | Last edited <?php echo format_date($article['last_edited_at']); ?>
                            <?php if ($article['last_editor_display_name']): ?>
                                by <?php echo htmlspecialchars($article['last_editor_display_name']); ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($article['status'] === 'published'): ?>
                        | <?php echo number_format($article['view_count']); ?> views
                    <?php endif; ?>
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
        
        <?php if ($article['status'] === 'draft'): ?>
        <div class="draft-actions">
            <h3>Draft Actions</h3>
            <div class="action-buttons">
                <?php if (is_logged_in() && (is_admin() || $article['author_id'] == $_SESSION['user_id'])): ?>
                    <a href="../edit_article.php?id=<?php echo $article['id']; ?>" class="btn btn-primary">✏️ Edit Draft</a>
                    <a href="../edit_article.php?id=<?php echo $article['id']; ?>&action=publish" class="btn btn-success">🚀 Publish Article</a>
                <?php endif; ?>
                
                <?php if (is_editor() && $article['collaboration_mode'] !== 'private'): ?>
                    <a href="../edit_article.php?id=<?php echo $article['id']; ?>&action=collaborate" class="btn btn-outline">🤝 Collaborate</a>
                <?php endif; ?>
                
                <?php if (is_logged_in() && is_scholar()): ?>
                    <a href="../verify_article.php?id=<?php echo $article['id']; ?>" class="btn btn-warning">🔍 Verify Content</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (is_logged_in() && (is_admin() || $article['author_id'] == $_SESSION['user_id'])): ?>
        <div class="article-actions">
            <a href="history.php?id=<?php echo $article["id"]; ?>" class="btn">View History</a>
            <a href="../edit_article.php?id=<?php echo $article['id']; ?>" class="btn">Edit Article</a>
            <a href="../delete_article.php?id=<?php echo $article['id']; ?>" 
               class="btn btn-danger" 
               onclick="return confirm('Are you sure you want to delete this article?')">Delete Article</a>
        </div>
        <?php endif; ?>
    </article>
    
    <!-- Related Articles -->
    <?php
    // Get related articles (same category, excluding current)
    $related_query = build_article_query("
        SELECT wa.*, u.display_name, u.username 
        FROM wiki_articles wa 
        JOIN users u ON wa.author_id = u.id 
        WHERE wa.category_id = ? AND wa.id != ?
    ", [], [$article['category_id'], $article['id']]);
    
    $stmt = $pdo->prepare($related_query['query']);
    $stmt->execute($related_query['params']);
    $related_articles = $stmt->fetchAll();
    ?>
    
    <?php if (!empty($related_articles)): ?>
    <div class="related-articles">
        <h3>Related Articles</h3>
        <div class="articles-grid">
            <?php foreach ($related_articles as $related): ?>
            <div class="card">
                <h4><a href="<?php echo ucfirst($related['slug']); ?>"><?php echo htmlspecialchars($related['title']); ?></a></h4>
                <p><?php echo truncate_text($related['excerpt'] ?: strip_tags($related['content']), 100); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
