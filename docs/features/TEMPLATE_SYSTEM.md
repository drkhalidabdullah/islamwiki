# Template System Documentation

## Overview

The IslamWiki Template System provides comprehensive support for MediaWiki-style templates, enabling users to create reusable content blocks, complex conditional logic, and dynamic content generation. The system supports advanced features like parser functions, switch statements, and template recursion protection.

## 🎯 Current Version: 0.0.0.18

**Last Updated:** January 2025  
**Status:** Production Ready ✅

## 🚀 Key Features

### 📝 **Template Types**
- **Basic Templates**: Simple parameter-based templates
- **Conditional Templates**: Templates with if/else logic
- **Switch Templates**: Complex switch statement templates
- **Parser Function Templates**: Templates using parser functions
- **Recursive Templates**: Templates that call other templates
- **Protection Templates**: Page protection and status templates

### 🔧 **Advanced Features**
- **Parameter Support**: Named and numbered parameters
- **Default Values**: Fallback values for missing parameters
- **Conditional Logic**: If/else and switch statements
- **Parser Functions**: Built-in functions like `{{lc:}}`, `{{uc:}}`
- **Magic Words**: Support for `{{PAGENAME}}`, `{{SITENAME}}`, etc.
- **Recursion Protection**: Prevents infinite template loops

### 🎨 **Template Categories**
- **Navigation Templates**: Sidebars, navigation boxes
- **Information Templates**: Infoboxes, fact boxes
- **Protection Templates**: Page protection indicators
- **Citation Templates**: Reference and citation formats
- **Utility Templates**: Helper templates for common tasks

## 🛠️ Technical Implementation

### **File Structure**
```
public/
├── includes/markdown/
│   └── TemplateParser.php        # Main template parsing engine
├── modules/wiki/
│   ├── template_preview.php      # Template preview API
│   └── create_template.php       # Template creation interface
├── pages/wiki/
│   ├── create_template.php       # Template creation page
│   └── edit_template.php         # Template editing page
└── api/
    └── template_preview.php      # Template preview endpoint
```

### **Core Components**

#### 1. **TemplateParser Class**
```php
class TemplateParser {
    private $pdo;
    private $templateCache = [];
    private $recursionDepth = 0;
    private $maxRecursion = 3;
    
    public function parseTemplates($content) {
        // Parse template calls in content
        $pattern = '/\{\{([^}]+)\}\}/';
        return preg_replace_callback($pattern, function($matches) {
            return $this->parseTemplate($matches[1]);
        }, $content);
    }
    
    private function parseTemplate($templateName) {
        // Handle special templates
        if ($this->isSpecialTemplate($templateName)) {
            return $this->parseSpecialTemplate($templateName);
        }
        
        // Parse regular template
        return $this->parseRegularTemplate($templateName);
    }
}
```

#### 2. **Template Database Schema**
```sql
CREATE TABLE wiki_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    description TEXT,
    category VARCHAR(100),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

#### 3. **Template Preview API**
```php
// POST /api/template_preview.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $templateName = $_POST['name'] ?? '';
    $templateContent = $_POST['content'] ?? '';
    
    $parser = new TemplateParser($pdo);
    $preview = $parser->parseTemplateContent($templateName, $templateContent);
    
    echo $preview;
}
```

## 📝 **Supported Template Syntax**

### **Basic Templates**
```wiki
{{Template Name}}
{{Template Name|param1|param2}}
{{Template Name|param1=value1|param2=value2}}
```

### **Conditional Templates**
```wiki
{{#if:condition|true text|false text}}
{{#ifeq:param1|param2|equal text|not equal text}}
{{#ifexist:Page Name|exists text|not exists text}}
```

### **Switch Templates**
```wiki
{{#switch:value
|case1=result1
|case2=result2
|default=default result
}}
```

### **Parser Functions**
```wiki
{{lc:text}}          <!-- Lowercase -->
{{uc:text}}          <!-- Uppercase -->
{{PAGENAME}}         <!-- Current page name -->
{{SITENAME}}         <!-- Site name -->
{{CURRENTYEAR}}      <!-- Current year -->
```

### **Parameter Handling**
```wiki
{{{param1|default value}}}
{{{param2|{{{param1|fallback}}}}}}
{{{param3|}}}
```

## 🎨 **Template Categories**

### **1. Navigation Templates**

#### **Sidebar Template**
```wiki
<div class="sidebar-template {{#if:class|{{{class}}}}">
    <div class="sidebar-header">
        <h3>{{{title|{{{1}}}}}}</h3>
    </div>
    <div class="sidebar-content">
        {{{content|{{{2}}}}}}
    </div>
</div>
```

#### **Navigation Box**
```wiki
<div class="navbox">
    <div class="navbox-title">{{{title}}}</div>
    <div class="navbox-content">
        {{{content}}}
    </div>
</div>
```

### **2. Information Templates**

#### **Infobox Template**
```wiki
<div class="infobox">
    <div class="infobox-header">{{{title}}}</div>
    <div class="infobox-image">{{{image}}}</div>
    <div class="infobox-content">
        <table>
            <tr><td>Field 1:</td><td>{{{field1}}}</td></tr>
            <tr><td>Field 2:</td><td>{{{field2}}}</td></tr>
        </table>
    </div>
</div>
```

#### **Fact Box**
```wiki
<div class="factbox">
    <div class="factbox-title">{{{title}}}</div>
    <div class="factbox-content">
        {{{content}}}
    </div>
</div>
```

### **3. Protection Templates**

#### **Page Protection**
```wiki
{{#if:{{{1}}}
|<div class="protection-template {{#switch:{{{1}}}
|semi=pp-semi
|move=pp-move
|full=pp-full
|default=pp-semi
}}">
    <div class="protection-banner">
        {{#switch:{{{1}}}
        |semi=This page is semi-protected
        |move=This page is move-protected
        |full=This page is fully protected
        |default=This page is protected
        }}
    </div>
</div>
|}}
```

#### **Good Article Template**
```wiki
<div class="good-article">
    <div class="good-article-icon">⭐</div>
    <div class="good-article-text">Good Article</div>
</div>
```

### **4. Citation Templates**

#### **Quote Template**
```wiki
<blockquote class="quote">
    <p>{{{quote}}}</p>
    <footer>
        <cite>{{{author|{{{1}}}}}}</cite>
        {{#if:{{{source|}}}}
        <span class="quote-source">{{{source}}}</span>
        {{/if}}
    </footer>
</blockquote>
```

#### **Reference Template**
```wiki
<ref>{{{content}}}</ref>
```

## 🔧 **Advanced Template Features**

### **1. Conditional Logic**
```php
private function parseConditional($content) {
    // Parse #if statements
    $content = preg_replace_callback('/\{\{#if:([^|]+)\|([^|]*)\|([^}]*)\}\}/', function($matches) {
        $condition = trim($matches[1]);
        $trueText = $matches[2];
        $falseText = $matches[3];
        
        if (!empty($condition)) {
            return $trueText;
        } else {
            return $falseText;
        }
    }, $content);
    
    return $content;
}
```

### **2. Switch Statements**
```php
private function parseSwitchStatements($content) {
    $pattern = '/\{\{#switch:([^|]+)(.*?)\}\}/s';
    
    return preg_replace_callback($pattern, function($matches) {
        $value = trim($matches[1]);
        $cases = $matches[2];
        
        // Parse cases
        $casePattern = '/\|([^=]+)=([^|]+)/';
        preg_match_all($casePattern, $cases, $caseMatches, PREG_SET_ORDER);
        
        foreach ($caseMatches as $case) {
            $caseValue = trim($case[1]);
            $caseResult = trim($case[2]);
            
            if ($caseValue === $value || $caseValue === 'default') {
                return $caseResult;
            }
        }
        
        return '';
    }, $content);
}
```

### **3. Parser Functions**
```php
private function parseParserFunctions($content) {
    // Parse lc (lowercase)
    $content = preg_replace_callback('/\{\{lc:([^}]+)\}\}/', function($matches) {
        return strtolower(trim($matches[1]));
    }, $content);
    
    // Parse uc (uppercase)
    $content = preg_replace_callback('/\{\{uc:([^}]+)\}\}/', function($matches) {
        return strtoupper(trim($matches[1]));
    }, $content);
    
    return $content;
}
```

### **4. Magic Words**
```php
private function parseMagicWords($content) {
    $magicWords = [
        'PAGENAME' => $this->getCurrentPageName(),
        'SITENAME' => $this->getSiteName(),
        'CURRENTYEAR' => date('Y'),
        'CURRENTMONTH' => date('F'),
        'CURRENTDAY' => date('j')
    ];
    
    foreach ($magicWords as $word => $value) {
        $content = str_replace('{{' . $word . '}}', $value, $content);
    }
    
    return $content;
}
```

## 🎨 **Template Styling**

### **CSS Classes**
```css
/* Template base styles */
.template {
    margin: 1rem 0;
    padding: 0.5rem;
    border-radius: 4px;
}

/* Sidebar templates */
.sidebar-template {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 1rem;
    margin: 1rem 0;
}

.sidebar-template .sidebar-header {
    font-weight: bold;
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #dee2e6;
}

/* Infobox templates */
.infobox {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 1rem;
    margin: 1rem 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.infobox .infobox-header {
    font-weight: bold;
    font-size: 1.2rem;
    margin-bottom: 1rem;
    color: #2c3e50;
}

/* Protection templates */
.protection-template {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    padding: 0.5rem;
    margin: 1rem 0;
}

.protection-template.pp-semi {
    background: #fff3cd;
    border-color: #ffeaa7;
}

.protection-template.pp-move {
    background: #d1ecf1;
    border-color: #bee5eb;
}

.protection-template.pp-full {
    background: #f8d7da;
    border-color: #f5c6cb;
}

/* Quote templates */
.quote {
    border-left: 4px solid #007bff;
    padding-left: 1rem;
    margin: 1rem 0;
    font-style: italic;
}

.quote footer {
    margin-top: 0.5rem;
    font-style: normal;
    font-size: 0.9rem;
    color: #6c757d;
}
```

## 🚀 **Template Management**

### **Creating Templates**
```php
// Template creation form
<form method="POST" action="/wiki/create_template">
    <div class="form-group">
        <label for="name">Template Name</label>
        <input type="text" id="name" name="name" required>
    </div>
    
    <div class="form-group">
        <label for="content">Template Content</label>
        <textarea id="content" name="content" rows="10" required></textarea>
    </div>
    
    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3"></textarea>
    </div>
    
    <div class="form-group">
        <label for="category">Category</label>
        <select id="category" name="category">
            <option value="navigation">Navigation</option>
            <option value="information">Information</option>
            <option value="protection">Protection</option>
            <option value="citation">Citation</option>
        </select>
    </div>
    
    <button type="submit" class="btn btn-primary">Create Template</button>
</form>
```

### **Template Preview**
```javascript
// Real-time template preview
function updatePreview() {
    const templateName = document.getElementById('name').value;
    const templateContent = document.getElementById('content').value;
    
    fetch('/api/template_preview.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `name=${encodeURIComponent(templateName)}&content=${encodeURIComponent(templateContent)}`
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('preview').innerHTML = html;
    });
}
```

## 🔒 **Security Features**

### **Template Validation**
```php
private function validateTemplate($name, $content) {
    // Check for dangerous HTML
    if (preg_match('/<script|javascript:|on\w+=/i', $content)) {
        throw new Exception('Template contains potentially dangerous content');
    }
    
    // Check for SQL injection attempts
    if (preg_match('/union|select|insert|update|delete|drop/i', $content)) {
        throw new Exception('Template contains potentially dangerous SQL');
    }
    
    // Check template name format
    if (!preg_match('/^[A-Za-z0-9_\s-]+$/', $name)) {
        throw new Exception('Invalid template name format');
    }
    
    return true;
}
```

### **Recursion Protection**
```php
private function checkRecursion($templateName, $content) {
    if ($this->recursionDepth >= $this->maxRecursion) {
        throw new Exception("Template recursion limit exceeded: {$templateName}");
    }
    
    $this->recursionDepth++;
    
    try {
        $result = $this->parseTemplateContent($templateName, $content);
    } finally {
        $this->recursionDepth--;
    }
    
    return $result;
}
```

## 📊 **Performance Optimization**

### **Template Caching**
```php
private function getTemplate($name) {
    if (isset($this->templateCache[$name])) {
        return $this->templateCache[$name];
    }
    
    $template = $this->db->query(
        "SELECT * FROM wiki_templates WHERE name = ?",
        [$name]
    )->fetch();
    
    if ($template) {
        $this->templateCache[$name] = $template;
    }
    
    return $template;
}
```

### **Lazy Loading**
```php
private function loadTemplate($name) {
    // Only load template when needed
    if (!isset($this->loadedTemplates[$name])) {
        $this->loadedTemplates[$name] = $this->getTemplate($name);
    }
    
    return $this->loadedTemplates[$name];
}
```

## 🧪 **Testing**

### **Unit Tests**
```php
class TemplateParserTest extends PHPUnit\Framework\TestCase {
    public function testBasicTemplate() {
        $parser = new TemplateParser($this->pdo);
        $result = $parser->parseTemplate('Test Template');
        $this->assertStringContainsString('Test Template', $result);
    }
    
    public function testConditionalTemplate() {
        $parser = new TemplateParser($this->pdo);
        $content = '{{#if:test|true|false}}';
        $result = $parser->parseTemplateContent('Test', $content);
        $this->assertEquals('true', $result);
    }
    
    public function testRecursionProtection() {
        $parser = new TemplateParser($this->pdo);
        $this->expectException(Exception::class);
        $parser->parseTemplate('Recursive Template');
    }
}
```

## 📚 **Usage Examples**

### **Creating a Sidebar Template**
```wiki
<!-- Template: Sidebar Islam -->
<div class="sidebar-template islam-sidebar">
    <div class="sidebar-header">
        <h3>Islam</h3>
    </div>
    <div class="sidebar-content">
        <h4>Core Beliefs (Aqidah)</h4>
        <ul>
            <li>Belief in Allah (The One God)</li>
            <li>Belief in His Angels</li>
            <li>Belief in His Books</li>
            <li>Belief in His Messengers</li>
            <li>Belief in the Day of Judgment</li>
            <li>Belief in Divine Decree</li>
        </ul>
        
        <h4>Core Practices (Ibadah)</h4>
        <ul>
            <li>Shahadah (Declaration of Faith)</li>
            <li>Salah (Prayer)</li>
            <li>Zakah (Charity)</li>
            <li>Sawm (Fasting)</li>
            <li>Hajj (Pilgrimage)</li>
        </ul>
    </div>
</div>
```

### **Using Templates in Articles**
```wiki
{{Sidebar Islam}}

'''Islam''' is the final and complete way of life revealed by [[Allah]] for all of humanity.

{{pp-semi-indef}}
{{good article}}

== Core Beliefs ==
The Six Pillars of Faith form the foundation of Islamic belief...

== Core Practices ==
The Five Pillars of Islam are the fundamental acts of worship...
```

## 🔧 **Configuration**

### **Template Settings**
```php
// config/templates.php
return [
    'max_recursion' => 3,
    'cache_templates' => true,
    'allowed_html' => [
        'div', 'span', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'table', 'tr', 'td', 'th',
        'a', 'strong', 'em', 'code', 'blockquote'
    ],
    'forbidden_patterns' => [
        '/<script/i',
        '/javascript:/i',
        '/on\w+=/i'
    ]
];
```

## 🐛 **Troubleshooting**

### **Common Issues**

#### **Template Not Rendering**
- Check if template exists in database
- Verify template syntax is correct
- Check for recursion loops

#### **Parameters Not Working**
- Verify parameter syntax `{{{param|default}}}`
- Check for proper parameter naming
- Ensure default values are set

#### **Conditional Logic Issues**
- Check if/else syntax is correct
- Verify condition values
- Check for proper nesting

### **Debug Mode**
Enable debug mode to see:
- Template parsing steps
- Parameter resolution
- Recursion depth
- Error messages

---

**Last Updated:** January 2025  
**Version:** 0.0.0.18  
**Status:** Production Ready ✅
