# SEO Extension Integration Guide

## Quick Start

### 1. Test the Extension
Visit `/public/extensions/seo/test.php` to see the SEO extension in action.

### 2. Integrate with Your Wiki System

#### Option A: Simple Integration
Add this to your main header file (e.g., `public/includes/header.php`):

```php
// Include SEO extension
require_once __DIR__ . '/../extensions/seo/seo_integration.php';

// Parse SEO template from content (if you have content)
if (isset($content)) {
    $content = parse_seo_template($content);
}

// Generate meta tags in the <head> section
echo generate_seo_meta_tags();
```

#### Option B: Advanced Integration
For more control, include the extension files directly:

```php
// Set the wiki system loaded flag
$GLOBALS['wiki_system_loaded'] = true;

// Include the SEO extension
require_once __DIR__ . '/../extensions/seo/extension.php';

// Initialize the SEO extension
$seo_extension = new SEOExtension();

// Parse SEO template from content
$seo_extension->parseSEOTemplate($content);

// Generate meta tags
$meta_tags = $seo_extension->generateMetaTags();
$structured_data = $seo_extension->generateStructuredData();

// Output in <head>
echo $meta_tags . "\n    " . $structured_data;
```

### 3. Use in Your Articles

Add the SEO template to the top of your wiki articles:

```wiki
{{#seo:|title=Page Title|description=Page description|keywords=keyword1, keyword2}}
```

## Template Syntax

### Basic Template
```wiki
{{#seo:|title=Your Page Title|description=Your page description|keywords=keyword1, keyword2}}
```

### Advanced Template
```wiki
{{#seo:|title=Muslims|title_mode=append|keywords=Islam, Muhammad, Quran, Allah|description=Learn about Muslims and Islam|site_name=MuslimWiki|locale=en_EN|type=website|published_time=2025-01-01|modified_time={{REVISIONYEAR}}-{{REVISIONMONTH}}-{{REVISIONDAY2}}}}
```

## Available Parameters

| Parameter | Description | Required | Default |
|-----------|-------------|----------|---------|
| `title` | Page title | Yes | - |
| `title_mode` | Title mode: append, prepend, replace | No | append |
| `description` | Meta description | Yes | - |
| `keywords` | Comma-separated keywords | No | - |
| `site_name` | Site name | No | MuslimWiki |
| `locale` | Locale code | No | en_EN |
| `type` | Content type | No | website |
| `url` | Canonical URL | No | Current URL |
| `image` | Social media image URL | No | - |
| `published_time` | Publication date (YYYY-MM-DD) | No | - |
| `modified_time` | Last modified date (YYYY-MM-DD) | No | - |
| `author` | Article author | No | - |
| `section` | Article section/category | No | - |

## Template Variables

The extension supports dynamic template variables:

- `{{REVISIONYEAR}}` - Current year
- `{{REVISIONMONTH}}` - Current month (01-12)
- `{{REVISIONDAY2}}` - Current day (01-31)

## Generated Output

The extension generates:

### Meta Tags
```html
<title>Page Title - MuslimWiki</title>
<meta name="description" content="Page description">
<meta name="keywords" content="keyword1, keyword2">
<meta name="robots" content="index, follow">
```

### Open Graph Tags
```html
<meta property="og:type" content="website">
<meta property="og:site_name" content="MuslimWiki">
<meta property="og:title" content="Page Title">
<meta property="og:description" content="Page description">
```

### Twitter Card Tags
```html
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@MuslimWiki">
<meta name="twitter:title" content="Page Title">
<meta name="twitter:description" content="Page description">
```

### Structured Data
```json
{
  "@context": "https://schema.org",
  "@type": "Article",
  "name": "Page Title",
  "description": "Page description",
  "url": "https://yoursite.com/page"
}
```

## Troubleshooting

### Common Issues

1. **"Direct access not allowed" error**: Make sure to set `$GLOBALS['wiki_system_loaded'] = true;` before including the extension files.

2. **Template not working**: Check that the template syntax is correct and the extension is properly loaded.

3. **Meta tags not appearing**: Verify that the `generate_seo_meta_tags()` function is called in the `<head>` section.

### Debug Mode

Add `?seo_debug=1` to any URL to see SEO information (if debug mode is enabled).

## Support

For issues and questions, check the README.md file or contact the development team.

