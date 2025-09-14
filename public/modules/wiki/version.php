<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../includes/markdown/MarkdownParser.php';

// Check maintenance mode
check_maintenance_mode();

$page_title = 'Article Version';

$version_id = (int)($_GET['id'] ?? 0);

if (!$version_id) {
    show_message('Version ID is required.', 'error');
    redirect('/');
}

// Get version
$stmt = $pdo->prepare("
    SELECT av.*, wa.slug, u.username, u.display_name, cc.name as category_name 
    FROM article_versions av 
    JOIN wiki_articles wa ON av.article_id = wa.id 
    JOIN users u ON av.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id 
    WHERE av.id = ?
");
$stmt->execute([$version_id]);
$version = $stmt->fetch();

if (!$version) {
    show_message('Version not found.', 'error');
    redirect('/');
}

$page_title = $version['title'] . ' (Version ' . $version['version_number'] . ')';

// Parse markdown content
$parser = new MarkdownParser('');
$parsed_content = $parser->parse($version['content']);

include "../../includes/header.php";;

?>
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_module_version.css">
<?php
?>

<div class="article-version">
    <div class="version-header">
        <h1><?php echo htmlspecialchars($version['title']); ?></h1>
        <div class="version-info">
            <span class="version-badge">Version <?php echo $version['version_number']; ?></span>
            <a href="<?php echo ucfirst(version['slug']); ?>" class="btn">View Current Version</a>
        </div>
    </div>
    
    <div class="version-meta">
        <p>
            By <strong><?php echo htmlspecialchars($version['display_name'] ?: $version['username']); ?></strong>
            | <?php echo format_date($version['created_at']); ?>
            <?php if ($version['category_name']): ?>
            | <span class="category-tag"><?php echo htmlspecialchars($version['category_name']); ?></span>
            <?php endif; ?>
        </p>
        
        <?php if ($version['change_summary']): ?>
        <div class="change-summary">
            <strong>Changes:</strong> <?php echo htmlspecialchars($version['change_summary']); ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="article-content">
        <?php echo $parsed_content; ?>
    </div>
    
    <div class="version-actions">
        <a href="/wiki/<?php echo $version['slug']; ?>/history" class="btn">Back to History</a>
        <?php if (is_logged_in() && (is_admin() || $version['author_id'] == $_SESSION['user_id'])): ?>
            <a href="../restore_version.php?id=<?php echo $version['id']; ?>" 
               class="btn btn-warning"
               onclick="return confirm('Are you sure you want to restore this version?')">Restore This Version</a>
        <?php endif; ?>
    </div>
</div>


<?php include "../../includes/footer.php";; ?>
