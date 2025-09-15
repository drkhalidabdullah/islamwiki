// Template Creation JavaScript

document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    setupSyntaxHighlighting();
});

// Setup event listeners
function setupEventListeners() {
    // Form submission
    document.querySelector('.template-form').addEventListener('submit', function(e) {
        validateForm(e);
    });
    
    // Real-time preview
    const contentTextarea = document.getElementById('templateContent');
    if (contentTextarea) {
        contentTextarea.addEventListener('input', function() {
            updatePreview();
        });
    }
    
    // Modal close on outside click
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('helpModal');
        if (e.target === modal) {
            closeHelpModal();
        }
    });
}

// Validate form before submission
function validateForm(e) {
    const name = document.getElementById('templateName').value.trim();
    const content = document.getElementById('templateContent').value.trim();
    
    if (!name) {
        e.preventDefault();
        alert('Template name is required.');
        document.getElementById('templateName').focus();
        return false;
    }
    
    if (!content) {
        e.preventDefault();
        alert('Template content is required.');
        document.getElementById('templateContent').focus();
        return false;
    }
    
    // Check for basic template syntax
    if (!content.includes('{{') || !content.includes('}}')) {
        if (!confirm('Your template doesn\'t contain any parameters ({{param}}). Are you sure you want to create a static template?')) {
            e.preventDefault();
            return false;
        }
    }
    
    return true;
}

// Preview template
function previewTemplate() {
    const content = document.getElementById('templateContent').value;
    const previewSection = document.getElementById('previewSection');
    const previewContent = document.getElementById('previewContent');
    
    if (!content.trim()) {
        alert('Please enter template content first.');
        return;
    }
    
    // Simple preview rendering
    let preview = renderTemplatePreview(content);
    
    previewContent.innerHTML = preview;
    previewSection.style.display = 'block';
    
    // Scroll to preview
    previewSection.scrollIntoView({ behavior: 'smooth' });
}

// Update preview in real-time
function updatePreview() {
    const content = document.getElementById('templateContent').value;
    const previewSection = document.getElementById('previewSection');
    const previewContent = document.getElementById('previewContent');
    
    if (previewSection.style.display === 'block') {
        let preview = renderTemplatePreview(content);
        previewContent.innerHTML = preview;
    }
}

// Render template preview
function renderTemplatePreview(content) {
    let preview = content;
    
    // Replace triple brace parameters with sample values
    preview = preview.replace(/\{\{\{([^|{}]+)\|([^}]+)\}\}\}/g, function(match, paramName, defaultValue) {
        return '<span class="template-param" title="Parameter: ' + paramName + '">' + defaultValue + '</span>';
    });
    
    // Replace template parameters with sample values
    preview = preview.replace(/\{\{([^|{}]+)\|([^}]+)\}\}/g, function(match, paramName, defaultValue) {
        return '<span class="template-param" title="Parameter: ' + paramName + '">' + defaultValue + '</span>';
    });
    
    // Replace single parameters
    preview = preview.replace(/\{\{([^|{}]+)\}\}/g, '<span class="template-param">$1</span>');
    
    // Replace magic words
    preview = preview.replace(/\{\{PAGENAME\}\}/g, 'Sample Page');
    preview = preview.replace(/\{\{CURRENTYEAR\}\}/g, new Date().getFullYear());
    preview = preview.replace(/\{\{CURRENTMONTH\}\}/g, new Date().toLocaleString('default', { month: 'long' }));
    preview = preview.replace(/\{\{SITENAME\}\}/g, 'IslamWiki');
    
    // Replace conditional logic
    preview = preview.replace(/\{\{#if:([^|]*)\|([^|]*)\|([^}]*)\}\}/g, function(match, condition, trueValue, falseValue) {
        return condition.trim() ? trueValue : falseValue;
    });
    
    // Replace ifeq logic
    preview = preview.replace(/\{\{#ifeq:([^|]*)\|([^|]*)\|([^|]*)\|([^}]*)\}\}/g, function(match, value1, value2, trueValue, falseValue) {
        return value1.trim() === value2.trim() ? trueValue : falseValue;
    });
    
    // Convert to HTML
    preview = convertToHtml(preview);
    
    return preview;
}

// Convert markdown-like content to HTML
function convertToHtml(content) {
    // Headers
    content = content.replace(/^### (.*$)/gm, '<h3>$1</h3>');
    content = content.replace(/^## (.*$)/gm, '<h2>$1</h2>');
    content = content.replace(/^# (.*$)/gm, '<h1>$1</h1>');
    
    // Bold and italic
    content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    content = content.replace(/\*(.*?)\*/g, '<em>$1</em>');
    
    // Code
    content = content.replace(/`(.*?)`/g, '<code>$1</code>');
    
    // Lists
    content = content.replace(/^\- (.*$)/gm, '<li>$1</li>');
    content = content.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');
    
    // Paragraphs
    content = content.replace(/\n\n/g, '</p><p>');
    content = '<p>' + content + '</p>';
    
    // Clean up
    content = content.replace(/<p><\/p>/g, '');
    content = content.replace(/<p>(<h[1-6])/g, '$1');
    content = content.replace(/(<\/h[1-6]>)<\/p>/g, '$1');
    
    return content;
}

// Show template help
function showTemplateHelp() {
    document.getElementById('helpModal').style.display = 'block';
}

// Close help modal
function closeHelpModal() {
    document.getElementById('helpModal').style.display = 'none';
}

// Setup syntax highlighting
function setupSyntaxHighlighting() {
    const textarea = document.getElementById('templateContent');
    if (!textarea) return;
    
    // Add syntax highlighting styles
    const style = document.createElement('style');
    style.textContent = `
        .template-param {
            background: #e3f2fd;
            color: #1976d2;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    `;
    document.head.appendChild(style);
    
    // Add placeholder text
    textarea.placeholder = `Example template:

{{Infobox
|name={{1|Unknown}}
|type={{2|General}}
|status={{3|Active}}
|created={{CURRENTYEAR}}
|description={{4|No description available}}
}}

This is a sample infobox template with parameters.`;
}

// Auto-save functionality (optional)
function setupAutoSave() {
    const form = document.querySelector('.template-form');
    const nameField = document.getElementById('templateName');
    const contentField = document.getElementById('templateContent');
    
    let autoSaveTimeout;
    
    function autoSave() {
        const name = nameField.value;
        const content = contentField.value;
        
        if (name && content) {
            localStorage.setItem('template_draft_name', name);
            localStorage.setItem('template_draft_content', content);
        }
    }
    
    function loadDraft() {
        const savedName = localStorage.getItem('template_draft_name');
        const savedContent = localStorage.getItem('template_draft_content');
        
        if (savedName && !nameField.value) {
            nameField.value = savedName;
        }
        if (savedContent && !contentField.value) {
            contentField.value = savedContent;
        }
    }
    
    // Load draft on page load
    loadDraft();
    
    // Auto-save on input
    [nameField, contentField].forEach(field => {
        field.addEventListener('input', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(autoSave, 2000);
        });
    });
    
    // Clear draft on successful submission
    form.addEventListener('submit', function() {
        localStorage.removeItem('template_draft_name');
        localStorage.removeItem('template_draft_content');
    });
}

// Initialize auto-save if enabled
// setupAutoSave(); // Uncomment to enable auto-save
