<?php
// Complete Profile Template
// This template includes the full profile structure: header, navigation, and main container
// Requires: $profile_user, $profile_data, $user_stats, $current_user_id, $is_following, $active_tab

if (!isset($profile_user) || !isset($profile_data) || !isset($user_stats)) {
    throw new Exception('Profile template requires $profile_user, $profile_data, and $user_stats variables');
}

$active_tab = $active_tab ?? 'posts';
?>

<div class="main-container">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="content-area">
        <div class="profile-page">
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
                                    <i class="iw iw-camera"></i>
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
                    <a href="/user/<?php echo $profile_user['username']; ?>" class="nav-tab <?php echo $active_tab == 'posts' ? 'active' : ''; ?>">
                        <i class="iw iw-file-alt"></i> Posts
                    </a>
                    <a href="/user/<?php echo $profile_user['username']; ?>/photos" class="nav-tab <?php echo $active_tab == 'photos' ? 'active' : ''; ?>">
                        <i class="iw iw-images"></i> Photos
                    </a>
                    <a href="/user/<?php echo $profile_user['username']; ?>/events" class="nav-tab <?php echo $active_tab == 'events' ? 'active' : ''; ?>">
                        <i class="iw iw-calendar-alt"></i> Events
                    </a>
                    <a href="/user/<?php echo $profile_user['username']; ?>/about" class="nav-tab <?php echo $active_tab == 'about' ? 'active' : ''; ?>">
                        <i class="iw iw-info"></i> About
                    </a>
                    <?php if (get_system_setting('achievements_enabled', false)): ?>
                    <a href="/user/<?php echo $profile_user['username']; ?>/achievements" class="nav-tab <?php echo $active_tab == 'achievements' ? 'active' : ''; ?>">
                        <i class="iw iw-trophy"></i> Achievements
                    </a>
                    <?php endif; ?>
                    <a href="/user/<?php echo $profile_user['username']; ?>/activity" class="nav-tab <?php echo $active_tab == 'activity' ? 'active' : ''; ?>">
                        <i class="iw iw-chart-line"></i> Activity
                    </a>
                </nav>
            </div>
        </div>
    </div>
</div>
