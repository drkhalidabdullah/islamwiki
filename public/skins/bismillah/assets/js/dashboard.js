// Tab functionality
// Global variable for storing uploaded images
let uploadedImages = [];

// Global functions for markdown parsing and preview

// Markdown parser (client-side)

document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // Remove active class from all buttons and contents
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.style.display = 'none');
            
            // Add active class to clicked button and show target content
            this.classList.add('active');
            document.getElementById(targetTab + '-tab').style.display = 'block';
        });
    });
    
    // Filter functionality
    const filterBtns = document.querySelectorAll('.filter-btn');
    const followingContent = document.getElementById('following-content');
    const allContent = document.getElementById('all-content');
    
    // Save filter state to localStorage
    function saveFilterState(filter) {
        localStorage.setItem('dashboardFilter', filter);
    }
    
    // Get saved filter state from localStorage
    function getSavedFilterState() {
        return localStorage.getItem('dashboardFilter') || 'all';
    }
    
    // Apply filter logic
    function applyFilter(filter) {
        // Remove active class from all buttons
        filterBtns.forEach(b => b.classList.remove('active'));
        
        // Add active class to current filter button
        const currentBtn = document.querySelector(`[data-filter="${filter}"]`);
        if (currentBtn) {
            currentBtn.classList.add('active');
        }
        
        if (filter === 'following') {
            followingContent.style.display = 'block';
            allContent.style.display = 'none';
        } else {
            followingContent.style.display = 'none';
            allContent.style.display = 'block';
            
            // If it's posts or articles filter, apply the filtering
            if (filter === 'posts' || filter === 'articles') {
                const feedItems = allContent.querySelectorAll('.feed-item');
                feedItems.forEach(item => {
                    const itemType = item.dataset.type;
                    
                    if (filter === 'posts' && itemType === 'post') {
                        item.style.display = 'block';
                    } else if (filter === 'articles' && (itemType === 'article' || itemType === 'featured_article')) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            } else if (filter === 'all') {
                // Show all items
                const feedItems = allContent.querySelectorAll('.feed-item');
                feedItems.forEach(item => {
                    item.style.display = 'block';
                });
            }
        }
        
        // Check liked posts after applying filter
        setTimeout(checkLikedPosts, 100);
    }
    
    // Initialize the correct content section on page load
    function initializeContent() {
        const savedFilter = getSavedFilterState();
        applyFilter(savedFilter);
    }
    
    // Initialize on page load
    initializeContent();
    
    // Check and display liked posts
    checkLikedPosts();
    
    // Initialize post creation functionality
    initializePostCreation();
    
    // Initialize image click handlers
    initializeImageHandlers();
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;
            
            // Save filter state and apply it
            saveFilterState(filter);
            applyFilter(filter);
        });
    });
    
    // Post action buttons (like, comment, share)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.like-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.like-btn');
            const postId = btn.dataset.postId;
            const icon = btn.querySelector('i');
            
            // Toggle like state based on current state
            if (btn.classList.contains('liked')) {
                btn.style.color = '';
                btn.classList.remove('liked');
                unlikePost(postId);
            } else {
                btn.style.color = '#ef4444';
                btn.classList.add('liked');
                likePost(postId);
            }
        } else if (e.target.closest('.comment-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.comment-btn');
            const postId = btn.dataset.postId;
            openCommentModal(postId);
        } else if (e.target.closest('.share-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.share-btn');
            const postId = btn.dataset.postId;
            sharePost(postId);
        }
    });
    
    // Smooth scrolling for better UX
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Global functions for unwatch and unfollow
function unwatchArticle(articleId) {
    if (confirm('Remove this article from your watchlist?')) {
        fetch('/api/ajax/watchlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                article_id: articleId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the item from DOM
                const item = document.querySelector(`[onclick="unwatchArticle(${articleId})"]`).closest('.watchlist-item');
                item.style.opacity = '0';
                setTimeout(() => item.remove(), 300);
                
                // Show success message
                showToast('Article removed from watchlist', 'success');
            } else {
                showToast('Error removing article from watchlist', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error removing article from watchlist', 'error');
        });
    }
}

function unfollowUser(userId) {
    if (confirm('Unfollow this user?')) {
        fetch('/api/ajax/follow_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'unfollow',
                user_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the item from DOM
                const item = document.querySelector(`[onclick="unfollowUser(${userId})"]`).closest('.following-item');
                item.style.opacity = '0';
                setTimeout(() => item.remove(), 300);
                
                // Show success message
                showToast('User unfollowed', 'success');
            } else {
                showToast('Error unfollowing user', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error unfollowing user', 'error');
        });
    }
}

function likePost(postId) {
    fetch('/api/ajax/like_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            post_id: postId,
            action: 'like'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update like button appearance
            const likeBtn = document.querySelector(`[data-post-id="${postId}"].like-btn`);
            if (likeBtn) {
                likeBtn.style.color = '#ef4444';
                likeBtn.classList.add('liked');
            }
            
            // Update like count
            updateLikeCount(postId, 1);
            showToast('Post liked!', 'success');
        } else {
            showToast(data.message || 'Error liking post', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error liking post', 'error');
    });
}

function unlikePost(postId) {
    fetch('/api/ajax/like_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            post_id: postId,
            action: 'unlike'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update like button appearance
            const likeBtn = document.querySelector(`[data-post-id="${postId}"].like-btn`);
            if (likeBtn) {
                likeBtn.style.color = '';
                likeBtn.classList.remove('liked');
            }
            
            // Update like count
            updateLikeCount(postId, -1);
            showToast('Post unliked', 'info');
        } else {
            showToast(data.message || 'Error unliking post', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error unliking post', 'error');
    });
}

function updateLikeCount(postId, change) {
    // Find all like count elements for this post
    const feedItems = document.querySelectorAll(`[data-post-id="${postId}"]`);
    feedItems.forEach(item => {
        const likeCountSpan = item.querySelector('.engagement-stats span i.iw-heart')?.parentElement;
        if (likeCountSpan) {
            const currentCount = parseInt(likeCountSpan.textContent.trim()) || 0;
            const newCount = Math.max(0, currentCount + change);
            likeCountSpan.innerHTML = `<i class="iw iw-heart"></i> ${newCount}`;
        }
    });
}

function openCommentModal(postId) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('commentModal');
    if (!modal) {
        modal = createCommentModal();
        document.body.appendChild(modal);
    }
    
    // Set the post ID
    modal.dataset.postId = postId;
    
    // Load comments for this post
    loadComments(postId);
    
    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function createCommentModal() {
    const modal = document.createElement('div');
    modal.id = 'commentModal';
    modal.className = 'comment-modal';
    modal.innerHTML = `
        <div class="comment-modal-content">
            <div class="comment-modal-header">
                <h3>Comments</h3>
                <button class="close-btn" onclick="closeCommentModal()">&times;</button>
            </div>
            <div class="comment-modal-body">
                <div class="comment-form">
                    <textarea id="commentText" placeholder="Write a comment..." rows="3"></textarea>
                    <button id="submitComment" class="btn btn-primary">Post Comment</button>
                </div>
                <div id="commentsList" class="comments-list">
                    <!-- Comments will be loaded here -->
                </div>
            </div>
        </div>
    `;
    
    // Add event listeners
    modal.querySelector('#submitComment').addEventListener('click', submitComment);
    modal.querySelector('#commentText').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && e.ctrlKey) {
            submitComment();
        }
    });
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeCommentModal();
        }
    });
    
    return modal;
}

function closeCommentModal() {
    const modal = document.getElementById('commentModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function loadComments(postId) {
    const commentsList = document.getElementById('commentsList');
    commentsList.innerHTML = '<div class="loading">Loading comments...</div>';
    
    fetch(`/api/ajax/get_comments.php?post_id=${postId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayComments(data.comments);
            } else {
                commentsList.innerHTML = `<div class="error">${data.message || 'Failed to load comments'}</div>`;
            }
        })
        .catch(error => {
            console.error('Error loading comments:', error);
            commentsList.innerHTML = '<div class="error">Error loading comments</div>';
        });
}

function displayComments(comments) {
    const commentsList = document.getElementById('commentsList');
    
    if (comments.length === 0) {
        commentsList.innerHTML = '<div class="no-comments">No comments yet. Be the first to comment!</div>';
        return;
    }
    
    let html = '';
    comments.forEach(comment => {
        html += `
            <div class="comment-item">
                <div class="comment-header">
                    <img src="/assets/images/default-avatar.svg" alt="User" class="comment-avatar">
                    <div class="comment-author">
                        <strong>${comment.display_name || comment.username}</strong>
                        <span class="comment-time">${formatCommentTime(comment.created_at)}</span>
                    </div>
                </div>
                <div class="comment-content">${escapeHtml(comment.content)}</div>
                <div class="comment-actions">
                    <button class="reply-btn" onclick="replyToComment(${comment.id})">Reply</button>
                </div>
                ${comment.replies && comment.replies.length > 0 ? displayReplies(comment.replies) : ''}
            </div>
        `;
    });
    
    commentsList.innerHTML = html;
}

function displayReplies(replies) {
    let html = '<div class="comment-replies">';
    replies.forEach(reply => {
        html += `
            <div class="reply-item">
                <div class="reply-header">
                    <img src="/assets/images/default-avatar.svg" alt="User" class="reply-avatar">
                    <div class="reply-author">
                        <strong>${reply.display_name || reply.username}</strong>
                        <span class="reply-time">${formatCommentTime(reply.created_at)}</span>
                    </div>
                </div>
                <div class="reply-content">${escapeHtml(reply.content)}</div>
            </div>
        `;
    });
    html += '</div>';
    return html;
}

function submitComment() {
    const postId = document.getElementById('commentModal').dataset.postId;
    const content = document.getElementById('commentText').value.trim();
    
    if (!content) {
        showToast('Please enter a comment', 'error');
        return;
    }
    
    const submitBtn = document.getElementById('submitComment');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Posting...';
    
    fetch('/api/ajax/add_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            post_id: postId,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('commentText').value = '';
            loadComments(postId);
            updateCommentCount(postId, 1);
            showToast('Comment posted!', 'success');
        } else {
            showToast(data.message || 'Failed to post comment', 'error');
        }
    })
    .catch(error => {
        console.error('Error posting comment:', error);
        showToast('Error posting comment', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Post Comment';
    });
}

function replyToComment(commentId) {
    // For now, just focus on the comment textarea
    // In a full implementation, this would create a reply form
    document.getElementById('commentText').focus();
    showToast('Reply feature coming soon!', 'info');
}

function updateCommentCount(postId, change) {
    const feedItems = document.querySelectorAll(`[data-post-id="${postId}"]`);
    feedItems.forEach(item => {
        const commentCountSpan = item.querySelector('.engagement-stats span i.iw-comment')?.parentElement;
        if (commentCountSpan) {
            const currentCount = parseInt(commentCountSpan.textContent.trim()) || 0;
            const newCount = Math.max(0, currentCount + change);
            commentCountSpan.innerHTML = `<i class="iw iw-comment"></i> ${newCount}`;
        }
    });
}

function formatCommentTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'just now';
    if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
    if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
    if (diff < 604800000) return Math.floor(diff / 86400000) + 'd ago';
    
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function checkLikedPosts() {
    fetch('/api/ajax/get_liked_posts.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI for liked posts
                data.liked_posts.forEach(postId => {
                    const likeBtns = document.querySelectorAll(`[data-post-id="${postId}"].like-btn`);
                    likeBtns.forEach(btn => {
                        btn.style.color = '#ef4444';
                        btn.classList.add('liked');
                    });
                });
            }
        })
        .catch(error => {
            console.error('Error checking liked posts:', error);
        });
}

function initializePostCreation() {
    const postInput = document.getElementById('postContent');
    const postInputSimple = document.getElementById('postContentSimple');
    const fullscreenBtn = document.getElementById('fullscreenBtn');
    const submitBtn = document.getElementById('submitPost');
    const cancelBtn = document.getElementById('cancelPost');
    const isPublicCheckbox = document.getElementById('isPublic');
    const toggleFormattingBtn = document.getElementById('toggleFormatting');
    const postToolbar = document.getElementById('postToolbar');
    const postEditorContainer = document.getElementById('postEditorContainer');
    const helpModal = document.getElementById('helpModal');
    const helpModalClose = document.querySelector('.help-modal-close');
    const createPostCard = document.querySelector('.create-post-card');
    
    let isFormattingMode = false;
    let isFullscreenMode = false;
    let currentInput = postInputSimple;
    
    // Handle formatting toggle
    if (toggleFormattingBtn) {
        toggleFormattingBtn.addEventListener('click', function() {
            isFormattingMode = !isFormattingMode;
        
        if (isFormattingMode) {
            // Switch to formatting mode
            postInputSimple.style.display = 'none';
            postToolbar.style.display = 'flex';
            postEditorContainer.style.display = 'flex';
            currentInput = postInput;
            
            // Copy content from simple input to formatting input
            postInput.value = postInputSimple.value;
            
            // Update button
            this.innerHTML = '<i class="iw iw-edit"></i><span>Simple</span>';
            this.title = 'Switch to simple mode';
            
            // Initialize toolbar
            initializeToolbar();
        } else {
            // Switch to simple mode
            postToolbar.style.display = 'none';
            postEditorContainer.style.display = 'none';
            postInputSimple.style.display = 'block';
            currentInput = postInputSimple;
            
            // Copy content from formatting input to simple input
            postInputSimple.value = postInput.value;
            
            // Update button
            this.innerHTML = '<i class="iw iw-edit"></i><span>Format</span>';
            this.title = 'Show formatting tools';
        }
        
        // Update input handlers
        updateInputHandlers();
        });
    }
    
    // Handle post input changes
    function updateInputHandlers() {
        // Remove existing listeners
        if (postInput) {
            postInput.removeEventListener('input', handleInputChange);
            postInput.removeEventListener('paste', handlePaste);
        }
        if (postInputSimple) {
            postInputSimple.removeEventListener('input', handleInputChange);
            postInputSimple.removeEventListener('paste', handlePaste);
        }
        
        // Add listeners to current input
        if (currentInput) {
            currentInput.addEventListener('input', handleInputChange);
            currentInput.addEventListener('paste', handlePaste);
        }
    }
    
    // Handle paste events
    function handlePaste(e) {
        const items = e.clipboardData.items;
        
        for (let i = 0; i < items.length; i++) {
            const item = items[i];
            
            if (item.type.indexOf('image') !== -1) {
                e.preventDefault();
                const file = item.getAsFile();
                if (file) {
                    // Auto-switch to formatting mode when pasting images
                    if (!isFormattingMode) {
                        toggleFormattingBtn.click();
                    }
                    handleImageUpload(file);
                }
            }
        }
    }
    
    function handleInputChange() {
        const hasContent = this.value.trim().length > 0;
        submitBtn.disabled = !hasContent;
        cancelBtn.style.display = hasContent ? 'block' : 'none';
        
        // Update preview if in formatting mode
        if (isFormattingMode) {
            updatePreview();
        }
    }
    
    // Initialize toolbar functionality
    function initializeToolbar() {
        // Toolbar buttons
        document.querySelectorAll('.toolbar-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const action = this.dataset.action;
                const start = postInput.selectionStart;
                const end = postInput.selectionEnd;
                const selectedText = postInput.value.substring(start, end);
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
                    postInput.value = postInput.value.substring(0, start) + newText + postInput.value.substring(end);
                    postInput.focus();
                    postInput.setSelectionRange(start + newText.length, start + newText.length);
                    handleInputChange.call(postInput);
                }
            });
        });
    }
    
    // Preview functionality
    function togglePreview() {
        const previewContainer = document.getElementById('postPreviewContainer');
        const isPreviewVisible = previewContainer.style.display !== 'none';
        
        if (isPreviewVisible) {
            previewContainer.style.display = 'none';
            document.querySelector('[data-action="toggle-preview"]').innerHTML = '<i class="iw iw-eye"></i>';
        } else {
            previewContainer.style.display = 'flex';
            updatePreview();
            document.querySelector('[data-action="toggle-preview"]').innerHTML = '<i class="iw iw-eye-slash"></i>';
        }
    }
    
    
    // Initialize input handlers
    updateInputHandlers();
    
    // Handle post submission
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
        let content = currentInput.value.trim();
        if (!content) return;
        
        // Convert image placeholders to Markdown
        content = convertImagePlaceholdersToMarkdown(content);
        
        const isPublic = isPublicCheckbox.checked;
        
        // Disable button during submission
        submitBtn.disabled = true;
        submitBtn.textContent = 'Posting...';
        
        // Submit post
        fetch('/api/ajax/create_post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                content: content,
                post_type: 'text',
                is_public: isPublic
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear input and images
                currentInput.value = '';
                uploadedImages = []; // Clear uploaded images
                clearImagePreviews(); // Clear image previews
                submitBtn.disabled = true;
                cancelBtn.style.display = 'none';
                
                // Show success message
                showToast('Post created successfully!', 'success');
                
                // Add new post to the top of the feed
                addPostToFeed(data.post);
                
                // Refresh the feed to show the new post
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast(data.message || 'Failed to create post', 'error');
            }
        })
        .catch(error => {
            console.error('Error creating post:', error);
            showToast('Failed to create post. Please try again.', 'error');
        })
        .finally(() => {
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.textContent = 'Post';
        });
        });
    }
    
    // Handle cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
        currentInput.value = '';
        uploadedImages = []; // Clear uploaded images
        clearImagePreviews(); // Clear image previews
        submitBtn.disabled = true;
        this.style.display = 'none';
        });
    }
    
    // Handle fullscreen toggle
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', function() {
        isFullscreenMode = !isFullscreenMode;
        const icon = this.querySelector('i');
        
        if (isFullscreenMode) {
            // Enter fullscreen mode
            createPostCard.classList.add('fullscreen');
            icon.className = 'iw iw-compress';
            this.title = 'Exit Fullscreen';
            
            // Auto-enable formatting mode in fullscreen
            if (!isFormattingMode) {
                toggleFormattingBtn.click();
            }
        } else {
            // Exit fullscreen mode
            createPostCard.classList.remove('fullscreen');
            icon.className = 'iw iw-expand';
            this.title = 'Toggle Fullscreen Editor';
        }
        });
    }
    
    // Handle escape key to exit fullscreen
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isFullscreenMode) {
            fullscreenBtn.click();
        }
    });
    
    // Handle Enter key (Ctrl+Enter to submit)
    function addKeydownListener() {
        if (currentInput) {
            currentInput.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'Enter') {
                    if (submitBtn && !submitBtn.disabled) {
                        submitBtn.click();
                    }
                }
            });
        }
    }
    
    // Add initial keydown listener
    addKeydownListener();
    
    // Help modal functionality
    if (helpModalClose) {
        helpModalClose.addEventListener('click', function() {
            helpModal.style.display = 'none';
        });
    }
    
    if (helpModal) {
        helpModal.addEventListener('click', function(e) {
            if (e.target === helpModal) {
                helpModal.style.display = 'none';
            }
        });
    }
}

function addPostToFeed(post) {
    // This function would add the new post to the top of the feed
    // For now, we'll just reload the page to show the new post
    console.log('New post created:', post);
}

function initializeImageHandlers() {
    // Add click handlers to all post images
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('post-image') || e.target.tagName === 'IMG') {
            const img = e.target;
            const src = img.src;
            const alt = img.alt || 'Image';
            
            // Create modal for image viewing
            const modal = document.createElement('div');
            modal.className = 'image-modal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.9);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
                cursor: pointer;
            `;
            
            const imgElement = document.createElement('img');
            imgElement.src = src;
            imgElement.alt = alt;
            imgElement.style.cssText = `
                max-width: 90%;
                max-height: 90%;
                border-radius: 8px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.5);
            `;
            
            modal.appendChild(imgElement);
            document.body.appendChild(modal);
            
            // Close modal on click
            modal.addEventListener('click', function() {
                document.body.removeChild(modal);
            });
            
            // Close modal on escape key
            const escapeHandler = function(e) {
                if (e.key === 'Escape') {
                    document.body.removeChild(modal);
                    document.removeEventListener('keydown', escapeHandler);
                }
            };
            document.addEventListener('keydown', escapeHandler);
        }
    });
}

function showToast(message, type = 'info') {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    // Add styles
    Object.assign(toast.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '12px 20px',
        borderRadius: '6px',
        color: 'white',
        fontWeight: '500',
        zIndex: '10000',
        opacity: '0',
        transform: 'translateX(100%)',
        transition: 'all 0.3s ease',
        maxWidth: '300px',
        wordWrap: 'break-word'
    });
    
    // Set background color based on type
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    toast.style.backgroundColor = colors[type] || colors.info;
    
    // Add to page
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

function handlePhotoVideo() {
    // Create file input for photo/video upload
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*,video/*';
    input.multiple = true;
    
    input.onchange = function(e) {
        const files = Array.from(e.target.files);
        const hasImages = files.some(file => file.type.startsWith('image/'));
        
        // Auto-switch to formatting mode when uploading images
        if (hasImages && !isFormattingMode) {
            toggleFormattingBtn.click();
        }
        
        files.forEach(file => {
            if (file.type.startsWith('image/')) {
                handleImageUpload(file);
            } else if (file.type.startsWith('video/')) {
                handleVideoUpload(file);
            }
        });
    };
    
    input.click();
}

function handleTagPeople() {
    const currentInput = document.getElementById('postContent') || document.getElementById('postContentSimple');
    const text = currentInput.value;
    const cursorPos = currentInput.selectionStart;
    
    // Insert @ symbol for tagging
    const newText = text.substring(0, cursorPos) + '@' + text.substring(cursorPos);
    currentInput.value = newText;
    currentInput.focus();
    currentInput.setSelectionRange(cursorPos + 1, cursorPos + 1);
    
    showToast('Type username after @ to tag someone', 'info');
}

function handleFeeling() {
    const currentInput = document.getElementById('postContent') || document.getElementById('postContentSimple');
    const text = currentInput.value;
    const cursorPos = currentInput.selectionStart;
    
    // Insert feeling/activity text
    const feeling = prompt('What are you feeling or doing?');
    if (feeling) {
        const newText = text.substring(0, cursorPos) + `Feeling ${feeling}` + text.substring(cursorPos);
        currentInput.value = newText;
        currentInput.focus();
        currentInput.setSelectionRange(cursorPos + feeling.length + 8, cursorPos + feeling.length + 8);
    }
}

function handleGIF() {
    showToast('GIF functionality coming soon!', 'info');
}

function handleImageUpload(file) {
    // Check file size (max 10MB - will be auto-scaled if larger than 2MB)
    if (file.size > 10 * 1024 * 1024) {
        showToast('Image too large. Please choose an image smaller than 10MB.', 'error');
        return;
    }
    
    // Show loading state
    showToast('Uploading image...', 'info');
    
    // Upload image to server first
    uploadImageToServer(file).then(uploadResult => {
        if (uploadResult.success) {
            // Create image preview with server URL
            const imageContainer = createImagePreview(file, uploadResult.url);
            
            // Insert image preview into editor
            insertImagePreview(imageContainer, uploadResult.url);
            
            // Show appropriate success message
            if (uploadResult.was_scaled) {
                const originalSize = (uploadResult.original_size / 1024 / 1024).toFixed(1);
                const finalSize = (uploadResult.final_size / 1024 / 1024).toFixed(1);
                showToast(`Image scaled from ${originalSize}MB to ${finalSize}MB and uploaded`, 'success');
            } else {
                showToast('Image uploaded successfully', 'success');
            }
        } else {
            console.error('Upload failed:', uploadResult);
            showToast('Failed to upload image: ' + uploadResult.message, 'error');
            
            // Show debug info if available
            if (uploadResult.debug) {
                console.log('Debug info:', uploadResult.debug);
            }
        }
    }).catch(error => {
        console.error('Image upload error:', error);
        showToast('Failed to upload image', 'error');
    });
}


function createImagePreview(file, imageUrl) {
    const container = document.createElement('div');
    container.className = 'image-preview-container';
    container.innerHTML = `
        <div class="image-preview">
            <img src="${imageUrl}" alt="${file.name}" class="preview-image">
            <button type="button" class="remove-image-btn" onclick="removeImagePreview(this)">
                <i class="iw iw-times"></i>
            </button>
        </div>
    `;
    return container;
}

function insertImagePreview(imageContainer, imageUrl) {
    const currentInput = document.getElementById('postContent') || document.getElementById('postContentSimple');
    const isFormattingMode = document.getElementById('postEditorContainer').style.display !== 'none';
    
    if (isFormattingMode) {
        // Insert into the editor container
        const editorContainer = document.getElementById('postEditorContainer');
        const editor = document.getElementById('postContent');
        
        // Create a temporary div to hold the image
        const tempDiv = document.createElement('div');
        tempDiv.className = 'temp-image-holder';
        tempDiv.appendChild(imageContainer);
        
        // Insert after the textarea
        editor.parentNode.insertBefore(tempDiv, editor.nextSibling);
        
        // Store the image URL for later use
        uploadedImages.push(imageUrl);
        
        // Update preview
        updatePreview();
    } else {
        // For simple mode, add to the simple image preview area (no textarea modification)
        const simplePreview = document.getElementById('simpleImagePreview');
        simplePreview.appendChild(imageContainer);
        simplePreview.style.display = 'flex';
        
        // Store the image URL for later use
        uploadedImages.push(imageUrl);
    }
    
    // Trigger input change for validation
    currentInput.dispatchEvent(new Event('input'));
}

function removeImagePreview(button) {
    const container = button.closest('.image-preview-container');
    if (container) {
        const placeholder = container.getAttribute('data-placeholder');
        if (placeholder) {
            // Remove placeholder from text
            const currentInput = document.getElementById('postContent') || document.getElementById('postContentSimple');
            const text = currentInput.value;
            const newText = text.replace(placeholder, '');
            currentInput.value = newText;
            
            // Trigger input change for validation
            currentInput.dispatchEvent(new Event('input'));
        }
        container.remove();
    }
}

function convertImagePlaceholdersToMarkdown(content) {
    // Add uploaded images to the content
    let processedContent = content;
    
    // Add all uploaded images at the end of the content
    if (uploadedImages.length > 0) {
        const imageMarkdown = uploadedImages.map(url => `![Image](${url})`).join('\n\n');
        processedContent = processedContent + (processedContent ? '\n\n' : '') + imageMarkdown;
    }
    
    return processedContent;
}

function clearImagePreviews() {
    // Clear simple image preview
    const simplePreview = document.getElementById('simpleImagePreview');
    if (simplePreview) {
        simplePreview.innerHTML = '';
        simplePreview.style.display = 'none';
    }
    
    // Clear formatting mode image previews
    const tempHolders = document.querySelectorAll('.temp-image-holder');
    tempHolders.forEach(holder => holder.remove());
    
    // Clear the global uploaded images array
    uploadedImages = [];
}

function handleVideoUpload(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        const currentInput = document.getElementById('postContent') || document.getElementById('postContentSimple');
        const text = currentInput.value;
        const cursorPos = currentInput.selectionStart;
        
        // Insert video markdown
        const videoMarkdown = `![${file.name}](${e.target.result})\n`;
        const newText = text.substring(0, cursorPos) + videoMarkdown + text.substring(cursorPos);
        currentInput.value = newText;
        currentInput.focus();
        currentInput.setSelectionRange(cursorPos + videoMarkdown.length, cursorPos + videoMarkdown.length);
        
        // Trigger input change for validation
        currentInput.dispatchEvent(new Event('input'));
    };
    reader.readAsDataURL(file);
}

function openPostModal(type) {
    // For now, redirect to create post page with type parameter
    // In a full implementation, this would open a modal
    const params = new URLSearchParams({ type: type });
    window.location.href = `/pages/social/create_post.php?${params.toString()}`;
}

function sharePost(postId) {
    // For now, just copy the post URL to clipboard
    const postUrl = window.location.origin + '/post/' + postId;
    navigator.clipboard.writeText(postUrl).then(() => {
        showToast('Post link copied to clipboard!', 'success');
    }).catch(() => {
        showToast('Unable to copy link', 'error');
    });
}

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    // Style the toast
    Object.assign(toast.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '12px 20px',
        borderRadius: '8px',
        color: 'white',
        fontWeight: '500',
        zIndex: '10000',
        transform: 'translateX(100%)',
        transition: 'transform 0.3s ease',
        backgroundColor: type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#6366f1'
    });
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
