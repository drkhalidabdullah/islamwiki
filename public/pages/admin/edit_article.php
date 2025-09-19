<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/wiki_functions.php';

$page_title = 'Edit Article';
require_login();

// Check if user is admin
if (!is_admin()) {
    show_message('Access denied. Admin privileges required.', 'error');
    redirect('/dashboard');
}

$article_id = (int)($_GET['id'] ?? 0);
if (!$article_id) {
    show_message('Article ID is required.', 'error');
    redirect('/admin');
}

// Get article details
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    WHERE wa.id = ?
");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    show_message('Article not found.', 'error');
    redirect('/admin');
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
        
        // Check if slug already exists (excluding current article)
        $stmt = $pdo->prepare("SELECT id FROM wiki_articles WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $article_id]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }
        
        try {
            $stmt = $pdo->prepare("
                UPDATE wiki_articles 
                SET title = ?, slug = ?, content = ?, status = ?, tags = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            
            if ($stmt->execute([$title, $slug, $content, $status, $tags, $article_id])) {
                $success = 'Article updated successfully.';
                log_activity('article_updated', "Updated article: $title (ID: $article_id)");
                
                // Refresh article data
                $stmt = $pdo->prepare("
                    SELECT wa.*, u.username, u.display_name 
                    FROM wiki_articles wa 
                    JOIN users u ON wa.author_id = u.id 
                    WHERE wa.id = ?
                ");
                $stmt->execute([$article_id]);
                $article = $stmt->fetch();
            } else {
                $error = 'Failed to update article.';
            }
        } catch (Exception $e) {
            $error = 'Error updating article: ' . $e->getMessage();
        }
    }
}

// Categories are now handled via [[Category:Name]] syntax in content

include "../../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/edit_article.css">
<?php
?>

<div class="admin-container">
    <div class="admin-header">
        <h1><i class="iw iw-edit"></i> Edit Article</h1>
        <p>Edit: <?php echo htmlspecialchars($article['title']); ?></p>
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
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($article['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="draft" <?php echo $article['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo $article['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                    <option value="archived" <?php echo $article['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tags">Tags (comma-separated)</label>
                <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars($article['tags']); ?>" placeholder="islam, quran, hadith">
            </div>

            <div class="form-group">
                <label for="content">Content *</label>
                <textarea id="content" name="content" rows="20" required><?php echo htmlspecialchars($article['content']); ?></textarea>
                <small class="form-help">
                    Use Markdown formatting for rich text content.<br>
                    <strong>Categories:</strong> Add categories at the end of your content using <code>[[Category:Category Name]]</code> syntax.<br>
                    <strong>Example:</strong> <code>[[Category:Islam]] [[Category:Religions]] [[Category:Theology]]</code>
                </small>
            </div>

            <div class="article-meta">
                <div class="meta-item">
                    <strong>Author:</strong> <?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?>
                </div>
                <div class="meta-item">
                    <strong>Created:</strong> <?php echo format_date($article['created_at']); ?>
                </div>
                <div class="meta-item">
                    <strong>Last Updated:</strong> <?php echo format_date($article['updated_at']); ?>
                </div>
                <div class="meta-item">
                    <strong>Slug:</strong> <?php echo htmlspecialchars($article['slug']); ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="iw iw-save"></i> Update Article
                </button>
                <a href="/wiki/article.php?slug=<?php echo $article['slug']; ?>" class="btn btn-info" target="_blank">
                    <i class="iw iw-external-link-alt"></i> View Article
                </a>
                <a href="/admin" class="btn btn-secondary">
                    <i class="iw iw-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </form>
    </div>
</div>


<?php include "../../includes/footer.php"; ?>
