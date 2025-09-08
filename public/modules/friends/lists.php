<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Custom Lists';
require_login();

$current_user = get_user($_SESSION['user_id']);

// Get custom friend lists (this would need a custom_lists table in a real implementation)
// For now, we'll show some example lists
$custom_lists = [
    ['id' => 1, 'name' => 'Close Friends', 'count' => 5, 'description' => 'My closest friends'],
    ['id' => 2, 'name' => 'Work Colleagues', 'count' => 12, 'description' => 'People I work with'],
    ['id' => 3, 'name' => 'Family', 'count' => 8, 'description' => 'Family members'],
    ['id' => 4, 'name' => 'Study Group', 'count' => 6, 'description' => 'Study partners and classmates']
];

include "../../includes/header.php";;
?>

<div class="friends-page">
    <div class="friends-container">
        <!-- Left Sidebar -->
        <div class="friends-sidebar">
            <div class="friends-nav">
                <h2><i class="fas fa-users"></i> Friends</h2>
                <nav class="friends-menu">
                    <a href="/friends" class="friends-nav-item">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                    <a href="/friends/requests" class="friends-nav-item">
                        <i class="fas fa-user-plus"></i>
                        <span>Friend Requests</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="/friends/suggestions" class="friends-nav-item">
                        <i class="fas fa-user-plus"></i>
                        <span>Suggestions</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="/friends/all" class="friends-nav-item">
                        <i class="fas fa-users"></i>
                        <span>All friends</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <a href="/friends/lists" class="friends-nav-item active">
                        <i class="fas fa-list"></i>
                        <span>Custom Lists</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="friends-main">
            <div class="custom-lists">
                <div class="section-header">
                    <h1>Custom Lists</h1>
                    <button class="btn-create-list" onclick="createNewList()">
                        <i class="fas fa-plus"></i>
                        Create List
                    </button>
                </div>
                
                <div class="lists-grid">
                    <?php foreach ($custom_lists as $list): ?>
                        <div class="list-card">
                            <div class="list-header">
                                <h3 class="list-name"><?php echo htmlspecialchars($list['name']); ?></h3>
                                <div class="list-actions">
                                    <button class="btn-edit-list" onclick="editList(<?php echo $list['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-delete-list" onclick="deleteList(<?php echo $list['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="list-info">
                                <p class="list-description"><?php echo htmlspecialchars($list['description']); ?></p>
                                <p class="list-count"><?php echo $list['count']; ?> friends</p>
                            </div>
                            <div class="list-footer">
                                <a href="/friends/lists/<?php echo $list['id']; ?>" class="btn-view-list">
                                    <i class="fas fa-eye"></i>
                                    View List
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($custom_lists)): ?>
                    <div class="no-lists">
                        <i class="fas fa-list"></i>
                        <h3>No custom lists yet</h3>
                        <p>Create custom lists to organize your friends into groups.</p>
                        <button class="btn btn-primary" onclick="createNewList()">Create Your First List</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function createNewList() {
    const listName = prompt('Enter list name:');
    if (listName && listName.trim()) {
        // This would typically make an AJAX call to create the list
        alert('List creation feature coming soon!');
    }
}

function editList(listId) {
    // This would typically open an edit modal or redirect to edit page
    alert('List editing feature coming soon!');
}

function deleteList(listId) {
    if (confirm('Are you sure you want to delete this list?')) {
        // This would typically make an AJAX call to delete the list
        alert('List deletion feature coming soon!');
    }
}
</script>

<?php include "../../includes/footer.php";; ?>
