<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

require_login();

$version_id = (int)($_GET['id'] ?? 0);

if (!$version_id) {
    show_message('Version ID is required.', 'error');
    redirect('/dashboard');
}

// Get version
$stmt = $pdo->prepare("
    SELECT av.*, wa.slug 
    FROM article_versions av 
    JOIN wiki_articles wa ON av.article_id = wa.id 
    WHERE av.id = ?
");
$stmt->execute([$version_id]);
$version = $stmt->fetch();

if (!$version) {
    show_message('Version not found.', 'error');
    redirect('/dashboard');
}

// Check permissions
if (!is_admin() && $version['author_id'] != $_SESSION['user_id']) {
    show_message('You do not have permission to restore this version.', 'error');
    redirect('/dashboard');
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Get current version number
    $stmt = $pdo->prepare("SELECT MAX(version_number) FROM article_versions WHERE article_id = ?");
    $stmt->execute([$version['article_id']]);
    $current_version = $stmt->fetchColumn() ?: 0;
    
    // Create new version from current article state
    $stmt = $pdo->prepare("SELECT * FROM wiki_articles WHERE id = ?");
    $stmt->execute([$version['article_id']]);
    $current_article = $stmt->fetch();
    
    $stmt = $pdo->prepare("
        INSERT INTO article_versions 
        (article_id, title, content, excerpt, version_number, author_id, change_summary) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $version['article_id'],
        $current_article['title'],
        $current_article['content'],
        $current_article['excerpt'],
        $current_version + 1,
        $_SESSION['user_id'],
        'Restored from version ' . $version['version_number']
    ]);
    
    // Restore the article to the selected version
    $stmt = $pdo->prepare("
        UPDATE wiki_articles 
        SET title = ?, content = ?, excerpt = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([
        $version['title'],
        $version['content'],
        $version['excerpt'],
        $version['article_id']
    ]);
    
    $pdo->commit();
    
    show_message('Version restored successfully!', 'success');
    redirect("wiki/article.php?slug=" . $version['slug']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    show_message('Error restoring version: ' . $e->getMessage(), 'error');
    redirect('/dashboard');
}
?>
