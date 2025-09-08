<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$page_title = 'Article History';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    show_message('Article slug is required.', 'error');
    redirect('/');
}

// Get article by slug
$stmt = $pdo->prepare("SELECT * FROM wiki_articles WHERE slug = ?");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    show_message('Article not found.', 'error');
    redirect('/');
}

$article_id = $article['id'];

$stmt = $pdo->prepare("SELECT av.*, u.username, u.display_name 
    FROM article_versions av 
    JOIN users u ON av.created_by = u.id 
    WHERE av.article_id = ? 
    ORDER BY av.version_number DESC
");
$stmt->execute([$article_id]);
$versions = $stmt->fetchAll();

include "../../includes/header.php";;
?>

<div class="article-history">
    <div class="history-header">
        <h1>History: <?php echo htmlspecialchars($article['title']); ?></h1>
        <div class="history-actions">
            <a href="/wiki/<?php echo $article['slug']; ?>" class="btn">View Article</a>
            <?php if (is_logged_in() && (is_admin() || $article['created_by'] == $_SESSION['user_id'])): ?>
                <a href="/wiki/<?php echo $article['slug']; ?>/edit<?php echo $article['id']; ?>" class="btn">Edit Article</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="versions-list">
        <?php if (empty($versions)): ?>
            <div class="card">
                <p>No version history available for this article.</p>
            </div>
        <?php else: ?>
            <?php foreach ($versions as $version): ?>
            <div class="card version-item">
                <div class="version-header">
                    <h3>Version <?php echo $version['version_number']; ?></h3>
                    <div class="version-meta">
                        <span class="author">By <?php echo htmlspecialchars($version['display_name'] ?: $version['username']); ?></span>
                        <span class="date"><?php echo format_date($version['created_at']); ?></span>
                    </div>
                </div>
                
                <?php if ($version['changes_summary']): ?>
                <div class="change-summary">
                    <strong>Changes:</strong> <?php echo htmlspecialchars($version['changes_summary']); ?>
                </div>
                <?php endif; ?>
                
                <div class="version-actions">
                    <?php if (is_logged_in() && (is_admin() || $version['created_by'] == $_SESSION['user_id'])): ?>
                        <a href="../restore_version.php?id=<?php echo $version['id']; ?>" 
                           class="btn btn-sm btn-warning"
                           onclick="return confirm('Are you sure you want to restore this version?')">Restore</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.article-history {
    max-width: 800px;
    margin: 0 auto;
}

.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.history-header h1 {
    color: #2c3e50;
    margin: 0;
}

.history-actions {
    display: flex;
    gap: 1rem;
}

.versions-list {
    display: grid;
    gap: 1rem;
}

.version-item {
    border-left: 4px solid #3498db;
}

.version-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.version-header h3 {
    margin: 0;
    color: #2c3e50;
}

.version-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
    color: #666;
}

.change-summary {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
    border-left: 3px solid #28a745;
}

.version-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
}

.btn-warning {
    background: #ffc107;
    color: #000;
    border-color: #ffc107;
}

.btn-warning:hover {
    background: #e0a800;
    border-color: #d39e00;
}

@media (max-width: 768px) {
    .history-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .version-header {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }
    
    .version-actions {
        justify-content: flex-start;
    }
}
</style>

<?php include "../../includes/footer.php";; ?>
