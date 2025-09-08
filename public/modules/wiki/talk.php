<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/wiki_functions.php';
require_once __DIR__ . '/../../includes/markdown/MarkdownParser.php';

$page_title = 'Talk Page';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    redirect('index.php');
}

// Get article
$stmt = $pdo->prepare("
    SELECT wa.*, u.username, u.display_name, cc.name as category_name, cc.slug as category_slug 
    FROM wiki_articles wa 
    JOIN users u ON wa.author_id = u.id 
    LEFT JOIN content_categories cc ON wa.category_id = cc.id 
    WHERE (wa.slug = ? OR wa.slug = ?) AND wa.status = 'published'
");
$stmt->execute([$slug, ucfirst($slug)]);
$article = $stmt->fetch();

if (!$article) {
    redirect("not_found.php?slug=" . urlencode($slug) . "&title=" . urlencode(ucfirst(str_replace('-', ' ', $slug))));
}

$page_title = 'Talk:' . $article['title'];

// Get or create talk page
$talk_page = get_talk_page($article['id']);

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in()) {
    $content = $_POST['content'] ?? '';
    
    if (empty($content)) {
        $errors[] = 'Content is required.';
    }
    
    if (empty($errors)) {
        $talk_id = create_or_update_talk_page($article['id'], $content, $_SESSION['user_id']);
        if ($talk_id) {
            $success = true;
            $talk_page = get_talk_page($article['id']); // Refresh talk page
        } else {
            $errors[] = 'Error saving talk page.';
        }
    }
}

include '../../includes/header.php';
?>

<div class="talk-page-container">
    <div class="talk-header">
        <div class="talk-navigation">
            <a href="/wiki/<?php echo $article['slug']; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Article
            </a>
            <a href="/wiki/history.php?slug=<?php echo urlencode($article['slug']); ?>" class="btn btn-secondary">
                <i class="fas fa-history"></i> Article History
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
                    $parser = new EnhancedMarkdownParser('');
                    echo $parser->parse($talk_page['content']); 
                    ?>
                </div>
            </div>
        <?php else: ?>
            <div class="no-talk-content">
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
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
                            <i class="fas fa-save"></i> <?php echo $talk_page ? 'Update Discussion' : 'Start Discussion'; ?>
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="previewContent()">
                            <i class="fas fa-eye"></i> Preview
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
                    <i class="fas fa-sign-in-alt"></i>
                    <h3>Sign in to participate</h3>
                    <p>You need to be logged in to contribute to discussions.</p>
                    <a href="/pages/auth/login.php" class="btn btn-primary">Sign In</a>
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

<style>
.talk-page-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 2rem;
}

.talk-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.talk-navigation {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.talk-header h1 {
    color: #2c3e50;
    margin: 0 0 0.5rem 0;
}

.talk-description {
    color: #6c757d;
    margin: 0;
}

.talk-description a {
    color: #007bff;
    text-decoration: none;
}

.talk-description a:hover {
    text-decoration: underline;
}

.talk-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

.talk-page-content {
    padding: 2rem;
}

.talk-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.9rem;
    color: #6c757d;
}

.talk-author a {
    color: #007bff;
    text-decoration: none;
}

.talk-author a:hover {
    text-decoration: underline;
}

.talk-text {
    line-height: 1.6;
    color: #2c3e50;
}

.talk-text h1, .talk-text h2, .talk-text h3, .talk-text h4, .talk-text h5, .talk-text h6 {
    color: #2c3e50;
    margin-top: 1.5rem;
    margin-bottom: 0.5rem;
}

.talk-text p {
    margin-bottom: 1rem;
}

.talk-text ul, .talk-text ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.talk-text blockquote {
    border-left: 4px solid #007bff;
    padding-left: 1rem;
    margin: 1rem 0;
    color: #6c757d;
    font-style: italic;
}

.talk-text code {
    background: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}

.talk-text pre {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 4px;
    overflow-x: auto;
    margin: 1rem 0;
}

.no-talk-content {
    padding: 3rem;
    text-align: center;
}

.empty-state {
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #dee2e6;
}

.empty-state h3 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
}

.empty-state p {
    margin: 0;
}

.talk-editor {
    padding: 2rem;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
}

.talk-editor h3 {
    color: #2c3e50;
    margin: 0 0 1.5rem 0;
}

.talk-form {
    background: white;
    padding: 1.5rem;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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

.form-group textarea {
    width: 100%;
    padding: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 1rem;
    line-height: 1.5;
    resize: vertical;
    min-height: 200px;
}

.form-group textarea:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.form-help {
    margin-top: 0.5rem;
    font-size: 0.9rem;
    color: #6c757d;
}

.form-help a {
    color: #007bff;
    text-decoration: none;
}

.form-help a:hover {
    text-decoration: underline;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

#preview-container {
    margin-top: 1rem;
    padding: 1.5rem;
    background: white;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

#preview-container h4 {
    margin: 0 0 1rem 0;
    color: #2c3e50;
}

#preview-content {
    line-height: 1.6;
    color: #2c3e50;
}

.login-prompt {
    padding: 3rem;
    text-align: center;
    background: #f8f9fa;
}

.prompt-content {
    color: #6c757d;
}

.prompt-content i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #dee2e6;
}

.prompt-content h3 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
}

.prompt-content p {
    margin: 0 0 1.5rem 0;
}

.discussion-guidelines {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.discussion-guidelines h3 {
    color: #2c3e50;
    margin: 0 0 1rem 0;
}

.discussion-guidelines ul {
    margin: 0;
    padding-left: 1.5rem;
}

.discussion-guidelines li {
    margin-bottom: 0.5rem;
    line-height: 1.5;
}

.discussion-guidelines code {
    background: #e9ecef;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
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

@media (max-width: 768px) {
    .talk-navigation {
        flex-direction: column;
    }
    
    .talk-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
function previewContent() {
    const content = document.getElementById('content').value;
    const previewContainer = document.getElementById('preview-container');
    const previewContent = document.getElementById('preview-content');
    
    if (content.trim()) {
        // Simple markdown preview (you might want to use a proper markdown parser)
        let html = content
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/`(.*?)`/g, '<code>$1</code>')
            .replace(/^### (.*$)/gim, '<h3>$1</h3>')
            .replace(/^## (.*$)/gim, '<h2>$1</h2>')
            .replace(/^# (.*$)/gim, '<h1>$1</h1>')
            .replace(/\n/g, '<br>');
        
        previewContent.innerHTML = html;
        previewContainer.style.display = 'block';
    } else {
        previewContainer.style.display = 'none';
    }
}

function showMarkdownHelp() {
    alert('Markdown Help:\n\n**Bold text**\n*Italic text*\n`Code`\n# Heading 1\n## Heading 2\n### Heading 3\n\nFor more help, visit: https://www.markdownguide.org/');
}
</script>

<?php include '../../includes/footer.php'; ?>
