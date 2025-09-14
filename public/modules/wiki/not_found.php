<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$page_title = 'Article Not Found';

$requested_slug = $_GET['slug'] ?? '';
$requested_title = $_GET['title'] ?? '';
$reason = $_GET['reason'] ?? '';

// If no title provided, try to convert slug to title
if (empty($requested_title) && !empty($requested_slug)) {
    $requested_title = ucfirst(str_replace('-', ' ', $requested_slug));
}

include "../../includes/header.php";;

?>
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_module_not_found.css">
<?php
?>

<div class="not-found-container">
    <div class="card">
        <div class="not-found-content">
            <h1>📄 Article Not Found</h1>
            
            <?php if ($reason === 'login_required'): ?>
                <p class="not-found-message">
                    The article "<strong><?php echo htmlspecialchars($requested_title); ?></strong>" exists as a draft, but you need to be logged in to view it.
                </p>
            <?php elseif ($reason === 'draft_no_access'): ?>
                <p class="not-found-message">
                    The article "<strong><?php echo htmlspecialchars($requested_title); ?></strong>" exists as a draft, but you don't have permission to view it.
                </p>
            <?php else: ?>
                <p class="not-found-message">
                    The article "<strong><?php echo htmlspecialchars($requested_title); ?></strong>" does not exist yet.
                </p>
            <?php endif; ?>
            
            <div class="suggestions">
                <h3>What would you like to do?</h3>
                <div class="action-buttons">
                    <?php if (is_logged_in() && is_editor()): ?>
                        <a href="../create_article.php?title=<?php echo urlencode($requested_title); ?>" class="btn btn-primary">
                            ✏️ Create This Article
                        </a>
                    <?php endif; ?>
                    
                    <a href="index.php" class="btn btn-secondary">
                        📚 Browse All Articles
                    </a>
                    
                    <a href="search.php" class="btn btn-secondary">
                        🔍 Search Articles
                    </a>
                    
                    <?php if (is_logged_in()): ?>
                        <a href="../create_article.php" class="btn btn-outline">
                            ➕ Create New Article
                        </a>
                    <?php else: ?>
                        <a href="../login.php" class="btn btn-outline">
                            🔐 Login to Create Articles
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Draft Collaboration Section -->
            <?php if (is_logged_in() && is_editor()): ?>
            <div class="collaboration-suggestions">
                <h3>🤝 Collaboration Opportunities</h3>
                <p>If you're interested in collaborating on this topic, you can:</p>
                <ul>
                    <li>Create a shared draft that other editors can contribute to</li>
                    <li>Request collaboration from other community members</li>
                    <li>Start a discussion about the article content</li>
                </ul>
            </div>
            <?php endif; ?>
            
            <!-- Related Articles -->
            <?php
            // Get related articles with proper permissions
            $related_query = build_article_query("
                SELECT wa.*, u.display_name, u.username 
                FROM wiki_articles wa 
                JOIN users u ON wa.author_id = u.id
            ", [], []);
            $related_query['query'] .= " ORDER BY wa.view_count DESC LIMIT 5";
            
            $stmt = $pdo->prepare($related_query['query']);
            $stmt->execute($related_query['params']);
            $related_articles = $stmt->fetchAll();
            ?>
            
            <?php if (!empty($related_articles)): ?>
            <div class="related-articles">
                <h3>You might be interested in these articles:</h3>
                <ul class="suggestions-list">
                    <?php foreach ($related_articles as $article): ?>
                    <li>
                        <a href="<?php echo ucfirst($article['slug']); ?>">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </a>
                        <span class="article-meta">
                            by <?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?>
                            <?php if ($article['status'] === 'published'): ?>
                                (<?php echo number_format($article['view_count']); ?> views)
                            <?php else: ?>
                                <span class="draft-indicator">📝 Draft</span>
                            <?php endif; ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php include "../../includes/footer.php";; ?>
