<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Skin Selection';
require_login();

// Check if skin selection is enabled
if (!get_system_setting('allow_skin_selection', true)) {
    show_message('Skin selection is currently disabled.', 'error');
    redirect('/dashboard');
}

// Load skins manager
require_once '../../skins/skins_manager.php';

// Handle skin selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'select_skin') {
    $skin_name = $_POST['skin_name'] ?? '';
    
    if ($skin_name) {
        if ($skins_manager->setUserSkin($skin_name)) {
            show_message('Skin updated successfully!', 'success');
            redirect('/skin_selection');
        } else {
            show_message('Failed to update skin.', 'error');
        }
    } else {
        show_message('Please select a skin.', 'error');
    }
}

$skins = $skins_manager->getAllSkins();
$current_skin = $skins_manager->getCurrentSkin();

include "../../includes/header.php";
?>

<div class="page-container">
    <div class="page-header">
        <h1><i class="fas fa-palette"></i> Choose Your Skin</h1>
        <p>Select a theme that matches your personal style and preferences.</p>
    </div>
    
    <div class="skins-selection">
        <div class="skins-grid">
            <?php foreach ($skins as $skin): ?>
            <div class="skin-option <?php echo $skin['name'] === $current_skin ? 'selected' : ''; ?>">
                <div class="skin-preview">
                    <?php 
                    $preview = $skins_manager->getSkinPreview($skin['name']);
                    if ($preview): 
                    ?>
                    <img src="<?php echo $preview; ?>" alt="<?php echo htmlspecialchars($skin['display_name']); ?> Preview">
                    <?php else: ?>
                    <div class="skin-preview-placeholder">
                        <i class="fas fa-palette"></i>
                        <span>No Preview</span>
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
                    
                    <div class="skin-actions">
                        <?php if ($skin['name'] === $current_skin): ?>
                        <span class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Current Skin
                        </span>
                        <?php else: ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="select_skin">
                            <input type="hidden" name="skin_name" value="<?php echo htmlspecialchars($skin['name']); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Select This Skin
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="skin-info">
        <h3>About Skins</h3>
        <p>Skins allow you to customize the visual appearance of the site to match your personal preferences. Each skin includes:</p>
        <ul>
            <li><strong>Color Scheme:</strong> Different color palettes and themes</li>
            <li><strong>Typography:</strong> Various font styles and sizes</li>
            <li><strong>Layout:</strong> Different arrangements and spacing</li>
            <li><strong>Components:</strong> Customized buttons, cards, and other elements</li>
        </ul>
        <p>You can change your skin at any time from this page. Your selection will be saved and applied across all pages.</p>
    </div>
</div>

<style>
.page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.page-header {
    text-align: center;
    margin-bottom: 3rem;
}

.page-header h1 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 2.5rem;
    font-weight: 700;
}

.page-header p {
    color: #7f8c8d;
    font-size: 1.1rem;
}

.skins-selection {
    margin-bottom: 3rem;
}

.skins-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
}

.skin-option {
    background: white;
    border: 2px solid #e1e8ed;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.skin-option:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.skin-option.selected {
    border-color: #3498db;
    box-shadow: 0 4px 16px rgba(52, 152, 219, 0.2);
}

.skin-preview {
    height: 250px;
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
    font-size: 4rem;
}

.skin-details {
    padding: 2rem;
}

.skin-details h3 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 1.3rem;
    font-weight: 600;
}

.skin-details p {
    color: #7f8c8d;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.skin-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
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
    font-size: 0.9rem;
}

.skin-actions {
    display: flex;
    justify-content: center;
}

.skin-info {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 12px;
    border-left: 4px solid #3498db;
}

.skin-info h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
    font-size: 1.2rem;
    font-weight: 600;
}

.skin-info p {
    color: #7f8c8d;
    margin-bottom: 1rem;
    line-height: 1.6;
}

.skin-info ul {
    color: #7f8c8d;
    margin-left: 1.5rem;
    margin-bottom: 1rem;
}

.skin-info li {
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .page-container {
        padding: 1rem;
    }
    
    .skins-grid {
        grid-template-columns: 1fr;
    }
    
    .skin-preview {
        height: 200px;
    }
}
</style>

<?php include "../../includes/footer.php"; ?>
