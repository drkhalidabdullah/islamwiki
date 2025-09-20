# Reference System Documentation

## Overview

The IslamWiki Reference System provides comprehensive support for academic-style references with clickable internal and external links. The system automatically parses links within reference content and makes them clickable, enhancing the user experience and providing proper navigation.

## 🎯 Current Version: 0.0.0.17

**Last Updated:** January 2025  
**Status:** Production Ready ✅

## 🚀 Key Features

### 🔗 **Clickable Reference Links**
- **Internal Links**: `[[Page Name]]` in references become clickable wiki links
- **External Links**: `[url]` in references become clickable external links
- **Link Validation**: URLs are validated before being made clickable
- **Security**: External links open in new tabs with proper security attributes
- **HTML Escaping**: All link text properly escaped for security

### 📚 **Reference Management**
- **Automatic Numbering**: References are automatically numbered
- **In-text Citations**: Clickable reference numbers in article text
- **Reference Section**: Auto-generated references section at bottom of articles
- **Link Parsing**: Comprehensive parsing of links within reference content
- **Slug Generation**: Internal links use proper slug generation for navigation

### 🎨 **Visual Design**
- **Professional Styling**: Clean, academic-style reference formatting
- **Link Styling**: Distinct styling for internal vs external links
- **Hover Effects**: Visual feedback for interactive elements
- **Responsive Design**: Works on all device sizes

## 🛠️ Technical Implementation

### **File Structure**
```
public/
├── includes/markdown/
│   └── WikiParser.php            # Main reference parsing logic
├── modules/wiki/
│   ├── article.php               # Article display with references
│   └── preview.php               # Preview with reference parsing
└── skins/bismillah/assets/
    └── css/wiki.css              # Reference styling
```

### **Core Components**

#### 1. **Reference Parsing in WikiParser**
```php
private function parseReferences($content) {
    $pattern = '/<ref>(.*?)<\/ref>/s';
    
    return preg_replace_callback($pattern, function($matches) {
        $ref_content = $matches[1];
        $ref_id = count($this->references) + 1;
        
        $this->references[] = [
            'id' => $ref_id,
            'content' => $ref_content
        ];
        
        return '<sup><a href="#ref' . $ref_id . '" class="reference-link">[' . $ref_id . ']</a></sup>';
    }, $content);
}
```

#### 2. **Reference Section Generation**
```php
private function generateReferencesSection() {
    if (empty($this->references)) {
        return '';
    }
    
    $html = '<div class="wiki-references">';
    $html .= '<h2>References</h2>';
    $html .= '<ol>';
    
    foreach ($this->references as $ref) {
        // Parse links in reference content
        $parsed_content = $this->parseLinksInReference($ref['content']);
        $html .= '<li id="ref' . $ref['id'] . '">' . $parsed_content . '</li>';
    }
    
    $html .= '</ol>';
    $html .= '</div>';
    
    return $html;
}
```

#### 3. **Link Parsing in References**
```php
private function parseLinksInReference($content) {
    // Parse external links [url] and [url text]
    $content = preg_replace_callback('/\[(https?:\/\/[^\s\]]+)(?:\s+([^\]]+))?\]/', function($matches) {
        $url = $matches[1];
        $text = isset($matches[2]) ? $matches[2] : $url;
        
        // Validate URL
        if ($this->isValidUrl($url)) {
            return '<a href="' . htmlspecialchars($url) . '" class="external-link" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($text) . '</a>';
        }
        
        return $matches[0]; // Return original if invalid
    }, $content);
    
    // Parse wiki links [[page]] and [[page|text]]
    $content = preg_replace_callback('/\[\[([^|\]]+)(?:\|([^\]]+))?\]\]/', function($matches) {
        $page = trim($matches[1]);
        $text = isset($matches[2]) ? trim($matches[2]) : $page;
        
        // Create slug from page name
        $slug = $this->createSlug($page);
        
        return '<a href="/wiki/' . $slug . '" class="wiki-link">' . htmlspecialchars($text) . '</a>';
    }, $content);
    
    return $content;
}
```

## 📝 **Supported Reference Syntax**

### **Basic References**
```html
<ref>This is a basic reference</ref>
```

### **References with Internal Links**
```html
<ref>See [[Islam]] for more information</ref>
<ref>According to [[Prophet Muhammad|Muhammad]], peace be upon him</ref>
```

### **References with External Links**
```html
<ref>Source: [https://example.com Example Website]</ref>
<ref>For more details, visit [https://islamic-resources.com]</ref>
```

### **References with Mixed Links**
```html
<ref>See [[Quran]] 2:255 and [https://quran.com/2/255 Quran.com]</ref>
<ref>According to [[Hadith]] collections and [https://sunnah.com Sunnah.com]</ref>
```

### **Complex References**
```html
<ref>Multiple sources: [[Islam]], [[Quran]], and [https://islamic-foundation.org Islamic Foundation]</ref>
<ref>See [[Prophet Muhammad|Muhammad]] (peace be upon him) in [https://sahih-bukhari.com Sahih Bukhari]</ref>
```

## 🎨 **Visual Design**

### **CSS Styling**
```css
.wiki-references {
    margin-top: 2rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 4px;
    border-left: 4px solid #007bff;
}

.wiki-references h2 {
    margin-top: 0;
    margin-bottom: 1rem;
    color: #2c3e50;
    font-size: 1.5rem;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 0.5rem;
}

.wiki-references ol {
    margin: 0;
    padding-left: 2rem;
}

.wiki-references li {
    margin-bottom: 0.75rem;
    line-height: 1.6;
}

.reference-link {
    color: #007bff;
    text-decoration: none;
    font-weight: 500;
}

.reference-link:hover {
    text-decoration: underline;
    color: #0056b3;
}

.external-link {
    color: #28a745;
    text-decoration: none;
}

.external-link:hover {
    text-decoration: underline;
    color: #1e7e34;
}

.wiki-link {
    color: #007bff;
    text-decoration: none;
}

.wiki-link:hover {
    text-decoration: underline;
    color: #0056b3;
}
```

### **Link Types and Styling**
- **Internal Links**: Blue color (`#007bff`) for wiki navigation
- **External Links**: Green color (`#28a745`) for external websites
- **Reference Numbers**: Blue color with hover effects
- **Hover Effects**: Underline on hover for better UX

## 🔒 **Security Features**

### **URL Validation**
```php
private function isValidUrl($url) {
    // Allow URLs starting with http:// or https://
    if (preg_match('/^https?:\/\//', $url)) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    // Allow anchor links starting with #
    if (strpos($url, '#') === 0) {
        return true;
    }
    
    return false;
}
```

### **HTML Escaping**
- All link text is HTML-escaped using `htmlspecialchars()`
- URLs are validated before being made clickable
- No raw HTML is output without proper escaping

### **Security Attributes**
- External links include `target="_blank"` and `rel="noopener noreferrer"`
- Internal links use proper slug generation
- All content is properly sanitized

## 📊 **Performance Considerations**

### **Parsing Efficiency**
- References are parsed once during content processing
- Link parsing is optimized with efficient regex patterns
- Caching is implemented for frequently accessed content

### **Memory Usage**
- Reference content is stored in memory during parsing
- Large documents with many references are handled efficiently
- Memory is freed after processing is complete

### **Database Impact**
- No additional database queries for reference parsing
- Link validation is done in-memory
- Slug generation is optimized for performance

## 🧪 **Testing**

### **Unit Tests**
```php
// Test basic reference parsing
public function testBasicReference() {
    $parser = new WikiParser();
    $content = 'This is text with <ref>a reference</ref>.';
    $result = $parser->parse($content);
    
    $this->assertStringContainsString('<sup><a href="#ref1"', $result);
    $this->assertStringContainsString('References</h2>', $result);
}

// Test reference with internal links
public function testReferenceWithInternalLinks() {
    $parser = new WikiParser();
    $content = 'Text with <ref>See [[Islam]] for details</ref>.';
    $result = $parser->parse($content);
    
    $this->assertStringContainsString('<a href="/wiki/islam" class="wiki-link">Islam</a>', $result);
}

// Test reference with external links
public function testReferenceWithExternalLinks() {
    $parser = new WikiParser();
    $content = 'Text with <ref>Source: [https://example.com Example]</ref>.';
    $result = $parser->parse($content);
    
    $this->assertStringContainsString('<a href="https://example.com" class="external-link"', $result);
}
```

### **Integration Tests**
- Test reference parsing in full article context
- Test reference display in different page layouts
- Test reference functionality with various content types

## 🚀 **Usage Examples**

### **Basic Usage**
```html
<!-- In article content -->
This is a statement that needs a reference.<ref>Source: [https://example.com Example Website]</ref>

<!-- Generated output -->
This is a statement that needs a reference.<sup><a href="#ref1" class="reference-link">[1]</a></sup>

<!-- At bottom of article -->
<div class="wiki-references">
    <h2>References</h2>
    <ol>
        <li id="ref1">Source: <a href="https://example.com" class="external-link" target="_blank" rel="noopener noreferrer">Example Website</a></li>
    </ol>
</div>
```

### **Advanced Usage**
```html
<!-- Complex reference with multiple links -->
<ref>See [[Quran]] 2:255, [[Hadith]] collections, and [https://islamic-resources.com Islamic Resources] for more information</ref>

<!-- Generated output -->
<li id="ref1">See <a href="/wiki/quran" class="wiki-link">Quran</a> 2:255, <a href="/wiki/hadith" class="wiki-link">Hadith</a> collections, and <a href="https://islamic-resources.com" class="external-link" target="_blank" rel="noopener noreferrer">Islamic Resources</a> for more information</li>
```

## 🔧 **Configuration**

### **Reference Settings**
- **Auto-numbering**: References are automatically numbered
- **Link Parsing**: Internal and external links are automatically parsed
- **Security**: External links open in new tabs with security attributes
- **Styling**: References use consistent styling across the site

### **Customization Options**
- **Reference Styling**: CSS can be customized for different themes
- **Link Colors**: Internal and external link colors can be modified
- **Reference Format**: Reference numbering and formatting can be adjusted

## 🐛 **Troubleshooting**

### **Common Issues**

#### **References Not Showing**
- Check if reference content is properly formatted
- Verify that `<ref>` tags are properly closed
- Check for HTML parsing errors

#### **Links Not Clickable**
- Verify that link syntax is correct
- Check if URL validation is working
- Ensure proper HTML escaping

#### **Styling Issues**
- Check if CSS files are loading properly
- Verify that CSS classes are applied correctly
- Check for CSS conflicts

### **Debug Mode**
Enable debug mode to see:
- Reference parsing steps
- Link validation results
- HTML output

## 📚 **API Reference**

### **Client-Side Functions**
- Reference parsing is handled server-side
- No client-side JavaScript required for basic functionality
- Preview updates automatically when content changes

### **Server-Side Functions**
- `parseReferences($content)` - Parse references in content
- `generateReferencesSection()` - Generate references section HTML
- `parseLinksInReference($content)` - Parse links within reference content

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

## 🎯 **Future Enhancements**

### **Planned Features**
- **Reference Groups**: Support for grouped references
- **Named References**: Support for named reference targets
- **Reference Templates**: Predefined reference formats
- **Citation Styles**: Support for different citation formats (APA, MLA, etc.)

### **Technical Improvements**
- **Caching**: Better caching for reference parsing
- **Performance**: Optimized parsing for large documents
- **Validation**: Enhanced URL and link validation
- **Accessibility**: Better accessibility features

---

**Last Updated:** January 2025  
**Version:** 0.0.0.17  
**Status:** Production Ready ✅
