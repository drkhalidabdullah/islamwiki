<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../config/database.php';

$page_title = 'Create Article';
check_maintenance_mode();
require_login();

// Check if wiki is enabled
$enable_wiki = get_system_setting('enable_wiki', true);
if (!$enable_wiki) {
    show_message('Wiki system is currently disabled.', 'error');
    redirect('/dashboard');
}

// Check if user can create articles
if (!is_editor()) {
    show_message('You do not have permission to create articles.', 'error');
    redirect_with_return_url();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $excerpt = sanitize_input($_POST['excerpt'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $status = sanitize_input($_POST['status'] ?? 'draft');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
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
    
    // Create slug from title
    $slug = createSlug($title);
    
    // Check if slug already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wiki_articles WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'An article with this title already exists.';
    }
    
    if (empty($errors)) {
        // Remove any H1 headings from content to prevent duplicate titles
        $content = preg_replace('/^# .*$/m', '', $content);
        $content = trim($content);
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO wiki_articles 
                (title, slug, content, excerpt, category_id, author_id, status, is_featured, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([
                $title,
                $slug,
                $content,
                $excerpt,
                $category_id ?: null,
                $_SESSION['user_id'],
                $status,
                $is_featured
            ]);
            
            $article_id = $pdo->lastInsertId();
            
            // If published, set published_at

            // Create initial version entry
            $stmt = $pdo->prepare("
                INSERT INTO article_versions 
                (article_id, version_number, title, content, excerpt, changes_summary, created_by) 
                VALUES (?, 1, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $article_id,
                $title,
                $content,
                $excerpt,
                "Initial creation",
                $_SESSION['user_id']
            ]);
            if ($status === 'published') {
                $stmt = $pdo->prepare("UPDATE wiki_articles SET published_at = NOW() WHERE id = ?");
                $stmt->execute([$article_id]);
            }
            
            show_message('Article created successfully!', 'success');
            redirect("/wiki/" . urlencode($slug));
            
        } catch (Exception $e) {
            $errors[] = 'Error creating article: ' . $e->getMessage();
        }
    }
}

// Get categories
$stmt = $pdo->query("SELECT * FROM content_categories WHERE is_active = 1 ORDER BY name");
$categories = $stmt->fetchAll();

include "../../includes/header.php";;
?>

<div class="article-editor">
    <div class="editor-header">
        <h1>Create New Article</h1>
        <div class="editor-actions">
            <a href="/wiki" class="btn btn-secondary">Back to Wiki</a>
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
    
    <form method="POST" class="article-form">
        <div class="form-row">
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" required 
                       value="<?php echo htmlspecialchars($_POST['title'] ?? $_GET['title'] ?? ''); ?>"
                       placeholder="Enter article title">
            </div>
            
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" 
                                <?php echo (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="excerpt">Excerpt</label>
            <textarea id="excerpt" name="excerpt" rows="3" 
                      placeholder="Brief description of the article"><?php echo htmlspecialchars($_POST['excerpt'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="content">Content *</label>
            <div class="wiki-editor-container">
                <div class="wiki-editor-main">
                    <textarea id="content" name="content" required 
                              placeholder="Write your article content using Markdown..."><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                </div>
                <div id="preview-container" style="display: none;">
                    <div id="preview-content"></div>
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="draft" <?php echo (($_POST['status'] ?? 'draft') === 'draft') ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo (($_POST['status'] ?? '') === 'published') ? 'selected' : ''; ?>>Published</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_featured" value="1" 
                           <?php echo isset($_POST['is_featured']) ? 'checked' : ''; ?>>
                    Featured Article
                </label>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Article</button>
            <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
        </div>
    </form>
</div>

<style>
.article-editor {
    max-width: 1200px;
    margin: 0 auto;
}

.editor-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.editor-header h1 {
    color: #2c3e50;
    margin: 0;
}

.editor-actions {
    display: flex;
    gap: 1rem;
}

.article-form {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    margin: 0;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert ul {
    margin: 0;
    padding-left: 1.5rem;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .editor-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<link rel="stylesheet" href="/assets/css/wiki-editor.css">
<script src="/assets/js/wiki-editor.js"></script>

<?php include "../../includes/footer.php";; ?>
