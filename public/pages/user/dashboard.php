<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/analytics.php';

$page_title = 'Dashboard';
require_login();

$current_user = get_user($_SESSION['user_id']);

// Get user's following list for personalized feed
$stmt = $pdo->prepare("
    SELECT uf.following_id, u.username, u.display_name, u.avatar
    FROM user_follows uf
    JOIN users u ON uf.following_id = u.id
    WHERE uf.follower_id = ?
    ORDER BY uf.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$following = $stmt->fetchAll();

// Get personalized feed content
$feed_items = [];

// Get recent posts from followed users
if (!empty($following)) {
    $following_ids = array_column($following, 'following_id');
    $placeholders = str_repeat('?,', count($following_ids) - 1) . '?';
    
    $stmt = $pdo->prepare("
        SELECT up.*, u.username, u.display_name, u.avatar, 'post' as content_type,
               COALESCE(like_counts.likes_count, 0) as likes_count,
               COALESCE(comment_counts.comments_count, 0) as comments_count,
               COALESCE(share_counts.shares_count, 0) as shares_count
        FROM user_posts up
        JOIN users u ON up.user_id = u.id
        LEFT JOIN (
            SELECT post_id, COUNT(*) as likes_count 
            FROM post_interactions 
            WHERE interaction_type = 'like' 
            GROUP BY post_id
        ) like_counts ON up.id = like_counts.post_id
        LEFT JOIN (
            SELECT post_id, COUNT(*) as comments_count 
            FROM post_comments 
            GROUP BY post_id
        ) comment_counts ON up.id = comment_counts.post_id
        LEFT JOIN (
            SELECT post_id, COUNT(*) as shares_count 
            FROM post_interactions 
            WHERE interaction_type = 'share' 
            GROUP BY post_id
        ) share_counts ON up.id = share_counts.post_id
        WHERE up.user_id IN ($placeholders) AND up.is_public = 1
        ORDER BY up.created_at DESC
        LIMIT 10
    ");
    $stmt->execute($following_ids);
    $followed_posts = $stmt->fetchAll();
    
    foreach ($followed_posts as $post) {
        $feed_items[] = $post;
    }
}

// Get recent public posts from all users (excluding current user)
$stmt = $pdo->prepare("
    SELECT up.*, u.username, u.display_name, u.avatar, 'post' as content_type,
           COALESCE(like_counts.likes_count, 0) as likes_count,
           COALESCE(comment_counts.comments_count, 0) as comments_count,
           COALESCE(share_counts.shares_count, 0) as shares_count
    FROM user_posts up
    JOIN users u ON up.user_id = u.id
    LEFT JOIN (
        SELECT post_id, COUNT(*) as likes_count 
        FROM post_interactions 
        WHERE interaction_type = 'like' 
        GROUP BY post_id
    ) like_counts ON up.id = like_counts.post_id
    LEFT JOIN (
        SELECT post_id, COUNT(*) as comments_count 
        FROM post_comments 
        GROUP BY post_id
    ) comment_counts ON up.id = comment_counts.post_id
    LEFT JOIN (
        SELECT post_id, COUNT(*) as shares_count 
        FROM post_interactions 
        WHERE interaction_type = 'share' 
        GROUP BY post_id
    ) share_counts ON up.id = share_counts.post_id
    WHERE up.is_public = 1 AND up.user_id != ?
    ORDER BY up.created_at DESC
    LIMIT 15
");
$stmt->execute([$_SESSION['user_id']]);
$recent_posts = $stmt->fetchAll();

// Get recent published articles
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name, u.avatar, cc.name as category_name, 'article' as content_type
        FROM wiki_articles wa 
        JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id
    WHERE wa.status = 'published'
    ORDER BY wa.published_at DESC
    LIMIT 10
");
$stmt->execute();
$recent_articles = $stmt->fetchAll();

// Get featured articles
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name, u.avatar, cc.name as category_name, 'featured_article' as content_type
    FROM wiki_articles wa
    JOIN users u ON wa.author_id = u.id
    LEFT JOIN content_categories cc ON wa.category_id = cc.id
    WHERE wa.status = 'published' AND wa.is_featured = 1
    ORDER BY wa.published_at DESC
    LIMIT 5
");
$stmt->execute();
$featured_articles = $stmt->fetchAll();

// Get user's own content
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name, u.avatar, cc.name as category_name, 'my_article' as content_type
    FROM wiki_articles wa
    JOIN users u ON wa.author_id = u.id
    LEFT JOIN content_categories cc ON wa.category_id = cc.id
    WHERE wa.author_id = ?
        ORDER BY wa.created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$my_articles = $stmt->fetchAll();

// Get user's posts
$stmt = $pdo->prepare("
    SELECT up.*, u.username, u.display_name, u.avatar, 'my_post' as content_type
    FROM user_posts up
    JOIN users u ON up.user_id = u.id
    WHERE up.user_id = ?
    ORDER BY up.created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$my_posts = $stmt->fetchAll();

// Get user's watchlist
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name, u.avatar, cc.name as category_name, 'watchlist' as content_type
    FROM user_watchlists uw
    JOIN wiki_articles wa ON uw.article_id = wa.id
    JOIN users u ON wa.author_id = u.id
    LEFT JOIN content_categories cc ON wa.category_id = cc.id
    WHERE uw.user_id = ? AND wa.status = 'published'
    ORDER BY uw.created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$watchlist_articles = $stmt->fetchAll();

// Get trending content
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name, u.avatar, cc.name as category_name, 'trending' as content_type
    FROM wiki_articles wa
    JOIN users u ON wa.author_id = u.id
    LEFT JOIN content_categories cc ON wa.category_id = cc.id
    WHERE wa.status = 'published'
    ORDER BY wa.view_count DESC, wa.published_at DESC
    LIMIT 5
    ");
    $stmt->execute();
$trending_articles = $stmt->fetchAll();

// Get user statistics
$user_stats = [
    'articles_count' => count($my_articles),
    'posts_count' => count($my_posts),
    'following_count' => count($following),
    'watchlist_count' => count($watchlist_articles)
];

// Get follower count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_follows WHERE following_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_stats['followers_count'] = $stmt->fetchColumn();

// Get total views on user's articles
$stmt = $pdo->prepare("SELECT SUM(view_count) FROM wiki_articles WHERE author_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_stats['total_views'] = $stmt->fetchColumn() ?: 0;

include "../../includes/header.php";
?>

<div class="dashboard-container">
    <div class="dashboard-layout">
        <!-- Left Sidebar -->
        <div class="dashboard-sidebar">
            <!-- User Profile Card -->
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <img src="/assets/images/default-avatar.png" alt="Profile" 
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iMzAiIGZpbGw9IiM0Mjg1RjQiLz4KPHN2ZyB4PSIxNSIgeT0iMTUiIHdpZHRoPSIzMCIgaGVpZ2h0PSIzMCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIj4KPHBhdGggZD0iTTEyIDEyQzE0LjIwOTEgMTIgMTYgMTAuMjA5MSAxNiA4QzE2IDUuNzkwODYgMTQuMjA5MSA0IDEyIDRDOS43OTA4NiA0IDggNS43OTA4NiA4IDhDOCAxMC4yMDkxIDkuNzkwNiAxMiAxMiAxMloiIGZpbGw9IndoaXRlIi8+CjxwYXRoIGQ9Ik0xMiAxNEM4LjY5MTE3IDE0IDYgMTYuNjkxMTcgNiAyMEgxOEMxOCAxNi42OTExNyAxNS4zMDg4IDE0IDEyIDE0WiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cjwvc3ZnPgo='">
                    </div>
                    <div class="profile-info">
                        <h3><?php echo htmlspecialchars($current_user['display_name'] ?: $current_user['username']); ?></h3>
                        <p>@<?php echo htmlspecialchars($current_user['username']); ?></p>
                    </div>
                </div>
                <div class="profile-stats">
                    <div class="stat">
                        <span class="stat-number"><?php echo number_format($user_stats['articles_count']); ?></span>
                        <span class="stat-label">Articles</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number"><?php echo number_format($user_stats['posts_count']); ?></span>
                        <span class="stat-label">Posts</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number"><?php echo number_format($user_stats['followers_count']); ?></span>
                        <span class="stat-label">Followers</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions-card">
                <h4>Quick Actions</h4>
                <div class="action-buttons">
                    <a href="/pages/social/create_post.php" class="action-btn">
                        <i class="fas fa-edit"></i>
                        <span>Create Post</span>
                    </a>
                    <?php if (is_editor()): ?>
                    <a href="/pages/wiki/create_article.php" class="action-btn">
                        <i class="fas fa-file-alt"></i>
                        <span>Write Article</span>
                    </a>
                    <?php endif; ?>
                    <a href="/search" class="action-btn">
                        <i class="fas fa-search"></i>
                        <span>Search</span>
                    </a>
                    <a href="/pages/user/settings.php" class="action-btn">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>
        </div>
        
            <!-- Trending Topics -->
            <div class="trending-card">
                <h4>Trending</h4>
                <div class="trending-list">
                    <?php foreach ($trending_articles as $article): ?>
                    <div class="trending-item">
                        <a href="/wiki/<?php echo $article['slug']; ?>">
                            <h5><?php echo htmlspecialchars($article['title']); ?></h5>
                            <p><?php echo number_format($article['view_count']); ?> views</p>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Main Feed -->
        <div class="dashboard-main">
            <!-- Feed Header -->
            <div class="feed-header">
                <h1>Your Feed</h1>
                <div class="feed-filters">
                    <button class="filter-btn" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="posts">Posts</button>
                    <button class="filter-btn" data-filter="articles">Articles</button>
                    <button class="filter-btn" data-filter="following">Following</button>
        </div>
    </div>
    
            <!-- Feed Content -->
            <div class="feed-content">
                <?php
                // Get following content (posts and articles from followed users)
                $following_content = [];
                $following_ids = array_column($following, 'following_id');
                
                if (!empty($following_ids)) {
                    $placeholders = str_repeat('?,', count($following_ids) - 1) . '?';
                    
                    // Get posts from followed users
                    $stmt = $pdo->prepare("
                        SELECT up.*, u.username, u.display_name, u.avatar, 'post' as content_type,
                               COALESCE(like_counts.likes_count, 0) as likes_count,
                               COALESCE(comment_counts.comments_count, 0) as comments_count,
                               COALESCE(share_counts.shares_count, 0) as shares_count
                        FROM user_posts up
                        JOIN users u ON up.user_id = u.id
                        LEFT JOIN (
                            SELECT post_id, COUNT(*) as likes_count 
                            FROM post_interactions 
                            WHERE interaction_type = 'like' 
                            GROUP BY post_id
                        ) like_counts ON up.id = like_counts.post_id
                        LEFT JOIN (
                            SELECT post_id, COUNT(*) as comments_count 
                            FROM post_comments 
                            GROUP BY post_id
                        ) comment_counts ON up.id = comment_counts.post_id
                        LEFT JOIN (
                            SELECT post_id, COUNT(*) as shares_count 
                            FROM post_interactions 
                            WHERE interaction_type = 'share' 
                            GROUP BY post_id
                        ) share_counts ON up.id = share_counts.post_id
                        WHERE up.user_id IN ($placeholders) AND up.is_public = 1
                        ORDER BY up.created_at DESC
                        LIMIT 20
                    ");
                    $stmt->execute($following_ids);
                    $following_posts = $stmt->fetchAll();
                    
                    // Get articles from followed users
                    $stmt = $pdo->prepare("
                        SELECT wa.*, u.username, u.display_name, u.avatar, cc.name as category_name, 'article' as content_type
                        FROM wiki_articles wa
                        JOIN users u ON wa.author_id = u.id
                        LEFT JOIN content_categories cc ON wa.category_id = cc.id
                        WHERE wa.author_id IN ($placeholders) AND wa.status = 'published'
                        ORDER BY wa.published_at DESC
                        LIMIT 20
                    ");
                    $stmt->execute($following_ids);
                    $following_articles = $stmt->fetchAll();
                    
                    $following_content = array_merge($following_posts, $following_articles);
                }
                
                // Combine all content into one feed, removing duplicates
                $all_content = [];
                
                // Add featured articles
                foreach ($featured_articles as $article) {
                    $article['is_featured'] = true;
                    $all_content[] = $article;
                }
                
                // Add recent posts
                foreach ($recent_posts as $post) {
                    $all_content[] = $post;
                }
                
                // Add recent articles (excluding featured ones to avoid duplicates)
                $featured_ids = array_column($featured_articles, 'id');
                foreach ($recent_articles as $article) {
                    if (!in_array($article['id'], $featured_ids)) {
                        $all_content[] = $article;
                    }
                }
                
                // Sort by creation/publish date
                usort($all_content, function($a, $b) {
                    $date_a = $a['created_at'] ?? $a['published_at'] ?? '1970-01-01';
                    $date_b = $b['created_at'] ?? $b['published_at'] ?? '1970-01-01';
                    return strtotime($date_b) - strtotime($date_a);
                });
                
                usort($following_content, function($a, $b) {
                    $date_a = $a['created_at'] ?? $a['published_at'] ?? '1970-01-01';
                    $date_b = $b['created_at'] ?? $b['published_at'] ?? '1970-01-01';
                    return strtotime($date_b) - strtotime($date_a);
                });
                
                // Debug: Check what content we have
                // echo "<!-- Debug: Following content count: " . count($following_content) . " -->";
                // echo "<!-- Debug: All content count: " . count($all_content) . " -->";
                // echo "<!-- Debug: Recent articles count: " . count($recent_articles) . " -->";
                // echo "<!-- Debug: Featured articles count: " . count($featured_articles) . " -->";
                ?>
                
                <div class="unified-feed">
                    <!-- Following Content (Default) -->
                    <div class="feed-section" id="following-content">
                        <?php if (!empty($following_content)): ?>
                            <?php foreach (array_slice($following_content, 0, 20) as $item): ?>
                            <div class="feed-item" data-type="<?php echo $item['content_type']; ?>" data-filter="following" <?php echo $item['content_type'] === 'post' ? 'data-post-id="' . $item['id'] . '"' : ''; ?>>
                                <?php if ($item['content_type'] === 'post'): ?>
                                <div class="card-header">
                                    <div class="author-info">
                                        <img src="/assets/images/default-avatar.png" alt="User" class="author-avatar">
                                        <div class="author-details">
                                            <a href="/pages/user/user_profile.php?username=<?php echo urlencode($item['username']); ?>" class="author-name">
                                                <?php echo htmlspecialchars($item['display_name'] ?: $item['username']); ?>
                                            </a>
                                            <span class="post-time">
                                                <?php echo format_date($item['created_at']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <span class="content-type-badge">
                                        <?php echo ucfirst($item['content_type']); ?>
                                    </span>
                                </div>
                                <div class="card-content">
                                    <p><?php echo nl2br(htmlspecialchars($item['content'])); ?></p>
                                </div>
                                <?php else: ?>
                                <div class="card-header">
                                    <div class="article-header-left">
                                        <!-- Empty space for alignment -->
                                    </div>
                                    <span class="content-type-badge">
                                        <?php 
                                        if (isset($item['is_featured']) && $item['is_featured']) {
                                            echo 'Featured Article';
                                        } else {
                                            echo ucfirst($item['content_type']);
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="card-content">
                                    <h4><a href="/wiki/<?php echo $item['slug']; ?>"><?php echo htmlspecialchars($item['title']); ?></a></h4>
                                    <p><?php echo htmlspecialchars(truncate_text($item['excerpt'] ?: strip_tags($item['content']), 150)); ?></p>
                                    <?php if (isset($item['category_name']) && $item['category_name']): ?>
                                    <span class="category-tag"><?php echo htmlspecialchars($item['category_name']); ?></span>
        <?php endif; ?>
                                    <div class="article-meta">
                                        <span class="updated-by">Updated by: <a href="/pages/user/user_profile.php?username=<?php echo urlencode($item['username']); ?>"><?php echo htmlspecialchars($item['display_name'] ?: $item['username']); ?></a> on <?php echo format_date($item['updated_at'] ?? $item['published_at']); ?></span>
    </div>
                                </div>
                                <?php endif; ?>
                                <div class="card-footer">
                                    <div class="engagement-stats">
                                        <?php if ($item['content_type'] === 'post'): ?>
                                            <span><i class="fas fa-comment"></i> <?php echo $item['comments_count']; ?></span>
                                            <span><i class="fas fa-heart"></i> <?php echo $item['likes_count']; ?></span>
                                            <span><i class="fas fa-share"></i> <?php echo $item['shares_count']; ?></span>
                                        <?php else: ?>
                                            <span><i class="fas fa-eye"></i> <?php echo number_format($item['view_count']); ?></span>
                                            <span><i class="fas fa-comment"></i> 0</span>
                                            <span><i class="fas fa-heart"></i> 0</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($item['content_type'] === 'post'): ?>
                                    <div class="post-actions">
                                        <button class="action-btn like-btn" data-post-id="<?php echo $item['id']; ?>"><i class="fas fa-heart"></i></button>
                                        <button class="action-btn comment-btn" data-post-id="<?php echo $item['id']; ?>"><i class="fas fa-comment"></i></button>
                                        <button class="action-btn share-btn" data-post-id="<?php echo $item['id']; ?>"><i class="fas fa-share"></i></button>
                                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>You're not following anyone yet. <a href="/search">Find people to follow</a></p>
        </div>
                        <?php endif; ?>
    </div>
                    
                    <!-- All Content -->
                    <div class="feed-section" id="all-content">
                        <?php if (!empty($all_content)): ?>
                            <?php foreach (array_slice($all_content, 0, 20) as $item): ?>
                            <div class="feed-item" data-type="<?php echo $item['content_type']; ?>" data-filter="all" <?php echo $item['content_type'] === 'post' ? 'data-post-id="' . $item['id'] . '"' : ''; ?>>
                                <?php if ($item['content_type'] === 'post'): ?>
                                <div class="card-header">
                                    <div class="author-info">
                                        <img src="/assets/images/default-avatar.png" alt="User" class="author-avatar">
                                        <div class="author-details">
                                            <a href="/pages/user/user_profile.php?username=<?php echo urlencode($item['username']); ?>" class="author-name">
                                                <?php echo htmlspecialchars($item['display_name'] ?: $item['username']); ?>
                                            </a>
                                            <span class="post-time">
                                                <?php echo format_date($item['created_at']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <span class="content-type-badge">
                                        <?php echo ucfirst($item['content_type']); ?>
                                    </span>
                                </div>
                                <div class="card-content">
                                    <p><?php echo nl2br(htmlspecialchars($item['content'])); ?></p>
                                </div>
                                <?php else: ?>
                                <div class="card-header">
                                    <div class="article-header-left">
                                        <!-- Empty space for alignment -->
                                    </div>
                                    <span class="content-type-badge">
                                        <?php 
                                        if (isset($item['is_featured']) && $item['is_featured']) {
                                            echo 'Featured Article';
                                        } else {
                                            echo ucfirst($item['content_type']);
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="card-content">
                                    <h4><a href="/wiki/<?php echo $item['slug']; ?>"><?php echo htmlspecialchars($item['title']); ?></a></h4>
                                    <p><?php echo htmlspecialchars(truncate_text($item['excerpt'] ?: strip_tags($item['content']), 150)); ?></p>
                                    <?php if (isset($item['category_name']) && $item['category_name']): ?>
                                    <span class="category-tag"><?php echo htmlspecialchars($item['category_name']); ?></span>
    <?php endif; ?>
                                    <div class="article-meta">
                                        <span class="updated-by">Updated by: <a href="/pages/user/user_profile.php?username=<?php echo urlencode($item['username']); ?>"><?php echo htmlspecialchars($item['display_name'] ?: $item['username']); ?></a> on <?php echo format_date($item['updated_at'] ?? $item['published_at']); ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div class="card-footer">
                                    <div class="engagement-stats">
                                        <?php if ($item['content_type'] === 'post'): ?>
                                            <span><i class="fas fa-comment"></i> <?php echo $item['comments_count']; ?></span>
                                            <span><i class="fas fa-heart"></i> <?php echo $item['likes_count']; ?></span>
                                            <span><i class="fas fa-share"></i> <?php echo $item['shares_count']; ?></span>
                                        <?php else: ?>
                                            <span><i class="fas fa-eye"></i> <?php echo number_format($item['view_count']); ?></span>
                                            <span><i class="fas fa-comment"></i> 0</span>
                                            <span><i class="fas fa-heart"></i> 0</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($item['content_type'] === 'post'): ?>
                                    <div class="post-actions">
                                        <button class="action-btn like-btn" data-post-id="<?php echo $item['id']; ?>"><i class="fas fa-heart"></i></button>
                                        <button class="action-btn comment-btn" data-post-id="<?php echo $item['id']; ?>"><i class="fas fa-comment"></i></button>
                                        <button class="action-btn share-btn" data-post-id="<?php echo $item['id']; ?>"><i class="fas fa-share"></i></button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No content available. <a href="/pages/wiki/create_article.php">Create the first article</a></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="dashboard-rightbar">
            <!-- My Content -->
            <div class="my-content-card">
                <h4>My Content</h4>
                <div class="content-tabs">
                    <button class="tab-btn active" data-tab="articles">Articles</button>
                    <button class="tab-btn" data-tab="posts">Posts</button>
                </div>
                
                <div class="tab-content" id="articles-tab">
                    <?php if (!empty($my_articles)): ?>
                    <div class="content-list">
                        <?php foreach ($my_articles as $article): ?>
                        <div class="content-item">
                            <h5><a href="/wiki/<?php echo $article['slug']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h5>
                            <p class="item-meta">
                                <span class="status-<?php echo $article['status']; ?>"><?php echo ucfirst($article['status']); ?></span>
                                • <?php echo format_date($article['created_at']); ?>
                            </p>
                </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="empty-state">No articles yet. <a href="/pages/wiki/create_article.php">Create your first article</a></p>
                    <?php endif; ?>
                </div>
                
                <div class="tab-content" id="posts-tab" style="display: none;">
                    <?php if (!empty($my_posts)): ?>
                    <div class="content-list">
                        <?php foreach ($my_posts as $post): ?>
                        <div class="content-item">
                            <p><?php echo htmlspecialchars(truncate_text($post['content'], 80)); ?></p>
                            <p class="item-meta"><?php echo format_date($post['created_at']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
                    <?php else: ?>
                    <p class="empty-state">No posts yet. <a href="/create_post">Create your first post</a></p>
                    <?php endif; ?>
    </div>
            </div>

            <!-- Watchlist -->
            <div class="watchlist-card">
                <div class="card-header">
                    <h4>My Watchlist</h4>
                    <a href="/pages/user/watchlist.php" class="view-all-link">View All</a>
                </div>
                <?php if (!empty($watchlist_articles)): ?>
                <div class="watchlist-list">
                    <?php foreach ($watchlist_articles as $article): ?>
                    <div class="watchlist-item">
                        <div class="item-content">
                            <h5><a href="/wiki/<?php echo $article['slug']; ?>"><?php echo htmlspecialchars($article['title']); ?></a></h5>
                            <p class="item-meta"><?php echo format_date($article['updated_at']); ?></p>
                        </div>
                        <button class="unwatch-btn" onclick="unwatchArticle(<?php echo $article['id']; ?>)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="empty-state">No articles in watchlist. <a href="/search">Find articles to watch</a></p>
    <?php endif; ?>
            </div>

            <!-- Following -->
            <div class="following-card">
                <div class="card-header">
                    <h4>Following</h4>
                    <a href="/pages/social/friends.php" class="view-all-link">View All</a>
                </div>
                <?php if (!empty($following)): ?>
                <div class="following-list">
                    <?php foreach (array_slice($following, 0, 5) as $user): ?>
                    <div class="following-item">
                        <img src="/assets/images/default-avatar.png" alt="User" class="user-avatar">
                        <div class="user-info">
                            <a href="/pages/user/user_profile.php?username=<?php echo urlencode($user['username']); ?>" class="user-name">
                                <?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?>
                            </a>
                            <span class="user-handle">@<?php echo htmlspecialchars($user['username']); ?></span>
                        </div>
                        <button class="unfollow-btn" onclick="unfollowUser(<?php echo $user['following_id']; ?>)">
                            <i class="fas fa-user-times"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="empty-state">Not following anyone yet. <a href="/search">Find people to follow</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Import Google Fonts for better typography */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

/* CSS Variables for consistent theming */
:root {
    --primary-color: #6366f1;
    --primary-hover: #4f46e5;
    --secondary-color: #8b5cf6;
    --accent-color: #06b6d4;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --error-color: #ef4444;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --text-muted: #9ca3af;
    --bg-primary: #ffffff;
    --bg-secondary: #f9fafb;
    --bg-tertiary: #f3f4f6;
    --border-color: #e5e7eb;
    --border-light: #f3f4f6;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    --radius-sm: 6px;
    --radius-md: 8px;
    --radius-lg: 12px;
    --radius-xl: 16px;
    --radius-full: 9999px;
}

/* Global Styles */
* {
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    color: var(--text-primary);
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
}

.dashboard-container {
    width: 100%;
    margin: 0;
    padding: 24px;
    min-height: 100vh;
}

.dashboard-layout {
    display: grid;
    grid-template-columns: 320px 1fr 300px;
    gap: 32px;
    align-items: start;
    max-width: 1600px;
    margin: 0 auto;
}

/* Enhanced Sidebar Styles */
.dashboard-sidebar {
    display: flex;
    flex-direction: column;
    gap: 32px;
}

.profile-card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    padding: 24px;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--border-light);
    position: relative;
    overflow: hidden;
}

.profile-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
}

.profile-avatar {
    position: relative;
}

.profile-avatar img {
    width: 60px;
    height: 60px;
    border-radius: var(--radius-full);
    object-fit: cover;
    border: 3px solid var(--bg-primary);
    box-shadow: var(--shadow-md);
}

.profile-avatar::after {
    content: '';
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 16px;
    height: 16px;
    background: var(--success-color);
    border: 3px solid var(--bg-primary);
    border-radius: var(--radius-full);
}

.profile-info h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1.2;
}

.profile-info p {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
    font-weight: 500;
}

.profile-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--border-light);
}

.stat {
    text-align: center;
    padding: 12px;
    background: var(--bg-secondary);
    border-radius: var(--radius-md);
    transition: all 0.2s ease;
}

.stat:hover {
    background: var(--bg-tertiary);
    transform: translateY(-1px);
}

.stat-number {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 0.8rem;
    color: var(--text-secondary);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Enhanced Card Styles */
.quick-actions-card,
.trending-card,
.my-content-card,
.watchlist-card,
.following-card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    padding: 24px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-light);
    transition: all 0.2s ease;
}

.quick-actions-card:hover,
.trending-card:hover,
.my-content-card:hover,
.watchlist-card:hover,
.following-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.quick-actions-card h4,
.trending-card h4,
.my-content-card h4,
.watchlist-card h4,
.following-card h4 {
    margin: 0 0 20px 0;
    color: var(--text-primary);
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.quick-actions-card h4::before,
.trending-card h4::before,
.my-content-card h4::before,
.watchlist-card h4::before,
.following-card h4::before {
    content: '';
    width: 4px;
    height: 20px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: var(--radius-sm);
    flex-shrink: 0;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: var(--bg-secondary);
    border: none;
    border-radius: var(--radius-lg);
    color: var(--text-primary);
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

.action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s ease;
}

.action-btn:hover::before {
    left: 100%;
}

.action-btn:hover {
    background: var(--primary-color);
    color: white;
    transform: translateX(4px);
    box-shadow: var(--shadow-md);
}

.action-btn i {
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
}

/* Enhanced Main Feed Styles */
.dashboard-main {
    min-height: 600px;
}

.feed-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
    padding: 24px;
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-light);
}

.feed-header h1 {
    margin: 0;
    color: var(--text-primary);
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.feed-filters {
    display: flex;
    gap: 8px;
    background: var(--bg-secondary);
    padding: 4px;
    border-radius: var(--radius-full);
}

.filter-btn {
    padding: 10px 20px;
    border: none;
    background: transparent;
    border-radius: var(--radius-full);
    color: var(--text-secondary);
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.filter-btn.active,
.filter-btn:hover {
    background: var(--primary-color);
    color: white;
    box-shadow: var(--shadow-sm);
}

.feed-section {
    margin-bottom: 60px;
}

.feed-section h3 {
    margin: 0 0 24px 0;
    color: var(--text-primary);
    font-size: 1.3rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
}

.feed-section h3::before {
    content: '';
    width: 4px;
    height: 24px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: var(--radius-sm);
}

.unified-feed {
    display: flex;
    flex-direction: column;
    gap: 32px;
}

.feed-item {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    padding: 24px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-light);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    margin-bottom: 8px;
}

.feed-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.feed-item:hover::before {
    transform: scaleX(1);
}

.feed-item:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
}

/* Article meta styling */
.article-meta {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid var(--border-light);
}

.updated-by {
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.updated-by a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.updated-by a:hover {
    text-decoration: underline;
}

/* Comment Modal Styles */
.comment-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.comment-modal-content {
    background-color: var(--bg-primary);
    margin: 5% auto;
    padding: 0;
    border-radius: var(--radius-xl);
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
    box-shadow: var(--shadow-xl);
    border: 1px solid var(--border-light);
}

.comment-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-light);
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: var(--radius-xl) var(--radius-xl) 0 0;
}

.comment-modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
}

.close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-sm);
    transition: background-color 0.2s;
}

.close-btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.comment-modal-body {
    padding: 24px;
    overflow-y: auto;
    flex: 1;
}

.comment-form {
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border-light);
}

.comment-form textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    font-family: inherit;
    font-size: 0.95rem;
    resize: vertical;
    min-height: 80px;
    margin-bottom: 12px;
    transition: border-color 0.2s;
}

.comment-form textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.comment-form .btn {
    padding: 8px 16px;
    font-size: 0.9rem;
}

.comments-list {
    max-height: 400px;
    overflow-y: auto;
}

.comment-item {
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-light);
}

.comment-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.comment-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.comment-avatar {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-full);
    object-fit: cover;
}

.comment-author strong {
    color: var(--text-primary);
    font-size: 0.9rem;
}

.comment-time {
    color: var(--text-secondary);
    font-size: 0.8rem;
    margin-left: 8px;
}

.comment-content {
    color: var(--text-primary);
    line-height: 1.5;
    margin-left: 44px;
    white-space: pre-wrap;
}

.comment-actions {
    margin-left: 44px;
    margin-top: 8px;
}

.reply-btn {
    background: none;
    border: none;
    color: var(--primary-color);
    font-size: 0.8rem;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: var(--radius-sm);
    transition: background-color 0.2s;
}

.reply-btn:hover {
    background-color: var(--bg-secondary);
}

.comment-replies {
    margin-left: 44px;
    margin-top: 12px;
    padding-left: 16px;
    border-left: 2px solid var(--border-light);
}

.reply-item {
    margin-bottom: 12px;
}

.reply-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}

.reply-avatar {
    width: 24px;
    height: 24px;
    border-radius: var(--radius-full);
    object-fit: cover;
}

.reply-author strong {
    color: var(--text-primary);
    font-size: 0.8rem;
}

.reply-time {
    color: var(--text-secondary);
    font-size: 0.75rem;
    margin-left: 6px;
}

.reply-content {
    color: var(--text-primary);
    line-height: 1.4;
    margin-left: 32px;
    white-space: pre-wrap;
    font-size: 0.9rem;
}

.loading, .error, .no-comments {
    text-align: center;
    padding: 40px 20px;
    color: var(--text-secondary);
}

.error {
    color: var(--error-color);
}

/* Enhanced Card Styles */
.featured-card,
.post-card,
.article-card {
    background: var(--bg-primary);
    border-radius: var(--radius-xl);
    padding: 24px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-light);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.featured-card::before,
.post-card::before,
.article-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.featured-card:hover::before,
.post-card:hover::before,
.article-card:hover::before {
    transform: scaleX(1);
}

.featured-card:hover,
.post-card:hover,
.article-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.author-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

/* For posts - avatar to the left of username */
.feed-item[data-type="post"] .author-info {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    flex: 1;
    flex-direction: row !important;
}

.feed-item[data-type="post"] .author-avatar {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-full);
    object-fit: cover;
    flex-shrink: 0;
    background-color: #f0f0f0;
}

.feed-item[data-type="post"] .author-details {
    display: flex !important;
    flex-direction: column !important;
    gap: 4px;
    flex: 1;
}

.feed-item[data-type="post"] .author-name {
    font-weight: 600;
    color: var(--text-primary);
    text-decoration: none;
    font-size: 0.95rem;
    line-height: 1.2;
}

.feed-item[data-type="post"] .author-name:hover {
    color: var(--primary-color);
    text-decoration: underline;
}

.feed-item[data-type="post"] .post-time {
    font-size: 0.875rem;
    color: var(--text-secondary);
}

/* Article header layout - badge on the right */
.feed-item[data-type="article"] .card-header,
.feed-item[data-type="featured_article"] .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.article-header-left {
    flex: 1;
}

.author-avatar {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-full);
    object-fit: cover;
    border: 2px solid var(--border-light);
    box-shadow: var(--shadow-sm);
}

.author-details {
    display: flex;
    flex-direction: column;
}

/* Override for posts to ensure horizontal layout */
.feed-item[data-type="post"] .author-info {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    flex: 1;
}

.author-name {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.95rem;
    line-height: 1.2;
    text-decoration: none;
    transition: color 0.2s ease;
}

.author-name:hover {
    color: var(--primary-color);
}

.post-time {
    font-size: 0.8rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.content-type-badge {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 6px 12px;
    border-radius: var(--radius-full);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: var(--shadow-sm);
}

.card-content h4 {
    margin: 0 0 12px 0;
    color: var(--text-primary);
    font-size: 1.2rem;
    font-weight: 600;
    line-height: 1.3;
}

.card-content h4 a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s ease;
}

.card-content h4 a:hover {
    color: var(--primary-color);
}

.card-content p {
    margin: 0 0 16px 0;
    color: var(--text-secondary);
    line-height: 1.6;
    font-size: 0.95rem;
}

.category-tag {
    display: inline-block;
    background: var(--accent-color);
    color: white;
    padding: 6px 12px;
    border-radius: var(--radius-full);
    font-size: 0.8rem;
    font-weight: 500;
    margin-top: 8px;
    box-shadow: var(--shadow-sm);
}

.card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid var(--border-light);
}

.engagement-stats {
    display: flex;
    gap: 20px;
}

.engagement-stats span {
    font-size: 0.9rem;
    color: var(--text-secondary);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
}

.engagement-stats i {
    color: var(--primary-color);
}

.post-actions {
    display: flex;
    gap: 8px;
}

.post-actions .action-btn {
    padding: 8px;
    background: var(--bg-secondary);
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    border-radius: var(--radius-md);
    transition: all 0.2s ease;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.post-actions .action-btn:hover {
    background: var(--primary-color);
    color: white;
    transform: scale(1.1);
}

/* Enhanced Right Sidebar Styles */
.dashboard-rightbar {
    display: flex;
    flex-direction: column;
    gap: 32px;
}

.content-tabs {
    display: flex;
    margin-bottom: 20px;
    background: var(--bg-secondary);
    border-radius: var(--radius-lg);
    padding: 4px;
}

.tab-btn {
    flex: 1;
    padding: 12px 16px;
    border: none;
    background: transparent;
    color: var(--text-secondary);
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    border-radius: var(--radius-md);
    transition: all 0.2s ease;
}

.tab-btn.active {
    background: var(--primary-color);
    color: white;
    box-shadow: var(--shadow-sm);
}

.content-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.content-item {
    padding: 16px 0;
    border-bottom: 1px solid var(--border-light);
    transition: all 0.2s ease;
}

.content-item:hover {
    padding-left: 8px;
}

.content-item:last-child {
    border-bottom: none;
}

.content-item h5 {
    margin: 0 0 6px 0;
    font-size: 0.95rem;
    font-weight: 600;
    line-height: 1.3;
}

.content-item h5 a {
    color: var(--text-primary);
    text-decoration: none;
    transition: color 0.2s ease;
}

.content-item h5 a:hover {
    color: var(--primary-color);
}

.item-meta {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin: 0;
    font-weight: 500;
}

.status-published {
    color: var(--success-color);
    font-weight: 600;
    background: rgba(16, 185, 129, 0.1);
    padding: 2px 8px;
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
}

.status-draft {
    color: var(--warning-color);
    font-weight: 600;
    background: rgba(245, 158, 11, 0.1);
    padding: 2px 8px;
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
}

.empty-state {
    text-align: center;
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin: 24px 0;
    padding: 24px;
    background: var(--bg-secondary);
    border-radius: var(--radius-lg);
}

.empty-state a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
}

.watchlist-list,
.following-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.watchlist-item,
.following-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    transition: all 0.2s ease;
    border-radius: var(--radius-md);
}

.watchlist-item:hover,
.following-item:hover {
    background: var(--bg-secondary);
    padding: 12px;
    margin: 0 -12px;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-full);
    object-fit: cover;
    border: 2px solid var(--border-light);
    box-shadow: var(--shadow-sm);
}

.user-info {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.user-name {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.9rem;
    line-height: 1.2;
}

.user-handle {
    font-size: 0.8rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.trending-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.trending-item {
    padding: 12px 0;
    border-bottom: 1px solid var(--border-light);
    transition: all 0.2s ease;
}

.trending-item:hover {
    padding-left: 8px;
}

.trending-item:last-child {
    border-bottom: none;
}

.trending-item a {
    text-decoration: none;
    color: inherit;
    display: block;
}

.trending-item h5 {
    margin: 0 0 6px 0;
    font-size: 0.9rem;
    color: var(--text-primary);
    font-weight: 600;
    line-height: 1.3;
}

.trending-item p {
    margin: 0;
    font-size: 0.8rem;
    color: var(--text-secondary);
    font-weight: 500;
}

/* New Elements */
.view-all-link {
    color: var(--primary-color);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    padding: 6px 12px;
    border-radius: var(--radius-md);
    transition: all 0.2s ease;
}

.view-all-link:hover {
    background: var(--bg-secondary);
    color: var(--primary-hover);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.watchlist-item,
.following-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    transition: all 0.2s ease;
    border-radius: var(--radius-md);
    position: relative;
}

.watchlist-item:hover,
.following-item:hover {
    background: var(--bg-secondary);
    padding: 12px;
    margin: 0 -12px;
}

.item-content {
    flex: 1;
}

.unwatch-btn,
.unfollow-btn {
    background: var(--error-color);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    opacity: 0;
}

.watchlist-item:hover .unwatch-btn,
.following-item:hover .unfollow-btn {
    opacity: 1;
}

.unwatch-btn:hover,
.unfollow-btn:hover {
    background: #dc2626;
    transform: scale(1.1);
}

.user-name {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.9rem;
    line-height: 1.2;
    text-decoration: none;
    transition: color 0.2s ease;
}

.user-name:hover {
    color: var(--primary-color);
}

/* Trending Visual Enhancement */
.trending-card {
    position: relative;
}

.trending-card::after {
    content: '';
    position: absolute;
    top: 20px;
    right: 20px;
    width: 60px;
    height: 40px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: var(--radius-md);
    opacity: 0.1;
}

.trending-item {
    position: relative;
    padding-left: 20px;
}

.trending-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 20px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: var(--radius-sm);
    opacity: 0.6;
}

.trending-item:nth-child(1)::before { height: 24px; opacity: 1; }
.trending-item:nth-child(2)::before { height: 20px; opacity: 0.8; }
.trending-item:nth-child(3)::before { height: 16px; opacity: 0.6; }
.trending-item:nth-child(4)::before { height: 12px; opacity: 0.4; }
.trending-item:nth-child(5)::before { height: 8px; opacity: 0.2; }

/* Responsive Design */
@media (max-width: 1200px) {
    .dashboard-layout {
        grid-template-columns: 300px 1fr 280px;
        gap: 20px;
    }
}

@media (max-width: 992px) {
    .dashboard-layout {
        grid-template-columns: 1fr;
        gap: 24px;
    }
    
    .dashboard-sidebar,
    .dashboard-rightbar {
        order: 2;
    }
    
    .dashboard-main {
        order: 1;
    }
    
    .featured-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .dashboard-container {
        padding: 16px;
    }
    
    .feed-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
        padding: 20px;
    }
    
    .feed-filters {
        width: 100%;
        justify-content: center;
    }
    
    .profile-stats {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .stat {
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-align: left;
    }
    
    .feed-header h1 {
        font-size: 1.5rem;
    }
}

/* Loading Animation */
@keyframes shimmer {
    0% {
        background-position: -200px 0;
    }
    100% {
        background-position: calc(200px + 100%) 0;
    }
}

.loading {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200px 100%;
    animation: shimmer 1.5s infinite;
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: var(--bg-secondary);
}

::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: var(--radius-full);
}

::-webkit-scrollbar-thumb:hover {
    background: var(--text-muted);
}
</style>

<script>
// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // Remove active class from all buttons and contents
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.style.display = 'none');
            
            // Add active class to clicked button and show target content
            this.classList.add('active');
            document.getElementById(targetTab + '-tab').style.display = 'block';
        });
    });
    
    // Filter functionality
    const filterBtns = document.querySelectorAll('.filter-btn');
    const followingContent = document.getElementById('following-content');
    const allContent = document.getElementById('all-content');
    
    // Save filter state to localStorage
    function saveFilterState(filter) {
        localStorage.setItem('dashboardFilter', filter);
    }
    
    // Get saved filter state from localStorage
    function getSavedFilterState() {
        return localStorage.getItem('dashboardFilter') || 'all';
    }
    
    // Apply filter logic
    function applyFilter(filter) {
        // Remove active class from all buttons
        filterBtns.forEach(b => b.classList.remove('active'));
        
        // Add active class to current filter button
        const currentBtn = document.querySelector(`[data-filter="${filter}"]`);
        if (currentBtn) {
            currentBtn.classList.add('active');
        }
        
        if (filter === 'following') {
            followingContent.style.display = 'block';
            allContent.style.display = 'none';
        } else {
            followingContent.style.display = 'none';
            allContent.style.display = 'block';
            
            // If it's posts or articles filter, apply the filtering
            if (filter === 'posts' || filter === 'articles') {
                const feedItems = allContent.querySelectorAll('.feed-item');
                feedItems.forEach(item => {
                    const itemType = item.dataset.type;
                    
                    if (filter === 'posts' && itemType === 'post') {
                        item.style.display = 'block';
                    } else if (filter === 'articles' && (itemType === 'article' || itemType === 'featured_article')) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            } else if (filter === 'all') {
                // Show all items
                const feedItems = allContent.querySelectorAll('.feed-item');
                feedItems.forEach(item => {
                    item.style.display = 'block';
                });
            }
        }
    }
    
    // Initialize the correct content section on page load
    function initializeContent() {
        const savedFilter = getSavedFilterState();
        applyFilter(savedFilter);
    }
    
    // Initialize on page load
    initializeContent();
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;
            
            // Save filter state and apply it
            saveFilterState(filter);
            applyFilter(filter);
        });
    });
    
    // Post action buttons (like, comment, share)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.like-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.like-btn');
            const postId = btn.dataset.postId;
            const icon = btn.querySelector('i');
            
            // Toggle like state
            btn.classList.toggle('active');
            if (btn.classList.contains('active')) {
                icon.style.color = '#e74c3c';
                likePost(postId);
            } else {
                icon.style.color = '';
                unlikePost(postId);
            }
        } else if (e.target.closest('.comment-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.comment-btn');
            const postId = btn.dataset.postId;
            openCommentModal(postId);
        } else if (e.target.closest('.share-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.share-btn');
            const postId = btn.dataset.postId;
            sharePost(postId);
        }
    });
    
    // Smooth scrolling for better UX
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Global functions for unwatch and unfollow
function unwatchArticle(articleId) {
    if (confirm('Remove this article from your watchlist?')) {
        fetch('/api/ajax/watchlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                article_id: articleId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the item from DOM
                const item = document.querySelector(`[onclick="unwatchArticle(${articleId})"]`).closest('.watchlist-item');
                item.style.opacity = '0';
                setTimeout(() => item.remove(), 300);
                
                // Show success message
                showToast('Article removed from watchlist', 'success');
            } else {
                showToast('Error removing article from watchlist', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error removing article from watchlist', 'error');
        });
    }
}

function unfollowUser(userId) {
    if (confirm('Unfollow this user?')) {
        fetch('/api/ajax/follow_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'unfollow',
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the item from DOM
                const item = document.querySelector(`[onclick="unfollowUser(${userId})"]`).closest('.following-item');
                item.style.opacity = '0';
                setTimeout(() => item.remove(), 300);
                
                // Show success message
                showToast('User unfollowed', 'success');
            } else {
                showToast('Error unfollowing user', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error unfollowing user', 'error');
        });
    }
}

function likePost(postId) {
    fetch('/api/ajax/like_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            post_id: postId,
            action: 'like'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update like button appearance
            const likeBtn = document.querySelector(`[data-post-id="${postId}"].like-btn`);
            if (likeBtn) {
                likeBtn.style.color = '#ef4444';
                likeBtn.classList.add('liked');
            }
            
            // Update like count
            updateLikeCount(postId, 1);
            showToast('Post liked!', 'success');
        } else {
            showToast(data.message || 'Error liking post', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error liking post', 'error');
    });
}

function unlikePost(postId) {
    fetch('/api/ajax/like_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            post_id: postId,
            action: 'unlike'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update like button appearance
            const likeBtn = document.querySelector(`[data-post-id="${postId}"].like-btn`);
            if (likeBtn) {
                likeBtn.style.color = '';
                likeBtn.classList.remove('liked');
            }
            
            // Update like count
            updateLikeCount(postId, -1);
            showToast('Post unliked', 'info');
        } else {
            showToast(data.message || 'Error unliking post', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error unliking post', 'error');
    });
}

function updateLikeCount(postId, change) {
    // Find all like count elements for this post
    const feedItems = document.querySelectorAll(`[data-post-id="${postId}"]`);
    feedItems.forEach(item => {
        const likeCountSpan = item.querySelector('.engagement-stats span i.fa-heart')?.parentElement;
        if (likeCountSpan) {
            const currentCount = parseInt(likeCountSpan.textContent.trim()) || 0;
            const newCount = Math.max(0, currentCount + change);
            likeCountSpan.innerHTML = `<i class="fas fa-heart"></i> ${newCount}`;
        }
    });
}

function openCommentModal(postId) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('commentModal');
    if (!modal) {
        modal = createCommentModal();
        document.body.appendChild(modal);
    }
    
    // Set the post ID
    modal.dataset.postId = postId;
    
    // Load comments for this post
    loadComments(postId);
    
    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function createCommentModal() {
    const modal = document.createElement('div');
    modal.id = 'commentModal';
    modal.className = 'comment-modal';
    modal.innerHTML = `
        <div class="comment-modal-content">
            <div class="comment-modal-header">
                <h3>Comments</h3>
                <button class="close-btn" onclick="closeCommentModal()">&times;</button>
            </div>
            <div class="comment-modal-body">
                <div class="comment-form">
                    <textarea id="commentText" placeholder="Write a comment..." rows="3"></textarea>
                    <button id="submitComment" class="btn btn-primary">Post Comment</button>
                </div>
                <div id="commentsList" class="comments-list">
                    <!-- Comments will be loaded here -->
                </div>
            </div>
        </div>
    `;
    
    // Add event listeners
    modal.querySelector('#submitComment').addEventListener('click', submitComment);
    modal.querySelector('#commentText').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && e.ctrlKey) {
            submitComment();
        }
    });
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeCommentModal();
        }
    });
    
    return modal;
}

function closeCommentModal() {
    const modal = document.getElementById('commentModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function loadComments(postId) {
    const commentsList = document.getElementById('commentsList');
    commentsList.innerHTML = '<div class="loading">Loading comments...</div>';
    
    fetch(`/api/ajax/get_comments.php?post_id=${postId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayComments(data.comments);
            } else {
                commentsList.innerHTML = '<div class="error">Failed to load comments</div>';
            }
        })
        .catch(error => {
            console.error('Error loading comments:', error);
            commentsList.innerHTML = '<div class="error">Error loading comments</div>';
        });
}

function displayComments(comments) {
    const commentsList = document.getElementById('commentsList');
    
    if (comments.length === 0) {
        commentsList.innerHTML = '<div class="no-comments">No comments yet. Be the first to comment!</div>';
        return;
    }
    
    let html = '';
    comments.forEach(comment => {
        html += `
            <div class="comment-item">
                <div class="comment-header">
                    <img src="/assets/images/default-avatar.png" alt="User" class="comment-avatar">
                    <div class="comment-author">
                        <strong>${comment.display_name || comment.username}</strong>
                        <span class="comment-time">${formatCommentTime(comment.created_at)}</span>
                    </div>
                </div>
                <div class="comment-content">${escapeHtml(comment.content)}</div>
                <div class="comment-actions">
                    <button class="reply-btn" onclick="replyToComment(${comment.id})">Reply</button>
                </div>
                ${comment.replies && comment.replies.length > 0 ? displayReplies(comment.replies) : ''}
            </div>
        `;
    });
    
    commentsList.innerHTML = html;
}

function displayReplies(replies) {
    let html = '<div class="comment-replies">';
    replies.forEach(reply => {
        html += `
            <div class="reply-item">
                <div class="reply-header">
                    <img src="/assets/images/default-avatar.png" alt="User" class="reply-avatar">
                    <div class="reply-author">
                        <strong>${reply.display_name || reply.username}</strong>
                        <span class="reply-time">${formatCommentTime(reply.created_at)}</span>
                    </div>
                </div>
                <div class="reply-content">${escapeHtml(reply.content)}</div>
            </div>
        `;
    });
    html += '</div>';
    return html;
}

function submitComment() {
    const postId = document.getElementById('commentModal').dataset.postId;
    const content = document.getElementById('commentText').value.trim();
    
    if (!content) {
        showToast('Please enter a comment', 'error');
        return;
    }
    
    const submitBtn = document.getElementById('submitComment');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Posting...';
    
    fetch('/api/ajax/add_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            post_id: postId,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('commentText').value = '';
            loadComments(postId);
            updateCommentCount(postId, 1);
            showToast('Comment posted!', 'success');
        } else {
            showToast(data.message || 'Failed to post comment', 'error');
        }
    })
    .catch(error => {
        console.error('Error posting comment:', error);
        showToast('Error posting comment', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Post Comment';
    });
}

function replyToComment(commentId) {
    // For now, just focus on the comment textarea
    // In a full implementation, this would create a reply form
    document.getElementById('commentText').focus();
    showToast('Reply feature coming soon!', 'info');
}

function updateCommentCount(postId, change) {
    const feedItems = document.querySelectorAll(`[data-post-id="${postId}"]`);
    feedItems.forEach(item => {
        const commentCountSpan = item.querySelector('.engagement-stats span i.fa-comment')?.parentElement;
        if (commentCountSpan) {
            const currentCount = parseInt(commentCountSpan.textContent.trim()) || 0;
            const newCount = Math.max(0, currentCount + change);
            commentCountSpan.innerHTML = `<i class="fas fa-comment"></i> ${newCount}`;
        }
    });
}

function formatCommentTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'just now';
    if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
    if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
    if (diff < 604800000) return Math.floor(diff / 86400000) + 'd ago';
    
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function sharePost(postId) {
    // For now, just copy the post URL to clipboard
    const postUrl = window.location.origin + '/post/' + postId;
    navigator.clipboard.writeText(postUrl).then(() => {
        showToast('Post link copied to clipboard!', 'success');
    }).catch(() => {
        showToast('Unable to copy link', 'error');
    });
}

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    // Style the toast
    Object.assign(toast.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '12px 20px',
        borderRadius: '8px',
        color: 'white',
        fontWeight: '500',
        zIndex: '10000',
        transform: 'translateX(100%)',
        transition: 'transform 0.3s ease',
        backgroundColor: type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#6366f1'
    });
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>

<?php include "../../includes/footer.php"; ?>
