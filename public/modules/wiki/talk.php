<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/wiki_functions.php';
require_once __DIR__ . '/../../includes/markdown/MarkdownParser.php';

// Check maintenance mode
check_maintenance_mode();

// Check if wiki is enabled
$enable_wiki = get_system_setting('enable_wiki', true);
if (!$enable_wiki) {
    show_message('Wiki system is currently disabled.', 'error');
    redirect('/dashboard');
}

$page_title = 'Talk Page';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    redirect('index.php');
}

// Get article
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    WHERE (wa.slug = ? OR wa.slug = ?) AND wa.status = 'published'
");
$stmt->execute([$slug, ucfirst($slug)]);
$article = $stmt->fetch();

if (!$article) {
    redirect("not_found.php?slug=" . urlencode($slug) . "&title=" . urlencode(ucfirst(str_replace('-', ' ', $slug))));
}

$page_title = 'Talk:' . $article['title'];

// Get or create talk page
try {
    $talk_page = get_talk_page($article['id']);
} catch (Exception $e) {
    error_log("Error getting talk page: " . $e->getMessage());
    $talk_page = false;
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in()) {
    $content = $_POST['content'] ?? '';
    
    if (empty($content)) {
        $errors[] = 'Content is required.';
    }
    
    if (empty($errors)) {
        try {
            $talk_id = create_or_update_talk_page($article['id'], $content, $_SESSION['user_id']);
            if ($talk_id) {
                $success = true;
                $talk_page = get_talk_page($article['id']); // Refresh talk page
            } else {
                $errors[] = 'Error saving talk page.';
            }
        } catch (Exception $e) {
            error_log("Error creating/updating talk page: " . $e->getMessage());
            $errors[] = 'Error saving talk page: ' . $e->getMessage();
        }
    }
}

include '../../includes/header.php';

?>
<script src="/skins/bismillah/assets/js/wiki_talk.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/wiki_module_talk.css">
<?php
?>

<div class="talk-page-container">
    <div class="talk-header">
        <div class="talk-navigation">
            <a href="/wiki/<?php echo $article['slug']; ?>" class="btn btn-secondary">
                <i class="iw iw-arrow-left"></i> Back to Article
            </a>
            <a href="/wiki/<?php echo $article['slug']; ?>/history" class="btn btn-secondary">
                <i class="iw iw-history"></i> Article History
            </a>
        </div>
        
        <h1>Talk: <?php echo htmlspecialchars($article['title']); ?></h1>
        <p class="talk-description">
            Discussion page for <a href="/wiki/<?php echo $article['slug']; ?>"><?php echo htmlspecialchars($article['title']); ?></a>
        </p>
    </div>

    <?php if (!empty($errors)): ?>
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
            Talk page updated successfully!
        </div>
    <?php endif; ?>

    <div class="talk-content">
        <?php if ($talk_page): ?>
            <div class="talk-page-content">
                <div class="talk-meta">
                    <span class="talk-author">
                        Last edited by <a href="/user/<?php echo $talk_page['username']; ?>">
                            <?php echo htmlspecialchars($talk_page['display_name'] ?: $talk_page['username']); ?>
                        </a>
                    </span>
                    <span class="talk-date">
                        on <?php echo format_date($talk_page['updated_at']); ?>
                    </span>
                </div>
                
                <div class="talk-text">
                    <?php 
                    $parser = new MarkdownParser('');
                    echo $parser->parse($talk_page['content']); 
                    ?>
                </div>
            </div>
        <?php else: ?>
            <div class="no-talk-content">
                <div class="empty-state">
                    <i class="iw iw-comments"></i>
                    <h3>No discussion yet</h3>
                    <p>This article doesn't have a talk page yet. Be the first to start a discussion!</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (is_logged_in()): ?>
            <div class="talk-editor">
                <h3><?php echo $talk_page ? 'Edit Discussion' : 'Start Discussion'; ?></h3>
                
                <form method="POST" class="talk-form">
                    <div class="form-group">
                        <label for="content">Discussion Content</label>
                        <textarea id="content" name="content" rows="10" required 
                                  placeholder="Share your thoughts, suggestions, or questions about this article..."><?php echo htmlspecialchars($talk_page['content'] ?? ''); ?></textarea>
                        <div class="form-help">
                            You can use <a href="#" onclick="showMarkdownHelp()">Markdown</a> formatting and wiki-style links.
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="iw iw-save"></i> <?php echo $talk_page ? 'Update Discussion' : 'Start Discussion'; ?>
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="previewContent()">
                            <i class="iw iw-eye"></i> Preview
                        </button>
                    </div>
                </form>
                
                <div id="preview-container" style="display: none;">
                    <h4>Preview</h4>
                    <div id="preview-content"></div>
                </div>
            </div>
        <?php else: ?>
            <div class="login-prompt">
                <div class="prompt-content">
                    <i class="iw iw-sign-in-alt"></i>
                    <h3>Sign in to participate</h3>
                    <p>You need to be logged in to contribute to discussions.</p>
                    <a href="/login?return=<?php echo urlencode('/wiki/' . htmlspecialchars($article['slug']) . '/talk'); ?>" class="btn btn-primary">Sign In</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Discussion Guidelines -->
    <div class="discussion-guidelines">
        <h3>Discussion Guidelines</h3>
        <ul>
            <li><strong>Be respectful:</strong> Treat other contributors with respect and courtesy.</li>
            <li><strong>Stay on topic:</strong> Keep discussions focused on improving the article.</li>
            <li><strong>Provide sources:</strong> When suggesting changes, provide reliable sources.</li>
            <li><strong>Be constructive:</strong> Offer specific suggestions for improvement.</li>
            <li><strong>Use signatures:</strong> Sign your comments with <code>~~~~</code> (four tildes).</li>
        </ul>
    </div>
</div>



<?php include '../../includes/footer.php'; ?>
