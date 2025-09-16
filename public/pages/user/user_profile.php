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
include "../../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/user_profile.js"></script>
<?php
?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/user_profile.css">
<?php
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
            <?php if ($current_user_id == $profile_user['id']): ?>
                <div class="profile-picture-container" onclick="openProfilePictureModal()">
                    <?php if (!empty($profile_user['avatar'])): ?>
                        <img src="<?php echo htmlspecialchars($profile_user['avatar']); ?>" alt="Profile picture" class="profile-picture">
                    <?php else: ?>
                        <div class="avatar-circle profile-picture">
                            <?php echo strtoupper(substr($profile_user['display_name'] ?: $profile_user['username'], 0, 2)); ?>
                        </div>
                    <?php endif; ?>
                    <div class="camera-icon-overlay">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
            <?php else: ?>
                <?php if (!empty($profile_user['avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($profile_user['avatar']); ?>" alt="Profile picture">
                <?php else: ?>
                    <div class="avatar-circle">
                        <?php echo strtoupper(substr($profile_user['display_name'] ?: $profile_user['username'], 0, 2)); ?>
                    </div>
                <?php endif; ?>
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

<!-- Profile Picture Selection Modal -->
<div id="profilePictureModal" class="profile-picture-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Choose profile picture</h3>
            <button class="close-btn" onclick="closeProfilePictureModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <!-- Initial Options -->
            <div id="initialOptions" class="options-container">
                <div class="option-buttons">
                    <button class="option-btn primary" onclick="showProfilePictureViewer()">
                        <i class="fas fa-eye"></i>
                        See profile picture
                    </button>
                    <button class="option-btn secondary" onclick="showPictureSelection()">
                        <i class="fas fa-camera"></i>
                        Choose profile picture
                    </button>
                </div>
            </div>
            
            <!-- Picture Selection Options -->
            <div id="pictureSelection" class="selection-container" style="display: none;">
                <div class="selection-actions">
                    <button class="action-btn primary" onclick="triggerFileUpload()">
                        <i class="fas fa-plus"></i>
                        Upload photo
                    </button>
                    <div class="upload-hint">
                        <small>💡 Hold Shift while clicking to upload directly without adjustment</small>
                    </div>
                    <button class="action-btn secondary" onclick="showFrames()">
                        <i class="fas fa-square"></i>
                        Add Frame
                    </button>
                </div>
                
                <div class="photo-sections">
                    <div class="photo-section">
                        <h4>Suggested photos</h4>
                        <div class="photo-grid" id="suggestedPhotos">
                            <!-- Suggested photos will be loaded here -->
                        </div>
                        <button class="see-more-btn" onclick="loadMoreSuggested()">See more</button>
                    </div>
                    
                    <div class="photo-section">
                        <h4>Uploads</h4>
                        <div class="photo-grid" id="userUploads">
                            <!-- User uploads will be loaded here -->
                        </div>
                        <button class="see-more-btn" onclick="loadMoreUploads()">See more</button>
                    </div>
                    
                    <div class="photo-section">
                        <h4>Profile pictures</h4>
                        <div class="photo-grid" id="profilePictures">
                            <!-- Profile pictures will be loaded here -->
                        </div>
                    </div>
                    
                    <div class="photo-section">
                        <h4>Cover photos</h4>
                        <div class="photo-grid" id="coverPhotos">
                            <!-- Cover photos will be loaded here -->
                        </div>
                        <button class="see-more-btn" onclick="loadMoreCoverPhotos()">See more</button>
                    </div>
                </div>
            </div>
            
            <!-- Thumbnail Adjustment -->
            <div id="thumbnailAdjustment" class="adjustment-container" style="display: none;">
                <div class="adjustment-preview">
                    <div class="profile-preview">
                        <img id="adjustmentImage" src="" alt="Profile preview">
                        <div class="drag-overlay">
                            <i class="fas fa-arrows-alt"></i>
                            <span>Drag to Reposition</span>
                        </div>
                    </div>
                    <div class="zoom-controls">
                        <span class="zoom-label">Zoom</span>
                        <input type="range" id="zoomSlider" min="0.5" max="2" step="0.1" value="1" oninput="adjustZoom(this.value)">
                        <div class="zoom-buttons">
                            <button onclick="adjustZoom(0.5)">-</button>
                            <button onclick="adjustZoom(2)">+</button>
                        </div>
                    </div>
                    <div class="privacy-info">
                        <i class="fas fa-globe"></i>
                        <span>Your profile picture is public.</span>
                    </div>
                </div>
                <div class="adjustment-actions">
                    <button class="btn-cancel" onclick="cancelThumbnailAdjustment()">Cancel</button>
                    <button class="btn-save" onclick="saveProfilePicture()">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden file input for uploads -->
<input type="file" id="profilePictureUpload" accept="image/*" style="display: none;" onchange="handleFileUpload(this)">

<!-- Full Screen Profile Picture Viewer -->
<div id="profilePictureViewer" class="profile-picture-viewer">
    <div class="viewer-container">
        <div class="viewer-image-section">
            <img id="viewerImage" class="viewer-image" src="" alt="Profile Picture">
        </div>
        <div class="viewer-comments-section">
            <div class="viewer-header">
                <h3>Profile Picture</h3>
                <div class="viewer-actions">
                    <div class="options-dropdown">
                        <button class="options-btn" onclick="toggleOptionsDropdown()">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="options-menu" id="optionsMenu">
                            <button class="option-item delete-btn" onclick="deleteCurrentPhoto()">
                                <i class="fas fa-trash"></i>
                                Delete Photo
                            </button>
                        </div>
                    </div>
                    <button class="viewer-close" onclick="closeProfilePictureViewer()">&times;</button>
                </div>
            </div>
            <div class="viewer-comments" id="viewerComments">
                <!-- Comments will be loaded here -->
            </div>
            <div class="comment-form">
                <textarea class="comment-input" placeholder="Add a comment..." id="commentInput"></textarea>
                <button class="comment-submit" onclick="submitComment()">Post Comment</button>
            </div>
        </div>
    </div>
</div>

<?php include "../../includes/footer.php";; ?>
