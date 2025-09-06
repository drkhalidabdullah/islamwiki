<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Article Not Found';

$requested_slug = $_GET['slug'] ?? '';
$requested_title = $_GET['title'] ?? '';
$reason = $_GET['reason'] ?? '';

// If no title provided, try to convert slug to title
if (empty($requested_title) && !empty($requested_slug)) {
    $requested_title = ucfirst(str_replace('-', ' ', $requested_slug));
}

include 'header.php';
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

<style>
.not-found-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.not-found-content {
    text-align: center;
}

.not-found-content h1 {
    color: #e74c3c;
    margin-bottom: 1rem;
}

.not-found-message {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 2rem;
}

.suggestions {
    margin: 2rem 0;
}

.suggestions h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: center;
    margin: 1.5rem 0;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    text-decoration: none;
    border-radius: 4px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.btn-outline {
    background: transparent;
    color: #3498db;
    border: 2px solid #3498db;
}

.btn-outline:hover {
    background: #3498db;
    color: white;
}

.related-articles {
    margin-top: 3rem;
    text-align: left;
}

.related-articles h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.suggestions-list {
    list-style: none;
    padding: 0;
}

.suggestions-list li {
    margin: 1rem 0;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 4px;
    border-left: 4px solid #3498db;
}

.suggestions-list a {
    color: #2c3e50;
    text-decoration: none;
    font-weight: 500;
}

.suggestions-list a:hover {
    color: #3498db;
}

.article-meta {
    display: block;
    font-size: 0.9rem;
    color: #666;
    margin-top: 0.25rem;
}

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        max-width: 300px;
    }
}
</style>

<?php include 'footer.php'; ?>
