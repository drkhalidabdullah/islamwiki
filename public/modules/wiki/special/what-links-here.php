<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/wiki_functions.php';

$page_title = 'What Links Here';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    redirect('index.php');
}

// Get the article to find what links to it
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    WHERE (wa.slug = ? OR wa.slug = ?) 
    AND wa.status = 'published'
");
$stmt->execute([$slug, ucfirst($slug)]);
$article = $stmt->fetch();

if (!$article) {
    redirect("not_found.php?slug=" . urlencode($slug) . "&title=" . urlencode(ucfirst(str_replace('-', ' ', $slug))));
}

// Find articles that link to this article
$stmt = $pdo->prepare("
    SELECT wa.id, wa.title, wa.slug, wa.view_count, wa.updated_at, u.username, u.display_name
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    WHERE wa.content LIKE ? 
    AND wa.status = 'published'
    AND wa.id != ?
    ORDER BY wa.title ASC
");
$search_term = '%[[' . $article['title'] . ']]%';
$stmt->execute([$search_term, $article['id']]);
$linking_articles = $stmt->fetchAll();

// Also search for slug-based links
$stmt = $pdo->prepare("
    SELECT wa.id, wa.title, wa.slug, wa.view_count, wa.updated_at, u.username, u.display_name
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    WHERE wa.content LIKE ? 
    AND wa.status = 'published'
    AND wa.id != ?
    AND wa.id NOT IN (SELECT id FROM wiki_articles WHERE content LIKE ? AND status = 'published' AND id != ?)
    ORDER BY wa.title ASC
");
$slug_search = '%[[' . $article['slug'] . ']]%';
$stmt->execute([$slug_search, $article['id'], $search_term, $article['id']]);
$slug_linking_articles = $stmt->fetchAll();

// Merge and deduplicate results
$all_linking_articles = array_merge($linking_articles, $slug_linking_articles);
$unique_articles = [];
$seen_ids = [];

foreach ($all_linking_articles as $article) {
    if (!in_array($article['id'], $seen_ids)) {
        $unique_articles[] = $article;
        $seen_ids[] = $article['id'];
    }
}

include '../../../includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h1 class="mb-0">
                        <i class="fas fa-link"></i>
                        What links here
                    </h1>
                    <p class="text-muted mb-0">
                        Pages that link to: 
                        <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>" class="text-primary">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </a>
                    </p>
                </div>
                
                <div class="card-body">
                    <?php if (empty($unique_articles)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-unlink fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No pages link here</h4>
                            <p class="text-muted">
                                No other pages currently link to 
                                <strong><?php echo htmlspecialchars($article['title']); ?></strong>.
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <p class="text-muted">
                                <strong><?php echo count($unique_articles); ?></strong> 
                                <?php echo count($unique_articles) === 1 ? 'page' : 'pages'; ?> 
                                link to this article:
                            </p>
                        </div>
                        
                        <div class="list-group">
                            <?php foreach ($unique_articles as $linking_article): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h5 class="mb-1">
                                                <a href="/wiki/<?php echo htmlspecialchars($linking_article['slug']); ?>" 
                                                   class="text-decoration-none">
                                                    <?php echo htmlspecialchars($linking_article['title']); ?>
                                                </a>
                                            </h5>
                                            <p class="mb-1 text-muted">
                                                <small>
                                                    by 
                                                    <a href="/user/<?php echo htmlspecialchars($linking_article['username']); ?>" 
                                                       class="text-decoration-none">
                                                        <?php echo htmlspecialchars($linking_article['display_name'] ?: $linking_article['username']); ?>
                                                    </a>
                                                    • 
                                                    Last edited <?php echo date('M j, Y', strtotime($linking_article['updated_at'])); ?>
                                                </small>
                                            </p>
                                        </div>
                                        <div class="text-muted">
                                            <small>
                                                <i class="fas fa-eye"></i> 
                                                <?php echo number_format($linking_article['view_count']); ?> views
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Back to article
                        </a>
                        <small class="text-muted">
                            Last updated: <?php echo date('M j, Y \a\t g:i A'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../../includes/footer.php'; ?>
