<?php
// Handle preferences updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update_preferences') {
    $language = sanitize_input($_POST['language'] ?? 'en');
    $timezone = sanitize_input($_POST['timezone'] ?? 'UTC');
    $theme = sanitize_input($_POST['theme'] ?? 'light');
    $date_format = sanitize_input($_POST['date_format'] ?? 'Y-m-d');
    $time_format = sanitize_input($_POST['time_format'] ?? '24');
    $posts_per_page = (int)($_POST['posts_per_page'] ?? 10);
    $auto_save = isset($_POST['auto_save']) ? 1 : 0;
    $show_avatars = isset($_POST['show_avatars']) ? 1 : 0;
    $compact_mode = isset($_POST['compact_mode']) ? 1 : 0;
    
    // Update or create user profile with preferences
    $stmt = $pdo->prepare("
        INSERT INTO user_profiles (user_id, preferences) 
        VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE preferences = VALUES(preferences)
    ");
    
    $preferences = json_encode([
        'language' => $language,
        'timezone' => $timezone,
        'theme' => $theme,
        'date_format' => $date_format,
        'time_format' => $time_format,
        'posts_per_page' => $posts_per_page,
        'auto_save' => $auto_save,
        'show_avatars' => $show_avatars,
        'compact_mode' => $compact_mode
    ]);
    
    if ($stmt->execute([$_SESSION['user_id'], $preferences])) {
        $success = 'Preferences updated successfully.';
        log_activity('preferences_updated', 'Updated user preferences');
        // Refresh user profile
        $user_profile = get_user_profile($_SESSION['user_id']);
    } else {
        $error = 'Failed to update preferences.';
    }
}

// Get current preferences
$preferences = [];
if ($user_profile && !empty($user_profile['preferences'])) {
    $preferences = json_decode($user_profile['preferences'], true) ?: [];
}

// Default values
$language = $preferences['language'] ?? 'en';
$timezone = $preferences['timezone'] ?? 'UTC';
$theme = $preferences['theme'] ?? 'light';
$date_format = $preferences['date_format'] ?? 'Y-m-d';
$time_format = $preferences['time_format'] ?? '24';
$posts_per_page = $preferences['posts_per_page'] ?? 10;
$auto_save = $preferences['auto_save'] ?? 1;
$show_avatars = $preferences['show_avatars'] ?? 1;
$compact_mode = $preferences['compact_mode'] ?? 0;
?>

<div class="settings-page">
    <div class="page-header">
        <h2>Preferences</h2>
        <p>Customize your experience and interface preferences.</p>
    </div>

    <div class="preferences-sections">
        <form method="POST" class="preferences-form">
            <input type="hidden" name="action" value="update_preferences">
            
            <!-- Language & Region Section -->
            <div class="preferences-section">
                <h3>Language & Region</h3>
                <p>Set your preferred language and regional settings.</p>
                
                <div class="form-group">
                    <label for="language">Language</label>
                    <select id="language" name="language">
                        <?php foreach ($languages as $lang): ?>
                            <option value="<?php echo $lang['code']; ?>" 
                                    <?php echo $language === $lang['code'] ? 'selected' : ''; ?>>
                                <?php echo $lang['native_name']; ?> (<?php echo $lang['name']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="timezone">Timezone</label>
                    <select id="timezone" name="timezone">
                        <option value="UTC" <?php echo $timezone === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                        <option value="America/New_York" <?php echo $timezone === 'America/New_York' ? 'selected' : ''; ?>>Eastern Time (ET)</option>
                        <option value="America/Chicago" <?php echo $timezone === 'America/Chicago' ? 'selected' : ''; ?>>Central Time (CT)</option>
                        <option value="America/Denver" <?php echo $timezone === 'America/Denver' ? 'selected' : ''; ?>>Mountain Time (MT)</option>
                        <option value="America/Los_Angeles" <?php echo $timezone === 'America/Los_Angeles' ? 'selected' : ''; ?>>Pacific Time (PT)</option>
                        <option value="Europe/London" <?php echo $timezone === 'Europe/London' ? 'selected' : ''; ?>>London (GMT)</option>
                        <option value="Europe/Paris" <?php echo $timezone === 'Europe/Paris' ? 'selected' : ''; ?>>Paris (CET)</option>
                        <option value="Asia/Dubai" <?php echo $timezone === 'Asia/Dubai' ? 'selected' : ''; ?>>Dubai (GST)</option>
                        <option value="Asia/Karachi" <?php echo $timezone === 'Asia/Karachi' ? 'selected' : ''; ?>>Karachi (PKT)</option>
                        <option value="Asia/Kolkata" <?php echo $timezone === 'Asia/Kolkata' ? 'selected' : ''; ?>>Mumbai (IST)</option>
                        <option value="Asia/Tokyo" <?php echo $timezone === 'Asia/Tokyo' ? 'selected' : ''; ?>>Tokyo (JST)</option>
                    </select>
                </div>
            </div>

            <!-- Display Settings Section -->
            <div class="preferences-section">
                <h3>Display Settings</h3>
                <p>Customize how content is displayed to you.</p>
                
                <div class="form-group">
                    <label for="theme">Theme</label>
                    <select id="theme" name="theme">
                        <option value="light" <?php echo $theme === 'light' ? 'selected' : ''; ?>>Light</option>
                        <option value="dark" <?php echo $theme === 'dark' ? 'selected' : ''; ?>>Dark</option>
                        <option value="auto" <?php echo $theme === 'auto' ? 'selected' : ''; ?>>Auto (System)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="show_avatars" <?php echo $show_avatars ? 'checked' : ''; ?>>
                        Show user avatars
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="compact_mode" <?php echo $compact_mode ? 'checked' : ''; ?>>
                        Compact mode (denser layout)
                    </label>
                </div>
            </div>

            <!-- Date & Time Section -->
            <div class="preferences-section">
                <h3>Date & Time</h3>
                <p>Set your preferred date and time formats.</p>
                
                <div class="form-group">
                    <label for="date_format">Date Format</label>
                    <select id="date_format" name="date_format">
                        <option value="Y-m-d" <?php echo $date_format === 'Y-m-d' ? 'selected' : ''; ?>>2024-01-15 (ISO)</option>
                        <option value="m/d/Y" <?php echo $date_format === 'm/d/Y' ? 'selected' : ''; ?>>01/15/2024 (US)</option>
                        <option value="d/m/Y" <?php echo $date_format === 'd/m/Y' ? 'selected' : ''; ?>>15/01/2024 (EU)</option>
                        <option value="M j, Y" <?php echo $date_format === 'M j, Y' ? 'selected' : ''; ?>>Jan 15, 2024</option>
                        <option value="j M Y" <?php echo $date_format === 'j M Y' ? 'selected' : ''; ?>>15 Jan 2024</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="time_format">Time Format</label>
                    <select id="time_format" name="time_format">
                        <option value="24" <?php echo $time_format === '24' ? 'selected' : ''; ?>>24-hour (14:30)</option>
                        <option value="12" <?php echo $time_format === '12' ? 'selected' : ''; ?>>12-hour (2:30 PM)</option>
                    </select>
                </div>
            </div>

            <!-- Content Settings Section -->
            <div class="preferences-section">
                <h3>Content Settings</h3>
                <p>Control how content is loaded and displayed.</p>
                
                <div class="form-group">
                    <label for="posts_per_page">Posts per page</label>
                    <select id="posts_per_page" name="posts_per_page">
                        <option value="5" <?php echo $posts_per_page === 5 ? 'selected' : ''; ?>>5 posts</option>
                        <option value="10" <?php echo $posts_per_page === 10 ? 'selected' : ''; ?>>10 posts</option>
                        <option value="20" <?php echo $posts_per_page === 20 ? 'selected' : ''; ?>>20 posts</option>
                        <option value="50" <?php echo $posts_per_page === 50 ? 'selected' : ''; ?>>50 posts</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="auto_save" <?php echo $auto_save ? 'checked' : ''; ?>>
                        Auto-save drafts while typing
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Preferences</button>
                <a href="?page=overview" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
