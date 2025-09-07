<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'My Profile';
require_login();

$current_user = get_user($_SESSION['user_id']);

// Get user's article statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_articles,
        COUNT(CASE WHEN status = 'published' THEN 1 END) as published_articles,
        COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_articles,
        SUM(view_count) as total_views
    FROM wiki_articles 
    WHERE author_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user_stats = $stmt->fetch();

// Get user's recent articles
$stmt = $pdo->prepare("
    SELECT * FROM wiki_articles 
    WHERE author_id = ? 
    ORDER BY updated_at DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_articles = $stmt->fetchAll();

// Get user's activity log
$stmt = $pdo->prepare("
    SELECT * FROM activity_logs 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$recent_activity = $stmt->fetchAll();

include "../../includes/header.php";;
?>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-info">
            <div class="profile-avatar">
                <div class="avatar-circle">
                    <?php echo strtoupper(substr($current_user['display_name'] ?: $current_user['username'], 0, 2)); ?>
                </div>
            </div>
            <div class="profile-details">
                <h1><?php echo htmlspecialchars($current_user['display_name'] ?: $current_user['first_name'] . ' ' . $current_user['last_name']); ?></h1>
                <p class="username">@<?php echo htmlspecialchars($current_user['username']); ?></p>
                <p class="email"><?php echo htmlspecialchars($current_user['email']); ?></p>
                <?php if ($current_user['bio']): ?>
                <p class="bio"><?php echo htmlspecialchars($current_user['bio']); ?></p>
                <?php endif; ?>
                <p class="member-since">Member since <?php echo format_date($current_user['created_at']); ?></p>
            </div>
        </div>
        <div class="profile-actions">
            <a href="settings.php" class="btn btn-primary">Edit Profile & Settings</a>
            <?php if (is_editor()): ?>
                <a href="create_article.php" class="btn btn-success">Create Article</a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo number_format($user_stats['total_articles']); ?></div>
            <div class="stat-label">Total Articles</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo number_format($user_stats['published_articles']); ?></div>
            <div class="stat-label">Published</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo number_format($user_stats['draft_articles']); ?></div>
            <div class="stat-label">Drafts</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo number_format($user_stats['total_views'] ?: 0); ?></div>
            <div class="stat-label">Total Views</div>
        </div>
    </div>
    
    <div class="profile-content">
        <!-- Recent Articles -->
        <div class="profile-section">
            <div class="card">
                <h2>Recent Articles</h2>
                <?php if (!empty($recent_articles)): ?>
                    <div class="articles-list">
                        <?php foreach ($recent_articles as $article): ?>
                        <div class="article-item">
                            <div class="article-info">
                                <h4>
                                    <?php if ($article['status'] === 'published'): ?>
                                        <a href="wiki/<?php echo ucfirst($article['slug']); ?>">
                                            <?php echo htmlspecialchars($article['title']); ?>
                                        </a>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    <?php endif; ?>
                                </h4>
                                <div class="article-meta">
                                    <span class="status status-<?php echo $article['status']; ?>">
                                        <?php echo ucfirst($article['status']); ?>
                                    </span>
                                    <span class="date"><?php echo format_date($article['updated_at']); ?></span>
                                    <span class="views"><?php echo number_format($article['view_count']); ?> views</span>
                                </div>
                            </div>
                            <div class="article-actions">
                                <a href="edit_article.php?id=<?php echo $article['id']; ?>" class="btn btn-sm">Edit</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="section-footer">
                        <a href="/dashboard" class="btn btn-secondary">View All Articles</a>
                    </div>
                <?php else: ?>
                    <p>You haven't created any articles yet.</p>
                    <?php if (is_editor()): ?>
                        <a href="create_article.php" class="btn btn-primary">Create Your First Article</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="profile-section">
            <div class="card">
                <h2>Recent Activity</h2>
                <?php if (!empty($recent_activity)): ?>
                    <div class="activity-list">
                        <?php foreach ($recent_activity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">📝</div>
                            <div class="activity-details">
                                <div class="activity-description">
                                    <?php echo htmlspecialchars($activity['description'] ?: ucfirst(str_replace('_', ' ', $activity['action']))); ?>
                                </div>
                                <div class="activity-time">
                                    <?php echo format_date($activity['created_at'], 'M j, Y \a\t g:i A'); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No recent activity.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 2rem;
}

.profile-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 3rem;
    padding: 2rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.profile-info {
    display: flex;
    gap: 2rem;
    align-items: center;
}

.profile-avatar {
    flex-shrink: 0;
}

.avatar-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #3498db;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: bold;
}

.profile-details h1 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
}

.profile-details .username {
    color: #666;
    margin: 0 0 0.5rem 0;
    font-weight: 500;
}

.profile-details .email {
    color: #666;
    margin: 0 0 1rem 0;
}

.profile-details .bio {
    color: #444;
    margin: 0 0 1rem 0;
    font-style: italic;
}

.profile-details .member-since {
    color: #888;
    font-size: 0.9rem;
    margin: 0;
}

.profile-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #3498db;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
}

.profile-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.profile-section {
    margin-bottom: 2rem;
}

.articles-list {
    margin: 1rem 0;
}

.article-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
}

.article-item:last-child {
    border-bottom: none;
}

.article-info h4 {
    margin: 0 0 0.5rem 0;
}

.article-info h4 a {
    color: #2c3e50;
    text-decoration: none;
}

.article-info h4 a:hover {
    color: #3498db;
}

.article-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
    color: #666;
}

.status {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-published {
    background: #d4edda;
    color: #155724;
}

.status-draft {
    background: #fff3cd;
    color: #856404;
}

.activity-list {
    margin: 1rem 0;
}

.activity-item {
    display: flex;
    gap: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #eee;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    font-size: 1.2rem;
    flex-shrink: 0;
}

.activity-details {
    flex: 1;
}

.activity-description {
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.activity-time {
    font-size: 0.85rem;
    color: #666;
}

.section-footer {
    text-align: center;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        gap: 2rem;
    }
    
    .profile-info {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-content {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include "../../includes/footer.php";; ?>
