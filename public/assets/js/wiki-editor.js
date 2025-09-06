class WikiEditor {
    constructor(textareaId, previewId) {
        this.textarea = document.getElementById(textareaId);
        this.preview = document.getElementById(previewId);
        this.toolbar = null;
        this.init();
    }
    
    init() {
        this.createToolbar();
        this.setupEventListeners();
        this.updatePreview();
    }
    
    createToolbar() {
        const toolbar = document.createElement('div');
        toolbar.className = 'wiki-toolbar';
        toolbar.innerHTML = `
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-action="bold" title="Bold">
                    <strong>B</strong>
                </button>
                <button type="button" class="toolbar-btn" data-action="italic" title="Italic">
                    <em>I</em>
                </button>
                <button type="button" class="toolbar-btn" data-action="code" title="Code">
                    <code>&lt;/&gt;</code>
                </button>
            </div>
            
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-action="h1" title="Heading 1">
                    H1
                </button>
                <button type="button" class="toolbar-btn" data-action="h2" title="Heading 2">
                    H2
                </button>
                <button type="button" class="toolbar-btn" data-action="h3" title="Heading 3">
                    H3
                </button>
            </div>
            
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-action="link" title="Link">
                    🔗
                </button>
                <button type="button" class="toolbar-btn" data-action="wikilink" title="Wiki Link">
                    ��
                </button>
                <button type="button" class="toolbar-btn" data-action="image" title="Image">
                    🖼️
                </button>
            </div>
            
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-action="ul" title="Bullet List">
                    • List
                </button>
                <button type="button" class="toolbar-btn" data-action="ol" title="Numbered List">
                    1. List
                </button>
                <button type="button" class="toolbar-btn" data-action="quote" title="Quote">
                    " Quote
                </button>
            </div>
            
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-action="preview" title="Toggle Preview">
                    👁️ Preview
                </button>
                <button type="button" class="toolbar-btn" data-action="help" title="Markdown Help">
                    ❓ Help
                </button>
            </div>
        `;
        
        this.textarea.parentNode.insertBefore(toolbar, this.textarea);
        this.toolbar = toolbar;
    }
    
    setupEventListeners() {
        // Toolbar button clicks
        this.toolbar.addEventListener('click', (e) => {
            if (e.target.classList.contains('toolbar-btn')) {
                e.preventDefault();
                const action = e.target.dataset.action;
                this.handleAction(action);
            }
        });
        
        // Textarea changes
        this.textarea.addEventListener('input', () => {
            this.updatePreview();
        });
        
        // Keyboard shortcuts
        this.textarea.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'b':
                        e.preventDefault();
                        this.handleAction('bold');
                        break;
                    case 'i':
                        e.preventDefault();
                        this.handleAction('italic');
                        break;
                    case 'k':
                        e.preventDefault();
                        this.handleAction('link');
                        break;
                }
            }
        });
    }
    
    handleAction(action) {
        const start = this.textarea.selectionStart;
        const end = this.textarea.selectionEnd;
        const selectedText = this.textarea.value.substring(start, end);
        const beforeCursor = this.textarea.value.substring(0, start);
        const afterCursor = this.textarea.value.substring(end);
        
        let replacement = '';
        
        switch(action) {
            case 'bold':
                replacement = `**${selectedText || 'bold text'}**`;
                break;
            case 'italic':
                replacement = `*${selectedText || 'italic text'}*`;
                break;
            case 'code':
                replacement = `\`${selectedText || 'code'}\``;
                break;
            case 'h1':
                replacement = `# ${selectedText || 'Heading 1'}`;
                break;
            case 'h2':
                replacement = `## ${selectedText || 'Heading 2'}`;
                break;
            case 'h3':
                replacement = `### ${selectedText || 'Heading 3'}`;
                break;
            case 'link':
                const url = prompt('Enter URL:', 'https://');
                if (url) {
                    const text = selectedText || prompt('Enter link text:', '');
                    replacement = `[${text}](${url})`;
                }
                break;
            case 'wikilink':
                const pageName = selectedText || prompt('Enter page name:', '');
                if (pageName) {
                    const displayText = prompt('Enter display text (optional):', pageName);
                    if (displayText && displayText !== pageName) {
                        replacement = `[[${pageName}|${displayText}]]`;
                    } else {
                        replacement = `[[${pageName}]]`;
                    }
                }
                break;
            case 'image':
                const imageUrl = prompt('Enter image URL:', '');
                if (imageUrl) {
                    const altText = selectedText || prompt('Enter alt text:', '');
                    replacement = `![${altText}](${imageUrl})`;
                }
                break;
            case 'ul':
                replacement = `* ${selectedText || 'List item'}`;
                break;
            case 'ol':
                replacement = `1. ${selectedText || 'List item'}`;
                break;
            case 'quote':
                replacement = `> ${selectedText || 'Quote text'}`;
                break;
            case 'preview':
                this.togglePreview();
                return;
            case 'help':
                this.showHelp();
                return;
        }
        
        this.textarea.value = beforeCursor + replacement + afterCursor;
        this.textarea.focus();
        
        // Set cursor position after the replacement
        const newCursorPos = start + replacement.length;
        this.textarea.setSelectionRange(newCursorPos, newCursorPos);
        
        this.updatePreview();
    }
    
    updatePreview() {
        if (this.preview) {
            // Send content to server for parsing
            fetch('wiki/preview.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'content=' + encodeURIComponent(this.textarea.value)
            })
            .then(response => response.text())
            .then(html => {
                this.preview.innerHTML = html;
            })
            .catch(error => {
                console.error('Preview error:', error);
                this.preview.innerHTML = '<p>Preview unavailable</p>';
            });
        }
    }
    
    togglePreview() {
        const previewContainer = document.getElementById('preview-container');
        if (previewContainer) {
            previewContainer.style.display = previewContainer.style.display === 'none' ? 'block' : 'none';
        }
    }
    
    showHelp() {
        const helpContent = `
            <h3>Markdown Help</h3>
            <div class="markdown-help">
                <h4>Text Formatting</h4>
                <ul>
                    <li><strong>Bold:</strong> **text** or __text__</li>
                    <li><em>Italic:</em> *text* or _text_</li>
                    <li><code>Code:</code> \`code\`</li>
                    <li><pre>Code Block:</pre> \`\`\`code\`\`\`</li>
                </ul>
                
                <h4>Headers</h4>
                <ul>
                    <li># Heading 1</li>
                    <li>## Heading 2</li>
                    <li>### Heading 3</li>
                </ul>
                
                <h4>Links</h4>
                <ul>
                    <li>Regular link: [text](url)</li>
                    <li>Wiki link: [[Page Name]]</li>
                    <li>Wiki link with display: [[Page Name|Display Text]]</li>
                </ul>
                
                <h4>Lists</h4>
                <ul>
                    <li>Bullet list: * item</li>
                    <li>Numbered list: 1. item</li>
                </ul>
                
                <h4>Other</h4>
                <ul>
                    <li>Quote: > quoted text</li>
                    <li>Image: ![alt](url)</li>
                </ul>
            </div>
        `;
        
        // Create modal or show help
        const modal = document.createElement('div');
        modal.className = 'help-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close">&times;</span>
                ${helpContent}
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Close modal
        modal.querySelector('.close').onclick = () => {
            document.body.removeChild(modal);
        };
        
        modal.onclick = (e) => {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        };
    }
}

// Initialize editor when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('content');
    const preview = document.getElementById('preview-content');
    
    if (textarea && preview) {
        new WikiEditor('content', 'preview-content');
    }
});
