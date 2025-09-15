<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/markdown/TemplateParser.php';

$page_title = 'Create Template';
require_login();

// Check if user can create templates
if (!is_editor()) {
    show_message('You do not have permission to create templates.', 'error');
    redirect_with_return_url();
}

$template_parser = new TemplateParser($pdo);
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

// Get template name from URL parameter and normalize spaces to underscores
$template_name = str_replace(' ', '_', $_GET['name'] ?? '');

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
    } else {
        // Create template in database
        if ($template_parser->createTemplate($name, $content, $description, [])) {
            // Also create as wiki article in Template namespace
            $template_namespace = get_wiki_namespace('Template');
            if ($template_namespace) {
                $slug = strtolower($template_namespace['name']) . ':' . createSlug($name);
                
                $stmt = $pdo->prepare("
                    INSERT INTO wiki_articles (title, slug, content, excerpt, author_id, status, namespace_id, content_model, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, 'published', ?, 'wikitext', NOW(), NOW())
                ");
                
                if ($stmt->execute([$name, $slug, $content, $description, $_SESSION['user_id'], $template_namespace['id']])) {
                    $success = 'Template created successfully!';
                    redirect("/wiki/Template:" . urlencode($name));
                } else {
                    $errors[] = 'Template created in database but failed to create wiki article.';
                }
            } else {
                $errors[] = 'Template namespace not found.';
            }
        } else {
            $errors[] = 'Error creating template.';
        }
    }
}

include '../../includes/header.php';
?>

<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_create_template.css">

<div class="create-template-container">
    <div class="page-header">
        <h1><i class="fas fa-puzzle-piece"></i> Create Template</h1>
        <p>Create a new reusable template for your wiki</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="template-form-section">
        <form method="POST" class="template-form">
            <div class="form-group">
                <label for="templateName">Template Name *</label>
                <input type="text" id="templateName" name="name" required 
                       value="<?php echo htmlspecialchars($template_name); ?>" 
                       class="form-control" placeholder="Enter template name">
                <small class="form-help">This will be the name used when calling the template with {{TemplateName}}</small>
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
            
            <div class="form-group">
                <label for="templateDescription">Description</label>
                <textarea id="templateDescription" name="description" class="form-control" rows="3" 
                          placeholder="Brief description of what this template does"></textarea>
            </div>
            
            <div class="form-group">
                <label for="templateContent">Template Content *</label>
                <textarea id="templateContent" name="content" required class="form-control" rows="15" 
                          placeholder="Enter template content with parameters like {{param1|default_value}}"></textarea>
                <small class="form-help">
                    Use {{param_name|default_value}} for parameters. 
                    <a href="#" onclick="showTemplateHelp()">View help</a>
                </small>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="previewTemplate()">
                    <i class="fas fa-eye"></i> Preview
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Template
                </button>
            </div>
        </form>
    </div>

    <!-- Preview Section -->
    <div id="previewSection" class="preview-section" style="display: none;">
        <h3>Template Preview</h3>
        <div id="previewContent" class="preview-content"></div>
    </div>
</div>

<!-- Template Help Modal -->
<div id="helpModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Template Syntax Help</h2>
            <span class="close" onclick="closeHelpModal()">&times;</span>
        </div>
        
        <div class="help-content">
            <h3>Basic Parameters</h3>
            <ul>
                <li><code>{{param_name|default_value}}</code> - Named parameter with default</li>
                <li><code>{{1|default}}</code> - Numbered parameter</li>
                <li><code>{{param_name}}</code> - Required parameter</li>
            </ul>
            
            <h3>Conditional Logic</h3>
            <ul>
                <li><code>{{#if:condition|true_value|false_value}}</code> - If statement</li>
                <li><code>{{#ifeq:value1|value2|true_value|false_value}}</code> - If equal</li>
            </ul>
            
            <h3>Magic Words</h3>
            <ul>
                <li><code>{{PAGENAME}}</code> - Current page name</li>
                <li><code>{{CURRENTYEAR}}</code> - Current year</li>
                <li><code>{{SITENAME}}</code> - Site name</li>
            </ul>
            
            <h3>Example Template</h3>
            <pre><code>{{Infobox
|name={{1|Unknown}}
|type={{2|General}}
|status={{3|Active}}
|created={{CURRENTYEAR}}
}}</code></pre>
        </div>
    </div>
</div>

<script src="/skins/bismillah/assets/js/wiki_create_template.js"></script>

<?php include '../../includes/footer.php'; ?>
