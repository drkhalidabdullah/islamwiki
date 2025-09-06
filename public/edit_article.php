<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$page_title = 'Edit Article';
require_login();

$article_id = $_GET['id'] ?? 0;

if (!$article_id) {
    redirect('dashboard.php');
}

// Get article
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    show_message('Article not found.', 'error');
    redirect('dashboard.php');
}

// Check permissions
if ($_SESSION['role'] !== 'admin' && $article['author_id'] != $_SESSION['user_id']) {
    show_message('You do not have permission to edit this article.', 'error');
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $status = sanitize_input($_POST['status'] ?? 'draft');
    $category_ids = $_POST['categories'] ?? [];
    
    if (empty($title) || empty($content)) {
        $error = 'Please fill in title and content.';
    } else {
        // Update article
        $stmt = $pdo->prepare("UPDATE articles SET title = ?, content = ?, status = ? WHERE id = ?");
        if ($stmt->execute([$title, $content, $status, $article_id])) {
            // Remove existing categories
            $stmt = $pdo->prepare("DELETE FROM article_categories WHERE article_id = ?");
            $stmt->execute([$article_id]);
            
            // Add new categories
            if (!empty($category_ids)) {
                $stmt = $pdo->prepare("INSERT INTO article_categories (article_id, category_id) VALUES (?, ?)");
                foreach ($category_ids as $category_id) {
                    $stmt->execute([$article_id, $category_id]);
                }
            }
            
            show_message('Article updated successfully!', 'success');
            redirect('dashboard.php');
        } else {
            $error = 'Failed to update article.';
        }
    }
} else {
    // Load existing data
    $title = $article['title'];
    $content = $article['content'];
    $status = $article['status'];
    
    // Get current categories
    $stmt = $pdo->prepare("SELECT category_id FROM article_categories WHERE article_id = ?");
    $stmt->execute([$article_id]);
    $current_categories = array_column($stmt->fetchAll(), 'category_id');
}

// Get all categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="form-container" style="max-width: 800px;">
    <h2>Edit Article</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required 
                   value="<?php echo htmlspecialchars($title); ?>">
        </div>
        
        <div class="form-group">
            <label for="content">Content:</label>
            <textarea id="content" name="content" rows="15" required><?php echo htmlspecialchars($content); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="status">Status:</label>
            <select id="status" name="status">
                <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Categories:</label>
            <div class="categories-list">
                <?php foreach ($categories as $category): ?>
                <label class="category-checkbox">
                    <input type="checkbox" name="categories[]" value="<?php echo $category['id']; ?>"
                           <?php echo in_array($category['id'], $current_categories) ? 'checked' : ''; ?>>
                    <?php echo htmlspecialchars($category['name']); ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-success">Update Article</button>
            <a href="article.php?id=<?php echo $article_id; ?>" class="btn">View Article</a>
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
        </div>
    </form>
</div>

<style>
.categories-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.category-checkbox {
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
</style>

<?php include 'includes/footer.php'; ?>
