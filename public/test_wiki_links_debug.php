<?php
require_once __DIR__ . '/includes/markdown/AdvancedTemplateParser.php';

// Test the parseWikiLinks method directly
$content = "* [[The Foundations of Islam (Wahhaab)|The Foundations of Islam]]";

echo "Original content: " . $content . "\n";

// Test the wiki link regex
$pattern = '/\[\[([^|\]]+)(?:\|([^\]]+))?\]\]/';
$result = preg_replace_callback($pattern, function($matches) {
    echo "Matched: " . $matches[0] . "\n";
    echo "Page name: " . $matches[1] . "\n";
    echo "Display text: " . (isset($matches[2]) ? $matches[2] : 'N/A') . "\n";
    return '<a href="/wiki/' . strtolower(str_replace(' ', '-', $matches[1])) . '">' . (isset($matches[2]) ? $matches[2] : $matches[1]) . '</a>';
}, $content);

echo "After regex: " . $result . "\n";
?>
