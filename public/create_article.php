<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$page_title = 'Create Article';
require_login();

if (!is_editor()) {
    show_message('You do not have permission to create articles.', 'error');
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $excerpt = sanitize_input($_POST['excerpt'] ?? '');
    $status = sanitize_input($_POST['status'] ?? 'draft');
    $category_id = $_POST['category_id'] ?? null;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    if (empty($title) || empty($content)) {
        $error = 'Please fill in title and content.';
    } else {
        $slug = generate_slug($title);
        
        // Check if slug already exists
        $stmt = $pdo->prepare("SELECT id FROM wiki_articles WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }
        
        // Insert article
        $stmt = $pdo->prepare("
            INSERT INTO wiki_articles (title, slug, content, excerpt, category_id, author_id, status, is_featured, published_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $published_at = $status === 'published' ? date('Y-m-d H:i:s') : null;
        
        if ($stmt->execute([$title, $slug, $content, $excerpt, $category_id, $_SESSION['user_id'], $status, $is_featured, $published_at])) {
            $article_id = $pdo->lastInsertId();
            
            // Create initial version
            $stmt = $pdo->prepare("
                INSERT INTO article_versions (article_id, version_number, title, content, excerpt, changes_summary, created_by) 
                VALUES (?, 1, ?, ?, ?, 'Initial version', ?)
            ");
            $stmt->execute([$article_id, $title, $content, $excerpt, $_SESSION['user_id']]);
            
            log_activity('article_created', 'Created new article: ' . $title, $_SESSION['user_id'], ['article_id' => $article_id]);
            
            show_message('Article created successfully!', 'success');
            redirect('dashboard.php');
        } else {
            $error = 'Failed to create article.';
        }
    }
}

// Get categories
$stmt = $pdo->query("SELECT * FROM content_categories WHERE is_active = 1 ORDER BY sort_order");
$categories = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="form-container" style="max-width: 800px;">
    <h2>Create New Article</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="title">Title *</label>
            <input type="text" id="title" name="title" required 
                   value="<?php echo htmlspecialchars($title ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="excerpt">Excerpt</label>
            <textarea id="excerpt" name="excerpt" rows="3"><?php echo htmlspecialchars($excerpt ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="content">Content *</label>
            <textarea id="content" name="content" rows="15" required><?php echo htmlspecialchars($content ?? ''); ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" 
                                <?php echo ($category_id ?? '') == $category['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="draft" <?php echo ($status ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo ($status ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                </select>
            </div>
        </div>
        
        <?php if (is_admin()): ?>
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_featured" <?php echo ($is_featured ?? 0) ? 'checked' : ''; ?>>
                Featured Article
            </label>
        </div>
        <?php endif; ?>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-success">Create Article</button>
            <a href="dashboard.php" class="btn">Cancel</a>
        </div>
    </form>
</div>

<style>
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: normal;
    cursor: pointer;
}

.form-actions {
    margin-top: 2rem;
    text-align: center;
}

.form-actions .btn {
    margin: 0 0.5rem;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
