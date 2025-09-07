<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$page_title = 'Search';
$query = sanitize_input($_GET['q'] ?? '');

include "../../includes/header.php";;
?>

<div class="search-page">
    <div class="search-header">
        <h1>Search Articles</h1>
        
        <form method="GET" class="search-form">
            <div class="search-input-group">
                <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" 
                       placeholder="Search articles..." required>
                <button type="submit" class="btn">Search</button>
            </div>
        </form>
    </div>
    
    <?php if (!empty($query)): ?>
        <div class="search-results">
            <h2>Search Results for "<?php echo htmlspecialchars($query); ?>"</h2>
            <p>Search functionality is working!</p>
        </div>
    <?php else: ?>
        <div class="search-intro">
            <div class="card">
                <h2>Search Tips</h2>
                <ul>
                    <li>Use specific keywords for better results</li>
                    <li>Try different spellings or synonyms</li>
                    <li>Use the category filter to narrow results</li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include "../../includes/footer.php";; ?>
