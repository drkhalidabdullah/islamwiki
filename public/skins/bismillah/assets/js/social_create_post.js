// Enhanced Markdown Parser

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
            previewBtn.innerHTML = '<i class="iw iw-eye"></i> Show Preview';
        } else {
            previewContainer.style.display = 'flex';
            updatePreview();
            previewBtn.innerHTML = '<i class="iw iw-eye-slash"></i> Hide Preview';
        }
    }
    
