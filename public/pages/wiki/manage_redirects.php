<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Manage Redirects';
require_login();
require_admin();

$current_user = get_user($_SESSION['user_id']);

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $from_slug = sanitize_input($_POST['from_slug'] ?? '');
        $to_slug = sanitize_input($_POST['to_slug'] ?? '');
        
        if (empty($from_slug) || empty($to_slug)) {
            $errors[] = 'Both from and to slugs are required.';
        } else {
            // Check if target article exists
            $stmt = $pdo->prepare("SELECT id FROM wiki_articles WHERE slug = ?");
            $stmt->execute([$to_slug]);
            $target_article = $stmt->fetch();
            
            if (!$target_article) {
                $errors[] = 'Target article does not exist.';
            } else {
                // Check if redirect already exists
                $stmt = $pdo->prepare("SELECT id FROM wiki_redirects WHERE from_slug = ?");
                $stmt->execute([$from_slug]);
                
                if ($stmt->fetch()) {
                    $errors[] = 'A redirect from this slug already exists.';
                } else {
                    // Check if source article exists (should not)
                    $stmt = $pdo->prepare("SELECT id FROM wiki_articles WHERE slug = ?");
                    $stmt->execute([$from_slug]);
                    
                    if ($stmt->fetch()) {
                        $errors[] = 'Cannot create redirect from an existing article slug.';
                    } else {
                        try {
                            $stmt = $pdo->prepare("
                                INSERT INTO wiki_redirects (from_slug, to_article_id, created_by) 
                                VALUES (?, ?, ?)
                            ");
                            $stmt->execute([$from_slug, $target_article['id'], $_SESSION['user_id']]);
                            $success = 'Redirect created successfully.';
                        } catch (Exception $e) {
                            $errors[] = 'Error creating redirect: ' . $e->getMessage();
                        }
                    }
                }
            }
        }
    } elseif ($action === 'delete') {
        $redirect_id = (int)($_POST['redirect_id'] ?? 0);
        
        if ($redirect_id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM wiki_redirects WHERE id = ?");
                $stmt->execute([$redirect_id]);
                $success = 'Redirect deleted successfully.';
            } catch (Exception $e) {
                $errors[] = 'Error deleting redirect: ' . $e->getMessage();
            }
        }
    }
}

// Get all redirects
$stmt = $pdo->query("
    SELECT r.*, a.title as target_title, a.slug as target_slug, u.username as created_by_username
    FROM wiki_redirects r
    JOIN wiki_articles a ON r.to_article_id = a.id
    JOIN users u ON r.created_by = u.id
    ORDER BY r.created_at DESC
");
$redirects = $stmt->fetchAll();

include '../../includes/header.php';

?>
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_manage_redirects.css">
<?php
?>

<div class="admin-page">
    <div class="admin-header">
        <h1>Manage Redirects</h1>
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

    <!-- Create Redirect Form -->
    <div class="card">
        <h2>Create New Redirect</h2>
        <form method="POST" class="form">
            <input type="hidden" name="action" value="create">
            
            <div class="form-group">
                <label for="from_slug">From Slug (source):</label>
                <input type="text" id="from_slug" name="from_slug" required 
                       placeholder="old-article-name" 
                       pattern="[a-z0-9\-]+" 
                       title="Only lowercase letters, numbers, and hyphens allowed">
                <small>This is the old URL that will redirect to the target article.</small>
            </div>
            
            <div class="form-group">
                <label for="to_slug">To Slug (target):</label>
                <input type="text" id="to_slug" name="to_slug" required 
                       placeholder="new-article-name"
                       pattern="[a-z0-9\-]+"
                       title="Only lowercase letters, numbers, and hyphens allowed">
                <small>This is the existing article that the redirect will point to.</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Create Redirect</button>
        </form>
    </div>

    <!-- Existing Redirects -->
    <div class="card">
        <h2>Existing Redirects</h2>
        
        <?php if (empty($redirects)): ?>
            <p>No redirects have been created yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>From Slug</th>
                            <th>To Article</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($redirects as $redirect): ?>
                            <tr>
                                <td>
                                    <code>/wiki/<?php echo htmlspecialchars($redirect['from_slug']); ?></code>
                                </td>
                                <td>
                                    <a href="/wiki/<?php echo $redirect['target_slug']; ?>" target="_blank">
                                        <?php echo htmlspecialchars($redirect['target_title']); ?>
                                    </a>
                                    <br>
                                    <small><code>/wiki/<?php echo htmlspecialchars($redirect['target_slug']); ?></code></small>
                                </td>
                                <td><?php echo htmlspecialchars($redirect['created_by_username']); ?></td>
                                <td><?php echo format_date($redirect['created_at']); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this redirect?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="redirect_id" value="<?php echo $redirect['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>


<?php include '../../includes/footer.php'; ?>
