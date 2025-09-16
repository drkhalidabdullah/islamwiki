<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Simulate logged in user for testing
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'testuser';

include 'includes/header.php';
?>

<div style="padding: 20px; background: white; margin: 20px;">
    <h1>User Menu Debug</h1>
    <p>This page is for debugging the user menu functionality.</p>
    
    <div style="margin: 20px 0;">
        <h2>User Menu HTML Structure:</h2>
        <div style="background: #f0f0f0; padding: 10px; border: 1px solid #ccc;">
            <?php
            // Get user data
            $current_user = get_user($_SESSION['user_id']);
            $user_roles = get_user_roles($_SESSION['user_id']);
            
            // Create user menu items
            $userItems = [
                ['url' => '/user/profile', 'icon' => 'fas fa-user', 'text' => 'My Profile'],
                ['url' => '/user/settings', 'icon' => 'fas fa-cog', 'text' => 'Settings'],
                ['url' => '/user/posts', 'icon' => 'fas fa-file-alt', 'text' => 'My Posts'],
                ['url' => '/user/articles', 'icon' => 'fas fa-edit', 'text' => 'My Articles'],
                ['url' => '/user/notifications', 'icon' => 'fas fa-bell', 'text' => 'Notifications']
            ];
            
            if (in_array('admin', $user_roles)) {
                $userItems[] = ['divider' => true];
                $userItems[] = ['url' => '/admin', 'icon' => 'fas fa-shield-alt', 'text' => 'Admin Panel'];
            }
            
            $userItems[] = ['divider' => true];
            $userItems[] = ['url' => '/logout', 'icon' => 'fas fa-sign-out-alt', 'text' => 'Logout'];
            
            $userAvatar = '<img src="/assets/images/default-avatar.png" alt="Profile" style="width: 36px; height: 36px; border-radius: 50%;">';
            
            echo "<div class=\"sidebar-dropdown sidebar-user\">";
            echo "<div class=\"user-icon-dropdown\">";
            echo "<a href=\"#\" class=\"sidebar-item user-icon-trigger\" title=\"User Menu\">";
            echo $userAvatar;
            echo "</a>";
            echo "<div class=\"user-dropdown-menu\">";
            
            foreach ($userItems as $item) {
                if (isset($item['divider']) && $item['divider']) {
                    echo "<hr class=\"dropdown-divider\">";
                } else {
                    echo "<a href=\"{$item['url']}\" class=\"dropdown-item\">";
                    echo "<i class=\"{$item['icon']}\"></i>";
                    echo "<span>{$item['text']}</span>";
                    echo "</a>";
                }
            }
            
            echo "</div>";
            echo "</div>";
            echo "</div>";
            ?>
        </div>
    </div>
    
    <div style="margin: 20px 0;">
        <h2>JavaScript Test:</h2>
        <button onclick="testUserMenu()">Test User Menu Click</button>
        <div id="test-results" style="margin-top: 10px; padding: 10px; background: #f0f0f0;"></div>
    </div>
</div>

<script>
function testUserMenu() {
    const userIconDropdowns = document.querySelectorAll('.user-icon-dropdown');
    const results = document.getElementById('test-results');
    
    results.innerHTML = `
        <p>Found ${userIconDropdowns.length} user icon dropdowns</p>
        <p>User menu structure:</p>
        <pre>${document.querySelector('.sidebar-user')?.outerHTML || 'Not found'}</pre>
    `;
    
    // Test click
    const trigger = document.querySelector('.user-icon-trigger');
    if (trigger) {
        trigger.click();
        results.innerHTML += '<p>Trigger clicked successfully</p>';
    } else {
        results.innerHTML += '<p>Trigger not found</p>';
    }
}

// Test on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Debug page loaded');
    const userIconDropdowns = document.querySelectorAll('.user-icon-dropdown');
    console.log('Found user icon dropdowns:', userIconDropdowns.length);
    
    userIconDropdowns.forEach((dropdown, index) => {
        console.log(`Dropdown ${index}:`, dropdown);
        const trigger = dropdown.querySelector('.user-icon-trigger');
        const menu = dropdown.querySelector('.user-dropdown-menu');
        console.log(`  Trigger:`, trigger);
        console.log(`  Menu:`, menu);
    });
});
</script>

<?php include 'includes/footer.php'; ?>
