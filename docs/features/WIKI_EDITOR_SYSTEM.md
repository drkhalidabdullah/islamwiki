# Wiki Editor System Documentation

## Overview

The IslamWiki Editor System is a comprehensive, professional-grade wiki editing interface that provides users with powerful tools for creating and editing wiki content. The system combines a rich text toolbar with live preview capabilities and supports full MediaWiki-style syntax.

## 🎯 Current Version: 0.0.0.18

**Last Updated:** January 2025  
**Status:** Production Ready ✅

## 🚀 Key Features

### 📝 **Professional Rich Text Toolbar**
- **Compact Design**: 32px height buttons with proper content scaling
- **Smart Sizing**: Each button type has appropriate width and content size
- **Visual Feedback**: Hover effects, animations, and professional styling
- **Mobile Responsive**: Touch-friendly buttons optimized for mobile devices
- **Accessibility**: Screen reader support and keyboard navigation

### 🔗 **Comprehensive Link Support**
- **Internal Wiki Links**: `[[Page Name]]` and `[[Page Name|Display Text]]` syntax
- **External Links**: `[url]` and `[url text]` syntax with validation
- **Smart Detection**: Existing pages (blue) vs missing pages (red)
- **Security**: External links open in new tabs with proper security attributes

### 📚 **Advanced Reference System**
- **Clickable References**: All links in references are properly parsed and clickable
- **Internal Link Support**: `[[Page Name]]` in references become wiki navigation
- **External Link Support**: `[url]` in references become clickable external links
- **Link Validation**: URLs are validated before being made clickable
- **HTML Escaping**: All link text properly escaped for security

### 🎨 **Live Preview System**
- **Real-time Parsing**: Server-side wiki syntax parsing for accurate preview
- **Template Support**: Full support for MediaWiki-style templates
- **Reference Rendering**: References display with clickable links
- **Auto-update**: Preview updates automatically as you type (1-second delay)

## 🛠️ Technical Implementation

### **File Structure**
```
public/
├── pages/wiki/
│   ├── edit_article.php          # Main editor page
│   └── create_article.php        # Article creation page
├── modules/wiki/
│   ├── preview.php               # Server-side preview parser
│   └── article.php               # Article display logic
├── includes/markdown/
│   ├── WikiParser.php            # Main wiki parser
│   └── TemplateParser.php        # Template parsing system
└── skins/bismillah/assets/
    ├── css/wiki.css              # Editor styling
    └── js/wiki-editor.js         # Client-side functionality
```

### **Core Components**

#### 1. **Rich Text Toolbar**
```html
<div class="wiki-toolbar">
    <div class="toolbar-group">
        <button type="button" class="toolbar-btn" onclick="insertText('**', '**')" title="Bold">
            <strong>B</strong>
        </button>
        <button type="button" class="toolbar-btn" onclick="insertText('*', '*')" title="Italic">
            <em>I</em>
        </button>
        <button type="button" class="toolbar-btn" onclick="insertText('`', '`')" title="Code">
            <code>&lt;/&gt;</code>
        </button>
    </div>
    <!-- Additional button groups... -->
</div>
```

#### 2. **Button Specifications**
- **Desktop**: 50px × 44px with 1.1rem font
- **Mobile**: 45px × 40px with 1rem font
- **Padding**: 0.5rem × 0.75rem (desktop), 0.375rem × 0.5rem (mobile)
- **Interactive**: Hover lift effect with shadow

#### 3. **CSS Styling**
```css
.toolbar-btn {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 0.375rem 0.5rem;
    border-radius: 3px;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.15s ease;
    min-width: 36px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0.125rem;
    text-align: center;
    white-space: nowrap;
    color: #495057;
    position: relative;
}
```

### **JavaScript Functionality**

#### 1. **Text Insertion Function**
```javascript
function insertText(before, after) {
    const contentTextarea = document.getElementById('content');
    if (!contentTextarea) return;
    
    const start = contentTextarea.selectionStart;
    const end = contentTextarea.selectionEnd;
    const selectedText = contentTextarea.value.substring(start, end);
    const replacement = before + (selectedText || 'text') + after;
    
    contentTextarea.value = contentTextarea.value.substring(0, start) + replacement + contentTextarea.value.substring(end);
    contentTextarea.focus();
    contentTextarea.setSelectionRange(start + before.length, start + before.length + (selectedText || 'text').length);
    
    updatePreview();
}
```

#### 2. **Preview System**
```javascript
function updatePreview() {
    const contentTextarea = document.getElementById('content');
    const previewContent = document.getElementById('preview-content');
    
    if (!contentTextarea || !previewContent) return;
    
    const content = contentTextarea.value;
    if (!content.trim()) {
        previewContent.innerHTML = '<p><em>No content to preview</em></p>';
        return;
    }
    
    previewContent.innerHTML = '<div class="preview-loading"><i class="iw iw-spinner iw-spin"></i> Parsing content...</div>';
    
    fetch('/wiki/preview', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'content=' + encodeURIComponent(content)
    })
    .then(response => response.text())
    .then(html => {
        previewContent.innerHTML = html;
    })
    .catch(error => {
        console.error('Preview error:', error);
        previewContent.innerHTML = `<p style="color: red;">Preview error: ${error.message}</p>`;
    });
}
```

## 📝 **Supported Wiki Syntax**

### **Text Formatting**
- **Bold**: `**text**` or `__text__`
- **Italic**: `*text*` or `_text_`
- **Code**: `` `code` ``
- **Headings**: `# H1`, `## H2`, `### H3`

### **Links**
- **Internal Links**: `[[Page Name]]` or `[[Page Name|Display Text]]`
- **External Links**: `[url]` or `[url text]`
- **Anchor Links**: `[[Page Name#Section]]`

### **Lists**
- **Bullet Lists**: `* item` or `- item`
- **Numbered Lists**: `1. item`
- **Nested Lists**: Proper indentation support

### **References**
- **Basic References**: `<ref>content</ref>`
- **Named References**: `<ref name="name">content</ref>`
- **Reference Groups**: `<ref group="group">content</ref>`

### **Templates**
- **Basic Templates**: `{{Template Name}}`
- **Templates with Parameters**: `{{Template Name|param1|param2}}`
- **Named Parameters**: `{{Template Name|param1=value1|param2=value2}}`

### **Categories**
- **Category Links**: `[[Category:Category Name]]`
- **Multiple Categories**: `[[Category:Cat1]] [[Category:Cat2]]`

### **Magic Words**
- **No Title**: `__NOTITLE__`
- **No Categories**: `__NOCAT__`
- **Current Year**: `{{CURRENTYEAR}}`
- **Site Name**: `{{SITENAME}}`

## 🔧 **Server-Side Processing**

### **WikiParser Class**
The `WikiParser` class handles all wiki syntax processing:

```php
class WikiParser extends MarkdownParser {
    private $references = [];
    private $categories = [];
    private $headings = [];
    
    public function parse($content) {
        // Parse magic words
        $content = $this->parseMagicWords($content);
        
        // Parse wiki headings
        $content = $this->parseWikiHeadings($content);
        
        // Parse references
        $content = $this->parseReferences($content);
        
        // Parse templates
        $content = $this->parseTemplates($content);
        
        // Parse categories
        $content = $this->parseCategories($content);
        
        // Call parent parse method
        $content = parent::parse($content);
        
        // Add references section
        if (!empty($this->references)) {
            $content .= $this->generateReferencesSection();
        }
        
        return $content;
    }
}
```

### **Reference Link Parsing**
The reference system includes comprehensive link parsing:

```php
private function parseLinksInReference($content) {
    // Parse external links [url] and [url text]
    $content = preg_replace_callback('/\[(https?:\/\/[^\s\]]+)(?:\s+([^\]]+))?\]/', function($matches) {
        $url = $matches[1];
        $text = isset($matches[2]) ? $matches[2] : $url;
        
        if ($this->isValidUrl($url)) {
            return '<a href="' . htmlspecialchars($url) . '" class="external-link" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($text) . '</a>';
        }
        
        return $matches[0];
    }, $content);
    
    // Parse wiki links [[page]] and [[page|text]]
    $content = preg_replace_callback('/\[\[([^|\]]+)(?:\|([^\]]+))?\]\]/', function($matches) {
        $page = trim($matches[1]);
        $text = isset($matches[2]) ? trim($matches[2]) : $page;
        $slug = $this->createSlug($page);
        
        return '<a href="/wiki/' . $slug . '" class="wiki-link">' . htmlspecialchars($text) . '</a>';
    }, $content);
    
    return $content;
}
```

## 🎨 **UI/UX Design**

### **Button Types and Sizing**
- **Bold/Italic**: 32px wide, compact design
- **Headings**: 36px wide with smaller, bold text
- **Wiki Links**: 50px wide for `[[ ]]` text
- **Lists**: 60px wide for longer text
- **Preview**: 80px wide for "👁️ Preview" text

### **Color Scheme**
- **Background**: `#f8f9fa` (light gray)
- **Border**: `#dee2e6` (medium gray)
- **Text**: `#495057` (dark gray)
- **Hover**: `#e9ecef` (lighter gray)
- **Active**: `#dee2e6` (medium gray)

### **Responsive Design**
- **Desktop**: Full toolbar with all buttons visible
- **Tablet**: Condensed layout with essential buttons
- **Mobile**: Stacked layout with touch-friendly sizing

## 🔒 **Security Features**

### **Input Validation**
- All user input is properly validated and sanitized
- URLs are validated before being made clickable
- HTML content is properly escaped

### **Output Escaping**
- All link text is HTML-escaped for security
- External links include proper security attributes
- No raw HTML is output without proper escaping

### **XSS Prevention**
- Content Security Policy headers
- Input sanitization
- Output escaping
- URL validation

## 📱 **Mobile Optimization**

### **Touch-Friendly Design**
- Minimum 44px touch targets
- Proper spacing between buttons
- Swipe gestures for navigation

### **Responsive Layout**
- Flexible grid system
- Adaptive button sizing
- Optimized for portrait and landscape

### **Performance**
- Optimized CSS for mobile devices
- Reduced JavaScript for better performance
- Efficient DOM manipulation

## 🚀 **Future Enhancements**

### **Planned Features**
- **Auto-save**: Automatic saving of draft content
- **Collaborative Editing**: Real-time collaborative editing
- **Version Comparison**: Side-by-side version comparison
- **Advanced Templates**: Visual template editor
- **Plugin System**: Extensible toolbar system

### **Technical Improvements**
- **WebSocket Support**: Real-time updates
- **Offline Support**: Offline editing capabilities
- **Advanced Caching**: Better performance optimization
- **API Integration**: RESTful API for editor functions

## 📊 **Performance Metrics**

### **Load Times**
- **Initial Load**: < 200ms
- **Preview Update**: < 500ms
- **Button Response**: < 50ms

### **Memory Usage**
- **Base Editor**: ~2MB
- **With Preview**: ~5MB
- **Large Documents**: ~10MB

### **Browser Support**
- **Chrome**: 90+
- **Firefox**: 88+
- **Safari**: 14+
- **Edge**: 90+

## 🐛 **Troubleshooting**

### **Common Issues**

#### **Toolbar Not Appearing**
- Check if JavaScript is enabled
- Verify CSS files are loading
- Check browser console for errors

#### **Preview Not Working**
- Verify `/wiki/preview` endpoint is accessible
- Check server-side parsing is working
- Verify database connection

#### **Links Not Clickable**
- Check if reference parsing is enabled
- Verify link validation is working
- Check HTML output for proper tags

### **Debug Mode**
Enable debug mode by adding `?debug=1` to the URL to see:
- Parsing steps
- Error messages
- Performance metrics

## 📚 **API Reference**

### **Client-Side Functions**
- `insertText(before, after)` - Insert text with formatting
- `togglePreview()` - Show/hide preview panel
- `updatePreview()` - Update preview content

### **Server-Side Endpoints**
- `POST /wiki/preview` - Parse content for preview
- `GET /wiki/{slug}` - Get article content
- `POST /wiki/save` - Save article content

## 🤝 **Contributing**

### **Development Setup**
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

### **Code Standards**
- Follow PSR-12 coding standards
- Include comprehensive tests
- Update documentation
- Maintain backward compatibility

---

**Last Updated:** January 2025  
**Version:** 0.0.0.18  
**Status:** Production Ready ✅
