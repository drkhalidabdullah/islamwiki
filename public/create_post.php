<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

$page_title = 'Create Post';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    $post_type = $_POST['post_type'] ?? 'text';
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    
    if (empty($content)) {
        show_message('Post content cannot be empty.', 'error');
    } else {
        $result = create_user_post($_SESSION['user_id'], $content, $post_type, null, null);
        
        if ($result) {
            // Update post privacy if needed
            if (!$is_public) {
                $stmt = $pdo->prepare("UPDATE user_posts SET is_public = 0 WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
                $stmt->execute([$_SESSION['user_id']]);
            }
            
            log_activity('create_post', 'Created a new post', $_SESSION['user_id']);
            show_message('Post created successfully!', 'success');
            header('Location: /user/' . get_user($_SESSION['user_id'])['username']);
            exit();
        } else {
            show_message('Failed to create post. Please try again.', 'error');
        }
    }
}

$current_user = get_user($_SESSION['user_id']);
include 'includes/header.php';
?>

<div class="create-post-container">
    <div class="card">
        <h1><i class="fas fa-edit"></i> Create Post</h1>
        <p>Share your thoughts with the community using Markdown</p>
    </div>

    <div class="card">
        <form method="POST" action="" id="postForm">
            <div class="form-group">
                <label for="content">What's on your mind?</label>
                
                <!-- Markdown Editor Toolbar -->
                <div class="markdown-toolbar">
                    <div class="toolbar-group">
                        <button type="button" class="toolbar-btn" data-action="bold" title="Bold">
                            <i class="fas fa-bold"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="italic" title="Italic">
                            <i class="fas fa-italic"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="strikethrough" title="Strikethrough">
                            <i class="fas fa-strikethrough"></i>
                        </button>
                    </div>
                    
                    <div class="toolbar-group">
                        <button type="button" class="toolbar-btn" data-action="heading" title="Heading">
                            <i class="fas fa-heading"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="quote" title="Quote">
                            <i class="fas fa-quote-left"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="code" title="Code">
                            <i class="fas fa-code"></i>
                        </button>
                    </div>
                    
                    <div class="toolbar-group">
                        <button type="button" class="toolbar-btn" data-action="link" title="Link">
                            <i class="fas fa-link"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="image" title="Image">
                            <i class="fas fa-image"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="list" title="List">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                    
                    <div class="toolbar-group">
                        <button type="button" class="toolbar-btn" data-action="preview" title="Preview">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="help" title="Markdown Help">
                            <i class="fas fa-question-circle"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Editor and Preview Container -->
                <div class="editor-container">
                    <textarea 
                        id="content" 
                        name="content" 
                        rows="12" 
                        placeholder="Share your thoughts, insights, or questions...&#10;&#10;You can use Markdown formatting:&#10;**bold text**&#10;*italic text*&#10;# Heading&#10;> Quote&#10;`code`&#10;[link](url)&#10;- list item"
                        required
                    ><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                    
                    <div id="preview" class="preview-container" style="display: none;">
                        <div class="preview-content"></div>
                    </div>
                </div>
                
                <!-- Character Count -->
                <div class="char-count">
                    <span id="charCount">0</span> characters
                </div>
            </div>
            
            <div class="form-group">
                <label for="post_type">Post Type</label>
                <select id="post_type" name="post_type">
                    <option value="text" <?php echo ($_POST['post_type'] ?? 'text') === 'text' ? 'selected' : ''; ?>>Text Post</option>
                    <option value="link" <?php echo ($_POST['post_type'] ?? '') === 'link' ? 'selected' : ''; ?>>Link Share</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_public" value="1" <?php echo !isset($_POST['is_public']) || $_POST['is_public'] ? 'checked' : ''; ?>>
                    <span class="checkmark"></span>
                    Make this post public (visible to everyone)
                </label>
                <small class="form-help">Uncheck to make this post visible only to your followers</small>
            </div>
            
            <div class="form-actions">
                <a href="/user/<?php echo $current_user['username']; ?>" class="btn btn-secondary">Cancel</a>
                <button type="button" id="previewBtn" class="btn btn-outline">Preview</button>
                <button type="submit" class="btn btn-primary">Post</button>
            </div>
        </form>
    </div>
    
    <!-- Markdown Help Modal -->
    <div id="helpModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Markdown Help</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="help-section">
                    <h4>Text Formatting</h4>
                    <ul>
                        <li><code>**bold text**</code> → <strong>bold text</strong></li>
                        <li><code>*italic text*</code> → <em>italic text</em></li>
                        <li><code>~~strikethrough~~</code> → <del>strikethrough</del></li>
                    </ul>
                </div>
                
                <div class="help-section">
                    <h4>Headings</h4>
                    <ul>
                        <li><code># Heading 1</code></li>
                        <li><code>## Heading 2</code></li>
                        <li><code>### Heading 3</code></li>
                    </ul>
                </div>
                
                <div class="help-section">
                    <h4>Lists</h4>
                    <ul>
                        <li><code>- Item 1</code></li>
                        <li><code>- Item 2</code></li>
                        <li><code>1. Numbered item</code></li>
                    </ul>
                </div>
                
                <div class="help-section">
                    <h4>Links & Images</h4>
                    <ul>
                        <li><code>[Link text](URL)</code></li>
                        <li><code>![Alt text](image URL)</code></li>
                    </ul>
                </div>
                
                <div class="help-section">
                    <h4>Code</h4>
                    <ul>
                        <li><code>`inline code`</code></li>
                        <li><code>```block code```</code></li>
                    </ul>
                </div>
                
                <div class="help-section">
                    <h4>Quotes</h4>
                    <ul>
                        <li><code>> This is a quote</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.create-post-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
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

/* Markdown Toolbar */
.markdown-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 12px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-bottom: none;
    border-radius: 6px 6px 0 0;
}

.toolbar-group {
    display: flex;
    gap: 4px;
    padding-right: 12px;
    border-right: 1px solid #dee2e6;
}

.toolbar-group:last-child {
    border-right: none;
}

.toolbar-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #495057;
}

.toolbar-btn:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}

.toolbar-btn.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

/* Editor Container */
.editor-container {
    position: relative;
}

#content {
    width: 100%;
    padding: 16px;
    border: 1px solid #e9ecef;
    border-top: none;
    border-radius: 0 0 6px 6px;
    font-size: 1rem;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    line-height: 1.5;
    resize: vertical;
    min-height: 200px;
    background: white;
}

#content:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

/* Preview Container */
.preview-container {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: white;
    border: 1px solid #e9ecef;
    border-top: none;
    border-radius: 0 0 6px 6px;
    padding: 16px;
    overflow-y: auto;
    z-index: 10;
}

.preview-content {
    line-height: 1.6;
}

.preview-content h1,
.preview-content h2,
.preview-content h3 {
    margin-top: 0;
    margin-bottom: 16px;
    color: #2c3e50;
}

.preview-content h1 { font-size: 1.8rem; }
.preview-content h2 { font-size: 1.5rem; }
.preview-content h3 { font-size: 1.3rem; }

.preview-content p {
    margin-bottom: 16px;
}

.preview-content blockquote {
    border-left: 4px solid #007bff;
    padding-left: 16px;
    margin: 16px 0;
    color: #6c757d;
    font-style: italic;
}

.preview-content code {
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.9em;
}

.preview-content pre {
    background: #f8f9fa;
    padding: 16px;
    border-radius: 6px;
    overflow-x: auto;
    margin: 16px 0;
}

.preview-content pre code {
    background: none;
    padding: 0;
}

.preview-content ul,
.preview-content ol {
    margin: 16px 0;
    padding-left: 24px;
}

.preview-content li {
    margin-bottom: 8px;
}

.preview-content a {
    color: #007bff;
    text-decoration: none;
}

.preview-content a:hover {
    text-decoration: underline;
}

/* Character Count */
.char-count {
    text-align: right;
    font-size: 0.9rem;
    color: #6c757d;
    margin-top: 8px;
}

/* Form Elements */
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
    font-family: inherit;
}

.form-group select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-weight: normal;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    margin: 0;
}

.form-help {
    display: block;
    margin-top: 0.25rem;
    color: #666;
    font-size: 0.9rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.modal-header h3 {
    margin: 0;
    color: #2c3e50;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.modal-close:hover {
    color: #000;
}

.modal-body {
    padding: 20px;
}

.help-section {
    margin-bottom: 24px;
}

.help-section h4 {
    margin-bottom: 12px;
    color: #2c3e50;
}

.help-section ul {
    list-style: none;
    padding: 0;
}

.help-section li {
    margin-bottom: 8px;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 4px;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
}

.help-section code {
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.9em;
}

/* Responsive Design */
@media (max-width: 768px) {
    .create-post-container {
        padding: 10px;
    }
    
    .markdown-toolbar {
        flex-direction: column;
        gap: 8px;
    }
    
    .toolbar-group {
        border-right: none;
        border-bottom: 1px solid #dee2e6;
        padding-right: 0;
        padding-bottom: 8px;
    }
    
    .toolbar-group:last-child {
        border-bottom: none;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .modal-content {
        width: 95%;
        margin: 20px;
    }
}
</style>

<script>
// Simple Markdown Parser
function parseMarkdown(text) {
    return text
        // Headers
        .replace(/^### (.*$)/gim, '<h3>$1</h3>')
        .replace(/^## (.*$)/gim, '<h2>$1</h2>')
        .replace(/^# (.*$)/gim, '<h1>$1</h1>')
        // Bold
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        // Italic
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        // Strikethrough
        .replace(/~~(.*?)~~/g, '<del>$1</del>')
        // Code blocks
        .replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>')
        // Inline code
        .replace(/`(.*?)`/g, '<code>$1</code>')
        // Links
        .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>')
        // Images
        .replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1">')
        // Blockquotes
        .replace(/^> (.*$)/gim, '<blockquote>$1</blockquote>')
        // Lists
        .replace(/^\* (.*$)/gim, '<li>$1</li>')
        .replace(/^- (.*$)/gim, '<li>$1</li>')
        .replace(/^(\d+)\. (.*$)/gim, '<li>$2</li>')
        // Line breaks
        .replace(/\n/g, '<br>');
}

// Toolbar functionality
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('content');
    const preview = document.getElementById('preview');
    const previewContent = document.querySelector('.preview-content');
    const charCount = document.getElementById('charCount');
    const previewBtn = document.getElementById('previewBtn');
    const helpModal = document.getElementById('helpModal');
    const modalClose = document.querySelector('.modal-close');
    
    // Character count
    function updateCharCount() {
        charCount.textContent = textarea.value.length;
    }
    
    textarea.addEventListener('input', updateCharCount);
    updateCharCount();
    
    // Toolbar buttons
    document.querySelectorAll('.toolbar-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.dataset.action;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            let newText = '';
            
            switch(action) {
                case 'bold':
                    newText = `**${selectedText}**`;
                    break;
                case 'italic':
                    newText = `*${selectedText}*`;
                    break;
                case 'strikethrough':
                    newText = `~~${selectedText}~~`;
                    break;
                case 'heading':
                    newText = `## ${selectedText}`;
                    break;
                case 'quote':
                    newText = `> ${selectedText}`;
                    break;
                case 'code':
                    newText = `\`${selectedText}\``;
                    break;
                case 'link':
                    const url = prompt('Enter URL:');
                    if (url) {
                        newText = `[${selectedText || 'link text'}](${url})`;
                    }
                    break;
                case 'image':
                    const imgUrl = prompt('Enter image URL:');
                    if (imgUrl) {
                        const altText = prompt('Enter alt text (optional):');
                        newText = `![${altText || ''}](${imgUrl})`;
                    }
                    break;
                case 'list':
                    newText = `- ${selectedText}`;
                    break;
                case 'preview':
                    togglePreview();
                    return;
                case 'help':
                    helpModal.style.display = 'flex';
                    return;
            }
            
            if (newText) {
                textarea.value = textarea.value.substring(0, start) + newText + textarea.value.substring(end);
                textarea.focus();
                textarea.setSelectionRange(start + newText.length, start + newText.length);
                updateCharCount();
            }
        });
    });
    
    // Preview functionality
    function togglePreview() {
        const isPreview = preview.style.display !== 'none';
        if (isPreview) {
            preview.style.display = 'none';
            textarea.style.display = 'block';
            previewBtn.innerHTML = '<i class="fas fa-eye"></i> Preview';
        } else {
            previewContent.innerHTML = parseMarkdown(textarea.value);
            preview.style.display = 'block';
            textarea.style.display = 'none';
            previewBtn.innerHTML = '<i class="fas fa-edit"></i> Edit';
        }
    }
    
    previewBtn.addEventListener('click', togglePreview);
    
    // Modal functionality
    modalClose.addEventListener('click', function() {
        helpModal.style.display = 'none';
    });
    
    helpModal.addEventListener('click', function(e) {
        if (e.target === helpModal) {
            helpModal.style.display = 'none';
        }
    });
    
    // Auto-resize textarea
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
});
</script>

<?php include 'includes/footer.php'; ?>
