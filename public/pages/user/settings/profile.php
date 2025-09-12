<?php
// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update_profile') {
    $first_name = sanitize_input($_POST['first_name'] ?? '');
    $last_name = sanitize_input($_POST['last_name'] ?? '');
    $display_name = sanitize_input($_POST['display_name'] ?? '');
    $bio = sanitize_input($_POST['bio'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $location = sanitize_input($_POST['location'] ?? '');
    $website = sanitize_input($_POST['website'] ?? '');
    $gender = sanitize_input($_POST['gender'] ?? '');
    $date_of_birth = sanitize_input($_POST['date_of_birth'] ?? '');
    
    if (empty($first_name) || empty($last_name) || empty($display_name) || empty($email)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email is already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $error = 'Email already exists.';
        } else {
            // Update user basic info
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, display_name = ?, bio = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$first_name, $last_name, $display_name, $bio, $email, $_SESSION['user_id']])) {
                // Update or create user profile
                $stmt = $pdo->prepare("
                    INSERT INTO user_profiles (user_id, location, website, gender, date_of_birth) 
                    VALUES (?, ?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    location = VALUES(location), 
                    website = VALUES(website), 
                    gender = VALUES(gender), 
                    date_of_birth = VALUES(date_of_birth)
                ");
                
                if ($stmt->execute([$_SESSION['user_id'], $location, $website, $gender, $date_of_birth ?: null])) {
                    $success = 'Profile updated successfully.';
                    log_activity('profile_updated', 'Updated profile information');
                    // Refresh user data
                    $current_user = get_user($_SESSION['user_id']);
                    $user_profile = get_user_profile($_SESSION['user_id']);
                } else {
                    $error = 'Failed to update profile details.';
                }
            } else {
                $error = 'Failed to update profile.';
            }
        }
    }
}
?>

<div class="settings-page">
    <div class="page-header">
        <h2>Profile Settings</h2>
        <p>Manage your personal information and profile details.</p>
    </div>

    <div class="settings-form">
        <form method="POST" class="profile-form">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="form-section">
                <h3>Basic Information</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required 
                               value="<?php echo htmlspecialchars($current_user['first_name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required 
                               value="<?php echo htmlspecialchars($current_user['last_name']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="display_name">Display Name *</label>
                    <input type="text" id="display_name" name="display_name" required 
                           value="<?php echo htmlspecialchars($current_user['display_name']); ?>">
                    <small>This is how your name appears to other users.</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($current_user['email']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="4" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($current_user['bio']); ?></textarea>
                    <small>Write a brief description about yourself.</small>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Additional Information</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" 
                               value="<?php echo htmlspecialchars(($user_profile['location'] ?? '')); ?>"
                               placeholder="City, Country">
                    </div>
                    
                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="url" id="website" name="website" 
                               value="<?php echo htmlspecialchars(($user_profile['website'] ?? '')); ?>"
                               placeholder="https://yourwebsite.com">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="male" <?php echo (($user_profile['gender'] ?? '') === 'male') ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo (($user_profile['gender'] ?? '') === 'female') ? 'selected' : ''; ?>>Female</option>
                            <option value="other" <?php echo (($user_profile['gender'] ?? '') === 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" 
                               value="<?php echo htmlspecialchars(($user_profile['date_of_birth'] ?? '')); ?>">
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Profile</button>
                <a href="?page=overview" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
