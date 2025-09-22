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

$page_title = 'Edit Article';
require_login();

// Check if user can edit articles
if (!is_editor()) {
    show_message('You do not have permission to edit articles.', 'error');
    redirect_with_return_url();
}

$article_id = (int)($_GET['id'] ?? 0);
$slug = $_GET['slug'] ?? '';
$title = $_GET['title'] ?? '';

// Handle ID, slug, or title parameters
if ($article_id) {
    // Get article by ID
    $stmt = $pdo->prepare("SELECT * FROM wiki_articles WHERE id = ?");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();
} elseif ($title) {
    // Handle namespace titles (e.g., Template:Colored_box)
    require_once '../../includes/wiki_functions.php';
    $parsed_title = parse_wiki_title($title);
    $namespace = $parsed_title['namespace'];
    $article_title = $parsed_title['title'];
    
    // Create slug from namespace and title (preserve case for namespace)
    $slug = $namespace['name'] . ':' . createSlug($article_title);
    
    // Get article by slug
    $stmt = $pdo->prepare("SELECT * FROM wiki_articles WHERE slug = ?");
    $stmt->execute([$slug]);
    $article = $stmt->fetch();
    if ($article) {
        $article_id = $article['id'];
    }
} elseif ($slug) {
    // Get article by slug
    $stmt = $pdo->prepare("SELECT * FROM wiki_articles WHERE slug = ?");
    $stmt->execute([$slug]);
    $article = $stmt->fetch();
    if ($article) {
        $article_id = $article['id'];
    }
} else {
    show_message('Invalid article ID, slug, or title.', 'error');
    redirect_with_return_url('/admin');
}

if (!$article) {
    show_message('Article not found.', 'error');
    redirect_with_return_url('/admin');
}

$errors = [];
$success = '';

// Categories are now handled via [[Category:Name]] syntax in content

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $excerpt = sanitize_input($_POST['excerpt'] ?? '');
    $status = sanitize_input($_POST['status'] ?? 'draft');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    $changes_summary = sanitize_input($_POST['changes_summary'] ?? '');
    
    // Validation
    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    
    if (empty($content)) {
        $errors[] = 'Content is required.';
    }
    
    if (strlen($title) > 255) {
        $errors[] = 'Title must be less than 255 characters.';
    }
    
    // Create slug from title, but preserve special slugs like Main_Page
    if ($article['slug'] === 'Main_Page' && $title === 'Main Page') {
        // Keep the special Main_Page slug
        $slug = 'Main_Page';
    } else {
        $slug = generate_slug($title);
    }
    
    // Check if slug already exists for other articles
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wiki_articles WHERE slug = ? AND id != ?");
    $stmt->execute([$slug, $article_id]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'An article with this title already exists.';
    }
    
    if (empty($errors)) {
        // Remove any H1 headings from content to prevent duplicate titles
        $content = preg_replace('/^# .*$/m', '', $content);
        $content = trim($content);
        
        try {
            $stmt = $pdo->prepare("
                UPDATE wiki_articles 
                SET title = ?, content = ?, excerpt = ?, status = ?, slug = ?, is_featured = ?, updated_at = NOW()
                WHERE id = ?
            ");
            if ($stmt->execute([$title, $content, $excerpt, $status, $slug, $is_featured, $article_id])) {
                // Create new version entry for the edit
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO article_versions 
                        (article_id, version_number, title, content, excerpt, changes_summary, created_by) 
                        VALUES (?, (SELECT COALESCE(MAX(version_number), 0) + 1 FROM article_versions WHERE article_id = ?), ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $article_id,
                        $article_id,
                        $title,
                        $content,
                        $excerpt,
                        $changes_summary ?: "Updated article",
                        $_SESSION['user_id']
                    ]);
                } catch (Exception $e) {
                    // Log the error but don't fail the update
                    error_log("Version creation failed: " . $e->getMessage());
                }
                $success = 'Article updated successfully.';
                log_activity('article_updated', "Updated article ID: $article_id");
                
                // Redirect back to the article page
                redirect("/wiki/$slug");
            } else {
                $errors[] = 'Failed to update article.';
            }
        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Article data is already fetched above

include "../../includes/header.php";;

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_edit_article.css">
<?php
?>

<div class="article-editor">
    <div class="editor-header">
        <h1>Edit Article</h1>
        <div class="editor-actions">
            <a href="/wiki/<?php echo $article['slug']; ?>" class="btn btn-secondary">Back to Article</a>
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
    
    <form method="POST" class="article-form">
        <div class="form-group">
            <label for="title">Title *</label>
            <input type="text" id="title" name="title" required 
                   value="<?php echo htmlspecialchars($article['title']); ?>"
                   placeholder="Enter article title">
        </div>
        
        <div class="form-group">
            <label for="excerpt">Excerpt</label>
            <textarea id="excerpt" name="excerpt" rows="3" 
                      placeholder="Brief description of the article"><?php echo htmlspecialchars($article['excerpt']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="content">Content *</label>
            <div class="wiki-editor-container">
                <div class="wiki-editor-main">
                    <div class="wiki-toolbar">
                        <div class="toolbar-group">
                            <button type="button" class="toolbar-btn" onclick="insertText('**', '**')" title="Bold">
                                <strong>B</strong>
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertText('*', '*')" title="Italic">
                                <em>I</em>
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertText('`', '`')" title="Code">
                                <code>&lt;/&gt;</code>
                            </button>
                        </div>
                        
                        <div class="toolbar-group">
                            <button type="button" class="toolbar-btn" onclick="insertText('# ', '')" title="Heading 1">
                                H1
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertText('## ', '')" title="Heading 2">
                                H2
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertText('### ', '')" title="Heading 3">
                                H3
                            </button>
                        </div>
                        
                        <div class="toolbar-group">
                            <button type="button" class="toolbar-btn" onclick="insertText('[[', ']]')" title="Wiki Link">
                                [[ ]]
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertText('[', '](url)')" title="External Link">
                                🔗
                            </button>
                        </div>
                        
                        <div class="toolbar-group">
                            <button type="button" class="toolbar-btn" onclick="insertText('* ', '')" title="Bullet List">
                                • List
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertText('1. ', '')" title="Numbered List">
                                1. List
                            </button>
                            <button type="button" class="toolbar-btn" onclick="insertText('> ', '')" title="Quote">
                                " Quote
                            </button>
                        </div>
                        
                        <div class="toolbar-group">
                            <button type="button" class="toolbar-btn" onclick="togglePreview()" title="Toggle Preview">
                                👁️ Preview
                            </button>
                        </div>
                    </div>
                    <textarea id="content" name="content" required 
                              placeholder="Write your article content using Markdown..."><?php echo htmlspecialchars($article['content']); ?></textarea>
                </div>
                <div id="preview-container" class="hidden">
                    <div id="preview-content"></div>
                </div>
            </div>
            <div class="form-help">
                <strong>Categories:</strong> Add categories at the end of your content using <code>[[Category:Category Name]]</code> syntax.<br>
                <strong>Example:</strong> <code>[[Category:Islam]] [[Category:Religions]] [[Category:Theology]]</code>
            </div>
        </div>
        
        <div class="form-group">
            <label for="changes_summary">Edit Summary</label>
            <input type="text" id="changes_summary" name="changes_summary" 
                   placeholder="Briefly describe what you changed (optional)">
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="draft" <?php echo ($article['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo ($article['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_featured" value="1" 
                           <?php echo $article['is_featured'] ? 'checked' : ''; ?>>
                    Featured Article
                </label>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Article</button>
            <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
        </div>
    </form>
</div>


<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki.css">
<script>
// Global functions for toolbar
function insertText(before, after) {
    const contentTextarea = document.getElementById('content');
    if (!contentTextarea) return;
    
    const start = contentTextarea.selectionStart;
    const end = contentTextarea.selectionEnd;
    const selectedText = contentTextarea.value.substring(start, end);
    const replacement = before + (selectedText || 'text') + after;
    
    contentTextarea.value = contentTextarea.value.substring(0, start) + replacement + contentTextarea.value.substring(end);
    contentTextarea.focus();
    contentTextarea.setSelectionRange(start + before.length, start + before.length + (selectedText || 'text').length);
    
    updatePreview();
}

function togglePreview() {
    const previewContainer = document.getElementById('preview-container');
    if (!previewContainer) {
        console.error('Preview container not found');
        return;
    }
    
    if (previewContainer.classList.contains('hidden')) {
        console.log('Showing preview');
        previewContainer.classList.remove('hidden');
        updatePreview();
    } else {
        console.log('Hiding preview');
        previewContainer.classList.add('hidden');
    }
}

function updatePreview() {
    const contentTextarea = document.getElementById('content');
    const previewContent = document.getElementById('preview-content');
    
    if (!contentTextarea || !previewContent) {
        console.error('Content textarea or preview content not found');
        return;
    }
    
    console.log('Updating preview...');
    
    const content = contentTextarea.value;
    if (!content.trim()) {
        previewContent.innerHTML = '<p><em>No content to preview</em></p>';
        return;
    }
    
    previewContent.innerHTML = '<div class="preview-loading"><i class="iw iw-spinner iw-spin"></i> Parsing content...</div>';
    
    fetch('/wiki/preview', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'content=' + encodeURIComponent(content)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.text();
    })
    .then(html => {
        previewContent.innerHTML = html;
    })
    .catch(error => {
        console.error('Preview error:', error);
        previewContent.innerHTML = `<p style="color: red;">Preview error: ${error.message}</p>`;
    })
    .finally(() => {
        // Heights are handled by flexbox layout
    });
}

// Heights are now handled by flexbox layout automatically

// Auto-update preview on content change
document.addEventListener('DOMContentLoaded', function() {
    const contentTextarea = document.getElementById('content');
    const previewContainer = document.getElementById('preview-container');
    
    if (contentTextarea && previewContainer) {
        contentTextarea.addEventListener('input', function() {
            if (!previewContainer.classList.contains('hidden')) {
                clearTimeout(window.previewTimeout);
                window.previewTimeout = setTimeout(updatePreview, 1000);
            }
        });
    }
});
</script>

<?php include "../../includes/footer.php";; ?>
