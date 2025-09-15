<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/wiki_functions.php';
require_once __DIR__ . '/../../includes/markdown/MarkdownParser.php';
require_once __DIR__ . '/../../includes/markdown/AdvancedWikiParser.php';
require_once __DIR__ . '/../../includes/markdown/SecureWikiParser.php';

// Ensure createSlug function is available
if (!function_exists('createSlug')) {
    function createSlug($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
}

// Check maintenance mode
check_maintenance_mode();

// Check if wiki is enabled
$enable_wiki = get_system_setting('enable_wiki', true);
if (!$enable_wiki) {
    show_message('Wiki system is currently disabled.', 'error');
    redirect('/dashboard');
}

$page_title = 'Template';

$slug = $_GET['slug'] ?? '';
$title = $_GET['title'] ?? '';

// Handle namespace titles (e.g., Template:Colored_box or Template:Colored box)
if ($title) {
    $parsed_title = parse_wiki_title($title);
    $namespace = $parsed_title['namespace'];
    $template_title = $parsed_title['title'];
    
    // Convert spaces to underscores in template title
    $template_title_normalized = str_replace(' ', '_', $template_title);
    
    // Create slug from namespace and title
    $slug = $namespace['name'] . ':' . createSlug($template_title);
} elseif (!$slug) {
    redirect('index.php');
}

// Get template from database - try multiple formats
$stmt = $pdo->prepare("
    SELECT wt.*, u.username, u.display_name 
    FROM wiki_templates wt 
    LEFT JOIN users u ON wt.created_by = u.id 
    WHERE wt.slug = ? OR wt.name = ? OR wt.slug = ? OR wt.name = ? OR wt.name = ?
");
$stmt->execute([
    $slug, 
    str_replace('template:', '', strtolower($slug)), 
    str_replace('Template:', '', $slug),
    $template_title_normalized ?? '',
    $template_title ?? '' // Also try the original title with spaces
]);
$template = $stmt->fetch();

if (!$template) {
    // Check if this is a template namespace request
    if ($title && strpos($title, 'Template:') === 0) {
        $template_name = substr($title, 9); // Remove "Template:" prefix
        redirect("/pages/wiki/create_template.php?name=" . urlencode($template_name));
    }
    
    redirect("not_found.php?slug=" . urlencode($slug) . "&title=" . urlencode(ucfirst(str_replace('-', ' ', $slug))));
}

$page_title = $template['name'];

// Parse template parameters if they exist
$parameters = [];
if ($template['parameters']) {
    $parameters = json_decode($template['parameters'], true) ?: [];
}

// Get template usage count
$stmt = $pdo->prepare("
    SELECT COUNT(*) as usage_count 
    FROM wiki_template_usage 
    WHERE template_id = ?
");
$stmt->execute([$template['id']]);
$usage_data = $stmt->fetch();
$usage_count = $usage_data['usage_count'] ?? 0;

include '../../includes/header.php';
?>

<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_template.css">

<div class="template-page">
    <div class="template-header">
        <div class="template-title-section">
            <h1 class="template-title">
                <span class="template-namespace">Template:</span>
                <?php echo htmlspecialchars($template['name']); ?>
            </h1>
            <div class="template-meta">
                <span class="template-type"><?php echo htmlspecialchars($template['template_type'] ?? 'other'); ?></span>
                <span class="template-usage">Used <?php echo number_format($usage_count); ?> times</span>
            </div>
        </div>
        
        <div class="template-actions">
            <a href="/wiki/Template:<?php echo htmlspecialchars($template['name']); ?>/edit" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Template
            </a>
            <a href="/wiki/manage/templates" class="btn btn-secondary">
                <i class="fas fa-list"></i> Manage Templates
            </a>
        </div>
    </div>
    
    <div class="template-content">
        <div class="template-main">
            <!-- Template Documentation -->
            <?php if ($template['description'] || $template['documentation']): ?>
            <div class="template-documentation">
                <h2>Documentation</h2>
                <?php if ($template['description']): ?>
                    <p class="template-description"><?php echo htmlspecialchars($template['description']); ?></p>
                <?php endif; ?>
                
                <?php if ($template['documentation']): ?>
                    <div class="template-doc-content">
                        <?php
                        $parser = new SecureWikiParser('');
                        echo $parser->parse($template['documentation']);
                        ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Template Parameters -->
            <?php if (!empty($parameters)): ?>
            <div class="template-parameters">
                <h2>Parameters</h2>
                <div class="parameters-list">
                    <?php foreach ($parameters as $param => $description): ?>
                    <div class="parameter-item">
                        <code class="parameter-name">{{{<?php echo htmlspecialchars($param); ?>}}}</code>
                        <span class="parameter-description"><?php echo htmlspecialchars($description); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Template Usage Example -->
            <div class="template-usage-example">
                <h2>Usage</h2>
                <div class="usage-example">
                    <h3>Basic Usage:</h3>
                    <pre><code>{{<?php echo htmlspecialchars($template['name']); ?>
| param1 = value1
| param2 = value2
| content = Your content here
}}</code></pre>
                </div>
            </div>
            
            <!-- Template Source -->
            <div class="template-source">
                <h2>Template Source</h2>
                <div class="source-code">
                    <pre><code><?php echo htmlspecialchars($template['content']); ?></code></pre>
                </div>
            </div>
        </div>
        
        <div class="template-sidebar">
            <!-- Template Info -->
            <div class="template-info">
                <h3>Template Information</h3>
                <div class="info-item">
                    <strong>Name:</strong> <?php echo htmlspecialchars($template['name']); ?>
                </div>
                <div class="info-item">
                    <strong>Type:</strong> <?php echo htmlspecialchars($template['template_type'] ?? 'other'); ?>
                </div>
                <div class="info-item">
                    <strong>Created:</strong> <?php echo date('M j, Y', strtotime($template['created_at'])); ?>
                </div>
                <div class="info-item">
                    <strong>Last Modified:</strong> <?php echo date('M j, Y', strtotime($template['updated_at'])); ?>
                </div>
                <div class="info-item">
                    <strong>Created By:</strong> <?php echo htmlspecialchars($template['display_name'] ?: $template['username']); ?>
                </div>
                <div class="info-item">
                    <strong>Usage Count:</strong> <?php echo number_format($usage_count); ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="template-actions-sidebar">
                <h3>Quick Actions</h3>
                <ul class="actions-list">
                    <li>
                        <a href="/wiki/Template:<?php echo htmlspecialchars($template['name']); ?>/edit">
                            <i class="fas fa-edit"></i> Edit Template
                        </a>
                    </li>
                    <li>
                        <a href="/wiki/manage/templates">
                            <i class="fas fa-list"></i> Manage Templates
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="copyTemplateCode()">
                            <i class="fas fa-copy"></i> Copy Template Code
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="copyUsageExample()">
                            <i class="fas fa-code"></i> Copy Usage Example
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Template Statistics -->
            <div class="template-stats">
                <h3>Statistics</h3>
                <div class="stats-item">
                    <span class="stat-label">Usage Count:</span>
                    <span class="stat-value"><?php echo number_format($usage_count); ?></span>
                </div>
                <div class="stats-item">
                    <span class="stat-label">Content Size:</span>
                    <span class="stat-value"><?php echo number_format(strlen($template['content'])); ?> chars</span>
                </div>
                <div class="stats-item">
                    <span class="stat-label">Parameters:</span>
                    <span class="stat-value"><?php echo count($parameters); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyTemplateCode() {
    const code = `<?php echo addslashes($template['content']); ?>`;
    navigator.clipboard.writeText(code).then(function() {
        alert('Template code copied to clipboard!');
    });
}

function copyUsageExample() {
    const code = `{{<?php echo htmlspecialchars($template['name']); ?>
| param1 = value1
| param2 = value2
| content = Your content here
}}`;
    navigator.clipboard.writeText(code).then(function() {
        alert('Usage example copied to clipboard!');
    });
}
</script>

<?php include "../../includes/footer.php"; ?>
