<?php
/**
 * User Achievements Page
 * 
 * @version 1.0.0
 */

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/header.php';

// Check if user is logged in
require_login();

// Include achievement extension
require_once __DIR__ . '/../../extensions/achievements/extension.php';

$achievements_extension = new AchievementsExtension();

// Get user's achievements and level
$user_id = $_SESSION['user_id'];
$user_level = $achievements_extension->getUserLevel($user_id);
$user_achievements = $achievements_extension->getUserAchievements($user_id);
$categories = $achievements_extension->getCategories();
$types = $achievements_extension->getTypes();
$stats = $achievements_extension->getAchievementStats($user_id);
$leaderboard = $achievements_extension->getLeaderboard(10);

?>

<div class="achievements-container">
    <div class="achievements-layout">
        <!-- Left Sidebar - Filters Only -->
        <div class="achievements-sidebar">
            <!-- Search and Filters -->
            <div class="achievement-filters">
                <h3>Filter Achievements</h3>
                
                <!-- Search Box -->
                <div class="filter-group">
                    <div class="filter-label">Search</div>
                    <input type="text" id="achievement-search" placeholder="Search achievements..." class="achievement-search-input">
                </div>
                
                <div class="filter-group">
                    <div class="filter-label">Category</div>
                    <div class="filter-options">
                        <div class="filter-option active" data-filter="category" data-value="all">All</div>
                        <?php foreach ($categories as $category): ?>
                            <div class="filter-option" data-filter="category" data-value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="filter-group">
                    <div class="filter-label">Type</div>
                    <div class="filter-options">
                        <div class="filter-option active" data-filter="type" data-value="all">All</div>
                        <?php foreach ($types as $type): ?>
                            <div class="filter-option" data-filter="type" data-value="<?php echo $type['id']; ?>">
                                <?php echo htmlspecialchars($type['name']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="filter-group">
                    <div class="filter-label">Rarity</div>
                    <div class="filter-options">
                        <div class="filter-option active" data-filter="rarity" data-value="all">All</div>
                        <div class="filter-option" data-filter="rarity" data-value="common">Common</div>
                        <div class="filter-option" data-filter="rarity" data-value="uncommon">Uncommon</div>
                        <div class="filter-option" data-filter="rarity" data-value="rare">Rare</div>
                        <div class="filter-option" data-filter="rarity" data-value="epic">Epic</div>
                        <div class="filter-option" data-filter="rarity" data-value="legendary">Legendary</div>
                    </div>
                </div>
                
                <div class="filter-group">
                    <div class="filter-label">Status</div>
                    <div class="filter-options">
                        <div class="filter-option active" data-filter="status" data-value="all">All</div>
                        <div class="filter-option" data-filter="status" data-value="completed">Completed</div>
                        <div class="filter-option" data-filter="status" data-value="in_progress">In Progress</div>
                        <div class="filter-option" data-filter="status" data-value="locked">Locked</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="achievements-main">
            <!-- Level Widget -->
            <div class="achievement-widget">
                <div class="level-info">
                    <div class="level-badge">
                        <div class="level-number"><?php echo $user_level['level']; ?></div>
                        <div class="level-text">Level</div>
                    </div>
                    <div class="level-details">
                        <div class="level-title">Level <?php echo $user_level['level']; ?></div>
                        <div class="level-progress">
                            <div class="level-progress-bar" style="width: <?php echo $user_level['xp_to_next_level'] > 0 ? ($user_level['current_level_xp'] / ($user_level['current_level_xp'] + $user_level['xp_to_next_level'])) * 100 : 100; ?>%"></div>
                        </div>
                        <div class="level-stats">
                            <span><?php echo $user_level['current_level_xp']; ?> XP</span>
                            <span><?php echo $user_level['total_achievements']; ?> Achievements</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Achievement Statistics -->
            <div class="achievement-stats">
                <h2>Your Achievement Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $stats['total_achievements']; ?></div>
                        <div class="stat-label">Total Achievements</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $user_level['total_xp']; ?></div>
                        <div class="stat-label">Total XP</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $user_level['total_points']; ?></div>
                        <div class="stat-label">Total Points</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $user_level['level']; ?></div>
                        <div class="stat-label">Current Level</div>
                    </div>
                </div>
                
                <?php if (!empty($stats['by_category'])): ?>
                    <h3>Achievements by Category</h3>
                    <div class="category-stats">
                        <?php foreach ($stats['by_category'] as $category): ?>
                            <div class="category-stat" style="background-color: <?php echo $category['color']; ?>">
                                <?php echo $category['name']; ?>: <?php echo $category['count']; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($stats['by_rarity'])): ?>
                    <h3>Achievements by Rarity</h3>
                    <div class="rarity-stats">
                        <?php foreach ($stats['by_rarity'] as $rarity): ?>
                            <div class="rarity-stat rarity-<?php echo $rarity['rarity']; ?>">
                                <?php echo ucfirst($rarity['rarity']); ?>: <?php echo $rarity['count']; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Achievements Grid -->
            <div class="achievement-grid">
        <?php foreach ($user_achievements as $achievement): ?>
            <div class="achievement-card <?php echo $achievement['is_completed'] ? 'completed' : ($achievement['level_requirement'] > $user_level['level'] ? 'locked' : ''); ?>" 
                 data-category="<?php echo $achievement['category_id']; ?>"
                 data-type="<?php echo $achievement['type_id']; ?>"
                 data-rarity="<?php echo $achievement['rarity']; ?>"
                 data-achievement-id="<?php echo $achievement['id']; ?>">
                
                <div class="achievement-rarity rarity-<?php echo $achievement['rarity']; ?>">
                    <?php echo ucfirst($achievement['rarity']); ?>
                </div>
                
                <div class="achievement-header">
                    <div class="achievement-icon <?php echo $achievement['is_completed'] ? 'completed' : ($achievement['level_requirement'] > $user_level['level'] ? 'locked' : ''); ?>">
                        <i class="<?php echo $achievement['icon']; ?>"></i>
                    </div>
                    <div class="achievement-info">
                        <div class="achievement-name"><?php echo htmlspecialchars($achievement['name']); ?></div>
                        <div class="achievement-category"><?php echo htmlspecialchars($achievement['category_name']); ?></div>
                    </div>
                </div>
                
                <div class="achievement-description">
                    <?php echo htmlspecialchars($achievement['description']); ?>
                </div>
                
                <?php if (!$achievement['is_completed'] && $achievement['level_requirement'] <= $user_level['level']): ?>
                    <div class="achievement-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" data-progress="<?php echo $achievement['progress']; ?>"></div>
                        </div>
                        <div class="progress-text"><?php echo $achievement['progress']; ?>% Complete</div>
                    </div>
                <?php endif; ?>
                
                <div class="achievement-rewards">
                    <div class="reward-item">
                        <i class="fas fa-star"></i>
                        <span><?php echo $achievement['points']; ?> Points</span>
                    </div>
                    <div class="reward-item">
                        <i class="fas fa-bolt"></i>
                        <span><?php echo $achievement['xp_reward']; ?> XP</span>
                    </div>
                </div>
                
                <?php if ($achievement['is_completed']): ?>
                    <div class="achievement-completed">
                        <i class="fas fa-check-circle"></i>
                        Completed on <?php echo date('M j, Y', strtotime($achievement['completed_at'])); ?>
                    </div>
                <?php elseif ($achievement['level_requirement'] > $user_level['level']): ?>
                    <div class="achievement-locked">
                        <i class="fas fa-lock"></i>
                        Requires Level <?php echo $achievement['level_requirement']; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            </div>
        </div>

        <!-- Right Sidebar - Top Contributors -->
        <div class="achievements-rightbar">
            <div class="leaderboard">
                <div class="leaderboard-header">
                    <h3>Top Contributors</h3>
                    <a href="/leaderboard" class="btn btn-secondary">View Full</a>
                </div>
                <div class="leaderboard-items">
                    <?php foreach ($leaderboard as $index => $user): ?>
                        <div class="leaderboard-item">
                            <div class="leaderboard-rank rank-<?php echo $index < 3 ? $index + 1 : 'other'; ?>">
                                <?php echo $index + 1; ?>
                            </div>
                            <div class="leaderboard-user">
                                <div class="leaderboard-username"><?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?></div>
                                <div class="leaderboard-level">Level <?php echo $user['level']; ?></div>
                            </div>
                            <div class="leaderboard-stats">
                                <div class="leaderboard-xp"><?php echo number_format($user['total_xp']); ?> XP</div>
                                <div class="leaderboard-achievements"><?php echo $user['total_achievements']; ?> Achievements</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Achievement System CSS and JS -->
<link rel="stylesheet" href="/extensions/achievements/assets/css/achievements.css">
<script src="/extensions/achievements/assets/js/achievements.js"></script>

<script>
// Enhanced filtering functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('achievement-search');
    const filterOptions = document.querySelectorAll('.filter-option');
    const achievementCards = document.querySelectorAll('.achievement-card');
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filterAchievements();
    });
    
    // Filter functionality
    filterOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove active class from siblings
            const siblings = this.parentNode.querySelectorAll('.filter-option');
            siblings.forEach(sibling => sibling.classList.remove('active'));
            
            // Add active class to clicked option
            this.classList.add('active');
            
            // Apply filters
            filterAchievements();
        });
    });
    
    function filterAchievements() {
        const searchTerm = searchInput.value.toLowerCase();
        const activeFilters = {};
        
        // Get active filter values
        document.querySelectorAll('.filter-group').forEach(group => {
            const activeOption = group.querySelector('.filter-option.active');
            if (activeOption) {
                const filterType = activeOption.dataset.filter;
                const filterValue = activeOption.dataset.value;
                activeFilters[filterType] = filterValue;
            }
        });
        
        // Filter achievements
        achievementCards.forEach(card => {
            let show = true;
            
            // Search filter
            if (searchTerm) {
                const cardText = card.textContent.toLowerCase();
                if (!cardText.includes(searchTerm)) {
                    show = false;
                }
            }
            
            // Category filter
            if (activeFilters.category && activeFilters.category !== 'all') {
                if (card.dataset.category !== activeFilters.category) {
                    show = false;
                }
            }
            
            // Type filter
            if (activeFilters.type && activeFilters.type !== 'all') {
                if (card.dataset.type !== activeFilters.type) {
                    show = false;
                }
            }
            
            // Rarity filter
            if (activeFilters.rarity && activeFilters.rarity !== 'all') {
                if (card.dataset.rarity !== activeFilters.rarity) {
                    show = false;
                }
            }
            
            // Status filter
            if (activeFilters.status && activeFilters.status !== 'all') {
                const isCompleted = card.classList.contains('completed');
                const isLocked = card.classList.contains('locked');
                const isInProgress = !isCompleted && !isLocked;
                
                if (activeFilters.status === 'completed' && !isCompleted) {
                    show = false;
                } else if (activeFilters.status === 'in_progress' && !isInProgress) {
                    show = false;
                } else if (activeFilters.status === 'locked' && !isLocked) {
                    show = false;
                }
            }
            
            // Show/hide card
            card.style.display = show ? 'block' : 'none';
        });
    }
});
</script>

<style>
.achievements-container {
    width: 100%;
    margin: 0;
    padding: 20px;
}

.achievements-layout {
    display: grid;
    grid-template-columns: 280px 1fr 300px;
    gap: 30px;
    align-items: start;
    max-width: 1600px;
    margin: 0 auto;
}

.achievements-sidebar {
    position: sticky;
    top: 20px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.achievements-main {
    min-width: 0; /* Prevents grid overflow */
}

.achievements-rightbar {
    position: sticky;
    top: 20px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Responsive design */
@media (max-width: 1400px) {
    .achievements-layout {
        grid-template-columns: 280px 1fr;
        gap: 25px;
    }
    
    .achievements-rightbar {
        display: none;
    }
}

@media (max-width: 1024px) {
    .achievements-layout {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .achievements-sidebar {
        position: static;
        order: 2;
    }
    
    .achievements-main {
        order: 1;
    }
    
    .achievements-rightbar {
        display: block;
        position: static;
        order: 3;
    }
}

@media (max-width: 768px) {
    .achievements-container {
        padding: 10px;
    }
    
    .achievements-layout {
        gap: 15px;
    }
}

/* Sidebar specific styles */
.achievements-sidebar .achievement-widget {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 0;
}

.achievements-sidebar .achievement-stats {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 0;
}

.achievements-sidebar .achievement-stats h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #495057;
    font-size: 1.1em;
}

.achievements-sidebar .achievement-stats h4 {
    margin-top: 15px;
    margin-bottom: 10px;
    color: #6c757d;
    font-size: 0.95em;
}

.achievements-sidebar .achievement-filters {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 0;
}

.achievements-sidebar .achievement-filters h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #495057;
    font-size: 1.1em;
}

.achievements-sidebar .filter-group {
    margin-bottom: 20px;
}

.achievements-sidebar .filter-group:last-child {
    margin-bottom: 0;
}

.achievements-sidebar .filter-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
    font-size: 0.9em;
}

.achievements-sidebar .filter-options {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.achievements-sidebar .filter-option {
    padding: 8px 12px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.85em;
}

.achievements-sidebar .filter-option:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}

.achievements-sidebar .filter-option.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.achievements-sidebar .achievement-search-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-size: 0.9em;
    background: white;
    transition: border-color 0.2s ease;
}

.achievements-sidebar .achievement-search-input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

/* Main content area improvements */
.achievements-main .achievement-widget {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
}

/* Level section styles */
.level-info {
    display: flex;
    align-items: center;
    gap: 20px;
}

.level-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    min-width: 100px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.level-number {
    font-size: 2.5em;
    font-weight: bold;
    line-height: 1;
    margin-bottom: 5px;
}

.level-text {
    font-size: 0.9em;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.level-details {
    flex: 1;
}

.level-title {
    font-size: 1.5em;
    font-weight: 600;
    color: #495057;
    margin-bottom: 15px;
}

.level-progress {
    background: #e9ecef;
    border-radius: 10px;
    height: 12px;
    margin-bottom: 15px;
    overflow: hidden;
}

.level-progress-bar {
    background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
    height: 100%;
    border-radius: 10px;
    transition: width 0.3s ease;
}

.level-stats {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.level-stats span {
    background: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 0.9em;
    font-weight: 600;
    color: #495057;
    border: 1px solid #dee2e6;
}

/* Responsive level section */
@media (max-width: 768px) {
    .level-info {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .level-badge {
        min-width: auto;
        width: 100%;
        max-width: 200px;
    }
    
    .level-stats {
        justify-content: center;
    }
}

.achievements-main .achievement-stats {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
}

.achievements-main .achievement-stats h2 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #495057;
    font-size: 1.5em;
}

.achievements-main .achievement-stats h3 {
    margin-top: 20px;
    margin-bottom: 15px;
    color: #6c757d;
    font-size: 1.1em;
}

.achievements-main .achievement-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

/* Right sidebar styles */
.achievements-rightbar .leaderboard {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 0;
}

.achievements-rightbar .leaderboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.achievements-rightbar .leaderboard-header h3 {
    margin: 0;
    color: #495057;
    font-size: 1.1em;
}

.achievements-rightbar .leaderboard-header .btn {
    padding: 6px 12px;
    font-size: 0.8em;
}

.achievements-rightbar .leaderboard-item {
    display: flex;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #e9ecef;
}

.achievements-rightbar .leaderboard-item:last-child {
    border-bottom: none;
}

.achievements-rightbar .leaderboard-rank {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.9em;
    margin-right: 12px;
}

.achievements-rightbar .leaderboard-user {
    flex: 1;
    min-width: 0;
}

.achievements-rightbar .leaderboard-username {
    font-weight: 600;
    color: #495057;
    font-size: 0.9em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.achievements-rightbar .leaderboard-level {
    color: #6c757d;
    font-size: 0.8em;
}

.achievements-rightbar .leaderboard-stats {
    text-align: right;
    font-size: 0.8em;
}

.achievements-rightbar .leaderboard-xp {
    color: #495057;
    font-weight: 600;
}

.achievements-rightbar .leaderboard-achievements {
    color: #6c757d;
}

.achievement-completed {
    background: #d5f4e6;
    color: #27ae60;
    padding: 10px;
    border-radius: 6px;
    text-align: center;
    font-size: 12px;
    font-weight: bold;
    margin-top: 10px;
}

.achievement-locked {
    background: #fadbd8;
    color: #e74c3c;
    padding: 10px;
    border-radius: 6px;
    text-align: center;
    font-size: 12px;
    font-weight: bold;
    margin-top: 10px;
}

.btn {
    padding: 8px 16px;
    background: #3498db;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: bold;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn:hover {
    background: #2980b9;
    color: white;
    text-decoration: none;
}

.btn-secondary {
    background: #95a5a6;
}

.btn-secondary:hover {
    background: #7f8c8d;
}
</style>
