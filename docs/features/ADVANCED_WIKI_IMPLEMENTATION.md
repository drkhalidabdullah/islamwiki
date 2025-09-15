# Advanced Wiki Implementation Guide

## Overview

This document outlines the comprehensive implementation of advanced wiki features including enhanced markup syntax, template system, and security measures.

## Implementation Phases

### Phase 1: Enhanced Wiki Syntax ✅ COMPLETED

**Features Implemented:**
- MediaWiki-style tables (`{| |}` syntax)
- Reference system (`<ref>content</ref>`)
- Category parsing (`[[Category:Name]]`)
- Magic words (`{{PAGENAME}}`, `{{CURRENTYEAR}}`, etc.)
- Advanced inline formatting

**Files Created/Modified:**
- `public/includes/markdown/AdvancedWikiParser.php` - Main parser class
- `public/modules/wiki/article.php` - Updated to use new parser

### Phase 2: Advanced Template System ✅ COMPLETED

**Features Implemented:**
- Template namespace support
- Named parameters (`{{param_name|value}}`)
- Conditional logic (`{{#if:condition|true|false}}`)
- Template inheritance and recursion
- Template management interface

**Files Created/Modified:**
- `public/includes/markdown/AdvancedTemplateParser.php` - Template parser
- `public/pages/wiki/manage_templates.php` - Management interface
- `public/skins/bismillah/assets/js/wiki_manage_templates.js` - Frontend logic
- `public/skins/bismillah/assets/css/wiki_manage_templates.css` - Styling

### Phase 3: HTML Security ✅ COMPLETED

**Features Implemented:**
- HTML sanitization
- Dangerous tag removal
- Attribute validation
- Link security (noopener for external links)
- Image validation

**Files Created/Modified:**
- `public/includes/markdown/SecureWikiParser.php` - Security-focused parser

### Phase 4: Database Schema ✅ COMPLETED

**Features Implemented:**
- Enhanced template table structure
- Category system with hierarchy
- Template usage tracking
- Reference management
- Namespace support
- Parser settings

**Files Created/Modified:**
- `database/database_migration_v0.0.0.16_advanced_wiki.sql` - Database migration

## Usage Examples

### Basic Wiki Syntax

```markdown
# Article Title

This is a paragraph with **bold** and *italic* text.

## Table Example
{| class="wikitable"
|-
! Header 1 !! Header 2
|-
| Cell 1 || Cell 2
|-
| Cell 3 || Cell 4
|}

## References
This is a statement with a reference<ref>Reference content here</ref>.

## Categories
[[Category:Main Category]]
[[Category:Sub Category]]
```

### Template Usage

```markdown
{{Infobox
|name=Article Name
|type=Biography
|born=1900
|died=2000
}}

{{Citation
|title=Book Title
|author=Author Name
|year=2023
|publisher=Publisher
}}
```

### Advanced Template Features

```markdown
{{#if:{{PAGENAME}}|Page: {{PAGENAME}}|Unknown Page}}

{{#ifeq:{{CURRENTYEAR}}|2024|This is 2024|This is not 2024}}

{{#foreach:list|Item: {{item}}}}
```

## API Reference

### AdvancedWikiParser Class

```php
$parser = new AdvancedWikiParser();
$html = $parser->parse($markdown_content);

// Get parsed categories
$categories = $parser->getCategories();

// Get parsed references
$references = $parser->getReferences();
```

### AdvancedTemplateParser Class

```php
$template_parser = new AdvancedTemplateParser($pdo);

// Parse template
$html = $template_parser->parseTemplate('TemplateName', [
    'param1' => 'value1',
    'param2' => 'value2'
]);

// Create template
$template_parser->createTemplate('NewTemplate', $content, $description, $parameters);
```

### SecureWikiParser Class

```php
$parser = new SecureWikiParser();
$html = $parser->parse($content); // Automatically sanitized

// Add allowed tags
$parser->addAllowedTag('custom-tag');

// Add allowed attributes
$parser->addAllowedAttribute('data-custom');
```

## Configuration

### Parser Settings

The system uses `wiki_parser_settings` table for configuration:

```sql
-- Enable/disable features
UPDATE wiki_parser_settings SET setting_value = 'true' WHERE setting_name = 'enable_wiki_syntax';
UPDATE wiki_parser_settings SET setting_value = 'true' WHERE setting_name = 'enable_tables';
UPDATE wiki_parser_settings SET setting_value = 'true' WHERE setting_name = 'enable_references';

-- Configure allowed HTML tags
UPDATE wiki_parser_settings 
SET setting_value = '["p", "br", "strong", "em", "a", "img", "table"]' 
WHERE setting_name = 'allowed_html_tags';
```

### Template Management

Access the template management interface at `/pages/wiki/manage_templates.php`

**Features:**
- Create/edit/delete templates
- Template type categorization
- Usage statistics
- Template preview
- Syntax highlighting

## Security Considerations

### HTML Sanitization

The `SecureWikiParser` automatically:
- Removes dangerous tags (`script`, `style`, `iframe`, etc.)
- Strips dangerous attributes (`onclick`, `onload`, etc.)
- Validates URLs in links and images
- Adds security attributes (`rel="noopener"` for external links)

### Template Security

- Maximum recursion depth (configurable, default: 10)
- Parameter validation
- Template existence checks
- Usage tracking for monitoring

### Content Security

- XSS prevention through HTML sanitization
- CSRF protection on forms
- Input validation and sanitization
- SQL injection prevention through prepared statements

## Performance Considerations

### Caching

- Template content is cached in memory during parsing
- Database queries are optimized with proper indexing
- Parser results can be cached for frequently accessed content

### Database Optimization

- Proper indexing on frequently queried columns
- Triggers for automatic count updates
- Efficient query patterns

## Migration Guide

### From Basic Markdown

1. **Backup existing content**
2. **Run database migration**: `database_migration_v0.0.0.16_advanced_wiki.sql`
3. **Update article parsing**: Change from `EnhancedMarkdownParser` to `SecureWikiParser`
4. **Test content rendering**
5. **Update editor interfaces**

### Content Migration

```php
// Example migration script
$articles = $pdo->query("SELECT id, content FROM wiki_articles")->fetchAll();

foreach ($articles as $article) {
    $parser = new SecureWikiParser();
    $new_content = $parser->parse($article['content']);
    
    $stmt = $pdo->prepare("UPDATE wiki_articles SET content = ? WHERE id = ?");
    $stmt->execute([$new_content, $article['id']]);
}
```

## Troubleshooting

### Common Issues

1. **Templates not rendering**
   - Check template exists in database
   - Verify template syntax
   - Check recursion depth limits

2. **HTML not displaying**
   - Check allowed HTML tags setting
   - Verify content is not being over-sanitized
   - Check for malformed HTML

3. **Performance issues**
   - Enable template caching
   - Check database indexes
   - Monitor query performance

### Debug Mode

Enable debug mode in parser settings:

```sql
INSERT INTO wiki_parser_settings (setting_name, setting_value, setting_type) 
VALUES ('debug_mode', 'true', 'boolean');
```

## Future Enhancements

### Planned Features

1. **Visual Editor Integration**
   - WYSIWYG editor with wiki syntax support
   - Real-time preview
   - Template insertion interface

2. **Advanced Templates**
   - Template inheritance
   - Template parameters with types
   - Template documentation system

3. **Content Analysis**
   - Broken link detection
   - Template usage analysis
   - Content quality metrics

4. **Import/Export**
   - MediaWiki import/export
   - Template library sharing
   - Content backup/restore

## Support

For issues or questions:
1. Check this documentation
2. Review error logs
3. Test with minimal content
4. Contact development team

## Version History

- **v0.0.0.16**: Initial advanced wiki implementation
- **v0.0.0.17**: Template management interface
- **v0.0.0.18**: Security enhancements
- **v0.0.0.19**: Performance optimizations
