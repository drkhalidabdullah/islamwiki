<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

require_login();

$article_id = $_GET['id'] ?? 0;

if (!$article_id) {
    redirect('/dashboard');
}

// Get article
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    show_message('Article not found.', 'error');
    redirect('/dashboard');
}

// Check permissions
if ($_SESSION['role'] !== 'admin' && $article['author_id'] != $_SESSION['user_id']) {
    show_message('You do not have permission to delete this article.', 'error');
    redirect('/dashboard');
}

// Delete article (cascade will handle related records)
$stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
if ($stmt->execute([$article_id])) {
    show_message('Article deleted successfully.', 'success');
} else {
    show_message('Failed to delete article.', 'error');
}

redirect('/dashboard');
?>
