<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Dashboard';
require_login();

$current_user = get_user($_SESSION['user_id']);

// Get user's articles
$stmt = $pdo->prepare("SELECT * FROM wiki_articles WHERE author_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$user_articles = $stmt->fetchAll();

// Get all articles for admin
$all_articles = [];
if (is_admin()) {
    $stmt = $pdo->prepare("
        SELECT wa.*, u.username, u.display_name 
        FROM wiki_articles wa 
        JOIN users u ON wa.author_id = u.id 
        ORDER BY wa.created_at DESC
    ");
    $stmt->execute();
    $all_articles = $stmt->fetchAll();
}

include "../../includes/header.php";;
?>

<div class="dashboard">
    <h1>Welcome, <?php echo htmlspecialchars($current_user['display_name'] ?: $current_user['username']); ?>!</h1>
    
    <div class="dashboard-stats">
        <div class="card">
            <h3>Your Articles</h3>
            <p class="stat-number"><?php echo count($user_articles); ?></p>
        </div>
        
        <div class="card">
            <h3>Published</h3>
            <p class="stat-number"><?php echo count(array_filter($user_articles, fn($a) => $a['status'] === 'published')); ?></p>
        </div>
        
        <div class="card">
            <h3>Drafts</h3>
            <p class="stat-number"><?php echo count(array_filter($user_articles, fn($a) => $a['status'] === 'draft')); ?></p>
        </div>
    </div>
    
    <div class="dashboard-actions">
        <?php if (is_editor()): ?>
            <a href="/pages/wiki/create_article.php" class="btn btn-success">Create New Article</a>
        <?php endif; ?>
        <a href="/pages/user/watchlist.php" class="btn">My Watchlist</a>
        <a href="profile" class="btn">Edit Profile</a>
        <a href="settings" class="btn">Settings</a>
    </div>
    
    <?php if (!empty($user_articles)): ?>
    <div class="user-articles">
        <h2>Your Articles</h2>
        <div class="articles-list">
            <?php foreach ($user_articles as $article): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                <p class="article-meta">
                    Status: <span class="status-<?php echo $article['status']; ?>"><?php echo ucfirst($article['status']); ?></span>
                    | Created: <?php echo format_date($article['created_at']); ?>
                </p>
                <p><?php echo truncate_text($article['excerpt'] ?: strip_tags($article['content']), 200); ?></p>
                <div class="article-actions">
                    <a href="wiki/article.php?slug=<?php echo $article['slug']; ?>" class="btn">View</a>
                    <?php if (is_editor() && ($article['author_id'] == $_SESSION['user_id'] || is_admin())): ?>
                        <a href="edit_article.php?id=<?php echo $article['id']; ?>" class="btn">Edit</a>
                        <a href="delete_article.php?id=<?php echo $article['id']; ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to delete this article?')">Delete</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (is_admin() && !empty($all_articles)): ?>
    <div class="admin-section">
        <h2>All Articles (Admin View)</h2>
        <div class="articles-list">
            <?php foreach ($all_articles as $article): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                <p class="article-meta">
                    By <?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?>
                    | Status: <span class="status-<?php echo $article['status']; ?>"><?php echo ucfirst($article['status']); ?></span>
                    | Created: <?php echo format_date($article['created_at']); ?>
                </p>
                <p><?php echo truncate_text($article['excerpt'] ?: strip_tags($article['content']), 200); ?></p>
                <div class="article-actions">
                    <a href="wiki/article.php?slug=<?php echo $article['slug']; ?>" class="btn">View</a>
                    <a href="edit_article.php?id=<?php echo $article['id']; ?>" class="btn">Edit</a>
                    <a href="delete_article.php?id=<?php echo $article['id']; ?>" 
                       class="btn btn-danger" 
                       onclick="return confirm('Are you sure you want to delete this article?')">Delete</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin: 2rem 0;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #3498db;
    margin: 0;
}

.dashboard-actions {
    margin: 2rem 0;
    text-align: center;
}

.dashboard-actions .btn {
    margin: 0 0.5rem;
}

.articles-list {
    display: grid;
    gap: 1rem;
}

.article-meta {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.status-published {
    color: #27ae60;
    font-weight: bold;
}

.status-draft {
    color: #f39c12;
    font-weight: bold;
}

.article-actions {
    margin-top: 1rem;
}

.article-actions .btn {
    margin-right: 0.5rem;
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}

.admin-section {
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 2px solid #ecf0f1;
}
</style>

<?php include "../../includes/footer.php";; ?>
