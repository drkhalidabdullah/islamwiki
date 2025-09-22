<?php
/**
 * SEO Extension Test
 * 
 * Simple test file to demonstrate the SEO extension functionality
 */

// Set the wiki system loaded flag
$GLOBALS['wiki_system_loaded'] = true;

// Include the SEO integration
require_once __DIR__ . '/seo_integration.php';

// Test content with SEO template
$test_content = '
{{#seo:|title=Muslims|title_mode=append|keywords=Islam, Muhammad, Quran, Allah, Five Pillars of Islam, Sunni, Shi\'a, Sufism, Mecca, Medina, Hajj, Ramadan, Eid al-Fitr, Eid al-Adha, Mosque, Imam, Caliphate, Islamic Golden Age, Sharia, Jihad, Prophet, Sahaba, Hadith, Tawhid, Zakat, Salah, Sawm, Islamic art, Islamic architecture, Islamic law, Islamic finance, Islamic calendar, Islamic education, Islamic philosophy, Islamic science, Islamic culture, Islamic history, Islamic civilization, Islamic theology, Islamic mysticism, Islamic literature, Islamic banking, Islamic ethics, Islamic festivals, Islamic rituals, Islamic symbols, Islamic clothing, Islamic holidays, Islamic traditions, Islamic countries, Islamic leaders, Islamic scholars, Islamic movements, Islamic reform, Islamic revival, Islamic sects, Islamic unity, Islamic diversity.|description=Explore the comprehensive Muslims wiki page to learn about Islam, its core beliefs, practices, and history. Discover the Five Pillars of Islam, the life of Prophet Muhammad, the Quran, and the diversity within the Muslim community, including Sunni, Shi\'a, and Sufi traditions. Delve into Islamic history, contributions to science and art, Sharia law, and contemporary issues. Perfect for understanding the global impact and cultural richness of Muslims worldwide.|site_name=MuslimWiki|locale=en_EN|type=website|modified_time={{REVISIONYEAR}}-{{REVISIONMONTH}}-{{REVISIONDAY2}}|published_time=2025-09-22}}

# Muslims

Muslims are followers of Islam, a monotheistic Abrahamic religion. This article explores the beliefs, practices, and diversity within the Muslim community.

## Core Beliefs

Muslims believe in:
- One God (Allah)
- The Prophet Muhammad as the final messenger
- The Quran as the holy book
- The Five Pillars of Islam

## Diversity

The Muslim community includes various traditions:
- Sunni Islam
- Shi\'a Islam
- Sufism
- And many other schools of thought

## History

Islamic history spans over 1400 years, from the time of Prophet Muhammad to the present day.
';

// Parse the SEO template
$processed_content = parse_seo_template($test_content);

// Generate meta tags
$meta_tags = generate_seo_meta_tags();

// Get SEO data
$seo_data = get_seo_data();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO Extension Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .code { background: #f5f5f5; padding: 10px; border-radius: 3px; font-family: monospace; white-space: pre-wrap; }
        .meta-tag { margin: 5px 0; padding: 5px; background: #e9ecef; border-radius: 3px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
    <?php echo $meta_tags; ?>
</head>
<body>
    <h1>SEO Extension Test</h1>
    
    <div class="section">
        <h2>Test Content</h2>
        <div class="code"><?php echo htmlspecialchars($test_content); ?></div>
    </div>
    
    <div class="section">
        <h2>Generated Meta Tags</h2>
        <div class="code"><?php echo htmlspecialchars($meta_tags); ?></div>
    </div>
    
    <div class="section">
        <h2>SEO Data</h2>
        <div class="code"><?php echo htmlspecialchars(json_encode($seo_data, JSON_PRETTY_PRINT)); ?></div>
    </div>
    
    <div class="section">
        <h2>Test Results</h2>
        <?php
        if (!empty($seo_data['title'])) {
            echo '<p class="success">✓ Title: ' . htmlspecialchars($seo_data['title']) . '</p>';
        } else {
            echo '<p class="error">✗ Title not found</p>';
        }
        
        if (!empty($seo_data['description'])) {
            echo '<p class="success">✓ Description: ' . htmlspecialchars(substr($seo_data['description'], 0, 100)) . '...</p>';
        } else {
            echo '<p class="error">✗ Description not found</p>';
        }
        
        if (!empty($seo_data['keywords'])) {
            echo '<p class="success">✓ Keywords: ' . htmlspecialchars(substr($seo_data['keywords'], 0, 100)) . '...</p>';
        } else {
            echo '<p class="error">✗ Keywords not found</p>';
        }
        
        if (!empty($seo_data['site_name'])) {
            echo '<p class="success">✓ Site Name: ' . htmlspecialchars($seo_data['site_name']) . '</p>';
        } else {
            echo '<p class="error">✗ Site Name not found</p>';
        }
        
        if (!empty($seo_data['locale'])) {
            echo '<p class="success">✓ Locale: ' . htmlspecialchars($seo_data['locale']) . '</p>';
        } else {
            echo '<p class="error">✗ Locale not found</p>';
        }
        
        if (!empty($seo_data['type'])) {
            echo '<p class="success">✓ Type: ' . htmlspecialchars($seo_data['type']) . '</p>';
        } else {
            echo '<p class="error">✗ Type not found</p>';
        }
        
        if (!empty($seo_data['modified_time'])) {
            echo '<p class="success">✓ Modified Time: ' . htmlspecialchars($seo_data['modified_time']) . '</p>';
        } else {
            echo '<p class="error">✗ Modified Time not found</p>';
        }
        
        if (!empty($seo_data['published_time'])) {
            echo '<p class="success">✓ Published Time: ' . htmlspecialchars($seo_data['published_time']) . '</p>';
        } else {
            echo '<p class="error">✗ Published Time not found</p>';
        }
        ?>
    </div>
    
    <div class="section">
        <h2>Instructions</h2>
        <p>To use the SEO extension in your wiki articles, add this template to the top of your article:</p>
        <div class="code">{{#seo:|title=Your Page Title|description=Your page description|keywords=keyword1, keyword2}}</div>
        
        <p>For more advanced usage, see the README.md file in the SEO extension directory.</p>
    </div>
</body>
</html>

