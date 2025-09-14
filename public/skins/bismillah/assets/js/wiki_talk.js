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
