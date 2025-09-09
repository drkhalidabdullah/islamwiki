<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Get username from URL parameter
$username = $_GET['username'] ?? '';
$tab = $_GET['tab'] ?? 'posts';

if (empty($username)) {
    header('Location: index.php');
    exit();
}

// Get user by username
$profile_user = get_user_by_username($username);
if (!$profile_user) {
    header('Location: 404.php');
    exit();
}

// Check if current user can view this profile
$current_user_id = $_SESSION['user_id'] ?? null;
if (!can_view_profile($current_user_id, $profile_user['id'])) {
    header('Location: login.php');
    exit();
}

// Get complete profile data
$profile_data = get_user_profile_complete($profile_user['id']);
$user_stats = get_user_stats($profile_user['id']);

// Check if current user is following this profile
$is_following = false;
if ($current_user_id && $current_user_id != $profile_user['id']) {
    $is_following = is_following($current_user_id, $profile_user['id']);
}

// Get profile content based on tab
$content = [];
switch ($tab) {
    case 'posts':
        $content = get_user_posts_with_markdown($profile_user['id'], 20, 0, true);
        break;
    case 'photos':
        $content = get_user_photos($profile_user['id'], 20, 0, true);
        break;
    case 'events':
        $content = get_user_events($profile_user['id'], 20, 0, true);
        break;
    case 'about':
        $content = [
            'profile' => $profile_data,
            'achievements' => get_user_achievements($profile_user['id'])
        ];
        break;
    case 'activity':
        $stmt = $pdo->prepare("
            SELECT * FROM activity_logs 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 20
        ");
        $stmt->execute([$profile_user['id']]);
        $content = $stmt->fetchAll();
        break;
}

$page_title = $profile_user['display_name'] . ' (@' . $profile_user['username'] . ')';
include "../../includes/header.php";;
?>

<!-- Profile Header -->
<div class="card profile-header-card">
    <div class="cover-photo">
        <?php if (!empty($profile_data['cover_photo'])): ?>
            <img src="<?php echo htmlspecialchars($profile_data['cover_photo']); ?>" alt="Cover photo">
        <?php else: ?>
            <div class="default-cover"></div>
        <?php endif; ?>
    </div>
    
    <div class="profile-info">
        <div class="profile-avatar">
            <?php if (!empty($profile_user['avatar'])): ?>
                <img src="<?php echo htmlspecialchars($profile_user['avatar']); ?>" alt="Profile picture">
            <?php else: ?>
                <div class="avatar-circle">
                    <?php echo strtoupper(substr($profile_user['display_name'] ?: $profile_user['username'], 0, 2)); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="profile-details">
            <h1><?php echo htmlspecialchars($profile_user['display_name'] ?: $profile_user['first_name'] . ' ' . $profile_user['last_name']); ?></h1>
            <p class="username">@<?php echo htmlspecialchars($profile_user['username']); ?></p>
            
            <?php if (!empty($profile_user['bio'])): ?>
                <p class="bio"><?php echo htmlspecialchars($profile_user['bio']); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($profile_data['location'])): ?>
                <p class="location">📍 <?php echo htmlspecialchars($profile_data['location']); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($profile_data['website'])): ?>
                <p class="website">
                    <a href="<?php echo htmlspecialchars($profile_data['website']); ?>" target="_blank" rel="noopener">
                        <?php echo htmlspecialchars($profile_data['website']); ?>
                    </a>
                </p>
            <?php endif; ?>
            
            <p class="member-since">Member since <?php echo format_date($profile_user['created_at']); ?></p>
        </div>
        
        <div class="profile-actions">
            <?php if ($current_user_id == $profile_user['id']): ?>
                <a href="/settings" class="btn btn-primary">Edit Profile</a>
            <?php else: ?>
                <?php if ($current_user_id): ?>
                    <button class="btn btn-primary follow-btn" data-user-id="<?php echo $profile_user['id']; ?>" data-following="<?php echo $is_following ? 'true' : 'false'; ?>">
                        <?php echo $is_following ? 'Following' : 'Follow'; ?>
                    </button>
                <?php else: ?>
                    <a href="/login.php" class="btn btn-primary">Follow</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Social Stats -->
    <div class="social-stats">
        <div class="stat-item">
            <span class="stat-number"><?php echo number_format($user_stats['followers']); ?></span>
            <span class="stat-label">Followers</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo number_format($user_stats['following']); ?></span>
            <span class="stat-label">Following</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo number_format($user_stats['articles']['published_articles']); ?></span>
            <span class="stat-label">Articles</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo number_format($user_stats['posts']['total_posts']); ?></span>
            <span class="stat-label">Posts</span>
        </div>
    </div>
</div>

<!-- Profile Navigation -->
<div class="card">
    <nav class="nav-tabs">
        <a href="/user/<?php echo $profile_user['username']; ?>/posts" class="nav-tab <?php echo $tab == 'posts' ? 'active' : ''; ?>">
            Posts
        </a>
        <a href="/user/<?php echo $profile_user['username']; ?>/photos" class="nav-tab <?php echo $tab == 'photos' ? 'active' : ''; ?>">
            Photos
        </a>
        <a href="/user/<?php echo $profile_user['username']; ?>/events" class="nav-tab <?php echo $tab == 'events' ? 'active' : ''; ?>">
            Events
        </a>
        <a href="/user/<?php echo $profile_user['username']; ?>/about" class="nav-tab <?php echo $tab == 'about' ? 'active' : ''; ?>">
            About
        </a>
        <a href="/user/<?php echo $profile_user['username']; ?>/activity" class="nav-tab <?php echo $tab == 'activity' ? 'active' : ''; ?>">
            Activity
        </a>
    </nav>
</div>

<!-- Profile Content -->
<div class="card">
    <?php switch ($tab): 
        case 'posts': ?>
            <h2>Posts</h2>
            <?php if (!empty($content)): ?>
                <div class="posts-section">
                    <?php foreach ($content as $post): ?>
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
                                        <span class="author-name"><?php echo htmlspecialchars($post['display_name'] ?: $post['username']); ?></span>
                                        <span class="post-time"><?php echo format_date($post['created_at'], 'M j, Y \a\t g:i A'); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="post-content">
                                <?php 
                                if (isset($post["parsed_content"]) && !empty($post["parsed_content"])) {
                                    echo $post["parsed_content"];
                                } else {
                                    echo '<p>' . nl2br(htmlspecialchars($post["content"])) . '</p>';
                                }
                                ?>
                                
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
                                <?php if ($current_user_id): ?>
                                    <button class="action-btn like-btn" data-post-id="<?php echo $post['id']; ?>" data-liked="<?php echo is_post_liked($current_user_id, $post['id']) ? 'true' : 'false'; ?>">
                                        <span class="icon">❤️</span>
                                        <span class="count"><?php echo number_format($post['likes_count']); ?></span>
                                    </button>
                                <?php else: ?>
                                    <span class="action-btn disabled">
                                        <span class="icon">❤️</span>
                                        <span class="count"><?php echo number_format($post['likes_count']); ?></span>
                                    </span>
                                <?php endif; ?>
                                
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
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No posts yet.</p>
                </div>
            <?php endif; ?>
        <?php break; 
        
        case 'photos': ?>
            <h2>Photos</h2>
            <?php if (!empty($content)): ?>
                <div class="photos-grid">
                    <?php foreach ($content as $photo): ?>
                        <div class="photo-item">
                            <img src="<?php echo htmlspecialchars($photo['file_path']); ?>" alt="<?php echo htmlspecialchars($photo['caption'] ?: 'Photo'); ?>">
                            <?php if (!empty($photo['caption'])): ?>
                                <div class="photo-caption"><?php echo htmlspecialchars($photo['caption']); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No photos yet.</p>
                </div>
            <?php endif; ?>
        <?php break; 
        
        case 'events': ?>
            <h2>Events</h2>
            <?php if (!empty($content)): ?>
                <div class="events-section">
                    <?php foreach ($content as $event): ?>
                        <div class="event-item">
                            <div class="event-date">
                                <span class="month"><?php echo date('M', strtotime($event['start_date'])); ?></span>
                                <span class="day"><?php echo date('j', strtotime($event['start_date'])); ?></span>
                            </div>
                            <div class="event-details">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <?php if (!empty($event['description'])): ?>
                                    <p><?php echo htmlspecialchars($event['description']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($event['location'])): ?>
                                    <p class="event-location">📍 <?php echo htmlspecialchars($event['location']); ?></p>
                                <?php endif; ?>
                                <p class="event-time"><?php echo format_date($event['start_date'], 'M j, Y \a\t g:i A'); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No events yet.</p>
                </div>
            <?php endif; ?>
        <?php break; 
        
        case 'about': ?>
            <h2>About</h2>
            <div class="about-content">
                <?php if (!empty($content['profile']['interests'])): ?>
                    <div class="about-item">
                        <h3>Interests</h3>
                        <p><?php echo htmlspecialchars($content['profile']['interests']); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($content['profile']['education'])): ?>
                    <div class="about-item">
                        <h3>Education</h3>
                        <p><?php echo htmlspecialchars($content['profile']['education']); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($content['profile']['profession'])): ?>
                    <div class="about-item">
                        <h3>Profession</h3>
                        <p><?php echo htmlspecialchars($content['profile']['profession']); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($content['profile']['expertise_areas'])): ?>
                    <div class="about-item">
                        <h3>Areas of Expertise</h3>
                        <p><?php echo htmlspecialchars($content['profile']['expertise_areas']); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($content['achievements'])): ?>
                    <div class="about-item">
                        <h3>Achievements</h3>
                        <div class="achievements-list">
                            <?php foreach ($content['achievements'] as $achievement): ?>
                                <div class="achievement-item">
                                    <span class="achievement-icon"><?php echo htmlspecialchars($achievement['icon']); ?></span>
                                    <div class="achievement-details">
                                        <h4><?php echo htmlspecialchars($achievement['title']); ?></h4>
                                        <p><?php echo htmlspecialchars($achievement['description']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php break; 
        
        case 'activity': ?>
            <h2>Activity</h2>
            <?php if (!empty($content)): ?>
                <div class="activity-section">
                    <?php foreach ($content as $activity): ?>
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
                <div class="empty-state">
                    <p>No recent activity.</p>
                </div>
            <?php endif; ?>
        <?php break; ?>
    <?php endswitch; ?>
</div>

<style>
/* Profile Header Card */
.profile-header-card {
    padding: 0;
    overflow: hidden;
    margin-bottom: 1rem;
}

.cover-photo {
    height: 200px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.cover-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.default-cover {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.profile-info {
    padding: 2rem;
    display: flex;
    gap: 2rem;
    align-items: flex-start;
}

.profile-avatar {
    flex-shrink: 0;
    margin-top: -60px;
    position: relative;
    z-index: 2;
}

.profile-avatar img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 4px solid white;
    object-fit: cover;
}

.avatar-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: #3498db;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: bold;
    border: 4px solid white;
}

.avatar-circle.small {
    width: 40px;
    height: 40px;
    font-size: 1rem;
    border: 2px solid white;
}

.profile-details {
    flex: 1;
}

.profile-details h1 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
    font-size: 1.8rem;
}

.profile-details .username {
    color: #666;
    margin: 0 0 1rem 0;
    font-weight: 500;
    font-size: 1rem;
}

.profile-details .bio {
    color: #444;
    margin: 0 0 1rem 0;
    font-size: 1rem;
    line-height: 1.5;
}

.profile-details .location,
.profile-details .website {
    color: #666;
    margin: 0.5rem 0;
    font-size: 0.9rem;
}

.profile-details .website a {
    color: #3498db;
    text-decoration: none;
}

.profile-details .website a:hover {
    text-decoration: underline;
}

.profile-details .member-since {
    color: #888;
    font-size: 0.9rem;
    margin: 1rem 0 0 0;
}

.profile-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.social-stats {
    display: flex;
    justify-content: space-around;
    padding: 1.5rem 2rem;
    border-top: 1px solid #eee;
    background: #f8f9fa;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 1.3rem;
    font-weight: bold;
    color: #2c3e50;
}

.stat-label {
    font-size: 0.9rem;
    color: #666;
}

/* Navigation Tabs */
.nav-tabs {
    display: flex;
    padding: 0;
    margin: 0;
    list-style: none;
}

.nav-tab {
    flex: 1;
    padding: 1rem 1.5rem;
    text-align: center;
    text-decoration: none;
    color: #666;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
}

.nav-tab:hover {
    background: #f8f9fa;
    color: #2c3e50;
}

.nav-tab.active {
    color: #3498db;
    border-bottom-color: #3498db;
    background: #f8f9fa;
}

/* Post Items */
.post-item {
    border-bottom: 1px solid #eee;
    padding: 1.5rem 0;
}

.post-item:last-child {
    border-bottom: none;
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

.author-info {
    flex: 1;
}

.author-name {
    font-weight: 600;
    color: #2c3e50;
    display: block;
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

.action-btn.disabled {
    cursor: default;
}

.action-btn.liked {
    color: #e74c3c;
}

/* Photos Grid */
.photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.photo-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.photo-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.photo-caption {
    padding: 0.5rem;
    background: white;
    font-size: 0.9rem;
    color: #666;
}

/* Events */
.event-item {
    display: flex;
    gap: 1.5rem;
    padding: 1.5rem 0;
    border-bottom: 1px solid #eee;
}

.event-item:last-child {
    border-bottom: none;
}

.event-date {
    flex-shrink: 0;
    text-align: center;
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    min-width: 80px;
}

.event-date .month {
    display: block;
    font-size: 0.9rem;
    color: #666;
    text-transform: uppercase;
}

.event-date .day {
    display: block;
    font-size: 1.5rem;
    font-weight: bold;
    color: #2c3e50;
}

.event-details h3 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
}

.event-details p {
    margin: 0.5rem 0;
    color: #666;
}

.event-location {
    color: #3498db !important;
}

.event-time {
    font-size: 0.9rem;
    color: #888 !important;
}

/* About Section */
.about-item {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #eee;
}

.about-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.about-item h3 {
    margin: 0 0 1rem 0;
    color: #2c3e50;
}

.about-item p {
    margin: 0;
    color: #666;
    line-height: 1.6;
}

.achievements-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.achievement-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.achievement-icon {
    font-size: 2rem;
    flex-shrink: 0;
}

.achievement-details h4 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
}

.achievement-details p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

/* Activity */
.activity-item {
    display: flex;
    gap: 1rem;
    padding: 1rem 0;
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
    color: #2c3e50;
}

.activity-time {
    font-size: 0.85rem;
    color: #666;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem;
    color: #666;
}

/* Responsive Design */
@media (max-width: 768px) {
    .profile-info {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .profile-avatar {
        margin-top: -40px;
        align-self: center;
    }
    
    .social-stats {
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .nav-tabs {
        flex-wrap: wrap;
    }
    
    .nav-tab {
        flex: 1 1 50%;
        min-width: 120px;
    }
    
    .photos-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    
    .event-item {
        flex-direction: column;
        text-align: center;
    }
    
    .event-date {
        align-self: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Follow/Unfollow functionality
    const followBtn = document.querySelector('.follow-btn');
    if (followBtn) {
        followBtn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const isFollowing = this.dataset.following === 'true';
            
            fetch('ajax/follow_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: userId,
                    action: isFollowing ? 'unfollow' : 'follow'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.dataset.following = isFollowing ? 'false' : 'true';
                    this.textContent = isFollowing ? 'Follow' : 'Following';
                    
                    // Update follower count
                    const followerStat = document.querySelector('.stat-item:first-child .stat-number');
                    if (followerStat) {
                        const currentCount = parseInt(followerStat.textContent.replace(/,/g, ''));
                        const newCount = isFollowing ? currentCount - 1 : currentCount + 1;
                        followerStat.textContent = newCount.toLocaleString();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }
    
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

<?php include "../../includes/footer.php";; ?>
