<?php
// Handle privacy updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update_privacy') {
    $profile_visibility = sanitize_input($_POST['profile_visibility'] ?? 'public');
    $show_email = isset($_POST['show_email']) ? 1 : 0;
    $show_activity = isset($_POST['show_activity']) ? 1 : 0;
    $allow_following = isset($_POST['allow_following']) ? 1 : 0;
    $allow_messages = sanitize_input($_POST['allow_messages'] ?? 'everyone');
    $search_visibility = isset($_POST['search_visibility']) ? 1 : 0;
    
    // Update or create user profile with privacy settings
    $stmt = $pdo->prepare("
        INSERT INTO user_profiles (user_id, privacy_settings) 
        VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE privacy_settings = VALUES(privacy_settings)
    ");
    
    $privacy_settings = json_encode([
        'profile_visibility' => $profile_visibility,
        'show_email' => $show_email,
        'show_activity' => $show_activity,
        'allow_following' => $allow_following,
        'allow_messages' => $allow_messages,
        'search_visibility' => $search_visibility
    ]);
    
    if ($stmt->execute([$_SESSION['user_id'], $privacy_settings])) {
        $success = 'Privacy settings updated successfully.';
        log_activity('privacy_updated', 'Updated privacy settings');
        // Refresh user profile
        $user_profile = get_user_profile($_SESSION['user_id']);
    } else {
        $error = 'Failed to update privacy settings.';
    }
}

// Get current privacy settings
$privacy_settings = [];
if ($user_profile && !empty($user_profile['privacy_settings'])) {
    $privacy_settings = json_decode($user_profile['privacy_settings'], true) ?: [];
}

// Default values
$profile_visibility = $privacy_settings['profile_visibility'] ?? 'public';
$show_email = $privacy_settings['show_email'] ?? 0;
$show_activity = $privacy_settings['show_activity'] ?? 1;
$allow_following = $privacy_settings['allow_following'] ?? 1;
$allow_messages = $privacy_settings['allow_messages'] ?? 'everyone';
$search_visibility = $privacy_settings['search_visibility'] ?? 1;
?>

<div class="settings-page">
    <div class="page-header">
        <h2>Privacy Settings</h2>
        <p>Control who can see your information and how you appear to others.</p>
    </div>

    <div class="privacy-sections">
        <form method="POST" class="privacy-form">
            <input type="hidden" name="action" value="update_privacy">
            
            <!-- Profile Visibility Section -->
            <div class="privacy-section">
                <h3>Profile Visibility</h3>
                <p>Control who can view your profile and personal information.</p>
                
                <div class="form-group">
                    <label for="profile_visibility">Profile Visibility</label>
                    <select id="profile_visibility" name="profile_visibility">
                        <option value="public" <?php echo $profile_visibility === 'public' ? 'selected' : ''; ?>>Public - Anyone can view your profile</option>
                        <option value="followers" <?php echo $profile_visibility === 'followers' ? 'selected' : ''; ?>>Followers Only - Only people you follow can view your profile</option>
                        <option value="private" <?php echo $profile_visibility === 'private' ? 'selected' : ''; ?>>Private - Only you can view your profile</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="show_email" <?php echo $show_email ? 'checked' : ''; ?>>
                        Show email address on profile
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="show_activity" <?php echo $show_activity ? 'checked' : ''; ?>>
                        Show recent activity on profile
                    </label>
                </div>
            </div>

            <!-- Social Settings Section -->
            <div class="privacy-section">
                <h3>Social Settings</h3>
                <p>Control how others can interact with you.</p>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="allow_following" <?php echo $allow_following ? 'checked' : ''; ?>>
                        Allow others to follow you
                    </label>
                </div>
                
                <div class="form-group">
                    <label for="allow_messages">Who can send you messages</label>
                    <select id="allow_messages" name="allow_messages">
                        <option value="everyone" <?php echo $allow_messages === 'everyone' ? 'selected' : ''; ?>>Everyone</option>
                        <option value="followers" <?php echo $allow_messages === 'followers' ? 'selected' : ''; ?>>Followers Only</option>
                        <option value="none" <?php echo $allow_messages === 'none' ? 'selected' : ''; ?>>No One</option>
                    </select>
                </div>
            </div>

            <!-- Search & Discovery Section -->
            <div class="privacy-section">
                <h3>Search & Discovery</h3>
                <p>Control how you appear in search results and recommendations.</p>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="search_visibility" <?php echo $search_visibility ? 'checked' : ''; ?>>
                        Allow my profile to appear in search results
                    </label>
                </div>
            </div>

            <!-- Data Control Section -->
            <div class="privacy-section">
                <h3>Data Control</h3>
                <p>Manage your personal data and account information.</p>
                
                <div class="data-actions">
                    <a href="?page=account&action=export_data" class="btn btn-secondary">
                        <i class="icon-download"></i>
                        Export My Data
                    </a>
                    <a href="?page=account&action=delete_data" class="btn btn-danger">
                        <i class="icon-trash"></i>
                        Delete My Data
                    </a>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Privacy Settings</button>
                <a href="?page=overview" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
