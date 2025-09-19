<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/wiki_functions.php';

// Check maintenance mode
check_maintenance_mode();

// Check if wiki is enabled
$enable_wiki = get_system_setting('enable_wiki', true);
if (!$enable_wiki) {
    show_message('Wiki system is currently disabled.', 'error');
    redirect('/dashboard');
}

$page_title = 'Category';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    redirect('/wiki');
}

// Get category from wiki_categories table
$stmt = $pdo->prepare("SELECT * FROM wiki_categories WHERE slug = ?");
$stmt->execute([$slug]);
$category = $stmt->fetch();

if (!$category) {
    show_message('Category not found.', 'error');
    redirect('/wiki');
}

// Get articles in this category
$articles = get_category_articles($slug, 100);

// Get subcategories
$subcategories = get_category_subcategories($category['id']);

// Get parent categories
$parent_categories = get_category_parents($category['id']);

$page_title = 'Category:' . $category['name'];

include "../../includes/header.php";
?>

<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_module_category.css">

<div class="wiki-layout">
    <!-- Main Content Area -->
    <main class="wiki-main-content">
        <div class="article-content">
            <h1>Category: <?php echo htmlspecialchars($category['name']); ?></h1>
            
            <?php if ($category['description']): ?>
                <div class="category-description">
                    <?php echo nl2br(htmlspecialchars($category['description'])); ?>
                </div>
            <?php endif; ?>
            
            <!-- Parent Categories -->
            <?php if (!empty($parent_categories)): ?>
            <div class="category-parents">
                <h3>Parent categories</h3>
                <div class="categories-list">
                    <?php foreach ($parent_categories as $parent): ?>
                    <a href="/wiki/category/<?php echo htmlspecialchars($parent['slug']); ?>" 
                       class="category-link">
                        <?php echo htmlspecialchars($parent['name']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Subcategories -->
            <?php if (!empty($subcategories)): ?>
            <div class="category-subcategories">
                <h3>Subcategories</h3>
                <div class="subcategories-grid">
                    <?php foreach ($subcategories as $subcategory): ?>
                    <div class="subcategory-item">
                        <a href="/wiki/category/<?php echo htmlspecialchars($subcategory['slug']); ?>" 
                           class="subcategory-link">
                            <?php echo htmlspecialchars($subcategory['name']); ?>
                        </a>
                        <span class="article-count">(<?php echo $subcategory['article_count']; ?> articles)</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Articles in Category -->
            <div class="category-articles">
                <h3>Pages in category "<?php echo htmlspecialchars($category['name']); ?>"</h3>
                <p class="article-count-info">
                    The following <?php echo count($articles); ?> pages are in this category, out of <?php echo $category['article_count']; ?> total.
                </p>
                
                <?php if (!empty($articles)): ?>
                <div class="articles-list">
                    <?php foreach ($articles as $article): ?>
                    <div class="article-item">
                        <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>" 
                           class="article-link">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </a>
                        <div class="article-meta">
                            <span class="last-updated">Last Updated by: <?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?> <?php echo format_date($article['updated_at']); ?> <?php echo number_format($article['view_count']); ?> views</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="no-articles">
                    <p>No articles found in this category yet.</p>
                    <?php if (is_logged_in() && is_editor()): ?>
                        <a href="/wiki/create_article.php" class="btn btn-primary">Create First Article</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Right Sidebar: Category Tools -->
    <aside class="wiki-tools">
        <div class="tools-section">
            <h3>Category Tools</h3>
            <ul class="tools-list">
                <li>
                    <a href="/wiki/special/category-tree?category=<?php echo urlencode($category['slug']); ?>" class="tool-link">
                        <i class="iw iw-sitemap"></i>
                        <span>Category tree</span>
                    </a>
                </li>
                <li>
                    <a href="/wiki/special/uncategorized" class="tool-link">
                        <i class="iw iw-tag"></i>
                        <span>Uncategorized pages</span>
                    </a>
                </li>
                <li>
                    <a href="/wiki/special/all-categories" class="tool-link">
                        <i class="iw iw-list"></i>
                        <span>All categories</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="tools-section">
            <h3>Category Statistics</h3>
            <div class="stats-list">
                <div class="stat-item">
                    <span class="stat-label">Articles:</span>
                    <span class="stat-value"><?php echo $category['article_count']; ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Subcategories:</span>
                    <span class="stat-value"><?php echo count($subcategories); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Created:</span>
                    <span class="stat-value"><?php echo format_date($category['created_at']); ?></span>
                </div>
            </div>
        </div>
    </aside>
</div>

<?php include "../../includes/footer.php"; ?>