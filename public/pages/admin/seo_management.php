<?php
/**
 * SEO Management Admin Page
 * 
 * This page provides an interface for managing SEO settings,
 * templates, and analytics for the MuslimWiki site.
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

// Check if user is admin
if (!is_logged_in() || !is_admin()) {
    header('Location: /login');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_settings':
                updateSEOSettings();
                break;
            case 'create_template':
                createSEOTemplate();
                break;
            case 'update_template':
                updateSEOTemplate();
                break;
            case 'delete_template':
                deleteSEOTemplate();
                break;
            case 'update_metadata':
                updateSEOMetadata();
                break;
        }
    }
}

// Get SEO data
$seo_templates = getSEOTemplates();
$seo_metadata = getSEOMetadata();
$seo_analytics = getSEOAnalytics();
$seo_settings = getSEOSettings();

function updateSEOSettings() {
    global $pdo;
    
    $settings = [
        'default_site_name' => $_POST['default_site_name'] ?? 'MuslimWiki',
        'default_locale' => $_POST['default_locale'] ?? 'en_EN',
        'enable_open_graph' => isset($_POST['enable_open_graph']) ? 1 : 0,
        'enable_twitter_cards' => isset($_POST['enable_twitter_cards']) ? 1 : 0,
        'enable_structured_data' => isset($_POST['enable_structured_data']) ? 1 : 0,
    ];
    
    // Update settings in database or config file
    // Implementation depends on your configuration system
    $_SESSION['success'] = 'SEO settings updated successfully';
}

function createSEOTemplate() {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO seo_templates (name, description, template_content) VALUES (?, ?, ?)");
    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['template_content']
    ]);
    
    $_SESSION['success'] = 'SEO template created successfully';
}

function updateSEOTemplate() {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE seo_templates SET name = ?, description = ?, template_content = ? WHERE id = ?");
    $stmt->execute([
        $_POST['name'],
        $_POST['description'],
        $_POST['template_content'],
        $_POST['template_id']
    ]);
    
    $_SESSION['success'] = 'SEO template updated successfully';
}

function deleteSEOTemplate() {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM seo_templates WHERE id = ?");
    $stmt->execute([$_POST['template_id']]);
    
    $_SESSION['success'] = 'SEO template deleted successfully';
}

function updateSEOMetadata() {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE seo_metadata SET title = ?, description = ?, keywords = ?, priority = ?, changefreq = ? WHERE id = ?");
    $stmt->execute([
        $_POST['title'],
        $_POST['description'],
        $_POST['keywords'],
        $_POST['priority'],
        $_POST['changefreq'],
        $_POST['metadata_id']
    ]);
    
    $_SESSION['success'] = 'SEO metadata updated successfully';
}

function getSEOTemplates() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM seo_templates ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSEOMetadata() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM seo_metadata ORDER BY page_id, page_type");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSEOAnalytics() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM seo_analytics ORDER BY seo_score DESC LIMIT 50");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSEOSettings() {
    // Return current SEO settings
    return [
        'default_site_name' => 'MuslimWiki',
        'default_locale' => 'en_EN',
        'enable_open_graph' => true,
        'enable_twitter_cards' => true,
        'enable_structured_data' => true,
    ];
}

include '../../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h1>SEO Management</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <!-- SEO Settings Tab -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3>SEO Settings</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_settings">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="default_site_name">Default Site Name</label>
                                    <input type="text" class="form-control" id="default_site_name" name="default_site_name" 
                                           value="<?php echo htmlspecialchars($seo_settings['default_site_name']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="default_locale">Default Locale</label>
                                    <input type="text" class="form-control" id="default_locale" name="default_locale" 
                                           value="<?php echo htmlspecialchars($seo_settings['default_locale']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="enable_open_graph" name="enable_open_graph" 
                                           <?php echo $seo_settings['enable_open_graph'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_open_graph">Enable Open Graph</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="enable_twitter_cards" name="enable_twitter_cards" 
                                           <?php echo $seo_settings['enable_twitter_cards'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_twitter_cards">Enable Twitter Cards</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="enable_structured_data" name="enable_structured_data" 
                                           <?php echo $seo_settings['enable_structured_data'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_structured_data">Enable Structured Data</label>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update Settings</button>
                    </form>
                </div>
            </div>
            
            <!-- SEO Templates Tab -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3>SEO Templates</h3>
                    <button class="btn btn-success" data-toggle="modal" data-target="#createTemplateModal">Create Template</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Template Content</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($seo_templates as $template): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($template['name']); ?></td>
                                    <td><?php echo htmlspecialchars($template['description']); ?></td>
                                    <td><code><?php echo htmlspecialchars(substr($template['template_content'], 0, 100)); ?>...</code></td>
                                    <td>
                                        <span class="badge badge-<?php echo $template['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $template['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="editTemplate(<?php echo $template['id']; ?>)">Edit</button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteTemplate(<?php echo $template['id']; ?>)">Delete</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- SEO Analytics Tab -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3>SEO Analytics</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Page ID</th>
                                    <th>Page Type</th>
                                    <th>SEO Score</th>
                                    <th>Title Length</th>
                                    <th>Description Length</th>
                                    <th>Has OG Tags</th>
                                    <th>Has Twitter Tags</th>
                                    <th>Has Structured Data</th>
                                    <th>Last Checked</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($seo_analytics as $analytics): ?>
                                <tr>
                                    <td><?php echo $analytics['page_id']; ?></td>
                                    <td><?php echo htmlspecialchars($analytics['page_type']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $analytics['seo_score'] >= 80 ? 'success' : ($analytics['seo_score'] >= 60 ? 'warning' : 'danger'); ?>">
                                            <?php echo $analytics['seo_score']; ?>%
                                        </span>
                                    </td>
                                    <td><?php echo $analytics['meta_title_length']; ?></td>
                                    <td><?php echo $analytics['meta_description_length']; ?></td>
                                    <td><?php echo $analytics['has_og_tags'] ? 'Yes' : 'No'; ?></td>
                                    <td><?php echo $analytics['has_twitter_tags'] ? 'Yes' : 'No'; ?></td>
                                    <td><?php echo $analytics['has_structured_data'] ? 'Yes' : 'No'; ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($analytics['last_checked'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Template Modal -->
<div class="modal fade" id="createTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create SEO Template</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_template">
                    
                    <div class="form-group">
                        <label for="name">Template Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="template_content">Template Content</label>
                        <textarea class="form-control" id="template_content" name="template_content" rows="10" required></textarea>
                        <small class="form-text text-muted">Use the {{#seo:|...}} syntax for the template.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Template</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTemplate(id) {
    // Implementation for editing template
    console.log('Edit template:', id);
}

function deleteTemplate(id) {
    if (confirm('Are you sure you want to delete this template?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_template">
            <input type="hidden" name="template_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../../includes/footer.php'; ?>

