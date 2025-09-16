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
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<?php
?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/skin_selection.css">
<?php
?>

<div class="page-container">
    <div class="page-header">
        <h1><i class="iw iw-palette"></i> Choose Your Skin</h1>
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
                        <i class="iw iw-palette"></i>
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
                            <i class="iw iw-check-circle"></i> Current Skin
                        </span>
                        <?php else: ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="select_skin">
                            <input type="hidden" name="skin_name" value="<?php echo htmlspecialchars($skin['name']); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="iw iw-check"></i> Select This Skin
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


<?php include "../../includes/footer.php"; ?>
