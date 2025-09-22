# SEO Extension for MuslimWiki

A comprehensive SEO extension that provides meta tags, Open Graph, Twitter Cards, and structured data for better search engine optimization.

## Features

- **Meta Tags Generation**: Automatic generation of title, description, keywords, and other meta tags
- **Open Graph Support**: Facebook and other social media platform optimization
- **Twitter Cards**: Enhanced Twitter sharing with rich cards
- **Structured Data**: JSON-LD structured data for better search engine understanding
- **Template System**: Easy-to-use `{{#seo:|...}}` template for adding SEO metadata
- **Debug Tools**: Built-in debugging tools for SEO validation
- **Responsive Design**: Mobile-friendly SEO implementation
- **Cache Support**: Performance optimization with caching

## Installation

1. Copy the extension files to `/public/extensions/seo/`
2. Include the extension in your wiki system
3. Configure the settings in `config.php`
4. Start using the SEO template in your articles

## Usage

### Basic SEO Template

```wiki
{{#seo:|title=Page Title|description=Page description|keywords=keyword1, keyword2}}
```

### Advanced SEO Template

```wiki
{{#seo:|title=Muslims|title_mode=append|keywords=Islam, Muhammad, Quran, Allah, Five Pillars of Islam, Sunni, Shi'a, Sufism, Mecca, Medina, Hajj, Ramadan, Eid al-Fitr, Eid al-Adha, Mosque, Imam, Caliphate, Islamic Golden Age, Sharia, Jihad, Prophet, Sahaba, Hadith, Tawhid, Zakat, Salah, Sawm, Islamic art, Islamic architecture, Islamic law, Islamic finance, Islamic calendar, Islamic education, Islamic philosophy, Islamic science, Islamic culture, Islamic history, Islamic civilization, Islamic theology, Islamic mysticism, Islamic literature, Islamic banking, Islamic ethics, Islamic festivals, Islamic rituals, Islamic symbols, Islamic clothing, Islamic holidays, Islamic traditions, Islamic countries, Islamic leaders, Islamic scholars, Islamic movements, Islamic reform, Islamic revival, Islamic sects, Islamic unity, Islamic diversity.|description=Explore the comprehensive Muslims wiki page to learn about Islam, its core beliefs, practices, and history. Discover the Five Pillars of Islam, the life of Prophet Muhammad, the Quran, and the diversity within the Muslim community, including Sunni, Shi'a, and Sufi traditions. Delve into Islamic history, contributions to science and art, Sharia law, and contemporary issues. Perfect for understanding the global impact and cultural richness of Muslims worldwide.|site_name=MuslimWiki|locale=en_EN|type=website|modified_time={{REVISIONYEAR}}-{{REVISIONMONTH}}-{{REVISIONDAY2}}|published_time=2025-09-22}}
```

## Template Parameters

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

## Configuration

Edit `config.php` to customize the extension behavior:

```php
return [
    'default_site_name' => 'MuslimWiki',
    'default_locale' => 'en_EN',
    'enable_open_graph' => true,
    'enable_twitter_cards' => true,
    'enable_structured_data' => true,
    // ... more options
];
```

## Debug Mode

Enable debug mode by adding `?seo_debug=1` to any URL to see SEO information:

```
https://yoursite.com/article?seo_debug=1
```

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
<meta property="og:url" content="https://yoursite.com/page">
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
  "url": "https://yoursite.com/page",
  "publisher": {
    "@type": "Organization",
    "name": "MuslimWiki"
  }
}
```

## Best Practices

1. **Always include title and description** in your SEO templates
2. **Keep titles under 60 characters** for optimal display
3. **Keep descriptions under 160 characters** for search results
4. **Use relevant keywords** that match your content
5. **Include social media images** for better sharing
6. **Set proper publication and modification dates**
7. **Use canonical URLs** to avoid duplicate content issues

## Troubleshooting

### Common Issues

1. **SEO template not working**: Check that the extension is properly loaded
2. **Meta tags not appearing**: Verify the template syntax is correct
3. **Debug panel not showing**: Add `?seo_debug=1` to the URL
4. **Structured data errors**: Validate JSON-LD syntax

### Debug Tools

- Use the debug panel to inspect generated meta tags
- Check browser developer tools for console errors
- Validate structured data with Google's Rich Results Test
- Test Open Graph with Facebook's Sharing Debugger

## Support

For issues and feature requests, please contact the MuslimWiki development team.

## License

This extension is part of the MuslimWiki project and follows the same license terms.

