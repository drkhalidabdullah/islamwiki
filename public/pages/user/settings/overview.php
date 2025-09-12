<?php
// Get user statistics with error handling
$user_stats = [
    'posts' => 0,
    'articles' => 0,
    'followers' => 0,
    'following' => 0,
    'comments' => 0
];

try {
    // Get user posts count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_posts WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    $user_stats['posts'] = $result['count'] ?? 0;

    // Get user articles count (using author_id instead of created_by)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM wiki_articles WHERE author_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    $user_stats['articles'] = $result['count'] ?? 0;

    // Get followers count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_follows WHERE following_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    $user_stats['followers'] = $result['count'] ?? 0;

    // Get following count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_follows WHERE follower_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    $user_stats['following'] = $result['count'] ?? 0;

    // Get comments count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM post_comments WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    $user_stats['comments'] = $result['count'] ?? 0;

    // Get recent activity
    $stmt = $pdo->prepare("
        SELECT action, description, created_at 
        FROM activity_logs 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_activity = $stmt->fetchAll() ?: [];
    
    // Get recent posts
    $stmt = $pdo->prepare("
        SELECT id, content, created_at 
        FROM user_posts 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_posts = $stmt->fetchAll() ?: [];
    
    // Get recent articles
    $stmt = $pdo->prepare("
        SELECT id, title, created_at 
        FROM wiki_articles 
        WHERE author_id = ? 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_articles = $stmt->fetchAll() ?: [];
    
} catch (Exception $e) {
    // Log error but don't break the page
    error_log("Error fetching user stats: " . $e->getMessage());
    $recent_activity = [];
    $recent_posts = [];
    $recent_articles = [];
}
?>

<div class="settings-overview">
    <div class="overview-header">
        <h2>Account Overview</h2>
        <p>Welcome back, <?php echo htmlspecialchars($current_user['display_name']); ?>! Here's a summary of your account activity.</p>
        
        <div class="account-summary">
            <div class="summary-item">
                <strong>Member since:</strong> <?php echo date('F j, Y', strtotime($current_user['created_at'])); ?>
            </div>
            <div class="summary-item">
                <strong>Last login:</strong> <?php echo $current_user['last_login_at'] ? date('F j, Y g:i A', strtotime($current_user['last_login_at'])) : 'Never'; ?>
            </div>
            <div class="summary-item">
                <strong>Account status:</strong> 
                <span class="status-<?php echo $current_user['is_active'] ? 'active' : 'inactive'; ?>">
                    <?php echo $current_user['is_active'] ? 'Active' : 'Inactive'; ?>
                </span>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="icon-posts"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($user_stats['posts']); ?></h3>
                <p>Posts</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="icon-articles"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($user_stats['articles']); ?></h3>
                <p>Articles</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="icon-followers"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($user_stats['followers']); ?></h3>
                <p>Followers</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="icon-following"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($user_stats['following']); ?></h3>
                <p>Following</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="icon-comments"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($user_stats['comments']); ?></h3>
                <p>Comments</p>
            </div>
        </div>
    </div>

    <div class="overview-sections">
        <div class="overview-section">
            <h3>Quick Actions</h3>
            <div class="quick-actions">
                <a href="?page=profile" class="quick-action">
                    <i class="icon-user"></i>
                    <span>Edit Profile</span>
                </a>
                <a href="?page=security" class="quick-action">
                    <i class="icon-shield"></i>
                    <span>Security Settings</span>
                </a>
                <a href="?page=notifications" class="quick-action">
                    <i class="icon-bell"></i>
                    <span>Notifications</span>
                </a>
                <a href="/pages/social/create_post.php" class="quick-action">
                    <i class="icon-plus"></i>
                    <span>Create Post</span>
                </a>
                <a href="/wiki" class="quick-action">
                    <i class="icon-articles"></i>
                    <span>Browse Wiki</span>
                </a>
                <a href="?page=preferences" class="quick-action">
                    <i class="icon-settings"></i>
                    <span>Preferences</span>
                </a>
                <a href="/pages/user/profile.php" class="quick-action">
                    <i class="icon-user"></i>
                    <span>View Profile</span>
                </a>
            </div>
        </div>

        <div class="overview-section">
            <h3>Recent Posts</h3>
            <div class="recent-content">
                <?php if (empty($recent_posts)): ?>
                    <p class="no-content">No recent posts to display.</p>
                <?php else: ?>
                    <?php foreach ($recent_posts as $post): ?>
                        <div class="content-item">
                            <div class="content-preview">
                                <p><?php echo htmlspecialchars(substr($post['content'], 0, 100)) . (strlen($post['content']) > 100 ? '...' : ''); ?></p>
                                <small><?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="overview-section">
            <h3>Recent Articles</h3>
            <div class="recent-content">
                <?php if (empty($recent_articles)): ?>
                    <p class="no-content">No recent articles to display.</p>
                <?php else: ?>
                    <?php foreach ($recent_articles as $article): ?>
                        <div class="content-item">
                            <div class="content-preview">
                                <h4><?php echo htmlspecialchars($article['title']); ?></h4>
                                <small><?php echo date('M j, Y g:i A', strtotime($article['created_at'])); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="overview-section">
            <h3>Recent Activity</h3>
            <div class="activity-list">
                <?php if (empty($recent_activity)): ?>
                    <p class="no-activity">No recent activity to display.</p>
                <?php else: ?>
                    <?php foreach ($recent_activity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="icon-<?php echo $activity['action']; ?>"></i>
                            </div>
                            <div class="activity-content">
                                <p><?php echo htmlspecialchars($activity['description']); ?></p>
                                <small><?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
