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

<?php 
$active_tab = $tab;
include "../../includes/profile_template.php"; 
?>

<!-- Profile Content -->
<?php switch ($tab): 
    case 'posts': ?>
        <div class="card">
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
        </div>
        <?php break; 
        
        case 'photos': ?>
            <!-- Photos section will be rendered outside container -->
            <div class="placeholder-for-full-width"></div>
        <?php break; 
        
        case 'events': ?>
            <!-- Events section will be rendered outside container -->
            <div class="placeholder-for-full-width"></div>
        <?php break; 
        
        case 'about': ?>
            <!-- About section will be rendered outside container -->
            <div class="placeholder-for-full-width"></div>
        <?php break; 
        
        case 'activity': ?>
            <!-- Activity section will be rendered outside container -->
            <div class="placeholder-for-full-width"></div>
        <?php break; ?>
    <?php endswitch; ?>

<?php if ($tab == 'photos'): ?>
<!-- Photos Gallery - Full Width -->
<div class="photos-section">
    <div class="photos-header">
        <h2>Photos</h2>
    </div>
    <div class="photos-content">
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
    </div>
</div>
<?php endif; ?>

<?php if ($tab == 'events'): ?>
<!-- Events Gallery - Full Width -->
<div class="events-section">
    <div class="events-header">
        <h2>Events</h2>
    </div>
    <div class="events-content">
        <?php if (!empty($content)): ?>
            <div class="events-list">
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
    </div>
</div>
<?php endif; ?>

<?php if ($tab == 'about'): ?>
<!-- About Section - Full Width -->
<div class="about-section">
    <div class="about-header">
        <h2>About</h2>
    </div>
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
</div>
<?php endif; ?>

<?php if ($tab == 'activity'): ?>
<!-- Activity Section - Full Width -->
<div class="activity-section">
    <div class="activity-header">
        <h2>Activity</h2>
    </div>
    <div class="activity-content">
        <?php if (!empty($content)): ?>
            <div class="activity-list">
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
    </div>
</div>
<?php endif; ?>

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
                        <i class="iw iw-eye"></i>
                        See profile picture
                    </button>
                    <button class="option-btn secondary" onclick="showPictureSelection()">
                        <i class="iw iw-camera"></i>
                        Choose profile picture
                    </button>
                </div>
            </div>
            
            <!-- Picture Selection Options -->
            <div id="pictureSelection" class="selection-container" style="display: none;">
                <div class="selection-actions">
                    <button class="action-btn primary" onclick="triggerFileUpload()">
                        <i class="iw iw-plus"></i>
                        Upload photo
                    </button>
                    <div class="upload-hint">
                        <small>💡 Hold Shift while clicking to upload directly without adjustment</small>
                    </div>
                    <button class="action-btn secondary" onclick="showFrames()">
                        <i class="iw iw-square"></i>
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
                            <i class="iw iw-arrows-alt"></i>
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
                        <i class="iw iw-globe"></i>
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
                            <i class="iw iw-ellipsis-v"></i>
                        </button>
                        <div class="options-menu" id="optionsMenu">
                            <button class="option-item delete-btn" onclick="deleteCurrentPhoto()">
                                <i class="iw iw-trash"></i>
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
