<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/markdown/MarkdownParser.php';

$page_title = 'Article Version';

$version_id = (int)($_GET['id'] ?? 0);

if (!$version_id) {
    show_message('Version ID is required.', 'error');
    redirect('index.php');
}

// Get version
$stmt = $pdo->prepare("
    SELECT av.*, wa.slug, u.username, u.display_name, cc.name as category_name 
    FROM article_versions av 
    JOIN wiki_articles wa ON av.article_id = wa.id 
    JOIN users u ON av.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id 
    WHERE av.id = ?
");
$stmt->execute([$version_id]);
$version = $stmt->fetch();

if (!$version) {
    show_message('Version not found.', 'error');
    redirect('index.php');
}

$page_title = $version['title'] . ' (Version ' . $version['version_number'] . ')';

// Parse markdown content
$parser = new MarkdownParser('');
$parsed_content = $parser->parse($version['content']);

include '../includes/header.php';
?>

<div class="article-version">
    <div class="version-header">
        <h1><?php echo htmlspecialchars($version['title']); ?></h1>
        <div class="version-info">
            <span class="version-badge">Version <?php echo $version['version_number']; ?></span>
            <a href="<?php echo ucfirst(version['slug']); ?>" class="btn">View Current Version</a>
        </div>
    </div>
    
    <div class="version-meta">
        <p>
            By <strong><?php echo htmlspecialchars($version['display_name'] ?: $version['username']); ?></strong>
            | <?php echo format_date($version['created_at']); ?>
            <?php if ($version['category_name']): ?>
            | <span class="category-tag"><?php echo htmlspecialchars($version['category_name']); ?></span>
            <?php endif; ?>
        </p>
        
        <?php if ($version['change_summary']): ?>
        <div class="change-summary">
            <strong>Changes:</strong> <?php echo htmlspecialchars($version['change_summary']); ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="article-content">
        <?php echo $parsed_content; ?>
    </div>
    
    <div class="version-actions">
        <a href="history.php?id=<?php echo $version['article_id']; ?>" class="btn">Back to History</a>
        <?php if (is_logged_in() && (is_admin() || $version['author_id'] == $_SESSION['user_id'])): ?>
            <a href="../restore_version.php?id=<?php echo $version['id']; ?>" 
               class="btn btn-warning"
               onclick="return confirm('Are you sure you want to restore this version?')">Restore This Version</a>
        <?php endif; ?>
    </div>
</div>

<style>
.article-version {
    max-width: 800px;
    margin: 0 auto;
}

.version-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.version-header h1 {
    color: #2c3e50;
    margin: 0;
}

.version-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.version-badge {
    background: #6c757d;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
}

.version-meta {
    margin-bottom: 2rem;
    color: #666;
    font-size: 0.9rem;
}

.category-tag {
    background-color: #3498db;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
}

.change-summary {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 4px;
    margin-top: 1rem;
    border-left: 3px solid #28a745;
}

.article-content {
    line-height: 1.8;
    font-size: 1.1rem;
    margin-bottom: 2rem;
}

.article-content h1,
.article-content h2,
.article-content h3,
.article-content h4,
.article-content h5,
.article-content h6 {
    color: #2c3e50;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.article-content h1 {
    font-size: 2rem;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 0.5rem;
}

.article-content h2 {
    font-size: 1.5rem;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 0.25rem;
}

.article-content h3 {
    font-size: 1.25rem;
}

.article-content p {
    margin-bottom: 1rem;
}

.article-content ul,
.article-content ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.article-content li {
    margin-bottom: 0.25rem;
}

.article-content code {
    background: #f8f9fa;
    padding: 0.125rem 0.25rem;
    border-radius: 3px;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.9em;
}

.article-content pre {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 4px;
    overflow-x: auto;
    margin-bottom: 1rem;
}

.article-content pre code {
    background: none;
    padding: 0;
}

.article-content blockquote {
    border-left: 4px solid #007bff;
    padding-left: 1rem;
    margin: 1rem 0;
    color: #6c757d;
    font-style: italic;
}

.article-content a {
    color: #007bff;
    text-decoration: none;
}

.article-content a:hover {
    text-decoration: underline;
}

/* Wiki Link Styles */
.article-content .wiki-link {
    color: #007bff;
    text-decoration: none;
    border-bottom: 1px dotted #007bff;
    padding: 0 2px;
}

.article-content .wiki-link:hover {
    background: #e3f2fd;
    text-decoration: none;
}

.article-content .wiki-link.missing {
    color: #dc3545;
    border-bottom-color: #dc3545;
}

.article-content .wiki-link.missing:hover {
    background: #f8d7da;
}

.version-actions {
    text-align: center;
    padding: 2rem 0;
    border-top: 1px solid #e9ecef;
}

.version-actions .btn {
    margin: 0 0.5rem;
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
    .version-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .version-info {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
