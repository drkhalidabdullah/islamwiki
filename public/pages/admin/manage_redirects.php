<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Manage Redirects';
require_login();

// Check if user is admin
if (!is_admin()) {
    show_message('Access denied. Admin privileges required.', 'error');
    redirect('/dashboard');
}

$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_redirect') {
        $from_path = sanitize_input($_POST['from_path'] ?? '');
        $to_path = sanitize_input($_POST['to_path'] ?? '');
        $redirect_type = (int)($_POST['redirect_type'] ?? 301);
        
        if (empty($from_path) || empty($to_path)) {
            $error = 'Both from and to paths are required.';
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO wiki_redirects (from_path, to_path, redirect_type, created_by, created_at) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
                
                if ($stmt->execute([$from_path, $to_path, $redirect_type, $_SESSION['user_id']])) {
                    $success = 'Redirect created successfully.';
                    log_activity('redirect_created', "Created redirect: $from_path -> $to_path");
                } else {
                    $error = 'Failed to create redirect.';
                }
            } catch (Exception $e) {
                $error = 'Error creating redirect: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete_redirect') {
        $redirect_id = (int)($_POST['redirect_id'] ?? 0);
        
        if ($redirect_id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM wiki_redirects WHERE id = ?");
                if ($stmt->execute([$redirect_id])) {
                    $success = 'Redirect deleted successfully.';
                    log_activity('redirect_deleted', "Deleted redirect ID: $redirect_id");
                } else {
                    $error = 'Failed to delete redirect.';
                }
            } catch (Exception $e) {
                $error = 'Error deleting redirect: ' . $e->getMessage();
            }
        }
    }
}

// Get all redirects
$stmt = $pdo->query("
    SELECT wr.*, u.username 
    FROM wiki_redirects wr 
    LEFT JOIN users u ON wr.created_by = u.id 
    ORDER BY wr.created_at DESC
");
$redirects = $stmt->fetchAll();

include "../../includes/header.php";

?>
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/manage_redirects.css">
<?php
?>

<div class="admin-container">
    <div class="admin-header">
        <h1><i class="fas fa-exchange-alt"></i> Manage Redirects</h1>
        <p>Create and manage page redirects</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Create Redirect Form -->
    <div class="card">
        <h3>Create New Redirect</h3>
        <form method="POST" class="redirect-form">
            <input type="hidden" name="action" value="create_redirect">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="from_path">From Path *</label>
                    <input type="text" id="from_path" name="from_path" placeholder="/old-page" required>
                    <small class="form-help">The old URL path (e.g., /old-page)</small>
                </div>
                
                <div class="form-group">
                    <label for="to_path">To Path *</label>
                    <input type="text" id="to_path" name="to_path" placeholder="/new-page" required>
                    <small class="form-help">The new URL path (e.g., /new-page)</small>
                </div>
            </div>

            <div class="form-group">
                <label for="redirect_type">Redirect Type</label>
                <select id="redirect_type" name="redirect_type">
                    <option value="301">301 - Permanent Redirect</option>
                    <option value="302">302 - Temporary Redirect</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Redirect
                </button>
            </div>
        </form>
    </div>

    <!-- Redirects List -->
    <div class="card">
        <h3>Existing Redirects</h3>
        
        <?php if (!empty($redirects)): ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>From Path</th>
                            <th>To Path</th>
                            <th>Type</th>
                            <th>Created By</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($redirects as $redirect): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($redirect['from_path']); ?></code></td>
                            <td><code><?php echo htmlspecialchars($redirect['to_path']); ?></code></td>
                            <td>
                                <span class="redirect-type redirect-<?php echo $redirect['redirect_type']; ?>">
                                    <?php echo $redirect['redirect_type']; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($redirect['username'] ?: 'System'); ?></td>
                            <td><?php echo format_date($redirect['created_at']); ?></td>
                            <td>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this redirect?')">
                                    <input type="hidden" name="action" value="delete_redirect">
                                    <input type="hidden" name="redirect_id" value="<?php echo $redirect['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No redirects found.</p>
        <?php endif; ?>
    </div>

    <div class="form-actions">
        <a href="/admin" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>


<?php include "../../includes/footer.php"; ?>
