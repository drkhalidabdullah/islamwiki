<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Get username from URL parameter
$username = $_GET['username'] ?? '';

if (empty($username)) {
    header('Location: /');
    exit();
}

// Get user by username
$profile_user = get_user_by_username($username);
if (!$profile_user) {
    header('Location: /404.php');
    exit();
}

// Check if current user can view this profile
$current_user_id = $_SESSION['user_id'] ?? null;
if (!can_view_profile($current_user_id, $profile_user['id'])) {
    header('Location: /login.php');
    exit();
}

// Get complete profile data for header
$profile_data = get_user_profile_complete($profile_user['id']);
$user_stats = get_user_stats($profile_user['id']);

// Check if current user is following this profile
$is_following = false;
if ($current_user_id && $current_user_id != $profile_user['id']) {
    $is_following = is_following($current_user_id, $profile_user['id']);
}

// Set active tab for navigation
$active_tab = 'about';

// Get about content
$about_content = [
    'profile' => $profile_data,
    'achievements' => get_user_achievements($profile_user['id'])
];

$page_title = $profile_user['display_name'] ?: $profile_user['username'] . "'s About";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - IslamWiki</title>
    <link rel="stylesheet" href="/skins/bismillah/assets/css/main.css">
    <link rel="stylesheet" href="/skins/bismillah/assets/css/user_profile.css">
    <link rel="stylesheet" href="/skins/bismillah/assets/css/about.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <?php include '../../includes/profile_template.php'; ?>
    
    <!-- About Section - Full Width -->
    <div class="about-section">
        <div class="about-header">
            <h2>About</h2>
            <?php if ($current_user_id == $profile_user['id']): ?>
                <button class="edit-about-btn" onclick="openEditAboutModal()">
                    <i class="fas fa-edit"></i> Edit About
                </button>
            <?php endif; ?>
        </div>
        
        <div class="about-content">
            <div class="about-grid">
                <!-- Basic Information -->
                <div class="about-card">
                    <div class="about-card-header">
                        <h3><i class="fas fa-user"></i> Basic Information</h3>
                        <div class="section-controls">
                            <?php if ($current_user_id == $profile_user['id']): ?>
                                <button class="edit-section-btn" onclick="editSection('basic')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="about-card-content">
                        <div class="info-item">
                            <span class="info-label">First Name</span>
                            <div class="info-item-controls">
                                <span class="info-value"><?php echo htmlspecialchars($profile_user['first_name'] ?: 'Not specified'); ?></span>
                                <?php if ($current_user_id == $profile_user['id']): ?>
                                    <select class="privacy-select" onchange="updatePrivacy('first_name', this.value)">
                                        <option value="public" <?php echo get_privacy_setting($profile_user['id'], 'first_name') == 'public' ? 'selected' : ''; ?>>Public</option>
                                        <option value="community" <?php echo get_privacy_setting($profile_user['id'], 'first_name') == 'community' ? 'selected' : ''; ?>>Community</option>
                                        <option value="followers" <?php echo get_privacy_setting($profile_user['id'], 'first_name') == 'followers' ? 'selected' : ''; ?>>Followers</option>
                                        <option value="private" <?php echo get_privacy_setting($profile_user['id'], 'first_name') == 'private' ? 'selected' : ''; ?>>Private</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Last Name</span>
                            <div class="info-item-controls">
                                <span class="info-value"><?php echo htmlspecialchars($profile_user['last_name'] ?: 'Not specified'); ?></span>
                                <?php if ($current_user_id == $profile_user['id']): ?>
                                    <select class="privacy-select" onchange="updatePrivacy('last_name', this.value)">
                                        <option value="public" <?php echo get_privacy_setting($profile_user['id'], 'last_name') == 'public' ? 'selected' : ''; ?>>Public</option>
                                        <option value="community" <?php echo get_privacy_setting($profile_user['id'], 'last_name') == 'community' ? 'selected' : ''; ?>>Community</option>
                                        <option value="followers" <?php echo get_privacy_setting($profile_user['id'], 'last_name') == 'followers' ? 'selected' : ''; ?>>Followers</option>
                                        <option value="private" <?php echo get_privacy_setting($profile_user['id'], 'last_name') == 'private' ? 'selected' : ''; ?>>Private</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Username</span>
                            <div class="info-item-controls">
                                <span class="info-value">@<?php echo htmlspecialchars($profile_user['username']); ?></span>
                                <?php if ($current_user_id == $profile_user['id']): ?>
                                    <select class="privacy-select" onchange="updatePrivacy('username', this.value)">
                                        <option value="public" <?php echo get_privacy_setting($profile_user['id'], 'username') == 'public' ? 'selected' : ''; ?>>Public</option>
                                        <option value="community" <?php echo get_privacy_setting($profile_user['id'], 'username') == 'community' ? 'selected' : ''; ?>>Community</option>
                                        <option value="followers" <?php echo get_privacy_setting($profile_user['id'], 'username') == 'followers' ? 'selected' : ''; ?>>Followers</option>
                                        <option value="private" <?php echo get_privacy_setting($profile_user['id'], 'username') == 'private' ? 'selected' : ''; ?>>Private</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <div class="info-item-controls">
                                <span class="info-value"><?php echo htmlspecialchars($profile_user['email']); ?></span>
                                <?php if ($current_user_id == $profile_user['id']): ?>
                                    <select class="privacy-select" onchange="updatePrivacy('email', this.value)">
                                        <option value="public" <?php echo get_privacy_setting($profile_user['id'], 'email') == 'public' ? 'selected' : ''; ?>>Public</option>
                                        <option value="community" <?php echo get_privacy_setting($profile_user['id'], 'email') == 'community' ? 'selected' : ''; ?>>Community</option>
                                        <option value="followers" <?php echo get_privacy_setting($profile_user['id'], 'email') == 'followers' ? 'selected' : ''; ?>>Followers</option>
                                        <option value="private" <?php echo get_privacy_setting($profile_user['id'], 'email') == 'private' ? 'selected' : ''; ?>>Private</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Member Since</span>
                            <div class="info-item-controls">
                                <span class="info-value"><?php echo format_date($profile_user['created_at']); ?></span>
                                <?php if ($current_user_id == $profile_user['id']): ?>
                                    <select class="privacy-select" onchange="updatePrivacy('member_since', this.value)">
                                        <option value="public" <?php echo get_privacy_setting($profile_user['id'], 'member_since') == 'public' ? 'selected' : ''; ?>>Public</option>
                                        <option value="community" <?php echo get_privacy_setting($profile_user['id'], 'member_since') == 'community' ? 'selected' : ''; ?>>Community</option>
                                        <option value="followers" <?php echo get_privacy_setting($profile_user['id'], 'member_since') == 'followers' ? 'selected' : ''; ?>>Followers</option>
                                        <option value="private" <?php echo get_privacy_setting($profile_user['id'], 'member_since') == 'private' ? 'selected' : ''; ?>>Private</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="about-card">
                    <div class="about-card-header">
                        <h3><i class="fas fa-address-book"></i> Contact Information</h3>
                        <div class="section-controls">
                            <?php if ($current_user_id == $profile_user['id']): ?>
                                <button class="edit-section-btn" onclick="editSection('contact')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="about-card-content">
                        <div class="info-item">
                            <span class="info-label">Location</span>
                            <div class="info-item-controls">
                                <span class="info-value"><?php echo htmlspecialchars($profile_data['location'] ?: 'Not specified'); ?></span>
                                <?php if ($current_user_id == $profile_user['id']): ?>
                                    <select class="privacy-select" onchange="updatePrivacy('location', this.value)">
                                        <option value="public" <?php echo get_privacy_setting($profile_user['id'], 'location') == 'public' ? 'selected' : ''; ?>>Public</option>
                                        <option value="community" <?php echo get_privacy_setting($profile_user['id'], 'location') == 'community' ? 'selected' : ''; ?>>Community</option>
                                        <option value="followers" <?php echo get_privacy_setting($profile_user['id'], 'location') == 'followers' ? 'selected' : ''; ?>>Followers</option>
                                        <option value="private" <?php echo get_privacy_setting($profile_user['id'], 'location') == 'private' ? 'selected' : ''; ?>>Private</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Website</span>
                            <div class="info-item-controls">
                                <span class="info-value">
                                    <?php if (!empty($profile_data['website'])): ?>
                                        <a href="<?php echo htmlspecialchars($profile_data['website']); ?>" target="_blank" rel="noopener">
                                            <?php echo htmlspecialchars($profile_data['website']); ?>
                                        </a>
                                    <?php else: ?>
                                        Not specified
                                    <?php endif; ?>
                                </span>
                                <?php if ($current_user_id == $profile_user['id']): ?>
                                    <select class="privacy-select" onchange="updatePrivacy('website', this.value)">
                                        <option value="public" <?php echo get_privacy_setting($profile_user['id'], 'website') == 'public' ? 'selected' : ''; ?>>Public</option>
                                        <option value="community" <?php echo get_privacy_setting($profile_user['id'], 'website') == 'community' ? 'selected' : ''; ?>>Community</option>
                                        <option value="followers" <?php echo get_privacy_setting($profile_user['id'], 'website') == 'followers' ? 'selected' : ''; ?>>Followers</option>
                                        <option value="private" <?php echo get_privacy_setting($profile_user['id'], 'website') == 'private' ? 'selected' : ''; ?>>Private</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone</span>
                            <div class="info-item-controls">
                                <span class="info-value"><?php echo htmlspecialchars($profile_data['phone'] ?: 'Not specified'); ?></span>
                                <?php if ($current_user_id == $profile_user['id']): ?>
                                    <select class="privacy-select" onchange="updatePrivacy('phone', this.value)">
                                        <option value="public" <?php echo get_privacy_setting($profile_user['id'], 'phone') == 'public' ? 'selected' : ''; ?>>Public</option>
                                        <option value="community" <?php echo get_privacy_setting($profile_user['id'], 'phone') == 'community' ? 'selected' : ''; ?>>Community</option>
                                        <option value="followers" <?php echo get_privacy_setting($profile_user['id'], 'phone') == 'followers' ? 'selected' : ''; ?>>Followers</option>
                                        <option value="private" <?php echo get_privacy_setting($profile_user['id'], 'phone') == 'private' ? 'selected' : ''; ?>>Private</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="about-card">
                    <div class="about-card-header">
                        <h3><i class="fas fa-heart"></i> Personal Information</h3>
                        <div class="section-controls">
                            <?php if ($current_user_id == $profile_user['id']): ?>
                                <button class="edit-section-btn" onclick="editSection('personal')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="about-card-content">
                        <div class="info-item">
                            <span class="info-label">Bio</span>
                            <div class="info-item-controls">
                                <span class="info-value"><?php echo htmlspecialchars($profile_user['bio'] ?: 'No bio provided'); ?></span>
                                <?php if ($current_user_id == $profile_user['id']): ?>
                                    <select class="privacy-select" onchange="updatePrivacy('bio', this.value)">
                                        <option value="public" <?php echo get_privacy_setting($profile_user['id'], 'bio') == 'public' ? 'selected' : ''; ?>>Public</option>
                                        <option value="community" <?php echo get_privacy_setting($profile_user['id'], 'bio') == 'community' ? 'selected' : ''; ?>>Community</option>
                                        <option value="followers" <?php echo get_privacy_setting($profile_user['id'], 'bio') == 'followers' ? 'selected' : ''; ?>>Followers</option>
                                        <option value="private" <?php echo get_privacy_setting($profile_user['id'], 'bio') == 'private' ? 'selected' : ''; ?>>Private</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Date of Birth</span>
                            <div class="info-item-controls">
                                <span class="info-value"><?php echo htmlspecialchars($profile_data['date_of_birth'] ?: 'Not specified'); ?></span>
                                <?php if ($current_user_id == $profile_user['id']): ?>
                                    <select class="privacy-select" onchange="updatePrivacy('date_of_birth', this.value)">
                                        <option value="public" <?php echo get_privacy_setting($profile_user['id'], 'date_of_birth') == 'public' ? 'selected' : ''; ?>>Public</option>
                                        <option value="community" <?php echo get_privacy_setting($profile_user['id'], 'date_of_birth') == 'community' ? 'selected' : ''; ?>>Community</option>
                                        <option value="followers" <?php echo get_privacy_setting($profile_user['id'], 'date_of_birth') == 'followers' ? 'selected' : ''; ?>>Followers</option>
                                        <option value="private" <?php echo get_privacy_setting($profile_user['id'], 'date_of_birth') == 'private' ? 'selected' : ''; ?>>Private</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Gender</span>
                            <div class="info-item-controls">
                                <span class="info-value"><?php echo htmlspecialchars($profile_data['gender'] ?: 'Not specified'); ?></span>
                                <?php if ($current_user_id == $profile_user['id']): ?>
                                    <select class="privacy-select" onchange="updatePrivacy('gender', this.value)">
                                        <option value="public" <?php echo get_privacy_setting($profile_user['id'], 'gender') == 'public' ? 'selected' : ''; ?>>Public</option>
                                        <option value="community" <?php echo get_privacy_setting($profile_user['id'], 'gender') == 'community' ? 'selected' : ''; ?>>Community</option>
                                        <option value="followers" <?php echo get_privacy_setting($profile_user['id'], 'gender') == 'followers' ? 'selected' : ''; ?>>Followers</option>
                                        <option value="private" <?php echo get_privacy_setting($profile_user['id'], 'gender') == 'private' ? 'selected' : ''; ?>>Private</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Interests</span>
                            <div class="info-item-controls">
                                <span class="info-value"><?php echo htmlspecialchars($profile_data['interests'] ?: 'Not specified'); ?></span>
                                <?php if ($current_user_id == $profile_user['id']): ?>
                                    <select class="privacy-select" onchange="updatePrivacy('interests', this.value)">
                                        <option value="public" <?php echo get_privacy_setting($profile_user['id'], 'interests') == 'public' ? 'selected' : ''; ?>>Public</option>
                                        <option value="community" <?php echo get_privacy_setting($profile_user['id'], 'interests') == 'community' ? 'selected' : ''; ?>>Community</option>
                                        <option value="followers" <?php echo get_privacy_setting($profile_user['id'], 'interests') == 'followers' ? 'selected' : ''; ?>>Followers</option>
                                        <option value="private" <?php echo get_privacy_setting($profile_user['id'], 'interests') == 'private' ? 'selected' : ''; ?>>Private</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Education & Work -->
                <div class="about-card">
                    <div class="about-card-header">
                        <h3><i class="fas fa-graduation-cap"></i> Education & Work</h3>
                        <div class="section-controls">
                            <?php if ($current_user_id == $profile_user['id']): ?>
                                <button class="edit-section-btn" onclick="editSection('education')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="about-card-content">
                        <div class="info-item">
                            <span class="info-label">Education</span>
                            <div class="info-item-controls">
                                <span class="info-value"><?php echo htmlspecialchars($profile_data['education'] ?: 'Not specified'); ?></span>
                                <?php if ($current_user_id == $profile_user['id']): ?>
                                    <select class="privacy-select" onchange="updatePrivacy('education', this.value)">
                                        <option value="public" <?php echo get_privacy_setting($profile_user['id'], 'education') == 'public' ? 'selected' : ''; ?>>Public</option>
                                        <option value="community" <?php echo get_privacy_setting($profile_user['id'], 'education') == 'community' ? 'selected' : ''; ?>>Community</option>
                                        <option value="followers" <?php echo get_privacy_setting($profile_user['id'], 'education') == 'followers' ? 'selected' : ''; ?>>Followers</option>
                                        <option value="private" <?php echo get_privacy_setting($profile_user['id'], 'education') == 'private' ? 'selected' : ''; ?>>Private</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Profession</span>
                            <div class="info-item-controls">
                                <span class="info-value"><?php echo htmlspecialchars($profile_data['profession'] ?: 'Not specified'); ?></span>
                                <?php if ($current_user_id == $profile_user['id']): ?>
                                    <select class="privacy-select" onchange="updatePrivacy('profession', this.value)">
                                        <option value="public" <?php echo get_privacy_setting($profile_user['id'], 'profession') == 'public' ? 'selected' : ''; ?>>Public</option>
                                        <option value="community" <?php echo get_privacy_setting($profile_user['id'], 'profession') == 'community' ? 'selected' : ''; ?>>Community</option>
                                        <option value="followers" <?php echo get_privacy_setting($profile_user['id'], 'profession') == 'followers' ? 'selected' : ''; ?>>Followers</option>
                                        <option value="private" <?php echo get_privacy_setting($profile_user['id'], 'profession') == 'private' ? 'selected' : ''; ?>>Private</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Areas of Expertise</span>
                            <div class="info-item-controls">
                                <span class="info-value"><?php echo htmlspecialchars($profile_data['expertise_areas'] ?: 'Not specified'); ?></span>
                                <?php if ($current_user_id == $profile_user['id']): ?>
                                    <select class="privacy-select" onchange="updatePrivacy('expertise_areas', this.value)">
                                        <option value="public" <?php echo get_privacy_setting($profile_user['id'], 'expertise_areas') == 'public' ? 'selected' : ''; ?>>Public</option>
                                        <option value="community" <?php echo get_privacy_setting($profile_user['id'], 'expertise_areas') == 'community' ? 'selected' : ''; ?>>Community</option>
                                        <option value="followers" <?php echo get_privacy_setting($profile_user['id'], 'expertise_areas') == 'followers' ? 'selected' : ''; ?>>Followers</option>
                                        <option value="private" <?php echo get_privacy_setting($profile_user['id'], 'expertise_areas') == 'private' ? 'selected' : ''; ?>>Private</option>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Achievements -->
                <?php if (!empty($about_content['achievements'])): ?>
                <div class="about-card">
                    <div class="about-card-header">
                        <h3><i class="fas fa-trophy"></i> Achievements</h3>
                        <?php if ($current_user_id == $profile_user['id']): ?>
                            <button class="edit-section-btn" onclick="editSection('achievements')">
                                <i class="fas fa-edit"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="about-card-content">
                        <div class="achievements-list">
                            <?php foreach ($about_content['achievements'] as $achievement): ?>
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
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit About Modal -->
    <div id="editAboutModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit About Information</h3>
                <button class="close-btn" onclick="closeEditAboutModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="editAboutForm">
                    <div class="form-section" id="basicSection" style="display: none;">
                        <h4>Basic Information</h4>
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($profile_user['first_name']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($profile_user['last_name']); ?>">
                        </div>
                    </div>

                    <div class="form-section" id="contactSection" style="display: none;">
                        <h4>Contact Information</h4>
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($profile_data['location']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="website">Website</label>
                            <input type="url" id="website" name="website" value="<?php echo htmlspecialchars($profile_data['website']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($profile_data['phone']); ?>">
                        </div>
                    </div>

                    <div class="form-section" id="personalSection" style="display: none;">
                        <h4>Personal Information</h4>
                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($profile_user['bio']); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($profile_data['date_of_birth']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="male" <?php echo $profile_data['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo $profile_data['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo $profile_data['gender'] == 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="interests">Interests</label>
                            <textarea id="interests" name="interests" rows="3"><?php echo htmlspecialchars($profile_data['interests']); ?></textarea>
                        </div>
                    </div>

                    <div class="form-section" id="educationSection" style="display: none;">
                        <h4>Education & Work</h4>
                        <div class="form-group">
                            <label for="education">Education</label>
                            <textarea id="education" name="education" rows="3"><?php echo htmlspecialchars($profile_data['education']); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="profession">Profession</label>
                            <input type="text" id="profession" name="profession" value="<?php echo htmlspecialchars($profile_data['profession']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="expertise_areas">Areas of Expertise</label>
                            <textarea id="expertise_areas" name="expertise_areas" rows="3"><?php echo htmlspecialchars($profile_data['expertise_areas']); ?></textarea>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeEditAboutModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="/skins/bismillah/assets/js/about.js"></script>
    <script src="/skins/bismillah/assets/js/user_profile.js"></script>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
