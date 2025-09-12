<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/wiki_functions.php';

// Check maintenance mode
check_maintenance_mode();

$page_title = 'User Contributions';

// Get parameters
$username = $_GET['user'] ?? '';
$limit = min((int)($_GET['limit'] ?? 50), 200); // Max 200 results
$namespace_id = $_GET['namespace'] ?? null;

// Get namespaces for filter
$namespaces = get_wiki_namespaces();

// Get user if specified
$user = null;
$user_contributions = [];
if ($username) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR display_name = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user) {
        $user_contributions = get_user_contributions($user['id'], $limit);
        
        // Filter by namespace if specified
        if ($namespace_id !== null) {
            $user_contributions = array_filter($user_contributions, function($contribution) use ($namespace_id) {
                return $contribution['namespace_id'] == $namespace_id;
            });
        }
    }
}

// Get recent contributors
$stmt = $pdo->query("
    SELECT u.id, u.username, u.display_name, COUNT(wa.id) as contribution_count,
           MAX(wa.updated_at) as last_contribution
    FROM users u
    JOIN wiki_articles wa ON (u.id = wa.author_id OR u.id = wa.last_edit_by)
    WHERE wa.status = 'published'
    GROUP BY u.id
    ORDER BY contribution_count DESC, last_contribution DESC
    LIMIT 20
");
$recent_contributors = $stmt->fetchAll();

include "../../../includes/header.php";
?>

<div class="special-page-container">
    <div class="special-page-header">
        <h1>User Contributions</h1>
        <p>View contributions by specific users</p>
    </div>

    <!-- User Search -->
    <div class="user-search">
        <form method="GET" class="search-form">
            <div class="search-group">
                <label for="user">Username or Display Name:</label>
                <input type="text" id="user" name="user" value="<?php echo htmlspecialchars($username); ?>" 
                       placeholder="Enter username or display name" required>
            </div>
            
            <div class="search-group">
                <label for="namespace">Namespace:</label>
                <select name="namespace" id="namespace">
                    <option value="">All namespaces</option>
                    <?php foreach ($namespaces as $ns): ?>
                        <option value="<?php echo $ns['id']; ?>" 
                                <?php echo $namespace_id == $ns['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ns['display_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="search-group">
                <label for="limit">Results per page:</label>
                <select name="limit" id="limit">
                    <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                    <option value="200" <?php echo $limit == 200 ? 'selected' : ''; ?>>200</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <!-- User Results -->
    <?php if ($username && $user): ?>
    <div class="user-profile">
        <div class="profile-header">
            <h2><?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?></h2>
            <div class="profile-meta">
                <span class="username">@<?php echo htmlspecialchars($user['username']); ?></span>
                <span class="join-date">Joined <?php echo format_date($user['created_at']); ?></span>
            </div>
        </div>
        
        <div class="profile-stats">
            <div class="stat-item">
                <span class="stat-number"><?php echo count($user_contributions); ?></span>
                <span class="stat-label">Contributions</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $user['last_login_at'] ? format_date($user['last_login_at']) : 'Never'; ?></span>
                <span class="stat-label">Last Active</span>
            </div>
        </div>
    </div>

    <!-- User Contributions List -->
    <div class="contributions-list">
        <?php if (empty($user_contributions)): ?>
            <div class="no-results">
                <p>No contributions found for this user.</p>
            </div>
        <?php else: ?>
            <div class="contributions-header">
                <h3>Contributions</h3>
                <div class="contributions-count"><?php echo count($user_contributions); ?> contributions</div>
            </div>
            
            <div class="contributions-table">
                <div class="contribution-item header">
                    <div class="contribution-page">Page</div>
                    <div class="contribution-role">Role</div>
                    <div class="contribution-date">Date</div>
                    <div class="contribution-actions">Actions</div>
                </div>
                
                <?php foreach ($user_contributions as $contribution): ?>
                <div class="contribution-item">
                    <div class="contribution-page">
                        <a href="/wiki/<?php echo $contribution['slug']; ?>" class="page-link">
                            <?php if ($contribution['namespace_name'] !== 'Main'): ?>
                                <span class="namespace"><?php echo htmlspecialchars($contribution['namespace_display']); ?>:</span>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($contribution['title']); ?>
                        </a>
                        <?php if ($contribution['is_redirect']): ?>
                            <span class="redirect-indicator" title="Redirect">↪</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="contribution-role">
                        <?php if ($contribution['author_id'] == $user['id']): ?>
                            <span class="role-badge author">Author</span>
                        <?php endif; ?>
                        <?php if ($contribution['last_edit_by'] == $user['id']): ?>
                            <span class="role-badge editor">Last Editor</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="contribution-date">
                        <span class="date-ago" title="<?php echo format_date($contribution['updated_at']); ?>">
                            <?php echo time_ago($contribution['updated_at']); ?>
                        </span>
                    </div>
                    
                    <div class="contribution-actions">
                        <a href="/wiki/<?php echo $contribution['slug']; ?>" class="btn btn-sm" title="View">View</a>
                        <a href="/wiki/<?php echo $contribution['slug']; ?>/history" class="btn btn-sm" title="History">History</a>
                        <?php if (is_logged_in() && is_editor()): ?>
                            <a href="/wiki/<?php echo $contribution['slug']; ?>/edit" class="btn btn-sm" title="Edit">Edit</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php elseif ($username && !$user): ?>
        <div class="no-results">
            <p>User "<?php echo htmlspecialchars($username); ?>" not found.</p>
        </div>
    <?php endif; ?>

    <!-- Recent Contributors -->
    <div class="recent-contributors">
        <h3>Top Contributors</h3>
        <div class="contributors-list">
            <?php foreach ($recent_contributors as $contributor): ?>
            <div class="contributor-item">
                <a href="?user=<?php echo urlencode($contributor['username']); ?>" class="contributor-link">
                    <div class="contributor-info">
                        <span class="contributor-name"><?php echo htmlspecialchars($contributor['display_name'] ?: $contributor['username']); ?></span>
                        <span class="contributor-username">@<?php echo htmlspecialchars($contributor['username']); ?></span>
                    </div>
                    <div class="contributor-stats">
                        <span class="contribution-count"><?php echo number_format($contributor['contribution_count']); ?> contributions</span>
                        <span class="last-contribution">Last: <?php echo time_ago($contributor['last_contribution']); ?></span>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Navigation -->
    <div class="special-navigation">
        <div class="nav-links">
            <a href="/wiki/special/recent_changes.php" class="nav-link">Recent Changes</a>
            <a href="/wiki/special/all_pages.php" class="nav-link">All Pages</a>
            <a href="/wiki/special/new_pages.php" class="nav-link">New Pages</a>
            <a href="/wiki/special/orphaned_pages.php" class="nav-link">Orphaned Pages</a>
        </div>
    </div>
</div>

<style>
.special-page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.special-page-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.special-page-header h1 {
    color: #2c3e50;
    margin: 0 0 0.5rem 0;
}

.user-search {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.search-form {
    display: flex;
    gap: 1rem;
    align-items: end;
    flex-wrap: wrap;
}

.search-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.search-group label {
    font-weight: 600;
    color: #2c3e50;
}

.search-group input,
.search-group select {
    padding: 0.5rem;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    min-width: 150px;
}

.user-profile {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.profile-header {
    margin-bottom: 1.5rem;
}

.profile-header h2 {
    color: #2c3e50;
    margin: 0 0 0.5rem 0;
}

.profile-meta {
    display: flex;
    gap: 1rem;
    color: #6c757d;
    font-size: 0.9rem;
}

.profile-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.stat-item {
    text-align: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.stat-number {
    display: block;
    font-size: 1.5rem;
    font-weight: bold;
    color: #007bff;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #6c757d;
    font-size: 0.9rem;
}

.contributions-list {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

.contributions-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.contributions-header h3 {
    margin: 0;
    color: #2c3e50;
}

.contributions-count {
    color: #6c757d;
    font-size: 0.9rem;
}

.contributions-table {
    display: flex;
    flex-direction: column;
}

.contribution-item {
    display: grid;
    grid-template-columns: 1fr 150px 120px 150px;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f1f3f4;
    align-items: center;
}

.contribution-item.header {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 2px solid #dee2e6;
}

.contribution-item:last-child {
    border-bottom: none;
}

.contribution-page {
    font-weight: 500;
}

.page-link {
    color: #007bff;
    text-decoration: none;
}

.page-link:hover {
    text-decoration: underline;
}

.namespace {
    color: #6c757d;
    font-weight: normal;
}

.redirect-indicator {
    color: #ffc107;
    margin-left: 0.5rem;
}

.contribution-role {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.role-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.role-badge.author {
    background: #d4edda;
    color: #155724;
}

.role-badge.editor {
    background: #cce5ff;
    color: #004085;
}

.contribution-date {
    font-size: 0.9rem;
    color: #6c757d;
}

.contribution-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.btn-sm:hover {
    transform: translateY(-1px);
}

.recent-contributors {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.recent-contributors h3 {
    color: #2c3e50;
    margin: 0 0 1.5rem 0;
}

.contributors-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.contributor-item {
    border: 1px solid #e9ecef;
    border-radius: 6px;
    overflow: hidden;
    transition: all 0.3s;
}

.contributor-item:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.1);
}

.contributor-link {
    display: block;
    padding: 1rem;
    text-decoration: none;
    color: inherit;
}

.contributor-info {
    margin-bottom: 0.5rem;
}

.contributor-name {
    display: block;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.contributor-username {
    color: #6c757d;
    font-size: 0.9rem;
}

.contributor-stats {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    font-size: 0.85rem;
    color: #6c757d;
}

.contribution-count {
    font-weight: 500;
    color: #007bff;
}

.special-navigation {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.nav-links {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
}

.nav-link {
    color: #007bff;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border: 1px solid #007bff;
    border-radius: 4px;
    transition: all 0.3s;
}

.nav-link:hover {
    background: #007bff;
    color: white;
}

.no-results {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}

@media (max-width: 768px) {
    .search-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .contribution-item {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    
    .contribution-item.header {
        display: none;
    }
    
    .contributors-list {
        grid-template-columns: 1fr;
    }
    
    .nav-links {
        justify-content: center;
    }
}
</style>

<?php include "../../../includes/footer.php"; ?>
