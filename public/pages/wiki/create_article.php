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

// All logged-in users can create articles, but they need approval
// No permission check needed - all users can create articles

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $excerpt = sanitize_input($_POST['excerpt'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    // Set status based on user role
    $status = is_editor() ? sanitize_input($_POST['status'] ?? 'draft') : 'pending_approval';
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
            } elseif ($status === 'pending_approval') {
                // Add to approval queue
                $stmt = $pdo->prepare("
                    INSERT INTO article_approval_queue (article_id, submitted_by, submitted_at, priority) 
                    VALUES (?, ?, NOW(), 'normal')
                ");
                $stmt->execute([$article_id, $_SESSION['user_id']]);
            }
            
            if ($status === 'pending_approval') {
                show_message('Article submitted for approval! It will be reviewed by moderators before being published.', 'success');
                redirect("/dashboard");
            } else {
                show_message('Article created successfully!', 'success');
                redirect("/wiki/" . urlencode($slug));
            }
            
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
<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_create_article.css">
<?php
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
        
        <?php if (is_editor()): ?>
        <div class="form-row">
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="draft" <?php echo (($_POST['status'] ?? 'draft') === 'draft') ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo (($_POST['status'] ?? '') === 'published') ? 'selected' : ''; ?>>Published</option>
                </select>
            </div>
        <?php else: ?>
        <div class="form-row">
            <div class="form-group">
                <div class="info-notice">
                    <i class="iw iw-info-circle"></i>
                    <span>Your article will be submitted for approval by moderators before being published.</span>
                </div>
            </div>
        <?php endif; ?>
            
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


<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki.css">
<script src="/skins/bismillah/assets/js/wiki-editor.js"></script>

<?php include "../../includes/footer.php";; ?>
