<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/wiki_functions.php';

$page_title = 'Page Information';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    redirect('index.php');
}

// Get the article with detailed information
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name, u.email, cc.name as category_name, cc.slug as category_slug 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id 
    WHERE (wa.slug = ? OR wa.slug = ?) 
    AND wa.status = 'published'
");
$stmt->execute([$slug, ucfirst($slug)]);
$article = $stmt->fetch();

if (!$article) {
    redirect("not_found.php?slug=" . urlencode($slug) . "&title=" . urlencode(ucfirst(str_replace('-', ' ', $slug))));
}

// Get article statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT wa.id) as total_articles,
        COUNT(DISTINCT u.id) as total_authors,
        AVG(wa.view_count) as avg_views,
        MAX(wa.created_at) as latest_article
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    WHERE wa.status = 'published'
");
$stmt->execute();
$stats = $stmt->fetch();

// Get recent edits for this article (if table exists)
$recent_edits = [];
try {
    $stmt = $pdo->prepare("
        SELECT we.*, u.username, u.display_name
        FROM wiki_edit_history we
        JOIN users u ON we.editor_id = u.id
        WHERE we.article_id = ?
        ORDER BY we.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$article['id']]);
    $recent_edits = $stmt->fetchAll();
} catch (PDOException $e) {
    // Table doesn't exist, skip recent edits
    $recent_edits = [];
}

// Calculate article metrics
$word_count = str_word_count(strip_tags($article['content']));
$char_count = strlen(strip_tags($article['content']));
$reading_time = ceil($word_count / 200); // Assuming 200 words per minute

include '../../../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h1 class="mb-0">
                        <i class="fas fa-info-circle"></i>
                        Page Information
                    </h1>
                    <p class="text-muted mb-0">
                        Detailed information about: 
                        <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>" class="text-primary">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </a>
                    </p>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6 mb-4">
                            <h4 class="text-primary mb-3">
                                <i class="fas fa-file-alt"></i> Basic Information
                            </h4>
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold">Title:</td>
                                    <td><?php echo htmlspecialchars($article['title']); ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Slug:</td>
                                    <td><code><?php echo htmlspecialchars($article['slug']); ?></code></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Status:</td>
                                    <td>
                                        <span class="badge bg-success">Published</span>
                                    </td>
                                </tr>
                                <?php if ($article['category_name']): ?>
                                <tr>
                                    <td class="fw-bold">Category:</td>
                                    <td>
                                        <a href="/wiki/category/<?php echo htmlspecialchars($article['category_slug']); ?>" 
                                           class="text-decoration-none">
                                            <?php echo htmlspecialchars($article['category_name']); ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="fw-bold">Created:</td>
                                    <td><?php echo date('F j, Y \a\t g:i A', strtotime($article['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Last Modified:</td>
                                    <td><?php echo date('F j, Y \a\t g:i A', strtotime($article['updated_at'])); ?></td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Content Statistics -->
                        <div class="col-md-6 mb-4">
                            <h4 class="text-primary mb-3">
                                <i class="fas fa-chart-bar"></i> Content Statistics
                            </h4>
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold">Word Count:</td>
                                    <td><?php echo number_format($word_count); ?> words</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Character Count:</td>
                                    <td><?php echo number_format($char_count); ?> characters</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Reading Time:</td>
                                    <td><?php echo $reading_time; ?> minute<?php echo $reading_time !== 1 ? 's' : ''; ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">View Count:</td>
                                    <td><?php echo number_format($article['view_count']); ?> views</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Edit Count:</td>
                                    <td><?php echo count($recent_edits); ?> edits</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Author Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="text-primary mb-3">
                                <i class="fas fa-user"></i> Author Information
                            </h4>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h5 class="mb-1">
                                                <a href="/user/<?php echo htmlspecialchars($article['username']); ?>" 
                                                   class="text-decoration-none">
                                                    <?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?>
                                                </a>
                                            </h5>
                                            <p class="text-muted mb-0">
                                                <small>@<?php echo htmlspecialchars($article['username']); ?></small>
                                            </p>
                                        </div>
                                        <div class="text-muted">
                                            <small>
                                                Member since <?php echo date('M Y', strtotime($article['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Edits -->
                    <?php if (!empty($recent_edits)): ?>
                    <div class="row">
                        <div class="col-12">
                            <h4 class="text-primary mb-3">
                                <i class="fas fa-history"></i> Recent Edit History
                            </h4>
                            <div class="list-group">
                                <?php foreach ($recent_edits as $edit): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <?php echo htmlspecialchars($edit['edit_summary'] ?: 'No summary provided'); ?>
                                            </h6>
                                            <p class="mb-1 text-muted">
                                                <small>
                                                    by 
                                                    <a href="/user/<?php echo htmlspecialchars($edit['username']); ?>" 
                                                       class="text-decoration-none">
                                                        <?php echo htmlspecialchars($edit['display_name'] ?: $edit['username']); ?>
                                                    </a>
                                                </small>
                                            </p>
                                        </div>
                                        <div class="text-muted">
                                            <small>
                                                <?php echo date('M j, Y \a\t g:i A', strtotime($edit['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Back to article
                        </a>
                        <div class="btn-group">
                            <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>/history" 
                               class="btn btn-outline-secondary">
                                <i class="fas fa-history"></i> View full history
                            </a>
                            <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>/what-links-here" 
                               class="btn btn-outline-secondary">
                                <i class="fas fa-link"></i> What links here
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../../includes/footer.php'; ?>
