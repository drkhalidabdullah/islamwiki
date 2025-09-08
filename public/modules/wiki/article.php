<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/wiki_functions.php';
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

// Parse markdown content with enhanced wiki features
$parser = new EnhancedMarkdownParser('');
$parsed_content = $parser->parse($article['content']);

// Get talk page status
$talk_page = get_talk_page($article['id']);

// Check if article is in user's watchlist
$is_watched = false;
if (is_logged_in()) {
    $is_watched = is_in_watchlist($_SESSION['user_id'], $article['id']);
}

include '../../includes/header.php';
?>

<div class="article-container">
    <article class="card">
        <header class="article-header">
            <div class="article-actions-top">
                <a href="/wiki/<?php echo $article['slug']; ?>/history" class="btn-icon" title="View History">
                    <i class="fas fa-history"></i>
                </a>
                <a href="/wiki/<?php echo $article['slug']; ?>/talk" class="btn-icon" title="Discussion">
                    <i class="fas fa-comments"></i>
                    <?php if ($talk_page): ?>
                        <span class="talk-indicator" title="Has discussion"></span>
                    <?php endif; ?>
                </a>
                <?php if (is_logged_in()): ?>
                    <a href="#" class="btn-icon watchlist-btn <?php echo $is_watched ? 'watched' : ''; ?>" 
                       title="<?php echo $is_watched ? 'Remove from watchlist' : 'Add to watchlist'; ?>"
                       onclick="toggleWatchlist(<?php echo $article['id']; ?>, this)">
                        <i class="fas fa-eye"></i>
                    </a>
                <?php endif; ?>
                <?php if (is_logged_in() && is_editor()): ?>
                    <a href="../edit_article.php?id=<?php echo $article['id']; ?>" class="btn-icon" title="Edit Article">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="../delete_article.php?id=<?php echo $article['id']; ?>" class="btn-icon btn-danger" title="Delete Article" onclick="return confirm('Are you sure you want to delete this article?')">
                        <i class="fas fa-trash"></i>
                    </a>
                <?php endif; ?>
            </div>
            <h1><?php echo htmlspecialchars($article['title']); ?></h1>
            
            <div class="article-meta">
                <p>
                    Published on <?php echo format_date($article['published_at']); ?>
                    | <?php echo number_format($article['view_count']); ?> views
                </p>
                
                <?php if ($article['category_name']): ?>
                <div class="article-categories">
                    <a href="/wiki/category/<?php echo $article['category_slug']; ?>" class="category-tag"><?php echo htmlspecialchars($article['category_name']); ?></a>
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
                <h4><a href="/wiki/<?php echo $related['slug']; ?>"><?php echo htmlspecialchars($related['title']); ?></a></h4>
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

<style>
.talk-indicator {
    position: absolute;
    top: -2px;
    right: -2px;
    width: 8px;
    height: 8px;
    background: #28a745;
    border-radius: 50%;
    border: 2px solid white;
}

.watchlist-btn {
    position: relative;
    transition: all 0.3s;
}

.watchlist-btn.watched {
    color: #ffc107;
}

.watchlist-btn:hover {
    transform: scale(1.1);
}

.article-actions-top {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    color: #6c757d;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.3s;
    position: relative;
}

.btn-icon:hover {
    background: #e9ecef;
    color: #495057;
    transform: translateY(-1px);
}

.btn-icon.btn-danger {
    color: #dc3545;
}

.btn-icon.btn-danger:hover {
    background: #f8d7da;
    color: #721c24;
}

.wiki-thumbnail {
    float: right;
    margin: 0 0 1rem 1rem;
    max-width: 200px;
    text-align: center;
}

.thumb-image {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.thumb-caption {
    font-size: 0.9rem;
    color: #6c757d;
    margin-top: 0.5rem;
    font-style: italic;
}

.missing-file {
    color: #dc3545;
    background: #f8d7da;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-size: 0.9rem;
}

/* Enhanced article styling */
.article-content {
    line-height: 1.7;
    color: #2c3e50;
    margin-top: 1rem;
}

.article-content h2:first-child {
    margin-top: 0;
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
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 0.5rem;
}

.article-content h1 {
    font-size: 2rem;
    border-bottom: 2px solid #007bff;
}

.article-content h2 {
    font-size: 1.5rem;
}

.article-content h3 {
    font-size: 1.25rem;
}

.article-content p {
    margin-bottom: 1.5rem;
}

.article-content ul,
.article-content ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.article-content li {
    margin-bottom: 0.5rem;
}

.article-content blockquote {
    border-left: 4px solid #007bff;
    padding-left: 1.5rem;
    margin: 2rem 0;
    color: #6c757d;
    font-style: italic;
    background: #f8f9fa;
    padding: 1rem 1.5rem;
    border-radius: 0 4px 4px 0;
}

.article-content code {
    background: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
    color: #e83e8c;
}

.article-content pre {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 6px;
    overflow-x: auto;
    margin: 2rem 0;
    border: 1px solid #e9ecef;
}

.article-content pre code {
    background: none;
    padding: 0;
    color: #2c3e50;
}

.article-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 2rem 0;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 6px;
    overflow: hidden;
}

.article-content th,
.article-content td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}

.article-content th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.article-content tr:hover {
    background: #f8f9fa;
}

/* Wiki link styling */
.article-content a {
    color: #007bff;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: all 0.3s;
}

.article-content a:hover {
    color: #0056b3;
    border-bottom-color: #0056b3;
}

.article-content a.missing-link,
.article-content a.wiki-link.missing {
    color: #dc3545;
    border-bottom: 1px dashed #dc3545;
}

.article-content a.missing-link:hover,
.article-content a.wiki-link.missing:hover {
    color: #a71e2a;
    border-bottom-color: #a71e2a;
}

.article-content a.wiki-link {
    color: #007bff;
    border-bottom: 1px solid transparent;
    transition: all 0.3s;
}

.article-content a.wiki-link:hover {
    color: #0056b3;
    border-bottom-color: #0056b3;
}

/* Category styling */
.article-categories {
    margin-top: 0.5rem;
}

.category-tag {
    display: inline-block;
    background: #007bff;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.3s;
}

.category-tag:hover {
    background: #0056b3;
    color: white;
    text-decoration: none;
    transform: translateY(-1px);
}
</style>

<script>
function toggleWatchlist(articleId, button) {
    const isWatched = button.classList.contains('watched');
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
            if (action === 'add') {
                button.classList.add('watched');
                button.title = 'Remove from watchlist';
                showToast('Added to watchlist', 'success');
            } else {
                button.classList.remove('watched');
                button.title = 'Add to watchlist';
                showToast('Removed from watchlist', 'success');
            }
        } else {
            showToast(data.message || 'Error updating watchlist', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating watchlist', 'error');
    });
}

function showToast(message, type = 'info') {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    // Style the toast
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
        color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
        padding: 1rem 1.5rem;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        z-index: 1000;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(toast);
    
    // Remove toast after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
</script>

<?php include '../../includes/footer.php'; ?>
