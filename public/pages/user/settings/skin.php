<?php
// Handle skin selection
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'change_skin') {
        $skin = sanitize_input($_POST['skin'] ?? 'bismillah');
        
        // Update user preferences
        $stmt = $pdo->prepare("
            INSERT INTO user_profiles (user_id, preferences) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE preferences = VALUES(preferences)
        ");
        
        // Get current preferences
        $current_preferences = [];
        if ($user_profile && !empty($user_profile['preferences'])) {
            $current_preferences = json_decode($user_profile['preferences'], true) ?: [];
        }
        
        // Update skin preference
        $current_preferences['skin'] = $skin;
        
        $preferences_json = json_encode($current_preferences);
        
        if ($stmt->execute([$_SESSION['user_id'], $preferences_json])) {
            $success = 'Skin updated successfully. The page will refresh to apply changes.';
            log_activity('skin_changed', "Changed skin to: $skin");
            
            // Refresh user profile
            $user_profile = get_user_profile($_SESSION['user_id']);
            
            // Redirect to prevent form resubmission
            header("Location: /settings?page=skin&success=skin_updated");
            exit;
        } else {
            $error = 'Failed to update skin preference.';
        }
    }
}

// Get current skin preference
$current_skin = 'bismillah';
if ($user_profile && !empty($user_profile['preferences'])) {
    $preferences = json_decode($user_profile['preferences'], true) ?: [];
    $current_skin = $preferences['skin'] ?? 'bismillah';
}

// Get available skins from filesystem
$skins_dir = __DIR__ . '/../../../skins';
$available_skins = [];

if (is_dir($skins_dir)) {
    $skin_folders = array_diff(scandir($skins_dir), ['.', '..', 'skins_manager.php']);
    
    foreach ($skin_folders as $skin_folder) {
        $skin_path = $skins_dir . '/' . $skin_folder;
        $skin_json = $skin_path . '/skin.json';
        
        if (is_dir($skin_path) && file_exists($skin_json)) {
            $skin_data = json_decode(file_get_contents($skin_json), true);
            if ($skin_data) {
                $available_skins[] = array_merge($skin_data, ['folder' => $skin_folder]);
            }
        }
    }
}

// Sort skins by display name
usort($available_skins, function($a, $b) {
    return strcmp($a['display_name'], $b['display_name']);
});
?>

<div class="settings-page">
    <div class="page-header">
        <h2>Skin Selection</h2>
        <p>Choose your preferred visual theme and layout style.</p>
    </div>

    <div class="preferences-sections">
        <div class="preferences-section">
            <h3>Available Skins</h3>
            <p>Select from the available visual themes below. Each skin provides a different look and feel while maintaining the same functionality.</p>
            
            <div class="skins-grid">
            <?php foreach ($available_skins as $skin): ?>
                <div class="skin-option <?php echo $current_skin === $skin['name'] ? 'selected' : ''; ?>" 
                     data-skin="<?php echo htmlspecialchars($skin['name']); ?>">
                    
                    <div class="skin-preview">
                        <?php if (file_exists($skins_dir . '/' . $skin['folder'] . '/assets/images/preview.png')): ?>
                            <img src="/skins/<?php echo htmlspecialchars($skin['folder']); ?>/assets/images/preview.png" 
                                 alt="<?php echo htmlspecialchars($skin['display_name']); ?> Preview">
                        <?php else: ?>
                            <div class="skin-preview-placeholder">
                                <i class="iw iw-palette"></i>
                                <span>Preview not available</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="skin-details">
                        <h3><?php echo htmlspecialchars($skin['display_name']); ?></h3>
                        <p><?php echo htmlspecialchars($skin['description']); ?></p>
                        
                        <div class="skin-meta">
                            <span class="skin-version">v<?php echo htmlspecialchars($skin['version']); ?></span>
                            <span class="skin-author">by <?php echo htmlspecialchars($skin['author']); ?></span>
                        </div>
                        
                        <?php if (!empty($skin['features'])): ?>
                            <div class="skin-features">
                                <strong>Features:</strong>
                                <ul>
                                    <?php foreach ($skin['features'] as $feature): ?>
                                        <li><?php echo htmlspecialchars($feature); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <div class="skin-actions">
                            <?php if ($current_skin === $skin['name']): ?>
                                <button class="btn btn-primary" disabled>
                                    <i class="iw iw-check"></i>
                                    Current Skin
                                </button>
                            <?php else: ?>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="action" value="change_skin">
                                    <input type="hidden" name="skin" value="<?php echo htmlspecialchars($skin['name']); ?>">
                                    <button type="submit" class="btn btn-secondary">
                                        <i class="iw iw-palette"></i>
                                        Select Skin
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>

        <?php if (empty($available_skins)): ?>
        <div class="preferences-section">
            <h3>No Skins Available</h3>
            <p>No skins are currently available. Please contact an administrator to install additional skins.</p>
        </div>
        <?php endif; ?>

        <div class="preferences-section">
            <h3>About Skins</h3>
            <p>Skins control the visual appearance and layout of the website. Each skin provides a different look and feel while maintaining the same functionality.</p>
            <p>You can switch between available skins at any time. Changes will be applied immediately and will be remembered for future visits.</p>
            
            <div class="form-group">
                <label>Current Skin</label>
                <div class="current-skin-info">
                    <strong><?php echo htmlspecialchars($available_skins[array_search($current_skin, array_column($available_skins, 'name'))]['display_name'] ?? 'Unknown'); ?></strong>
                    <span class="skin-status">Active</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Skin Selection Styles */
.skins-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.skin-option {
    background: white;
    border: 2px solid #e1e8ed;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    cursor: pointer;
}

.skin-option:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    border-color: #3498db;
}

.skin-option.selected {
    border-color: #3498db;
    box-shadow: 0 4px 16px rgba(52, 152, 219, 0.2);
}

.skin-preview {
    height: 200px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.skin-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.skin-preview-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    color: #7f8c8d;
}

.skin-preview-placeholder i {
    font-size: 3rem;
}

.skin-details {
    padding: 1.5rem;
}

.skin-details h3 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 1.2rem;
    font-weight: 600;
}

.skin-details p {
    color: #7f8c8d;
    margin-bottom: 1rem;
    line-height: 1.5;
    font-size: 0.9rem;
}

.skin-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.skin-version {
    background: #f8f9fa;
    color: #6c757d;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.skin-author {
    color: #7f8c8d;
    font-size: 0.85rem;
}

.skin-features {
    margin-bottom: 1rem;
}

.skin-features strong {
    display: block;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.skin-features ul {
    margin: 0;
    padding-left: 1.2rem;
}

.skin-features li {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.skin-actions {
    display: flex;
    justify-content: center;
    margin-top: 1rem;
}

.inline-form {
    display: inline;
}

.inline-form button {
    margin: 0;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.current-skin-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.skin-status {
    background: #28a745;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

@media (max-width: 768px) {
    .skins-grid {
        grid-template-columns: 1fr;
    }
    
    .skin-preview {
        height: 150px;
    }
    
    .skin-details {
        padding: 1rem;
    }
}
</style>

<script>
// Add click handler for skin selection
document.addEventListener('DOMContentLoaded', function() {
    const skinOptions = document.querySelectorAll('.skin-option');
    
    skinOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            // Don't trigger if clicking on a button
            if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
                return;
            }
            
            const form = this.querySelector('form');
            if (form) {
                form.submit();
            }
        });
    });
});
</script>
