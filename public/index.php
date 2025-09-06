<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$page_title = 'Home';

// Get recent wiki articles (simplified)
$stmt = $pdo->query("
    SELECT wa.*, u.username, u.display_name 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id
    WHERE wa.status = 'published'
    ORDER BY wa.published_at DESC 
    LIMIT 6
");
$recent_articles = $stmt->fetchAll();

// Get recent user posts (if logged in)
$recent_posts = [];
if (is_logged_in()) {
    $stmt = $pdo->query("
        SELECT up.*, u.username, u.display_name 
        FROM user_posts up 
        JOIN users u ON up.user_id = u.id 
        WHERE up.is_public = 1 
        ORDER BY up.created_at DESC 
        LIMIT 5
    ");
    $recent_posts = $stmt->fetchAll();
}

include 'includes/header.php';
?>

<div class="homepage-container">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1>Welcome to <?php echo SITE_NAME; ?></h1>
            <p>Your comprehensive source for Islamic knowledge, community discussions, and educational resources.</p>
            <?php if (!is_logged_in()): ?>
                <div class="hero-actions">
                    <a href="/register" class="btn btn-primary">Get Started</a>
                    <a href="/login" class="btn btn-secondary">Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
        <!-- Left Column: Recent Posts & Feed -->
        <div class="left-column">
            <?php if (is_logged_in() && !empty($recent_posts)): ?>
                <div class="content-section">
                    <h2><i class="fas fa-newspaper"></i> Recent Community Posts</h2>
                    <div class="posts-feed">
                        <?php foreach ($recent_posts as $post): ?>
                            <div class="post-card">
                                <div class="post-header">
                                    <div class="post-author">
                                        <img src="/assets/images/default-avatar.png" alt="Avatar" class="author-avatar" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMjAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkMxNC4yMDkxIDEyIDE2IDEwLjIwOTEgMTYgOEMxNiA1Ljc5MDg2IDE0LjIwOTEgNCAxMiA0QzkuNzkwODYgNCA4IDUuNzkwODYgOCA4QzggMTAuMjA5MSA5Ljc5MDg2IDEyIDEyIDEyWiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTEyIDE0QzguNjkxMTcgMTQgNiAxNi42OTExNyA2IDIwSDIwQzIwIDE2LjY5MTE3IDE3LjMwODggMTQgMTIgMTRaIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4KPC9zdmc+';">
                                        <div class="author-info">
                                            <span class="author-name"><?php echo htmlspecialchars($post['display_name'] ?: $post['username']); ?></span>
                                            <span class="post-time"><?php echo format_date($post['created_at']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="post-content">
                                    <p><?php echo htmlspecialchars(truncate_text($post['content'], 200)); ?></p>
                                </div>
                                <div class="post-actions">
                                    <span class="post-stats">
                                        <i class="fas fa-heart"></i> <?php echo $post['likes_count']; ?>
                                        <i class="fas fa-comment"></i> <?php echo $post['comments_count']; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="section-footer">
                        <a href="/feed" class="btn btn-outline">View All Posts</a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recent Wiki Articles -->
            <div class="content-section">
                <h2><i class="fas fa-book"></i> Recent Wiki Articles</h2>
                <div class="articles-list">
                    <?php foreach ($recent_articles as $article): ?>
                        <div class="article-card">
                            <div class="article-meta">
                                <span class="article-date"><?php echo format_date($article['published_at']); ?></span>
                            </div>
                            <h3><a href="/wiki/<?php echo $article['slug']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h3>
                            <p><?php echo htmlspecialchars(truncate_text($article['excerpt'] ?: strip_tags($article['content']), 120)); ?></p>
                            <div class="article-footer">
                                <span class="article-author">By <?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?></span>
                                <span class="article-views"><?php echo number_format($article['view_count']); ?> views</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="section-footer">
                    <a href="/wiki" class="btn btn-outline">Browse All Articles</a>
                </div>
            </div>
        </div>

        <!-- Right Column: Quick Actions & Stats -->
        <div class="right-column">
            <!-- Quick Actions -->
            <div class="content-section">
                <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                <div class="quick-actions">
                    <?php if (is_logged_in()): ?>
                        <a href="/create_post" class="quick-action">
                            <i class="fas fa-plus"></i>
                            <span>Create Post</span>
                        </a>
                        <a href="/create_article" class="quick-action">
                            <i class="fas fa-edit"></i>
                            <span>Write Article</span>
                        </a>
                        <a href="/dashboard" class="quick-action">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="/feed" class="quick-action">
                            <i class="fas fa-newspaper"></i>
                            <span>View Feed</span>
                        </a>
                    <?php else: ?>
                        <a href="/register" class="quick-action">
                            <i class="fas fa-user-plus"></i>
                            <span>Join Community</span>
                        </a>
                        <a href="/wiki" class="quick-action">
                            <i class="fas fa-book"></i>
                            <span>Browse Articles</span>
                        </a>
                        <a href="/login" class="quick-action">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Community Stats -->
            <div class="content-section">
                <h2><i class="fas fa-chart-bar"></i> Community</h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $pdo->query("SELECT COUNT(*) FROM wiki_articles WHERE status = 'published'")->fetchColumn(); ?></span>
                        <span class="stat-label">Articles</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(); ?></span>
                        <span class="stat-label">Members</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $pdo->query("SELECT COUNT(*) FROM user_posts WHERE is_public = 1")->fetchColumn(); ?></span>
                        <span class="stat-label">Posts</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

