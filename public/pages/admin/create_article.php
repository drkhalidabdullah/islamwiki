<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/wiki_functions.php';

$page_title = 'Create Article';
require_login();

// Check if user is admin
if (!is_admin()) {
    show_message('Access denied. Admin privileges required.', 'error');
    redirect('/dashboard');
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $tags = sanitize_input($_POST['tags'] ?? '');
    
    if (empty($title) || empty($content)) {
        $error = 'Title and content are required.';
    } else {
        $slug = generate_slug($title);
        
        // Check if slug already exists
        $stmt = $pdo->prepare("SELECT id FROM wiki_articles WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO wiki_articles (title, slug, content, author_id, status, tags, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            if ($stmt->execute([$title, $slug, $content, $_SESSION['user_id'], $status, $tags])) {
                $article_id = $pdo->lastInsertId();
                $success = 'Article created successfully.';
                log_activity('article_created', "Created article: $title (ID: $article_id)");
                
                // Redirect to edit page
                redirect("/admin/edit_article?id=$article_id");
            } else {
                $error = 'Failed to create article.';
            }
        } catch (Exception $e) {
            $error = 'Error creating article: ' . $e->getMessage();
        }
    }
}

// Categories are now handled via [[Category:Name]] syntax in content

include "../../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/create_article.css">
<?php
?>

<div class="admin-container">
    <div class="admin-header">
        <h1><i class="iw iw-plus"></i> Create New Article</h1>
        <p>Add a new article to the wiki</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" class="article-form">
            <div class="form-group">
                <label for="title">Article Title *</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="draft" <?php echo ($_POST['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo ($_POST['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="archived" <?php echo ($_POST['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tags">Tags (comma-separated)</label>
                <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars($_POST['tags'] ?? ''); ?>" placeholder="islam, quran, hadith">
            </div>

            <div class="form-group">
                <label for="content">Content *</label>
                <textarea id="content" name="content" rows="20" required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                <small class="form-help">
                    Use Markdown formatting for rich text content.<br>
                    <strong>Categories:</strong> Add categories at the end of your content using <code>[[Category:Category Name]]</code> syntax.<br>
                    <strong>Example:</strong> <code>[[Category:Islam]] [[Category:Religions]] [[Category:Theology]]</code>
                </small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="iw iw-save"></i> Create Article
                </button>
                <a href="/admin" class="btn btn-secondary">
                    <i class="iw iw-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </form>
    </div>
</div>


<?php include "../../includes/footer.php"; ?>
