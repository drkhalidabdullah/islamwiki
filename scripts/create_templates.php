<?php
require_once 'public/config/database.php';

$templates = [
    [
        'name' => 'About',
        'slug' => 'about',
        'content' => '<div class="about-template">
<div class="about-header">
<strong>This article is about {{1}}.</strong>
{{#if:{{2}}|For {{2}}, see [[{{2}}]].}}
{{#if:{{3}}|For other uses, see [[{{3}}]].}}
</div>
</div>',
        'description' => 'Disambiguation template for articles',
        'template_type' => 'other',
        'is_system_template' => 1
    ],
    [
        'name' => 'pp-semi-indef',
        'slug' => 'pp-semi-indef',
        'content' => '<div class="protection-template semi-indef">
<div class="protection-banner">
<strong>This page is semi-protected.</strong> Only registered users can edit it.
</div>
</div>',
        'description' => 'Semi-protection notice template',
        'template_type' => 'other',
        'is_system_template' => 1
    ],
    [
        'name' => 'pp-move',
        'slug' => 'pp-move',
        'content' => '<div class="protection-template move-protection">
<div class="protection-banner">
<strong>This page is move-protected.</strong> Only administrators can move it.
</div>
</div>',
        'description' => 'Move protection notice template',
        'template_type' => 'other',
        'is_system_template' => 1
    ],
    [
        'name' => 'good article',
        'slug' => 'good-article',
        'content' => '<div class="quality-template good-article">
<div class="quality-banner">
<strong>This is a good article.</strong> It meets the quality standards for featured content.
</div>
</div>',
        'description' => 'Good article quality indicator',
        'template_type' => 'other',
        'is_system_template' => 1
    ],
    [
        'name' => 'Use dmy dates',
        'slug' => 'use-dmy-dates',
        'content' => '<div class="date-format-template">
<div class="date-format-notice">
<small>This article uses dmy dates (day month year) format.</small>
</div>
</div>',
        'description' => 'Date format specification template',
        'template_type' => 'other',
        'is_system_template' => 1
    ],
    [
        'name' => 'Use Oxford spelling',
        'slug' => 'use-oxford-spelling',
        'content' => '<div class="spelling-template">
<div class="spelling-notice">
<small>This article uses Oxford spelling conventions.</small>
</div>
</div>',
        'description' => 'Spelling convention template',
        'template_type' => 'other',
        'is_system_template' => 1
    ],
    [
        'name' => 'Sidebar Islam',
        'slug' => 'sidebar-islam',
        'content' => '<div class="sidebar-template islam-sidebar">
<div class="sidebar-header">
<h3>Islam</h3>
</div>
<div class="sidebar-image">
<img src="/skins/bismillah/assets/images/islam-symbol.svg" alt="Islamic symbol" />
<p class="sidebar-caption">The Shahada in Arabic calligraphy</p>
</div>
<div class="sidebar-section">
<h4>Beliefs</h4>
<div class="sidebar-content">
<ul>
<li><strong>God:</strong> [[Allah]] ([[Tawhid]])</li>
<li><strong>Prophets:</strong> [[Muhammad]], [[Jesus]], [[Moses]], [[Abraham]]</li>
<li><strong>Scriptures:</strong> [[Quran]], [[Torah]], [[Gospel]]</li>
<li><strong>Angels:</strong> [[Gabriel]], [[Michael]], [[Israfil]]</li>
<li><strong>Afterlife:</strong> [[Heaven]], [[Hell]], [[Day of Judgment]]</li>
</ul>
</div>
</div>
<div class="sidebar-section">
<h4>Practices</h4>
<div class="sidebar-content">
<ul>
<li><strong>[[Five Pillars of Islam|Five Pillars]]:</strong></li>
<li>[[Shahada]] (Declaration of Faith)</li>
<li>[[Salah]] (Prayer)</li>
<li>[[Zakat]] (Charity)</li>
<li>[[Sawm]] (Fasting)</li>
<li>[[Hajj]] (Pilgrimage)</li>
</ul>
</div>
</div>
<div class="sidebar-section">
<h4>Denominations</h4>
<div class="sidebar-content">
<ul>
<li>[[Sunni Islam|Sunni]] (85-90%)</li>
<li>[[Shia Islam|Shia]] (10-15%)</li>
<li>[[Sufism|Sufi]]</li>
<li>[[Ahmadiyya]]</li>
</ul>
</div>
</div>
<div class="sidebar-section">
<h4>History</h4>
<div class="sidebar-content">
<ul>
<li>[[Muhammad]] (570-632 CE)</li>
<li>[[Rashidun Caliphate]] (632-661)</li>
<li>[[Umayyad Caliphate]] (661-750)</li>
<li>[[Abbasid Caliphate]] (750-1258)</li>
<li>[[Ottoman Empire]] (1299-1922)</li>
</ul>
</div>
</div>
<div class="sidebar-section">
<h4>Sacred Texts</h4>
<div class="sidebar-content">
<ul>
<li>[[Quran]] (Primary)</li>
<li>[[Hadith]] (Secondary)</li>
<li>[[Sunnah]] (Traditions)</li>
<li>[[Tafsir]] (Commentary)</li>
</ul>
</div>
</div>
<div class="sidebar-section">
<h4>Places</h4>
<div class="sidebar-content">
<ul>
<li>[[Mecca]] (Birthplace)</li>
<li>[[Medina]] (Hijra)</li>
<li>[[Jerusalem]] (Al-Aqsa)</li>
<li>[[Karbala]] (Shia holy city)</li>
</ul>
</div>
</div>
</div>',
        'description' => 'Comprehensive Islam sidebar template',
        'template_type' => 'sidebar',
        'is_system_template' => 1
    ]
];

try {
    $stmt = $pdo->prepare("
        INSERT INTO wiki_templates (name, slug, content, description, template_type, is_system_template, created_by, updated_by, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, 1, 1, NOW(), NOW())
        ON DUPLICATE KEY UPDATE 
        content = VALUES(content),
        description = VALUES(description),
        template_type = VALUES(template_type),
        updated_by = VALUES(updated_by),
        updated_at = VALUES(updated_at)
    ");
    
    foreach ($templates as $template) {
        $stmt->execute([
            $template['name'],
            $template['slug'],
            $template['content'],
            $template['description'],
            $template['template_type'],
            $template['is_system_template']
        ]);
        echo "Created template: {$template['name']}\n";
    }
    
    echo "\nAll templates created successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

