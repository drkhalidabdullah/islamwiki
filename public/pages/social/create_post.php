<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Create Post';
check_maintenance_mode();
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
include "../../includes/header.php";;
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
                        <button type="button" class="toolbar-btn" data-action="toggle-preview" title="Toggle Preview">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="help" title="Markdown Help">
                            <i class="fas fa-question-circle"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Editor and Preview Container -->
                <div class="post-editor-container">
                    <div class="post-editor-main">
                        <textarea 
                            id="content" 
                            name="content" 
                            rows="12" 
                            placeholder="Share your thoughts, insights, or questions...&#10;&#10;You can use Markdown formatting:&#10;**bold text**&#10;*italic text*&#10;# Heading&#10;> Quote&#10;`code`&#10;[link](url)&#10;- list item"
                            required
                        ><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                    </div>
                    <div id="preview-container" class="preview-container">
                        <div class="preview-header">
                            <h4>Preview</h4>
                        </div>
                        <div id="preview-content" class="preview-content"></div>
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
    max-width: 1400px;
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

/* Post Editor Container */
.post-editor-container {
    display: flex;
    gap: 1rem;
    margin: 1rem 0;
}

.post-editor-main {
    flex: 1;
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
    min-height: 400px;
    background: white;
}

#content:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

/* Preview Container */
.preview-container {
    flex: 1;
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    min-height: 400px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
}

.preview-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 12px 16px;
    border-radius: 6px 6px 0 0;
}

.preview-header h4 {
    margin: 0;
    color: #495057;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.preview-content {
    line-height: 1.6;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    padding: 16px;
    flex: 1;
}

.preview-content h1,
.preview-content h2,
.preview-content h3,
.preview-content h4,
.preview-content h5,
.preview-content h6 {
    margin-top: 0;
    margin-bottom: 16px;
    color: #2c3e50;
    font-weight: 600;
}

.preview-content h1 { font-size: 1.8rem; border-bottom: 2px solid #e9ecef; padding-bottom: 8px; }
.preview-content h2 { font-size: 1.5rem; border-bottom: 1px solid #e9ecef; padding-bottom: 4px; }
.preview-content h3 { font-size: 1.3rem; }
.preview-content h4 { font-size: 1.1rem; }
.preview-content h5 { font-size: 1rem; }
.preview-content h6 { font-size: 0.9rem; color: #6c757d; }

.preview-content p {
    margin-bottom: 16px;
    color: #333;
}

.preview-content blockquote {
    border-left: 4px solid #007bff;
    padding: 12px 16px;
    margin: 16px 0;
    background: #f8f9fa;
    color: #6c757d;
    font-style: italic;
    border-radius: 0 4px 4px 0;
}

.preview-content code {
    background: #f1f3f4;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.9em;
    color: #d63384;
}

.preview-content pre {
    background: #f8f9fa;
    padding: 16px;
    border-radius: 6px;
    overflow-x: auto;
    margin: 16px 0;
    border: 1px solid #e9ecef;
}

.preview-content pre code {
    background: none;
    padding: 0;
    color: #333;
}

.preview-content ul,
.preview-content ol {
    margin: 16px 0;
    padding-left: 24px;
}

.preview-content ul {
    list-style-type: disc;
}

.preview-content ol {
    list-style-type: decimal;
}

.preview-content li {
    margin-bottom: 8px;
    color: #333;
}

.preview-content a {
    color: #007bff;
    text-decoration: none;
    font-weight: 500;
}

.preview-content a:hover {
    text-decoration: underline;
    color: #0056b3;
}

.preview-content img {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
    margin: 8px 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.preview-content hr {
    border: none;
    height: 1px;
    background: #e9ecef;
    margin: 24px 0;
}

/* Empty state styling */
.preview-content p[style*="color: #999"] {
    text-align: center;
    padding: 40px 20px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 2px dashed #dee2e6;
}

.preview-content p[style*="color: #dc3545"] {
    text-align: center;
    padding: 20px;
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
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
        max-width: 100%;
    }
    
    .post-editor-container {
        flex-direction: column;
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
    
    #content {
        min-height: 300px;
    }
    
    .preview-container {
        min-height: 300px;
    }
}
</style>

<script>
// Enhanced Markdown Parser
function parseMarkdown(text) {
    if (!text) return '';
    
    let html = text;
    
    // Code blocks (must be processed first to avoid conflicts)
    html = html.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');
    
    // Headers
    html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
    html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
    html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');
    
    // Blockquotes
    html = html.replace(/^> (.*$)/gim, '<blockquote>$1</blockquote>');
    
    // Lists - process unordered lists
    html = html.replace(/^(\*|\-)\s+(.*$)/gim, '<li>$2</li>');
    
    // Lists - process ordered lists
    html = html.replace(/^(\d+)\.\s+(.*$)/gim, '<li>$2</li>');
    
    // Wrap consecutive list items in ul/ol tags
    html = html.replace(/(<li>.*<\/li>)(\s*<li>.*<\/li>)*/g, function(match) {
        // Check if it's an ordered list (contains numbers)
        const isOrdered = /^\d+\./.test(text.split('\n').find(line => line.trim().match(/^\d+\./)));
        const tag = isOrdered ? 'ol' : 'ul';
        return `<${tag}>${match}</${tag}>`;
    });
    
    // Inline formatting (must be processed after block elements)
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
    html = html.replace(/~~(.*?)~~/g, '<del>$1</del>');
    html = html.replace(/`(.*?)`/g, '<code>$1</code>');
    
    // Links and images
    html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');
    html = html.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1" style="max-width: 100%; height: auto;">');
    
    // Convert line breaks to paragraphs for better structure
    html = html.split('\n\n').map(paragraph => {
        paragraph = paragraph.trim();
        if (!paragraph) return '';
        
        // Skip if it's already a block element
        if (paragraph.match(/^<(h[1-6]|pre|blockquote|ul|ol|li)/)) {
            return paragraph;
        }
        
        // Convert single line breaks to <br> within paragraphs
        paragraph = paragraph.replace(/\n/g, '<br>');
        return `<p>${paragraph}</p>`;
    }).join('\n');
    
    // Clean up empty paragraphs
    html = html.replace(/<p><br><\/p>/g, '');
    html = html.replace(/<p><\/p>/g, '');
    
    return html;
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
    
    // Debounced preview update
    let previewTimeout;
    
    // Update preview when content changes (if preview is visible)
    function onContentChange() {
        updateCharCount();
        const previewContainer = document.getElementById('preview-container');
        if (previewContainer && previewContainer.style.display !== 'none') {
            // Clear previous timeout
            if (previewTimeout) {
                clearTimeout(previewTimeout);
            }
            // Update preview with a small delay to avoid too many updates
            previewTimeout = setTimeout(updatePreview, 100);
        }
    }
    
    textarea.addEventListener('input', onContentChange);
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
                case 'toggle-preview':
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
        const previewContainer = document.getElementById('preview-container');
        const isPreviewVisible = previewContainer.style.display !== 'none' && previewContainer.style.display !== '';
        
        if (isPreviewVisible) {
            previewContainer.style.display = 'none';
            previewBtn.innerHTML = '<i class="fas fa-eye"></i> Show Preview';
        } else {
            previewContainer.style.display = 'flex';
            updatePreview();
            previewBtn.innerHTML = '<i class="fas fa-eye-slash"></i> Hide Preview';
        }
    }
    
    function updatePreview() {
        const content = textarea.value.trim();
        console.log('Updating preview with content:', content.substring(0, 50) + '...');
        
        if (!content) {
            previewContent.innerHTML = '<p style="color: #999; font-style: italic;">No content to preview</p>';
            return;
        }
        
        try {
            const html = parseMarkdown(content);
            previewContent.innerHTML = html;
            console.log('Preview updated successfully');
        } catch (error) {
            console.error('Preview error:', error);
            previewContent.innerHTML = '<p style="color: #dc3545;">Error generating preview</p>';
        }
    }
    
    // Initialize preview on page load
    const previewContainer = document.getElementById('preview-container');
    previewContainer.style.display = 'flex'; // Show preview by default
    previewBtn.innerHTML = '<i class="fas fa-eye-slash"></i> Hide Preview'; // Set initial button text
    updatePreview();
    
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

<?php include "../../includes/footer.php";; ?>
