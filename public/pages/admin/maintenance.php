<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/wiki_functions.php';

$page_title = 'Maintenance Tools';
require_login();
require_admin();

$current_user = get_user($_SESSION['user_id']);

$errors = [];
$success = '';

// Handle maintenance actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'cleanup_orphaned_pages') {
        try {
            // Find orphaned pages (no content, no redirects)
            $stmt = $pdo->query("
                SELECT wa.id, wa.title, wa.slug 
                FROM wiki_articles wa 
                WHERE (wa.content IS NULL OR wa.content = '' OR wa.content = ' ') 
                AND wa.id NOT IN (SELECT DISTINCT to_article_id FROM wiki_redirects)
            ");
            $orphaned = $stmt->fetchAll();
            
            $count = 0;
            foreach ($orphaned as $page) {
                $stmt = $pdo->prepare("DELETE FROM wiki_articles WHERE id = ?");
                $stmt->execute([$page['id']]);
                $count++;
            }
            
            $success = "Cleaned up $count orphaned pages.";
        } catch (Exception $e) {
            $errors[] = 'Error cleaning orphaned pages: ' . $e->getMessage();
        }
    } elseif ($action === 'fix_broken_links') {
        try {
            // Find articles with broken wiki links
            $stmt = $pdo->query("
                SELECT wa.id, wa.title, wa.content 
                FROM wiki_articles wa 
                WHERE wa.content LIKE '%[[%]]%'
            ");
            $articles = $stmt->fetchAll();
            
            $fixed_count = 0;
            foreach ($articles as $article) {
                $content = $article['content'];
                $original_content = $content;
                
                // Find wiki links in content
                preg_match_all('/\[\[([^\]]+)\]\]/', $content, $matches);
                
                foreach ($matches[1] as $link) {
                    $link_parts = explode('|', $link);
                    $page_name = trim($link_parts[0]);
                    $display_text = isset($link_parts[1]) ? trim($link_parts[1]) : $page_name;
                    
                    // Check if page exists
                    $slug = strtolower(str_replace(' ', '-', $page_name));
                    $stmt = $pdo->prepare("SELECT id FROM wiki_articles WHERE slug = ?");
                    $stmt->execute([$slug]);
                    
                    if (!$stmt->fetch()) {
                        // Page doesn't exist, mark as missing
                        $content = str_replace(
                            "[[" . $link . "]]",
                            "[[" . $link . "]] <span class='missing-page'>(page doesn't exist)</span>",
                            $content
                        );
                    }
                }
                
                if ($content !== $original_content) {
                    $stmt = $pdo->prepare("UPDATE wiki_articles SET content = ? WHERE id = ?");
                    $stmt->execute([$content, $article['id']]);
                    $fixed_count++;
                }
            }
            
            $success = "Fixed broken links in $fixed_count articles.";
        } catch (Exception $e) {
            $errors[] = 'Error fixing broken links: ' . $e->getMessage();
        }
    } elseif ($action === 'cleanup_unused_files') {
        try {
            // Find unused files
            $stmt = $pdo->query("
                SELECT wf.id, wf.filename, wf.file_path 
                FROM wiki_files wf 
                WHERE wf.usage_count = 0 
                AND wf.created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $unused_files = $stmt->fetchAll();
            
            $count = 0;
            foreach ($unused_files as $file) {
                // Delete physical file
                if (file_exists($file['file_path'])) {
                    unlink($file['file_path']);
                }
                
                // Delete database record
                $stmt = $pdo->prepare("DELETE FROM wiki_files WHERE id = ?");
                $stmt->execute([$file['id']]);
                $count++;
            }
            
            $success = "Cleaned up $count unused files.";
        } catch (Exception $e) {
            $errors[] = 'Error cleaning unused files: ' . $e->getMessage();
        }
    } elseif ($action === 'optimize_database') {
        try {
            // Optimize database tables
            $tables = ['wiki_articles', 'wiki_redirects', 'wiki_files', 'article_versions', 'users', 'user_roles'];
            $optimized = 0;
            
            foreach ($tables as $table) {
                $stmt = $pdo->prepare("OPTIMIZE TABLE $table");
                $stmt->execute();
                $optimized++;
            }
            
            $success = "Optimized $optimized database tables.";
        } catch (Exception $e) {
            $errors[] = 'Error optimizing database: ' . $e->getMessage();
        }
    } elseif ($action === 'rebuild_search_index') {
        try {
            // Rebuild search suggestions
            $stmt = $pdo->prepare("DELETE FROM search_suggestions");
            $stmt->execute();
            
            // Rebuild from articles
            $stmt = $pdo->query("
                SELECT title, 'article' as type, COUNT(*) as count 
                FROM wiki_articles 
                WHERE status = 'published' 
                GROUP BY title
            ");
            $articles = $stmt->fetchAll();
            
            $count = 0;
            foreach ($articles as $article) {
                $stmt = $pdo->prepare("
                    INSERT INTO search_suggestions (suggestion, suggestion_type, content_type, search_count) 
                    VALUES (?, 'title', ?, ?)
                ");
                $stmt->execute([$article['title'], $article['type'], $article['count']]);
                $count++;
            }
            
            $success = "Rebuilt search index with $count entries.";
        } catch (Exception $e) {
            $errors[] = 'Error rebuilding search index: ' . $e->getMessage();
        }
    }
}

// Get system statistics
$stats = [];

// Database size
$stmt = $pdo->query("
    SELECT 
        ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
    FROM information_schema.tables 
    WHERE table_schema = DATABASE()
");
$stats['db_size'] = $stmt->fetch()['size_mb'];

// Article statistics
$stmt = $pdo->query("SELECT COUNT(*) as count FROM wiki_articles");
$stats['total_articles'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM wiki_articles WHERE status = 'published'");
$stats['published_articles'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM wiki_articles WHERE status = 'draft'");
$stats['draft_articles'] = $stmt->fetch()['count'];

// File statistics
$stmt = $pdo->query("SELECT COUNT(*) as count FROM wiki_files");
$stats['total_files'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM wiki_files WHERE usage_count = 0");
$stats['unused_files'] = $stmt->fetch()['count'];

// User statistics
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE last_login_at > DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stats['active_users'] = $stmt->fetch()['count'];

// Orphaned pages
$stmt = $pdo->query("
    SELECT COUNT(*) as count 
    FROM wiki_articles wa 
    WHERE (wa.content IS NULL OR wa.content = '' OR wa.content = ' ') 
    AND wa.id NOT IN (SELECT DISTINCT to_article_id FROM wiki_redirects)
");
$stats['orphaned_pages'] = $stmt->fetch()['count'];

// Broken redirects
$stmt = $pdo->query("
    SELECT COUNT(*) as count 
    FROM wiki_redirects wr 
    LEFT JOIN wiki_articles wa ON wr.to_article_id = wa.id 
    WHERE wa.id IS NULL
");
$stats['broken_redirects'] = $stmt->fetch()['count'];

include '../../includes/header.php';
?>

<div class="admin-page">
    <div class="admin-header">
        <h1>Maintenance Tools</h1>
        <div class="admin-actions">
            <a href="/admin" class="btn btn-secondary">Back to Admin Panel</a>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- System Statistics -->
    <div class="card">
        <h2>System Statistics</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-label">Database Size</div>
                <div class="stat-value"><?php echo $stats['db_size']; ?> MB</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Total Articles</div>
                <div class="stat-value"><?php echo number_format($stats['total_articles']); ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Published Articles</div>
                <div class="stat-value"><?php echo number_format($stats['published_articles']); ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Draft Articles</div>
                <div class="stat-value"><?php echo number_format($stats['draft_articles']); ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Total Files</div>
                <div class="stat-value"><?php echo number_format($stats['total_files']); ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Unused Files</div>
                <div class="stat-value"><?php echo number_format($stats['unused_files']); ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Active Users (30 days)</div>
                <div class="stat-value"><?php echo number_format($stats['active_users']); ?></div>
            </div>
        </div>
    </div>

    <!-- Issues Found -->
    <div class="card">
        <h2>Issues Found</h2>
        <div class="issues-grid">
            <div class="issue-item <?php echo $stats['orphaned_pages'] > 0 ? 'has-issues' : 'no-issues'; ?>">
                <div class="issue-icon">📄</div>
                <div class="issue-content">
                    <h3>Orphaned Pages</h3>
                    <p><?php echo $stats['orphaned_pages']; ?> pages with no content</p>
                </div>
            </div>
            <div class="issue-item <?php echo $stats['broken_redirects'] > 0 ? 'has-issues' : 'no-issues'; ?>">
                <div class="issue-icon">🔄</div>
                <div class="issue-content">
                    <h3>Broken Redirects</h3>
                    <p><?php echo $stats['broken_redirects']; ?> redirects pointing to non-existent pages</p>
                </div>
            </div>
            <div class="issue-item <?php echo $stats['unused_files'] > 0 ? 'has-issues' : 'no-issues'; ?>">
                <div class="issue-icon">📁</div>
                <div class="issue-content">
                    <h3>Unused Files</h3>
                    <p><?php echo $stats['unused_files']; ?> files not used in any content</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Tools -->
    <div class="card">
        <h2>Maintenance Tools</h2>
        <div class="tools-grid">
            <div class="tool-item">
                <div class="tool-header">
                    <h3>Cleanup Orphaned Pages</h3>
                    <p>Remove pages with no content that aren't redirects</p>
                </div>
                <form method="POST" onsubmit="return confirm('This will permanently delete orphaned pages. Continue?')">
                    <input type="hidden" name="action" value="cleanup_orphaned_pages">
                    <button type="submit" class="btn btn-warning">Cleanup (<?php echo $stats['orphaned_pages']; ?>)</button>
                </form>
            </div>
            
            <div class="tool-item">
                <div class="tool-header">
                    <h3>Fix Broken Links</h3>
                    <p>Mark broken wiki links in article content</p>
                </div>
                <form method="POST" onsubmit="return confirm('This will update article content to mark broken links. Continue?')">
                    <input type="hidden" name="action" value="fix_broken_links">
                    <button type="submit" class="btn btn-warning">Fix Links</button>
                </form>
            </div>
            
            <div class="tool-item">
                <div class="tool-header">
                    <h3>Cleanup Unused Files</h3>
                    <p>Remove files that haven't been used in 30+ days</p>
                </div>
                <form method="POST" onsubmit="return confirm('This will permanently delete unused files. Continue?')">
                    <input type="hidden" name="action" value="cleanup_unused_files">
                    <button type="submit" class="btn btn-warning">Cleanup (<?php echo $stats['unused_files']; ?>)</button>
                </form>
            </div>
            
            <div class="tool-item">
                <div class="tool-header">
                    <h3>Optimize Database</h3>
                    <p>Optimize database tables for better performance</p>
                </div>
                <form method="POST" onsubmit="return confirm('This will optimize database tables. Continue?')">
                    <input type="hidden" name="action" value="optimize_database">
                    <button type="submit" class="btn btn-info">Optimize</button>
                </form>
            </div>
            
            <div class="tool-item">
                <div class="tool-header">
                    <h3>Rebuild Search Index</h3>
                    <p>Rebuild search suggestions and indexes</p>
                </div>
                <form method="POST" onsubmit="return confirm('This will rebuild the search index. Continue?')">
                    <input type="hidden" name="action" value="rebuild_search_index">
                    <button type="submit" class="btn btn-info">Rebuild</button>
                </form>
            </div>
        </div>
    </div>

    <!-- System Health -->
    <div class="card">
        <h2>System Health</h2>
        <div class="health-indicators">
            <div class="health-item">
                <div class="health-label">Database Performance</div>
                <div class="health-status good">Good</div>
            </div>
            <div class="health-item">
                <div class="health-label">File Storage</div>
                <div class="health-status <?php echo $stats['unused_files'] > 50 ? 'warning' : 'good'; ?>">
                    <?php echo $stats['unused_files'] > 50 ? 'Needs Cleanup' : 'Good'; ?>
                </div>
            </div>
            <div class="health-item">
                <div class="health-label">Content Quality</div>
                <div class="health-status <?php echo $stats['orphaned_pages'] > 10 ? 'warning' : 'good'; ?>">
                    <?php echo $stats['orphaned_pages'] > 10 ? 'Needs Attention' : 'Good'; ?>
                </div>
            </div>
            <div class="health-item">
                <div class="health-label">User Activity</div>
                <div class="health-status <?php echo $stats['active_users'] < 5 ? 'warning' : 'good'; ?>">
                    <?php echo $stats['active_users'] < 5 ? 'Low Activity' : 'Active'; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.admin-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.admin-header h1 {
    margin: 0;
    color: #2c3e50;
}

.admin-actions {
    display: flex;
    gap: 1rem;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 2rem;
    margin-bottom: 2rem;
}

.card h2 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    color: #2c3e50;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 0.5rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.stat-item {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 6px;
    text-align: center;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.stat-value {
    color: #2c3e50;
    font-size: 1.5rem;
    font-weight: 600;
}

.issues-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.issue-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    border-radius: 6px;
    border: 2px solid;
}

.issue-item.has-issues {
    border-color: #dc3545;
    background: #f8d7da;
}

.issue-item.no-issues {
    border-color: #28a745;
    background: #d4edda;
}

.issue-icon {
    font-size: 2rem;
}

.issue-content h3 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
}

.issue-content p {
    margin: 0;
    color: #666;
}

.tools-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
}

.tool-item {
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.tool-header h3 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
}

.tool-header p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.health-indicators {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.health-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.health-label {
    color: #2c3e50;
    font-weight: 500;
}

.health-status {
    padding: 0.25rem 0.75rem;
    border-radius: 3px;
    font-size: 0.875rem;
    font-weight: 500;
}

.health-status.good {
    background: #d4edda;
    color: #155724;
}

.health-status.warning {
    background: #fff3cd;
    color: #856404;
}

.health-status.error {
    background: #f8d7da;
    color: #721c24;
}

.btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

.btn-warning {
    background: #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background: #e0a800;
}

.btn-info {
    background: #17a2b8;
    color: white;
}

.btn-info:hover {
    background: #138496;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert ul {
    margin: 0;
    padding-left: 1.5rem;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
    
    .issues-grid {
        grid-template-columns: 1fr;
    }
    
    .tools-grid {
        grid-template-columns: 1fr;
    }
    
    .health-indicators {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>
