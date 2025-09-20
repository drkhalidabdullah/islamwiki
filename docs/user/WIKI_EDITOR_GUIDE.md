# Wiki Editor User Guide

## Overview

The IslamWiki Editor is a powerful, professional-grade wiki editing interface that provides users with comprehensive tools for creating and editing wiki content. This guide will help you understand and use all the features of the wiki editor effectively.

## 🎯 Getting Started

### Accessing the Editor

1. **Create New Article**: Navigate to `/create_article` or click "Create Article" in the navigation
2. **Edit Existing Article**: Go to any article and click the "Edit" button
3. **Admin Edit**: Use the admin panel to edit any article

### Editor Interface

The editor consists of three main components:
- **Toolbar**: Rich text formatting tools
- **Text Area**: Main editing area with wiki syntax
- **Preview Panel**: Live preview of your content (toggleable)

## 📝 Rich Text Toolbar

### Text Formatting

#### Bold Text
- **Button**: Click the **B** button
- **Syntax**: `**text**` or `__text__`
- **Example**: `**Important text**` becomes **Important text**

#### Italic Text
- **Button**: Click the *I* button
- **Syntax**: `*text*` or `_text_`
- **Example**: `*Emphasized text*` becomes *Emphasized text*

#### Code
- **Button**: Click the `</>` button
- **Syntax**: `` `code` ``
- **Example**: `` `function_name()` `` becomes `function_name()`

### Headings

#### Heading 1 (H1)
- **Button**: Click the **H1** button
- **Syntax**: `# Heading`
- **Use**: Main article titles

#### Heading 2 (H2)
- **Button**: Click the **H2** button
- **Syntax**: `## Heading`
- **Use**: Major sections

#### Heading 3 (H3)
- **Button**: Click the **H3** button
- **Syntax**: `### Heading`
- **Use**: Subsections

### Links

#### Internal Wiki Links
- **Button**: Click the **[[ ]]** button
- **Syntax**: `[[Page Name]]` or `[[Page Name|Display Text]]`
- **Examples**:
  - `[[Islam]]` → Links to Islam article
  - `[[Prophet Muhammad|Muhammad]]` → Links to Prophet Muhammad article but displays "Muhammad"

#### External Links
- **Button**: Click the **🔗** button
- **Syntax**: `[url]` or `[url text]`
- **Examples**:
  - `[https://example.com]` → Clickable link
  - `[https://example.com Example Website]` → Link with custom text

### Lists

#### Bullet Lists
- **Button**: Click the **• List** button
- **Syntax**: `* item` or `- item`
- **Example**:
  ```markdown
  * First item
  * Second item
  * Third item
  ```

#### Numbered Lists
- **Button**: Click the **1. List** button
- **Syntax**: `1. item`
- **Example**:
  ```markdown
  1. First step
  2. Second step
  3. Third step
  ```

#### Quotes
- **Button**: Click the **" Quote** button
- **Syntax**: `> quote`
- **Example**:
  ```markdown
  > This is a quote from someone important.
  ```

### Preview
- **Button**: Click the **👁️ Preview** button
- **Function**: Toggle live preview panel
- **Features**: Real-time rendering of your content

## 🔗 Reference System

### Creating References

#### Basic References
```html
<ref>This is a basic reference</ref>
```

#### References with Internal Links
```html
<ref>See [[Islam]] for more information</ref>
<ref>According to [[Prophet Muhammad|Muhammad]], peace be upon him</ref>
```

#### References with External Links
```html
<ref>Source: [https://example.com Example Website]</ref>
<ref>For more details, visit [https://islamic-resources.com]</ref>
```

#### References with Mixed Links
```html
<ref>See [[Quran]] 2:255 and [https://quran.com/2/255 Quran.com]</ref>
<ref>According to [[Hadith]] collections and [https://sunnah.com Sunnah.com]</ref>
```

### Reference Features

- **Automatic Numbering**: References are automatically numbered
- **Clickable Links**: All links in references are clickable
- **Internal Links**: `[[Page]]` becomes proper wiki navigation
- **External Links**: `[url]` opens in new tab with security attributes
- **Reference Section**: Auto-generated at bottom of articles

## 📚 Template System

### Using Templates

#### Basic Templates
```wiki
{{Template Name}}
{{Template Name|param1|param2}}
{{Template Name|param1=value1|param2=value2}}
```

#### Common Templates

##### Sidebar Template
```wiki
{{Sidebar Islam}}
```

##### Page Protection
```wiki
{{pp-semi-indef}}
{{pp-move}}
{{pp-full}}
```

##### Good Article
```wiki
{{good article}}
```

##### Quote Template
```wiki
{{Cquote|"Quote text"|Author|Source}}
```

### Template Parameters

- **Positional Parameters**: `{{Template|param1|param2}}`
- **Named Parameters**: `{{Template|param1=value1|param2=value2}}`
- **Default Values**: Templates can have default values for missing parameters

## 🎨 Categories

### Adding Categories

#### Single Category
```wiki
[[Category:Islam]]
```

#### Multiple Categories
```wiki
[[Category:Islam]] [[Category:Religions]] [[Category:Theology]]
```

#### Category Guidelines
- Use singular nouns
- Capitalize first letter
- Be specific and descriptive
- Use existing categories when possible

## 🔧 Advanced Features

### Magic Words

#### No Title
```wiki
__NOTITLE__
```
Hides the page title (useful for special pages)

#### No Categories
```wiki
__NOCAT__
```
Prevents automatic categorization

#### Current Year
```wiki
{{CURRENTYEAR}}
```
Displays current year (2025)

#### Site Name
```wiki
{{SITENAME}}
```
Displays site name (IslamWiki)

### Anchor Links

#### Creating Anchors
Headings automatically become anchors:
```markdown
## Etymology
```
Creates anchor: `#etymology`

#### Linking to Anchors
```wiki
[[Page Name#Section]]
[[Islam#Etymology]]
```

### Tables

#### Basic Table
```markdown
| Header 1 | Header 2 | Header 3 |
|----------|----------|----------|
| Cell 1   | Cell 2   | Cell 3   |
| Cell 4   | Cell 5   | Cell 6   |
```

## 📱 Mobile Editing

### Mobile Features
- **Touch-Friendly Buttons**: All toolbar buttons are optimized for touch
- **Responsive Layout**: Editor adapts to mobile screen sizes
- **Swipe Gestures**: Navigate between sections easily
- **Auto-Save**: Content is automatically saved as you type

### Mobile Tips
- Use the preview panel to check formatting
- Test links on mobile to ensure they work properly
- Keep paragraphs short for better mobile reading
- Use headings to break up long content

## 🚀 Best Practices

### Content Organization
1. **Use Clear Headings**: Structure your content with proper headings
2. **Short Paragraphs**: Keep paragraphs concise and readable
3. **Internal Linking**: Link to related articles for better navigation
4. **Categories**: Always add relevant categories
5. **References**: Include references for factual claims

### Writing Style
1. **Neutral Tone**: Write in an encyclopedic, neutral tone
2. **Factual Content**: Focus on verifiable facts
3. **Clear Language**: Use clear, simple language
4. **Consistent Formatting**: Use consistent formatting throughout

### Technical Tips
1. **Preview Regularly**: Use the preview panel to check your work
2. **Test Links**: Verify all links work correctly
3. **Check References**: Ensure references are properly formatted
4. **Save Frequently**: Save your work regularly

## 🐛 Troubleshooting

### Common Issues

#### Toolbar Not Working
- **Check JavaScript**: Ensure JavaScript is enabled
- **Refresh Page**: Try refreshing the page
- **Clear Cache**: Clear browser cache and cookies

#### Preview Not Updating
- **Check Internet**: Ensure you have internet connection
- **Wait for Processing**: Preview may take a moment to update
- **Check Syntax**: Verify your wiki syntax is correct

#### Links Not Working
- **Check Syntax**: Ensure link syntax is correct
- **Verify URLs**: Check that URLs are valid
- **Test Manually**: Test links in preview mode

#### Formatting Issues
- **Check Markdown**: Verify markdown syntax is correct
- **Use Preview**: Use preview panel to check formatting
- **Clear Formatting**: Remove extra spaces and characters

### Getting Help

#### Documentation
- Check this user guide for detailed instructions
- Review the wiki syntax reference
- Look at existing articles for examples

#### Support
- Contact administrators for technical issues
- Check the community forum for help
- Report bugs through the appropriate channels

## 📚 Additional Resources

### Wiki Syntax Reference
- **Markdown Guide**: Complete markdown syntax reference
- **Template Library**: Available templates and their parameters
- **Category List**: All available categories

### Examples
- **Sample Articles**: Look at well-formatted articles for examples
- **Template Examples**: See how templates are used in practice
- **Reference Examples**: Examples of proper reference formatting

### Advanced Topics
- **Custom Templates**: Creating your own templates
- **Complex Formatting**: Advanced formatting techniques
- **Performance Tips**: Optimizing article performance

---

**Last Updated:** January 2025  
**Version:** 0.0.0.17  
**Status:** Production Ready ✅

*This guide covers the basic and advanced features of the IslamWiki Editor. For more detailed technical information, see the [Wiki Editor System Documentation](../features/WIKI_EDITOR_SYSTEM.md).*
