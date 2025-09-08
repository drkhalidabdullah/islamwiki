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

<style>
.admin-page {
    max-width: 1200px;
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

.form {
    display: grid;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.form-group input {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.form-group small {
    margin-top: 0.25rem;
    color: #6c757d;
    font-size: 0.875rem;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.table th,
.table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e9ecef;
}

.table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.table tr:hover {
    background: #f8f9fa;
}

.table code {
    background: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
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

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
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
</style>

<?php include '../../includes/footer.php'; ?>
