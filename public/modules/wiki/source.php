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
            <!-- View Source (current page indicator) -->
            <span class="btn btn-primary" style="opacity: 0.7;">
                <i class="iw iw-code"></i> View Source
            </span>
            
            <!-- Edit Source -->
            <?php if (is_logged_in() && is_editor()): ?>
                <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>/edit" class="btn btn-outline">
                    <i class="iw iw-edit"></i> Edit Source
                </a>
            <?php elseif (!is_logged_in()): ?>
                <a href="/login?return=<?php echo urlencode('/wiki/' . htmlspecialchars($article['slug']) . '/edit'); ?>" class="btn btn-outline">
                    <i class="iw iw-edit"></i> Login to Edit
                </a>
            <?php endif; ?>
            
            <!-- View History -->
            <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>/history" class="btn btn-outline">
                <i class="iw iw-history"></i> View History
            </a>
            
            <!-- Discussion (for logged in users only) -->
            <?php if (is_logged_in()): ?>
                <?php if ($talk_page): ?>
                    <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>/talk" class="btn btn-outline">
                        <i class="iw iw-comments"></i> Discussion
                    </a>
                <?php else: ?>
                    <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>/talk" class="btn btn-outline">
                        <i class="iw iw-comments"></i> Start Discussion
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="/login?return=<?php echo urlencode('/wiki/' . htmlspecialchars($article['slug']) . '/talk'); ?>" class="btn btn-outline">
                    <i class="iw iw-comments"></i> Login to Discuss
                </a>
            <?php endif; ?>
            
            <!-- Add to Watchlist (for logged in users) -->
            <?php if (is_logged_in()): ?>
                <button class="btn btn-outline" onclick="toggleWatchlist(<?php echo $article['id']; ?>, this)">
                    <i class="iw <?php echo $is_watched ? 'iw-eye-slash' : 'iw-eye'; ?>"></i> 
                    <?php echo $is_watched ? 'Remove from Watchlist' : 'Add to Watchlist'; ?>
                </button>
            <?php endif; ?>
            
            <!-- More Actions (for logged in users) -->
            <?php if (is_logged_in()): ?>
                <div class="dropdown">
                    <button class="btn btn-outline dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="iw iw-ellipsis-h"></i> More Actions
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>">
                            <i class="iw iw-arrow-left"></i> Back to Article
                        </a></li>
                        <li><a class="dropdown-item" href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>/history">
                            <i class="iw iw-history"></i> View History
                        </a></li>
                        <?php if (is_editor()): ?>
                            <li><a class="dropdown-item" href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>/edit">
                                <i class="iw iw-edit"></i> Edit Article
                            </a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="showReportModal(<?php echo $article['id']; ?>, 'wiki_article')">
                            <i class="iw iw-flag"></i> Report Content
                        </a></li>
                    </ul>
                </div>
            <?php else: ?>
                <!-- For non-logged in users, show back to article button -->
                <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>" class="btn btn-secondary">
                    <i class="iw iw-arrow-left"></i> Back to Article
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
.wiki-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.source-view {
    max-width: 100%;
    margin: 2rem 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    overflow: hidden;
}

.source-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-radius: 12px 12px 0 0;
    padding: 2rem;
    margin-bottom: 0;
}

.source-header h1 {
    margin: 0 0 1.5rem 0;
    color: #2c3e50;
    font-size: 1.75rem;
    font-weight: 600;
    border-bottom: 2px solid #007bff;
    padding-bottom: 0.5rem;
}

.source-meta {
    display: flex;
    gap: 2.5rem;
    flex-wrap: wrap;
    margin-top: 1rem;
}

.source-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #495057;
    font-size: 0.95rem;
    background: rgba(255, 255, 255, 0.7);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    border: 1px solid rgba(0, 123, 255, 0.2);
}

.source-info i {
    color: #007bff;
    font-size: 1.1rem;
}

.source-content {
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 12px 12px;
    background: #fff;
}

.source-toolbar {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: center;
}

.source-toolbar .btn {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.source-toolbar .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.source-code {
    margin: 0;
    padding: 2rem;
    background: #f8f9fa;
    border: none;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', monospace;
    font-size: 14px;
    line-height: 1.6;
    color: #2c3e50;
    overflow-x: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
    border-radius: 0 0 12px 12px;
    position: relative;
}

.source-code::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, #dee2e6, transparent);
}

.source-code code {
    display: block;
    padding: 0;
    background: transparent;
    border: none;
    font-size: inherit;
    line-height: inherit;
    color: inherit;
}

.source-code.with-line-numbers {
    counter-reset: line-numbering;
    padding-left: 3rem;
}

.source-code.with-line-numbers code {
    counter-increment: line-numbering;
    position: relative;
}

.source-code.with-line-numbers code:before {
    content: counter(line-numbering);
    position: absolute;
    left: -2.5rem;
    top: 0;
    color: #adb5bd;
    text-align: right;
    width: 2rem;
    font-size: 0.85em;
    line-height: 1.6;
}

/* Wiki header improvements */
.wiki-header {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.wiki-breadcrumb {
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: #6c757d;
}

.wiki-breadcrumb a {
    color: #007bff;
    text-decoration: none;
    transition: color 0.2s ease;
}

.wiki-breadcrumb a:hover {
    color: #0056b3;
    text-decoration: underline;
}

.wiki-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: center;
}

.wiki-actions .btn {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    border-radius: 6px;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.wiki-actions .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Responsive improvements */
@media (max-width: 768px) {
    .wiki-container {
        padding: 0 0.5rem;
    }
    
    .source-view {
        margin: 1rem 0;
    }
    
    .source-header {
        padding: 1.5rem;
    }
    
    .source-header h1 {
        font-size: 1.5rem;
    }
    
    .source-meta {
        flex-direction: column;
        gap: 1rem;
    }
    
    .source-info {
        padding: 0.4rem 0.8rem;
        font-size: 0.9rem;
    }
    
    .source-toolbar {
        flex-direction: column;
        align-items: stretch;
        padding: 1rem;
    }
    
    .source-toolbar .btn {
        justify-content: center;
    }
    
    .source-code {
        font-size: 12px;
        padding: 1.5rem;
    }
    
    .source-code.with-line-numbers {
        padding-left: 2.5rem;
    }
    
    .source-code.with-line-numbers code:before {
        left: -2rem;
        width: 1.5rem;
    }
    
    .wiki-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .wiki-actions .btn {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .source-code {
        font-size: 11px;
        padding: 1rem;
    }
    
    .source-header {
        padding: 1rem;
    }
    
    .source-header h1 {
        font-size: 1.25rem;
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

function toggleWatchlist(articleId, button) {
    const isWatched = button.querySelector('i').classList.contains('iw-eye-slash');
    const action = isWatched ? 'remove' : 'add';
    
    fetch('/api/ajax/watchlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: action,
            article_id: articleId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update button appearance
            const icon = button.querySelector('i');
            const text = button.querySelector('span') || button.childNodes[2];
            
            if (data.watched !== undefined) {
                if (data.watched) {
                    icon.className = 'iw iw-eye-slash';
                    text.textContent = ' Remove from Watchlist';
                } else {
                    icon.className = 'iw iw-eye';
                    text.textContent = ' Add to Watchlist';
                }
            }
            
            // Show success message
            if (typeof showMessage === 'function') {
                showMessage(data.message, 'success');
            }
        } else {
            if (typeof showMessage === 'function') {
                showMessage(data.message, 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof showMessage === 'function') {
            showMessage('An error occurred while updating watchlist', 'error');
        }
    });
}
</script>

<?php include '../../includes/footer.php'; ?>
