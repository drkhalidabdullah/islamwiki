<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Edit Article';
require_login();

// Check if user can edit articles
if (!is_editor()) {
    show_message('You do not have permission to edit articles.', 'error');
    redirect('/dashboard');
}

$article_id = (int)($_GET['id'] ?? 0);

if (!$article_id) {
    show_message('Invalid article ID.', 'error');
    redirect('/admin');
}

$errors = [];
$success = '';

// Get categories
$stmt = $pdo->query("SELECT * FROM content_categories ORDER BY name");
$categories = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $excerpt = sanitize_input($_POST['excerpt'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
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
    
    // Create slug from title
    $slug = createSlug($title);
    
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
                SET title = ?, content = ?, excerpt = ?, category_id = ?, status = ?, slug = ?, is_featured = ?, updated_at = NOW()
                WHERE id = ?
            ");
            if ($stmt->execute([$title, $content, $excerpt, $category_id ?: null, $status, $slug, $is_featured, $article_id])) {

            // Create new version entry for the edit
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
                $_SESSION[user_id]
            ]);
                $success = 'Article updated successfully.';
                log_activity('article_updated', "Updated article ID: $article_id");
            } else {
                $errors[] = 'Failed to update article.';
            }
        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get article data
$stmt = $pdo->prepare("SELECT * FROM wiki_articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    show_message('Article not found.', 'error');
    redirect('/admin');
}

include "../../includes/header.php";;
?>

<div class="article-editor">
    <div class="editor-header">
        <h1>Edit Article</h1>
        <div class="editor-actions">
            <a href="admin.php" class="btn btn-secondary">Back to Admin Panel</a>
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
        <div class="form-row">
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" required 
                       value="<?php echo htmlspecialchars($article['title']); ?>"
                       placeholder="Enter article title">
            </div>
            
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" 
                                <?php echo ($article['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
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
                    <textarea id="content" name="content" required 
                              placeholder="Write your article content using Markdown..."><?php echo htmlspecialchars($article['content']); ?></textarea>
                </div>
                <div id="preview-container" style="display: none;">
                    <div id="preview-content"></div>

        <div class="form-group">
            <label for="changes_summary">Edit Summary</label>
            <input type="text" id="changes_summary" name="changes_summary" 
                   placeholder="Briefly describe what you changed (optional)">
        </div>
                </div>
            </div>
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

<style>
.article-editor {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
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
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #2c3e50;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
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
    border-top: 1px solid #eee;
}

.wiki-editor-container {
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.wiki-editor-main textarea {
    border: none;
    border-radius: 0;
    min-height: 400px;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 14px;
    line-height: 1.5;
}

#preview-container {
    border-top: 1px solid #ddd;
    padding: 1rem;
    background: #f8f9fa;
}

#preview-content {
    max-width: none;
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
