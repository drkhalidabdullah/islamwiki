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

<style>
.admin-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.admin-header {
    text-align: center;
    margin-bottom: 2rem;
}

.admin-header h1 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.admin-header p {
    color: #666;
    font-size: 1.1rem;
}

.redirect-form {
    max-width: 600px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.form-help {
    color: #666;
    font-size: 0.9rem;
    margin-top: 0.25rem;
}

.table-responsive {
    overflow-x: auto;
    margin-top: 1rem;
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

.admin-table code {
    background: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
}

.redirect-type {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

.redirect-301 {
    background: #d4edda;
    color: #155724;
}

.redirect-302 {
    background: #fff3cd;
    color: #856404;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.8rem;
}

.btn-danger {
    background: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .admin-table {
        font-size: 0.9rem;
    }
}
</style>

<?php include "../../includes/footer.php"; ?>
