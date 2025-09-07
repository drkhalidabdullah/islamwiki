<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Comprehensive Search';
$query = sanitize_input($_GET['q'] ?? '');
$content_type = sanitize_input($_GET['type'] ?? 'all');
$category = (int)($_GET['category'] ?? 0);
$sort = sanitize_input($_GET['sort'] ?? 'relevance');

$user_id = $_SESSION['user_id'] ?? null;
$is_logged_in = is_logged_in();

$results = ['articles' => [], 'users' => [], 'messages' => []];
$total_results = 0;

if (!empty($query)) {
    $search_terms = array_filter(explode(' ', trim($query)), function($term) {
        return strlen($term) > 1;
    });
    
    // Search Articles
    if ($content_type === 'all' || $content_type === 'articles') {
        $article_where = ["wa.status = 'published'"];
        $article_params = [];
        
        if ($category > 0) {
            $article_where[] = "wa.category_id = ?";
            $article_params[] = $category;
        }
        
        $article_search_conditions = [];
        foreach ($search_terms as $term) {
            $article_search_conditions[] = "(wa.title LIKE ? OR wa.content LIKE ? OR wa.excerpt LIKE ?)";
            $article_params[] = '%' . $term . '%';
            $article_params[] = '%' . $term . '%';
            $article_params[] = '%' . $term . '%';
        }
        
        if (!empty($article_search_conditions)) {
            $article_where[] = '(' . implode(' AND ', $article_search_conditions) . ')';
        }
        
        $where_clause = implode(' AND ', $article_where);
        
        $order_by = 'wa.published_at DESC';
        switch ($sort) {
            case 'title': $order_by = 'wa.title ASC'; break;
            case 'date': $order_by = 'wa.published_at DESC'; break;
            case 'views': $order_by = 'wa.view_count DESC'; break;
            default: $order_by = 'wa.view_count DESC, wa.published_at DESC'; break;
        }
        
        try {
            $sql = "SELECT wa.*, u.username, u.display_name, cc.name as category_name
                    FROM wiki_articles wa 
                    JOIN users u ON wa.author_id = u.id 
                    LEFT JOIN content_categories cc ON wa.category_id = cc.id 
                    WHERE $where_clause
                    ORDER BY $order_by
                    LIMIT 20";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($article_params);
            $results['articles'] = $stmt->fetchAll();
            
            $count_sql = "SELECT COUNT(*) FROM wiki_articles wa WHERE $where_clause";
            $stmt = $pdo->prepare($count_sql);
            $stmt->execute($article_params);
            $total_results += $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Search error: " . $e->getMessage());
        }
    }
    
    // Search Users
    if ($content_type === 'all' || $content_type === 'users') {
        $user_search_conditions = [];
        $user_params = [];
        
        foreach ($search_terms as $term) {
            $user_search_conditions[] = "(u.username LIKE ? OR u.display_name LIKE ? OR up.bio LIKE ?)";
            $user_params[] = '%' . $term . '%';
            $user_params[] = '%' . $term . '%';
            $user_params[] = '%' . $term . '%';
        }
        
        if (!empty($user_search_conditions)) {
            $user_where = '(' . implode(' AND ', $user_search_conditions) . ')';
            
            try {
                $sql = "SELECT u.*, up.bio, up.avatar
                        FROM users u 
                        LEFT JOIN user_profiles up ON u.id = up.user_id 
                        WHERE $user_where AND u.is_active = 1
                        ORDER BY u.display_name ASC
                        LIMIT 20";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($user_params);
                $results['users'] = $stmt->fetchAll();
                
                $count_sql = "SELECT COUNT(*) FROM users u LEFT JOIN user_profiles up ON u.id = up.user_id WHERE $user_where AND u.is_active = 1";
                $stmt = $pdo->prepare($count_sql);
                $stmt->execute($user_params);
                $total_results += $stmt->fetchColumn();
            } catch (Exception $e) {
                error_log("User search error: " . $e->getMessage());
            }
        }
    }
    
    // Search Messages (only for logged-in users)
    if ($is_logged_in && ($content_type === 'all' || $content_type === 'messages')) {
        $message_search_conditions = [];
        $message_params = [$user_id, $user_id];
        
        foreach ($search_terms as $term) {
            $message_search_conditions[] = "m.message LIKE ?";
            $message_params[] = '%' . $term . '%';
        }
        
        if (!empty($message_search_conditions)) {
            $message_where = '(' . implode(' AND ', $message_search_conditions) . ')';
            
            try {
                $sql = "SELECT m.*, sender.username as sender_username, sender.display_name as sender_name,
                               recipient.username as recipient_username, recipient.display_name as recipient_name
                        FROM messages m 
                        JOIN users sender ON m.sender_id = sender.id
                        JOIN users recipient ON m.recipient_id = recipient.id
                        WHERE (m.sender_id = ? OR m.recipient_id = ?) AND $message_where
                        ORDER BY m.created_at DESC
                        LIMIT 20";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($message_params);
                $results['messages'] = $stmt->fetchAll();
                
                $count_sql = "SELECT COUNT(*) FROM messages m WHERE (m.sender_id = ? OR m.recipient_id = ?) AND $message_where";
                $stmt = $pdo->prepare($count_sql);
                $stmt->execute($message_params);
                $total_results += $stmt->fetchColumn();
            } catch (Exception $e) {
                error_log("Message search error: " . $e->getMessage());
            }
        }
    }
}

// Get categories
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM content_categories WHERE is_active = 1 ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Categories error: " . $e->getMessage());
}

include "../includes/header.php";
?>

<div class="search-page">
    <div class="search-header">
        <h1>Comprehensive Search</h1>
        
        <form method="GET" class="search-form">
            <div class="search-input-group">
                <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" 
                       placeholder="Search everything..." required>
                <button type="submit" class="btn">Search</button>
            </div>
            
            <div class="search-filters">
                <select name="type">
                    <option value="all" <?php echo ($content_type === 'all') ? 'selected' : ''; ?>>All Content</option>
                    <option value="articles" <?php echo ($content_type === 'articles') ? 'selected' : ''; ?>>Articles</option>
                    <option value="users" <?php echo ($content_type === 'users') ? 'selected' : ''; ?>>Users</option>
                    <?php if ($is_logged_in): ?>
                    <option value="messages" <?php echo ($content_type === 'messages') ? 'selected' : ''; ?>>My Messages</option>
                    <?php endif; ?>
                </select>
                
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($category == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="sort">
                    <option value="relevance" <?php echo ($sort === 'relevance') ? 'selected' : ''; ?>>Relevance</option>
                    <option value="title" <?php echo ($sort === 'title') ? 'selected' : ''; ?>>Title</option>
                    <option value="date" <?php echo ($sort === 'date') ? 'selected' : ''; ?>>Date</option>
                    <option value="views" <?php echo ($sort === 'views') ? 'selected' : ''; ?>>Views</option>
                </select>
            </div>
        </form>
    </div>
    
    <?php if (!empty($query)): ?>
        <div class="search-results">
            <h2>
                <?php if ($total_results > 0): ?>
                    <?php echo number_format($total_results); ?> result<?php echo $total_results !== 1 ? 's' : ''; ?> for "<?php echo htmlspecialchars($query); ?>"
                <?php else: ?>
                    No results found for "<?php echo htmlspecialchars($query); ?>"
                <?php endif; ?>
            </h2>
            
            <?php if (!empty($results['articles'])): ?>
            <div class="results-section">
                <h3>Articles (<?php echo count($results['articles']); ?>)</h3>
                <div class="results-list">
                    <?php foreach ($results['articles'] as $article): ?>
                    <div class="card result-item">
                        <div class="result-meta">
                            <?php if ($article['category_name']): ?>
                            <span class="category"><?php echo htmlspecialchars($article['category_name']); ?></span>
                            <?php endif; ?>
                            <span class="date"><?php echo date('M j, Y', strtotime($article['published_at'])); ?></span>
                            <span class="views"><?php echo number_format($article['view_count']); ?> views</span>
                        </div>
                        
                        <h4><a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>"><?php echo htmlspecialchars($article['title']); ?></a></h4>
                        
                        <p class="result-excerpt">
                            <?php 
                            $excerpt = $article['excerpt'] ?: strip_tags($article['content']);
                            echo htmlspecialchars(substr($excerpt, 0, 200)) . (strlen($excerpt) > 200 ? '...' : '');
                            ?>
                        </p>
                        
                        <div class="result-footer">
                            <span class="author">By <?php echo htmlspecialchars($article['display_name'] ?: $article['username']); ?></span>
                            <a href="/wiki/<?php echo htmlspecialchars($article['slug']); ?>" class="read-more">Read more →</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($results['users'])): ?>
            <div class="results-section">
                <h3>Users (<?php echo count($results['users']); ?>)</h3>
                <div class="results-list">
                    <?php foreach ($results['users'] as $user): ?>
                    <div class="card result-item user-result">
                        <div class="user-avatar">
                            <?php if ($user['avatar']): ?>
                            <img src="/uploads/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar">
                            <?php else: ?>
                            <div class="avatar-placeholder"><?php echo strtoupper(substr($user['display_name'] ?: $user['username'], 0, 1)); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="user-info">
                            <h4><a href="/user/<?php echo htmlspecialchars($user['username']); ?>"><?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?></a></h4>
                            <p class="username">@<?php echo htmlspecialchars($user['username']); ?></p>
                            
                            <?php if ($user['bio']): ?>
                            <p class="user-bio"><?php echo htmlspecialchars(substr($user['bio'], 0, 150)) . (strlen($user['bio']) > 150 ? '...' : ''); ?></p>
                            <?php endif; ?>
                            
                            <div class="user-meta">
                                <span>Joined <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($results['messages'])): ?>
            <div class="results-section">
                <h3>My Messages (<?php echo count($results['messages']); ?>)</h3>
                <div class="results-list">
                    <?php foreach ($results['messages'] as $message): ?>
                    <div class="card result-item message-result">
                        <div class="message-header">
                            <span class="message-participants">
                                <?php if ($message['sender_id'] == $user_id): ?>
                                    To: <?php echo htmlspecialchars($message['recipient_name'] ?: $message['recipient_username']); ?>
                                <?php else: ?>
                                    From: <?php echo htmlspecialchars($message['sender_name'] ?: $message['sender_username']); ?>
                                <?php endif; ?>
                            </span>
                            <span class="message-date"><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></span>
                        </div>
                        
                        <p class="message-content">
                            <?php echo htmlspecialchars(substr($message['message'], 0, 200)) . (strlen($message['message']) > 200 ? '...' : ''); ?>
                        </p>
                        
                        <div class="message-footer">
                            <a href="/messages" class="view-conversation">View Conversation →</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="search-intro">
            <div class="card">
                <h2>Search Everything</h2>
                <p>Find articles, users, messages, and more across the platform.</p>
                
                <div class="search-features">
                    <div class="feature">
                        <h3>📚 Articles</h3>
                        <p>Search through wiki articles by title, content, and category</p>
                    </div>
                    
                    <div class="feature">
                        <h3>👥 Users</h3>
                        <p>Find users by username, display name, or bio</p>
                    </div>
                    
                    <?php if ($is_logged_in): ?>
                    <div class="feature">
                        <h3>💬 Messages</h3>
                        <p>Search your private message conversations</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="search-tips">
                    <h3>Search Tips</h3>
                    <ul>
                        <li>Use specific keywords for better results</li>
                        <li>Try different spellings or synonyms</li>
                        <li>Use filters to narrow your search</li>
                        <li>Search terms are highlighted in results</li>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.search-page { max-width: 1200px; margin: 0 auto; padding: 2rem 1rem; margin-top: 2rem; position: relative; z-index: 1; }
.search-header { text-align: center; margin-bottom: 3rem; margin-top: 1rem; padding-top: 1rem; }
.search-header h1 { color: #2c3e50; margin-bottom: 2rem; }
.search-page .search-form { max-width: 800px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-top: 1rem; position: relative; z-index: 10; }
.search-page .search-input-group { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; align-items: center; }
.search-page .search-input-group input { flex: 1; max-width: 500px; padding: 1rem; border: 2px solid #ddd; border-radius: 6px; font-size: 1rem; }
.search-page .search-input-group input:focus { outline: none; border-color: #3498db; }
.search-page .search-input-group .btn { min-width: 140px; padding: 1rem 2rem; font-size: 1rem; font-weight: 500; }
.search-page .search-filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
.search-page .search-filters select { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; background: white; }
.results-section { margin-bottom: 3rem; }
.results-section h3 { color: #2c3e50; margin-bottom: 1.5rem; border-bottom: 2px solid #3498db; padding-bottom: 0.5rem; }
.results-list { display: grid; gap: 1.5rem; }
.result-item { transition: transform 0.2s, box-shadow 0.2s; }
.result-item:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
.result-meta { display: flex; gap: 1rem; margin-bottom: 1rem; font-size: 0.9rem; color: #666; flex-wrap: wrap; }
.result-meta .category { background: #3498db; color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.8rem; }
.result-item h4 { margin-bottom: 1rem; }
.result-item h4 a { color: #2c3e50; text-decoration: none; }
.result-item h4 a:hover { color: #3498db; }
.result-excerpt { margin-bottom: 1rem; line-height: 1.6; color: #555; }
.result-footer { display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; color: #666; }
.read-more { color: #3498db; text-decoration: none; font-weight: 500; }
.read-more:hover { text-decoration: underline; }
.user-result { display: flex; gap: 1rem; align-items: flex-start; }
.user-avatar img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; }
.avatar-placeholder { width: 60px; height: 60px; border-radius: 50%; background: #3498db; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: bold; }
.user-info { flex: 1; }
.user-info h4 { margin-bottom: 0.5rem; }
.username { color: #666; margin-bottom: 0.5rem; }
.user-bio { margin-bottom: 1rem; color: #555; }
.user-meta { font-size: 0.9rem; color: #666; }
.message-result { border-left: 4px solid #3498db; }
.message-header { display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 0.9rem; color: #666; }
.message-participants { font-weight: 500; }
.message-content { margin-bottom: 1rem; color: #555; }
.message-footer { text-align: right; }
.view-conversation { color: #3498db; text-decoration: none; font-size: 0.9rem; }
.view-conversation:hover { text-decoration: underline; }
.search-intro { margin-top: 3rem; }
.search-intro .card { max-width: 800px; margin: 0 auto; text-align: center; }
.search-features { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin: 2rem 0; }
.feature { padding: 1.5rem; background: #f8f9fa; border-radius: 6px; }
.feature h3 { margin-bottom: 1rem; color: #2c3e50; }
.search-tips { text-align: left; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee; }
.search-tips h3 { color: #2c3e50; margin-bottom: 1rem; }
.search-tips ul { margin: 0; padding-left: 1.5rem; }
.search-tips li { margin-bottom: 0.5rem; color: #555; }
@media (max-width: 768px) { .search-page { padding: 1rem 0.5rem; } .search-page .search-form { padding: 1.5rem; } .search-page .search-input-group { flex-direction: column; } .search-page .search-filters { grid-template-columns: 1fr; } .result-meta { flex-direction: column; gap: 0.5rem; } .result-footer { flex-direction: column; gap: 0.5rem; align-items: flex-start; } .user-result { flex-direction: column; text-align: center; } .message-header { flex-direction: column; gap: 0.5rem; } .search-features { grid-template-columns: 1fr; } }
</style>

<?php include "../includes/footer.php"; ?>
