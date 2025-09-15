<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Manage Templates';
require_login();

// Check if user can manage templates
if (!is_editor()) {
    show_message('You do not have permission to manage templates.', 'error');
    redirect_with_return_url();
}

$errors = [];
$success = '';

// Get template types
$template_types = [
    'infobox' => 'Infobox',
    'citation' => 'Citation',
    'navbox' => 'Navigation Box',
    'stub' => 'Stub',
    'disambiguation' => 'Disambiguation',
    'main' => 'Main',
    'other' => 'Other'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name'] ?? '');
    $content = $_POST['content'] ?? '';
    $description = sanitize_input($_POST['description'] ?? '');
    $template_type = sanitize_input($_POST['template_type'] ?? 'other');
    
    // Ensure template_type is valid, default to 'other' if not
    if (!in_array($template_type, array_keys($template_types))) {
        $template_type = 'other';
    }
    
    if (empty($name) || empty($content)) {
        $errors[] = 'Name and content are required.';
    }
    
    if (strlen($name) > 255) {
        $errors[] = 'Name must be less than 255 characters.';
    }
    
    // Create slug from name
    $slug = createSlug($name);
    
    // Check if slug already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wiki_templates WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'A template with this name already exists.';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO wiki_templates (name, slug, namespace, template_type, content, description, created_by, updated_by) 
                VALUES (?, ?, 'Template', ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$name, $slug, $template_type, $content, $description, $_SESSION['user_id'], $_SESSION['user_id']])) {
                $success = 'Template created successfully.';
                log_activity('template_created', "Created template: $name");
            } else {
                $errors[] = 'Failed to create template.';
            }
        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get all templates
$stmt = $pdo->prepare("
    SELECT wt.*, u.username, u.display_name 
    FROM wiki_templates wt 
    LEFT JOIN users u ON wt.created_by = u.id 
    ORDER BY wt.name
");
$stmt->execute();
$templates = $stmt->fetchAll();

include "../../includes/header.php";
?>

<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_manage_templates.css">

<div class="templates-manager">
    <div class="manager-header">
        <h1>Manage Templates</h1>
        <div class="manager-actions">
            <button class="btn btn-primary" onclick="showCreateForm()">Create New Template</button>
        </div>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <!-- Create Template Form -->
    <div id="create-form" class="create-form" style="display: none;">
        <h2>Create New Template</h2>
        <form method="POST" class="template-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Template Name *</label>
                    <input type="text" id="name" name="name" required 
                           placeholder="Enter template name">
                </div>
                
                <div class="form-group">
                    <label for="templateType">Template Type (Optional)</label>
                    <select id="templateType" name="template_type" class="form-control">
                        <?php foreach ($template_types as $value => $label): ?>
                            <option value="<?php echo $value; ?>" <?php echo ($value === 'other') ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Choose a category to help organize your templates (defaults to 'Other')</small>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="2" 
                          placeholder="Brief description of what this template does"></textarea>
            </div>
            
            <div class="form-group">
                <label for="content">Template Content *</label>
                <textarea id="content" name="content" required rows="10" 
                          placeholder="Enter template content with parameters..."></textarea>
                <div class="template-help">
                    <h4>Template Syntax Help:</h4>
                    <ul>
                        <li><code>{{{param|default}}}</code> - Parameter with default value</li>
                        <li><code>{{#if:condition|true|false}}</code> - Conditional logic</li>
                        <li><code>{{{param}}}</code> - Required parameter</li>
                        <li><code>{{PAGENAME}}</code> - Magic word for current page name</li>
                    </ul>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Template</button>
                <button type="button" class="btn btn-secondary" onclick="hideCreateForm()">Cancel</button>
            </div>
        </form>
    </div>
    
    <!-- Templates List -->
    <div class="templates-list">
        <h2>Existing Templates</h2>
        
        <?php if (empty($templates)): ?>
            <div class="no-templates">
                <p>No templates found. <a href="#" onclick="showCreateForm()">Create your first template</a>.</p>
            </div>
        <?php else: ?>
            <div class="templates-grid">
                <?php foreach ($templates as $template): ?>
                    <div class="template-card">
                        <div class="template-header">
                            <h3>
                                <a href="/wiki/Template:<?php echo htmlspecialchars($template['name']); ?>">
                                    <?php echo htmlspecialchars($template['name']); ?>
                                </a>
                            </h3>
                            <div class="template-type">
                                <?php echo htmlspecialchars($template_types[$template['template_type']] ?? 'Other'); ?>
                            </div>
                        </div>
                        
                        <div class="template-content">
                            <?php if ($template['description']): ?>
                                <p class="template-description"><?php echo htmlspecialchars($template['description']); ?></p>
                            <?php endif; ?>
                            
                            <div class="template-preview">
                                <pre><?php echo htmlspecialchars(substr($template['content'], 0, 200)) . (strlen($template['content']) > 200 ? '...' : ''); ?></pre>
                            </div>
                        </div>
                        
                        <div class="template-actions">
                            <a href="/wiki/Template:<?php echo htmlspecialchars($template['name']); ?>" class="btn btn-sm btn-outline">View</a>
                            <a href="/wiki/Template:<?php echo htmlspecialchars($template['name']); ?>/edit" class="btn btn-sm btn-primary">Edit</a>
                            <a href="/pages/wiki/delete_template.php?id=<?php echo $template['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this template?')">Delete</a>
                        </div>
                        
                        <div class="template-meta">
                            <small>
                                Created by <?php echo htmlspecialchars($template['display_name'] ?: $template['username']); ?>
                                on <?php echo date('M j, Y', strtotime($template['created_at'])); ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function showCreateForm() {
    document.getElementById('create-form').style.display = 'block';
    document.getElementById('name').focus();
}

function hideCreateForm() {
    document.getElementById('create-form').style.display = 'none';
    document.querySelector('.template-form').reset();
}
</script>

<?php include "../../includes/footer.php"; ?>