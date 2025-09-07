<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Admin Panel';
require_login();

// Check if user is admin
if (!is_admin()) {
    show_message('Access denied. Admin privileges required.', 'error');
    redirect('/dashboard');
}

// Get statistics
$stats = [];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $stmt->fetch()['count'];

// Total articles
$stmt = $pdo->query("SELECT COUNT(*) as count FROM wiki_articles");
$stats['total_articles'] = $stmt->fetch()['count'];

// Published articles
$stmt = $pdo->query("SELECT COUNT(*) as count FROM wiki_articles WHERE status = 'published'");
$stats['published_articles'] = $stmt->fetch()['count'];

// Draft articles
$stmt = $pdo->query("SELECT COUNT(*) as count FROM wiki_articles WHERE status = 'draft'");
$stats['draft_articles'] = $stmt->fetch()['count'];

// Total categories
$stmt = $pdo->query("SELECT COUNT(*) as count FROM content_categories");
$stats['total_categories'] = $stmt->fetch()['count'];

// Recent users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $stmt->fetchAll();

// Recent articles
$stmt = $pdo->query("
    SELECT a.*, u.username, u.display_name 
    FROM wiki_articles a 
    JOIN users u ON a.author_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 5
");
$recent_articles = $stmt->fetchAll();

include "../../includes/header.php";;
?>

<div class="admin-dashboard">
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
        <p>Manage your IslamWiki site</p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_users']); ?></h3>
                <p>Total Users</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📄</div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_articles']); ?></h3>
                <p>Total Articles</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['published_articles']); ?></h3>
                <p>Published</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['draft_articles']); ?></h3>
                <p>Drafts</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📂</div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_categories']); ?></h3>
                <p>Categories</p>
            </div>
        </div>
    </div>
    
    <div class="admin-content">
        <div class="admin-section">
            <h2>Recent Users</h2>
            <div class="card">
                <?php if (!empty($recent_users)): ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Full Name</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['display_name'] ?: $user['full_name']); ?></td>
                                    <td><?php echo format_date($user['created_at']); ?></td>
                                    <td>
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm">Edit</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No users found.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="admin-section">
            <h2>Recent Articles</h2>
            <div class="card">
                <?php if (!empty($recent_articles)): ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_articles as $article): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($article['title']); ?></td>
                                    <td><?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $article['status']; ?>">
                                            <?php echo ucfirst($article['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo format_date($article['created_at']); ?></td>
                                    <td>
                                        <a href="edit_article.php?id=<?php echo $article['id']; ?>" class="btn btn-sm">Edit</a>
                                        <a href="wiki/article.php?slug=<?php echo $article['slug']; ?>" class="btn btn-sm">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No articles found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="admin-actions">
        <h2>Quick Actions</h2>
        <div class="action-buttons">
            <a href="create_article.php" class="btn btn-primary">Create Article</a>
            <a href="manage_users.php" class="btn btn-secondary">Manage Users</a>
            <a href="manage_categories.php" class="btn btn-secondary">Manage Categories</a>
            <a href="system_settings.php" class="btn btn-secondary">System Settings</a>
        </div>
    </div>
</div>

<style>
.admin-dashboard {
    max-width: 1200px;
    margin: 0 auto;
}

.admin-header {
    text-align: center;
    margin-bottom: 3rem;
}

.admin-header h1 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.admin-header p {
    color: #666;
    font-size: 1.1rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    font-size: 2rem;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 50%;
}

.stat-content h3 {
    font-size: 2rem;
    margin: 0;
    color: #2c3e50;
}

.stat-content p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.admin-content {
    display: grid;
    gap: 2rem;
    margin-bottom: 3rem;
}

.admin-section h2 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.table-responsive {
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th,
.admin-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.admin-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-published {
    background: #d4edda;
    color: #155724;
}

.status-draft {
    background: #fff3cd;
    color: #856404;
}

.status-archived {
    background: #f8d7da;
    color: #721c24;
}

.admin-actions {
    text-align: center;
}

.admin-actions h2 {
    color: #2c3e50;
    margin-bottom: 2rem;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.8rem;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .admin-table {
        font-size: 0.9rem;
    }
}
</style>

<?php include "../../includes/footer.php";; ?>
