// Template Management JavaScript

let templates = [];

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadTemplates();
    setupEventListeners();
});

// Load templates data
function loadTemplates() {
    // This would typically be loaded via AJAX
    // For now, we'll work with the server-rendered data
}

// Setup event listeners
function setupEventListeners() {
    // Template form submission
    document.getElementById('templateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitTemplateForm();
    });
    
    // Modal close on outside click
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('templateModal');
        if (e.target === modal) {
            closeTemplateModal();
        }
    });
}

// Show create template modal
function showCreateTemplateModal() {
    document.getElementById('modalTitle').textContent = 'Create Template';
    document.getElementById('formAction').value = 'create';
    document.getElementById('templateId').value = '';
    document.getElementById('templateForm').reset();
    document.getElementById('templateModal').style.display = 'block';
}

// Edit template
function editTemplate(templateId) {
    // Get template data (this would typically be via AJAX)
    const template = getTemplateById(templateId);
    if (!template) return;
    
    document.getElementById('modalTitle').textContent = 'Edit Template';
    document.getElementById('formAction').value = 'update';
    document.getElementById('templateId').value = templateId;
    document.getElementById('templateName').value = template.name;
    document.getElementById('templateType').value = template.template_type;
    document.getElementById('templateDescription').value = template.description || '';
    document.getElementById('templateContent').value = template.content;
    
    document.getElementById('templateModal').style.display = 'block';
}

// View template
function viewTemplate(templateId) {
    const template = getTemplateById(templateId);
    if (!template) return;
    
    // Create view modal
    const viewModal = document.createElement('div');
    viewModal.className = 'modal';
    viewModal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h2>${escapeHtml(template.name)}</h2>
                <span class="close" onclick="this.parentElement.parentElement.parentElement.remove()">&times;</span>
            </div>
            <div class="template-view">
                <div class="template-info">
                    <p><strong>Type:</strong> ${template.template_type}</p>
                    <p><strong>Usage Count:</strong> ${template.usage_count}</p>
                    <p><strong>Last Used:</strong> ${template.last_used_at || 'Never'}</p>
                </div>
                <div class="template-description">
                    <h3>Description</h3>
                    <p>${escapeHtml(template.description || 'No description')}</p>
                </div>
                <div class="template-content">
                    <h3>Template Content</h3>
                    <pre><code>${escapeHtml(template.content)}</code></pre>
                </div>
                <div class="template-preview">
                    <h3>Preview</h3>
                    <div class="preview-content">
                        ${renderTemplatePreview(template.content)}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(viewModal);
    viewModal.style.display = 'block';
}

// Delete template
function deleteTemplate(templateId) {
    if (confirm('Are you sure you want to delete this template? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${templateId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Submit template form
function submitTemplateForm() {
    const form = document.getElementById('templateForm');
    const formData = new FormData(form);
    
    // Validate required fields
    const name = formData.get('name');
    const content = formData.get('content');
    
    if (!name.trim()) {
        alert('Template name is required.');
        return;
    }
    
    if (!content.trim()) {
        alert('Template content is required.');
        return;
    }
    
    // Submit form
    form.submit();
}

// Close template modal
function closeTemplateModal() {
    document.getElementById('templateModal').style.display = 'none';
}

// Show template help
function showTemplateHelp() {
    document.getElementById('helpModal').style.display = 'block';
}

// Close help modal
function closeHelpModal() {
    document.getElementById('helpModal').style.display = 'none';
}

// Get template by ID (placeholder - would typically be via AJAX)
function getTemplateById(templateId) {
    // This would typically fetch from server
    // For now, return null to trigger server-side handling
    return null;
}

// Render template preview
function renderTemplatePreview(content) {
    // Simple preview rendering
    // In a real implementation, this would use the actual template parser
    let preview = content;
    
    // Replace basic parameters
    preview = preview.replace(/\{\{([^|{}]+)\|([^}]+)\}\}/g, '<span class="param">$1</span>');
    preview = preview.replace(/\{\{([^|{}]+)\}\}/g, '<span class="param">$1</span>');
    
    // Replace magic words
    preview = preview.replace(/\{\{PAGENAME\}\}/g, 'Current Page');
    preview = preview.replace(/\{\{CURRENTYEAR\}\}/g, new Date().getFullYear());
    preview = preview.replace(/\{\{SITENAME\}\}/g, 'IslamWiki');
    
    return preview;
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Template syntax highlighter
function highlightTemplateSyntax() {
    const textarea = document.getElementById('templateContent');
    if (!textarea) return;
    
    // Simple syntax highlighting for template content
    textarea.addEventListener('input', function() {
        const content = this.value;
        const highlighted = content
            .replace(/\{\{([^}]+)\}\}/g, '<span class="template-param">$1</span>')
            .replace(/\{\{#if[^}]*\}\}/g, '<span class="template-conditional">$&</span>')
            .replace(/\{\{#foreach[^}]*\}\}/g, '<span class="template-loop">$&</span>');
        
        // This is a simplified version - a real implementation would use a proper syntax highlighter
    });
}

// Initialize syntax highlighting
document.addEventListener('DOMContentLoaded', function() {
    highlightTemplateSyntax();
});
