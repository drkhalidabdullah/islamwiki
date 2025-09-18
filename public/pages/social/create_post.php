<?php
require_once '../../config/config.php';
require_once '../../includes/functions.php';

$page_title = 'Create Post';
check_maintenance_mode();
require_login();

// Check if social features are enabled
$enable_social = get_system_setting('enable_social', true);
if (!$enable_social) {
    show_message('Social features are currently disabled.', 'error');
    redirect('/dashboard');
}

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
<script src="/skins/bismillah/assets/js/social_create_post.js"></script>
<script src="/skins/bismillah/assets/js/mentions.js"></script>
<?php

?>
<link rel="stylesheet" href="/skins/bismillah/assets/css/social_create_post.css">
<link rel="stylesheet" href="/skins/bismillah/assets/css/mentions.css">
<?php
?>

<div class="create-post-container">
    <div class="card">
        <h1><i class="iw iw-edit"></i> Create Post</h1>
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
                            <i class="iw iw-bold"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="italic" title="Italic">
                            <i class="iw iw-italic"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="strikethrough" title="Strikethrough">
                            <i class="iw iw-strikethrough"></i>
                        </button>
                    </div>
                    
                    <div class="toolbar-group">
                        <button type="button" class="toolbar-btn" data-action="heading" title="Heading">
                            <i class="iw iw-heading"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="quote" title="Quote">
                            <i class="iw iw-quote-left"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="code" title="Code">
                            <i class="iw iw-code"></i>
                        </button>
                    </div>
                    
                    <div class="toolbar-group">
                        <button type="button" class="toolbar-btn" data-action="link" title="Link">
                            <i class="iw iw-link"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="image" title="Image">
                            <i class="iw iw-image"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="list" title="List">
                            <i class="iw iw-list"></i>
                        </button>
                    </div>
                    
                    <div class="toolbar-group">
                        <button type="button" class="toolbar-btn" data-action="toggle-preview" title="Toggle Preview">
                            <i class="iw iw-eye"></i>
                        </button>
                        <button type="button" class="toolbar-btn" data-action="help" title="Markdown Help">
                            <i class="iw iw-question-circle"></i>
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



<?php include "../../includes/footer.php";; ?>
