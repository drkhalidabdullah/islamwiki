<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Ensure createSlug function is available
if (!function_exists('createSlug')) {
    function createSlug($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
}

$page_title = 'Edit Template';
require_login();

// Check if user can edit templates
if (!is_editor()) {
    show_message('You do not have permission to edit templates.', 'error');
    redirect_with_return_url();
}

$template_id = (int)($_GET['id'] ?? 0);
$slug = $_GET['slug'] ?? '';
$title = $_GET['title'] ?? '';

// Handle ID, slug, or title parameters
if ($template_id) {
    // Get template by ID
    $stmt = $pdo->prepare("SELECT * FROM wiki_templates WHERE id = ?");
    $stmt->execute([$template_id]);
    $template = $stmt->fetch();
} elseif ($title) {
    // Handle namespace titles (e.g., Template:Colored_box or Template:Colored box)
    require_once '../../includes/wiki_functions.php';
    $parsed_title = parse_wiki_title($title);
    $namespace = $parsed_title['namespace'];
    $template_title = $parsed_title['title'];
    
    // Convert spaces to underscores in template title
    $template_title_normalized = str_replace(' ', '_', $template_title);
    
    // Create slug from namespace and title
    $slug = $namespace['name'] . ':' . createSlug($template_title);
    
    // Get template by slug or name (try both formats)
    $stmt = $pdo->prepare("SELECT * FROM wiki_templates WHERE slug = ? OR name = ? OR name = ?");
    $stmt->execute([$slug, $template_title_normalized, $template_title]);
    $template = $stmt->fetch();
    if ($template) {
        $template_id = $template['id'];
    }
} elseif ($slug) {
    // Get template by slug
    $stmt = $pdo->prepare("SELECT * FROM wiki_templates WHERE slug = ?");
    $stmt->execute([$slug]);
    $template = $stmt->fetch();
    if ($template) {
        $template_id = $template['id'];
    }
} else {
    show_message('Invalid template ID, slug, or title.', 'error');
    redirect_with_return_url('/admin');
}

if (!$template) {
    show_message('Template not found.', 'error');
    redirect_with_return_url('/admin');
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
    $parameters = $_POST['parameters'] ?? '';
    $documentation = $_POST['documentation'] ?? '';
    
    // Ensure template_type is valid, default to 'other' if not
    if (!in_array($template_type, array_keys($template_types))) {
        $template_type = 'other';
    }
    
    // Validation
    if (empty($name) || empty($content)) {
        $errors[] = 'Name and content are required.';
    }
    
    if (strlen($name) > 255) {
        $errors[] = 'Name must be less than 255 characters.';
    }
    
    // Create slug from name
    $slug = createSlug($name);
    
    // Check if slug already exists for other templates
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wiki_templates WHERE slug = ? AND id != ?");
    $stmt->execute([$slug, $template_id]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'A template with this name already exists.';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE wiki_templates 
                SET name = ?, slug = ?, content = ?, description = ?, template_type = ?, 
                    parameters = ?, documentation = ?, updated_at = NOW(), updated_by = ?
                WHERE id = ?
            ");
            if ($stmt->execute([$name, $slug, $content, $description, $template_type, 
                               $parameters, $documentation, $_SESSION['user_id'], $template_id])) {
                $success = 'Template updated successfully.';
                log_activity('template_updated', "Updated template ID: $template_id");
                
                // Redirect back to the template page
                redirect("/wiki/Template:$name");
            } else {
                $errors[] = 'Failed to update template.';
            }
        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

include "../../includes/header.php";
?>

<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_edit_template.css">

<div class="template-editor">
    <div class="editor-header">
        <h1>Edit Template: <?php echo htmlspecialchars($template['name']); ?></h1>
        <div class="editor-actions">
            <a href="/wiki/Template:<?php echo $template['name']; ?>" class="btn btn-secondary">Back to Template</a>
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
    
    <form method="POST" class="template-form">
        <div class="form-row">
            <div class="form-group">
                <label for="name">Template Name *</label>
                <input type="text" id="name" name="name" required 
                       value="<?php echo htmlspecialchars($template['name']); ?>"
                       placeholder="Enter template name">
            </div>
            
            <div class="form-group">
                <label for="templateType">Template Type</label>
                <select id="templateType" name="template_type" class="form-control">
                    <?php foreach ($template_types as $value => $label): ?>
                        <option value="<?php echo $value; ?>" 
                                <?php echo ($template['template_type'] === $value) ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="2" 
                      placeholder="Brief description of what this template does"><?php echo htmlspecialchars($template['description']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="content">Template Content *</label>
            <div class="template-editor-container">
                <div class="template-editor-main">
                    <textarea id="content" name="content" required 
                              placeholder="Enter template content with parameters..."><?php echo htmlspecialchars($template['content']); ?></textarea>
                </div>
                <div id="preview-container" style="display: none;">
                    <div id="preview-content"></div>
                </div>
            </div>
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
        
        <div class="form-group">
            <label for="parameters">Parameters (JSON)</label>
            <textarea id="parameters" name="parameters" rows="3" 
                      placeholder='{"param1": "Description of param1", "param2": "Description of param2"}'><?php echo htmlspecialchars($template['parameters']); ?></textarea>
            <small class="form-text text-muted">Define template parameters in JSON format for documentation</small>
        </div>
        
        <div class="form-group">
            <label for="documentation">Documentation</label>
            <textarea id="documentation" name="documentation" rows="4" 
                      placeholder="Documentation for this template..."><?php echo htmlspecialchars($template['documentation']); ?></textarea>
            <small class="form-text text-muted">This will be shown in the &lt;noinclude&gt; section</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Template</button>
            <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
            <button type="button" class="btn btn-info" onclick="togglePreview()">Preview</button>
        </div>
    </form>
</div>

<script src="/skins/bismillah/assets/js/wiki_edit_template.js"></script>

<?php include "../../includes/footer.php"; ?>
