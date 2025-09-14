<link rel="stylesheet" href="/skins/bismillah/assets/css/bismillah.css">
<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

require_login();

$article_id = $_GET['id'] ?? 0;

if (!$article_id) {
    redirect_with_return_url();
}

// Get article
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    show_message('Article not found.', 'error');
    redirect_with_return_url();
}

// Check permissions
if ($_SESSION['role'] !== 'admin' && $article['author_id'] != $_SESSION['user_id']) {
    show_message('You do not have permission to delete this article.', 'error');
    redirect_with_return_url();
}

// Delete article (cascade will handle related records)
$stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
if ($stmt->execute([$article_id])) {
    show_message('Article deleted successfully.', 'success');
} else {
    show_message('Failed to delete article.', 'error');
}

redirect_with_return_url();
?>
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
