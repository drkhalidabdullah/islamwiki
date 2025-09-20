/**
 * Template Editor JavaScript
 */

// Template preview functionality
function togglePreview() {
    const previewContainer = document.getElementById('preview-container');
    const contentTextarea = document.getElementById('content');
    const previewContent = document.getElementById('preview-content');
    const previewBtn = document.querySelector('button[onclick="togglePreview()"]');
    
    if (previewContainer.style.display === 'none') {
        // Show preview
        previewContainer.style.display = 'block';
        previewBtn.textContent = 'Hide Preview';
        
        // Show loading state
        previewContent.innerHTML = '<div class="preview-loading"><i class="iw iw-spinner iw-spin"></i> Parsing template...</div>';
        
        // Parse and render template preview using server-side parser
        const templateContent = contentTextarea.value;
        const templateName = document.getElementById('name').value || 'Preview Template';
        
        parseTemplatePreview(templateName, templateContent, previewContent);
        
    } else {
        // Hide preview
        previewContainer.style.display = 'none';
        previewBtn.textContent = 'Preview';
    }
}

// Parse template preview using server-side parser
function parseTemplatePreview(templateName, templateContent, previewElement) {
    // Create form data
    const formData = new FormData();
    formData.append('name', templateName);
    formData.append('content', templateContent);
    
    // Make AJAX request to parse template
    fetch('/api/template_preview.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Display parsed content
            previewElement.innerHTML = '<div class="preview-success">' + data.parsed_content + '</div>';
        } else {
            // Display error
            previewElement.innerHTML = '<div class="preview-error"><strong>Parsing Error:</strong> ' + data.error + '</div>';
        }
    })
    .catch(error => {
        // Display network error
        previewElement.innerHTML = '<div class="preview-error"><strong>Network Error:</strong> ' + error.message + '</div>';
    });
}

// Fallback template preview (client-side only)
function renderTemplatePreview(templateContent) {
    // Replace template parameters with sample values
    let preview = templateContent;
    
    // Replace triple brace parameters with sample values
    preview = preview.replace(/\{\{\{([^|{}]+)\|([^}]+)\}\}\}/g, function(match, paramName, defaultValue) {
        return '<span class="template-param" title="Parameter: ' + paramName + '">' + defaultValue + '</span>';
    });
    
    // Replace double brace parameters with sample values
    preview = preview.replace(/\{\{([^|{}]+)\|([^|{}]+)\}\}/g, function(match, paramName, defaultValue) {
        return '<span class="template-param" title="Parameter: ' + paramName + '">' + defaultValue + '</span>';
    });
    
    // Replace single brace parameters
    preview = preview.replace(/\{\{([^|{}]+)\}\}/g, function(match, paramName) {
        return '<span class="template-param" title="Parameter: ' + paramName + '">' + paramName + '</span>';
    });
    
    // Replace magic words
    preview = preview.replace(/\{\{PAGENAME\}\}/g, 'Sample Page');
    preview = preview.replace(/\{\{SITENAME\}\}/g, 'IslamWiki');
    preview = preview.replace(/\{\{CURRENTYEAR\}\}/g, new Date().getFullYear());
    
    // Replace conditional logic with sample output
    preview = preview.replace(/\{\{#if:([^|]+)\|([^|]+)\|([^}]+)\}\}/g, function(match, condition, trueValue, falseValue) {
        return '<span class="template-conditional" title="Conditional: ' + condition + '">' + trueValue + '</span>';
    });
    
    // Replace file links
    preview = preview.replace(/\[\[File:([^|]+)\|([^|]+)\|([^|]+)\|([^|]+)\|([^\]]+)\]\]/g, function(match, filename, size, align, link, alt) {
        return '<span class="template-file" title="File: ' + filename + '">📁 ' + filename + '</span>';
    });
    
    // Replace wiki links
    preview = preview.replace(/\[\[([^|]+)\|([^\]]+)\]\]/g, function(match, target, display) {
        return '<a href="#" class="template-link">' + display + '</a>';
    });
    
    preview = preview.replace(/\[\[([^\]]+)\]\]/g, function(match, target) {
        return '<a href="#" class="template-link">' + target + '</a>';
    });
    
    return preview;
}

// Auto-save functionality
let autoSaveTimeout;
function autoSave() {
    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(function() {
        // Auto-save logic could be implemented here
        console.log('Auto-save triggered');
    }, 30000); // Auto-save every 30 seconds
}

// Initialize editor
document.addEventListener('DOMContentLoaded', function() {
    const contentTextarea = document.getElementById('content');
    const previewContainer = document.getElementById('preview-container');
    const previewContent = document.getElementById('preview-content');
    
    // Add auto-save on content change
    if (contentTextarea) {
        contentTextarea.addEventListener('input', autoSave);
    }
    
    // Add syntax highlighting for template parameters
    if (contentTextarea) {
        contentTextarea.addEventListener('input', function() {
            highlightTemplateSyntax(this);
        });
    }
    
    // Add real-time preview update when preview is visible
    if (contentTextarea && previewContainer && previewContent) {
        let previewTimeout;
        contentTextarea.addEventListener('input', function() {
            // Only update if preview is visible
            if (previewContainer.style.display !== 'none') {
                clearTimeout(previewTimeout);
                previewTimeout = setTimeout(function() {
                    const templateName = document.getElementById('name').value || 'Preview Template';
                    const templateContent = contentTextarea.value;
                    
                    // Show loading state
                    previewContent.innerHTML = '<div class="preview-loading"><i class="iw iw-spinner iw-spin"></i> Updating preview...</div>';
                    
                    // Parse template
                    parseTemplatePreview(templateName, templateContent, previewContent);
                }, 1000); // Wait 1 second after user stops typing
            }
        });
    }
});

// Basic syntax highlighting for template parameters
function highlightTemplateSyntax(textarea) {
    // This is a basic implementation
    // In a full implementation, you'd use a proper syntax highlighter
    const content = textarea.value;
    
    // Highlight template parameters
    const highlighted = content
        .replace(/(\{\{\{[^}]+\}\}\})/g, '<span class="template-param-highlight">$1</span>')
        .replace(/(\{\{[^}]+\}\})/g, '<span class="template-brace-highlight">$1</span>')
        .replace(/(\{\{#if:[^}]+\}\})/g, '<span class="template-conditional-highlight">$1</span>');
    
    // Note: This is just for demonstration
    // Real syntax highlighting would require a more sophisticated approach
}

// Template validation
function validateTemplate() {
    const content = document.getElementById('content').value;
    const errors = [];
    
    // Check for unclosed braces
    const openBraces = (content.match(/\{\{/g) || []).length;
    const closeBraces = (content.match(/\}\}/g) || []).length;
    
    if (openBraces !== closeBraces) {
        errors.push('Mismatched template braces: ' + openBraces + ' opening, ' + closeBraces + ' closing');
    }
    
    // Check for unclosed triple braces
    const openTripleBraces = (content.match(/\{\{\{/g) || []).length;
    const closeTripleBraces = (content.match(/\}\}\}/g) || []).length;
    
    if (openTripleBraces !== closeTripleBraces) {
        errors.push('Mismatched triple braces: ' + openTripleBraces + ' opening, ' + closeTripleBraces + ' closing');
    }
    
    if (errors.length > 0) {
        alert('Template validation errors:\n' + errors.join('\n'));
        return false;
    }
    
    return true;
}

// Add validation to form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.template-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateTemplate()) {
                e.preventDefault();
            }
        });
    }
});
