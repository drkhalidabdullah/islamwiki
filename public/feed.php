<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$page_title = 'Feed';
require_login();

$current_user = get_user($_SESSION['user_id']);
$posts = get_feed_posts($_SESSION['user_id'], 20, 0);

include 'includes/header.php';
?>

<div class="card">
    <h1>Your Feed</h1>
    <p>Latest posts from people you follow</p>
</div>

<?php if (!empty($posts)): ?>
    <?php foreach ($posts as $post): ?>
        <div class="card">
            <div class="post-item">
                <div class="post-header">
                    <div class="post-author">
                        <div class="author-avatar">
                            <?php if (!empty($post['avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($post['avatar']); ?>" alt="Avatar">
                            <?php else: ?>
                                <div class="avatar-circle small">
                                    <?php echo strtoupper(substr($post['display_name'] ?: $post['username'], 0, 2)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="author-info">
                            <a href="/user/<?php echo $post['username']; ?>" class="author-name">
                                <?php echo htmlspecialchars($post['display_name'] ?: $post['username']); ?>
                            </a>
                            <span class="post-time"><?php echo format_date($post['created_at'], 'M j, Y \a\t g:i A'); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="post-content">
                    <p><?php echo $post["parsed_content"] ?? nl2br(htmlspecialchars($post["content"])); ?></p>
                    
                    <?php if ($post['post_type'] == 'image' && !empty($post['media_url'])): ?>
                        <div class="post-media">
                            <img src="<?php echo htmlspecialchars($post['media_url']); ?>" alt="Post image">
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($post['post_type'] == 'article_share' && !empty($post['article_id'])): ?>
                        <div class="article-share">
                            <p><strong>Shared an article</strong></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="post-actions">
                    <button class="action-btn like-btn" data-post-id="<?php echo $post['id']; ?>" data-liked="<?php echo is_post_liked($_SESSION['user_id'], $post['id']) ? 'true' : 'false'; ?>">
                        <span class="icon">❤️</span>
                        <span class="count"><?php echo number_format($post['likes_count']); ?></span>
                    </button>
                    
                    <span class="action-btn">
                        <span class="icon">💬</span>
                        <span class="count"><?php echo number_format($post['comments_count']); ?></span>
                    </span>
                    
                    <span class="action-btn">
                        <span class="icon">📤</span>
                        <span class="count"><?php echo number_format($post['shares_count']); ?></span>
                    </span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card">
        <div class="empty-feed">
            <h3>No posts yet</h3>
            <p>Follow some users to see their posts in your feed, or <a href="/create_post.php">create your first post</a>.</p>
        </div>
    </div>
<?php endif; ?>

<style>
/* Post Items */
.post-item {
    padding: 0;
}

.post-header {
    margin-bottom: 1rem;
}

.post-author {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.author-avatar {
    flex-shrink: 0;
}

.author-avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #3498db;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    font-weight: bold;
}

.avatar-circle.small {
    width: 40px;
    height: 40px;
    font-size: 1rem;
}

.author-info {
    flex: 1;
}

.author-name {
    font-weight: 600;
    color: #2c3e50;
    text-decoration: none;
    display: block;
}

.author-name:hover {
    color: #3498db;
}

.post-time {
    color: #666;
    font-size: 0.9rem;
}

.post-content {
    margin-bottom: 1rem;
}

.post-content p {
    margin: 0;
    line-height: 1.6;
}

.post-media img {
    max-width: 100%;
    border-radius: 8px;
    margin-top: 1rem;
}

.post-actions {
    display: flex;
    gap: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.action-btn:hover {
    background: #f8f9fa;
    color: #2c3e50;
}

.action-btn.liked {
    color: #e74c3c;
}

.empty-feed {
    text-align: center;
    padding: 3rem;
}

.empty-feed h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.empty-feed p {
    color: #666;
    margin-bottom: 1rem;
}

.empty-feed a {
    color: #3498db;
    text-decoration: none;
}

.empty-feed a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .post-actions {
        flex-wrap: wrap;
        gap: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Like/Unlike functionality
    const likeBtns = document.querySelectorAll('.like-btn');
    likeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const isLiked = this.dataset.liked === 'true';
            
            fetch('ajax/like_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    post_id: postId,
                    action: isLiked ? 'unlike' : 'like'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.dataset.liked = isLiked ? 'false' : 'true';
                    this.classList.toggle('liked', !isLiked);
                    
                    // Update like count
                    const countSpan = this.querySelector('.count');
                    if (countSpan) {
                        const currentCount = parseInt(countSpan.textContent.replace(/,/g, ''));
                        const newCount = isLiked ? currentCount - 1 : currentCount + 1;
                        countSpan.textContent = newCount.toLocaleString();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
