<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/wiki_functions.php';

// Check maintenance mode
check_maintenance_mode();

// Check if wiki is enabled
$enable_wiki = get_system_setting('enable_wiki', true);
if (!$enable_wiki) {
    show_message('Wiki system is currently disabled.', 'error');
    redirect('/dashboard');
}

// Source view is available to everyone (no login required)

$page_title = 'Source Code';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    redirect('/wiki');
}

// Get article from database
try {
    $stmt = $pdo->prepare("
        SELECT a.*, u.username, u.display_name, u.avatar
        FROM wiki_articles a
        LEFT JOIN users u ON a.author_id = u.id
        WHERE a.slug = ? AND a.status = 'published'
    ");
    $stmt->execute([$slug]);
    $article = $stmt->fetch();
    
    if (!$article) {
        show_message('Article not found.', 'error');
        redirect('/wiki');
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    show_message('Database error occurred.', 'error');
    redirect('/wiki');
}

// Get article categories
$article_categories = get_article_categories($article['id']);

// Check if user is watching this article
$is_watched = false;
if (is_logged_in()) {
    $stmt = $pdo->prepare("SELECT id FROM user_watchlist WHERE user_id = ? AND article_id = ?");
    $stmt->execute([$_SESSION['user_id'], $article['id']]);
    $is_watched = $stmt->fetch() !== false;
}

// Check if talk page exists
$talk_page = false;
try {
    $stmt = $pdo->prepare("SELECT id FROM wiki_articles WHERE slug = ? AND status = 'published'");
    $stmt->execute([$article['slug'] . '/talk']);
    $talk_page = $stmt->fetch() !== false;
} catch (PDOException $e) {
    // Ignore error, talk page doesn't exist
}

include '../../includes/header.php';
?>

<div class="wiki-container">
    <div class="wiki-header">
        <div class="wiki-breadcrumb">
            <a href="/wiki">Wiki</a> &gt; 
            <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>"><?php echo htmlspecialchars($article['title']); ?></a> &gt; 
            <span>Source</span>
        </div>
        
        <div class="wiki-actions">
            <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>" class="btn btn-secondary">
                <i class="iw iw-arrow-left"></i> Back to Article
            </a>
            <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>/history" class="btn btn-outline">
                <i class="iw iw-history"></i> View History
            </a>
            <?php if (is_logged_in() && is_editor()): ?>
                <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>/edit" class="btn btn-primary">
                    <i class="iw iw-edit"></i> Edit
                </a>
            <?php elseif (!is_logged_in()): ?>
                <a href="/login" class="btn btn-outline">
                    <i class="iw iw-login"></i> Login to Edit
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="wiki-content">
        <div class="source-view">
            <div class="source-header">
                <h1>Source Code: <?php echo htmlspecialchars($article['title']); ?></h1>
                <div class="source-meta">
                    <span class="source-info">
                        <i class="iw iw-user"></i>
                        <?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?>
                    </span>
                    <span class="source-info">
                        <i class="iw iw-calendar"></i>
                        <?php echo date('F j, Y \a\t g:i A', strtotime($article['updated_at'])); ?>
                    </span>
                    <span class="source-info">
                        <i class="iw iw-file-text"></i>
                        <?php echo number_format(strlen($article['content'])); ?> characters
                    </span>
                </div>
            </div>

            <div class="source-content">
                <div class="source-toolbar">
                    <button onclick="copySource()" class="btn btn-sm btn-outline" title="Copy source code">
                        <i class="iw iw-copy"></i> Copy
                    </button>
                    <button onclick="downloadSource()" class="btn btn-sm btn-outline" title="Download as file">
                        <i class="iw iw-download"></i> Download
                    </button>
                    <button onclick="toggleLineNumbers()" class="btn btn-sm btn-outline" title="Toggle line numbers">
                        <i class="iw iw-list"></i> Line Numbers
                    </button>
                </div>
                
                <pre id="source-code" class="source-code"><code><?php echo htmlspecialchars($article['content']); ?></code></pre>
            </div>
        </div>
    </div>
</div>

<style>
.source-view {
    max-width: 100%;
    margin: 0 auto;
}

.source-header {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px 8px 0 0;
    padding: 1.5rem;
    margin-bottom: 0;
}

.source-header h1 {
    margin: 0 0 1rem 0;
    color: #2c3e50;
    font-size: 1.5rem;
}

.source-meta {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.source-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6c757d;
    font-size: 0.9rem;
}

.source-info i {
    color: #007bff;
}

.source-content {
    border: 1px solid #e9ecef;
    border-top: none;
    border-radius: 0 0 8px 8px;
    background: #fff;
}

.source-toolbar {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 0.75rem 1rem;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.source-code {
    margin: 0;
    padding: 1.5rem;
    background: #f8f9fa;
    border: none;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 14px;
    line-height: 1.5;
    color: #2c3e50;
    overflow-x: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.source-code.with-line-numbers {
    counter-reset: line-numbering;
}

.source-code.with-line-numbers code {
    counter-increment: line-numbering;
}

.source-code.with-line-numbers code:before {
    content: counter(line-numbering);
    float: left;
    margin-right: 1rem;
    color: #adb5bd;
    text-align: right;
    width: 2rem;
}

@media (max-width: 768px) {
    .source-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .source-toolbar {
        flex-direction: column;
    }
    
    .source-code {
        font-size: 12px;
        padding: 1rem;
    }
}
</style>

<script>
function copySource() {
    const sourceCode = document.getElementById('source-code').textContent;
    navigator.clipboard.writeText(sourceCode).then(function() {
        // Show success message
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="iw iw-check"></i> Copied!';
        btn.classList.add('btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
        }, 2000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
        alert('Failed to copy source code');
    });
}

function downloadSource() {
    const sourceCode = document.getElementById('source-code').textContent;
    const blob = new Blob([sourceCode], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = '<?php echo htmlspecialchars($article['slug']); ?>.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

function toggleLineNumbers() {
    const sourceCode = document.getElementById('source-code');
    const btn = event.target.closest('button');
    
    if (sourceCode.classList.contains('with-line-numbers')) {
        sourceCode.classList.remove('with-line-numbers');
        btn.innerHTML = '<i class="iw iw-list"></i> Line Numbers';
    } else {
        sourceCode.classList.add('with-line-numbers');
        btn.innerHTML = '<i class="iw iw-list"></i> Hide Numbers';
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
