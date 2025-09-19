<?php
// Debug template processing step by step
session_start();
$_SESSION['user_id'] = 1;

require_once 'public/config/config.php';
require_once 'public/includes/functions.php';
require_once 'public/includes/markdown/TemplateParser.php';

$parser = new TemplateParser($pdo);

// Use reflection to access private methods
$reflection = new ReflectionClass($parser);

// Test the template content processing
$template_content = '<includeonly>{{#invoke:Protection banner|main|action=edit|level=semi-indef}}</includeonly><noinclude>
{{documentation}}
</noinclude>';

echo "Original template content: $template_content\n\n";

// Test includeonly parsing
$parseIncludeOnlyMethod = $reflection->getMethod('parseIncludeOnlyTags');
$parseIncludeOnlyMethod->setAccessible(true);
$after_includeonly = $parseIncludeOnlyMethod->invoke($parser, $template_content);
echo "After includeonly parsing: $after_includeonly\n\n";

// Test parseTemplatesRecursive
$parseTemplatesRecursiveMethod = $reflection->getMethod('parseTemplatesRecursive');
$parseTemplatesRecursiveMethod->setAccessible(true);
$after_recursive = $parseTemplatesRecursiveMethod->invoke($parser, $after_includeonly, []);
echo "After parseTemplatesRecursive: $after_recursive\n\n";

// Test the full parseTemplate method
$parseTemplateMethod = $reflection->getMethod('parseTemplate');
$parseTemplateMethod->setAccessible(true);
$final_result = $parseTemplateMethod->invoke($parser, 'pp-semi-indef', []);
echo "Final parseTemplate result: $final_result\n";
?>

