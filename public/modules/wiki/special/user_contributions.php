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
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_special_user_contributions.css">
<?php
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


<?php include "../../../includes/footer.php"; ?>
