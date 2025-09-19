<?php
// Test template parser initialization
session_start();
$_SESSION['user_id'] = 1;

require_once 'public/config/config.php';
require_once 'public/includes/functions.php';
require_once 'public/includes/markdown/WikiParser.php';

echo "Testing template parser initialization...\n";

// Test with a simple template
$content = "{{pp-semi-indef}}";
echo "Input: $content\n";

$parser = new WikiParser($content);
$result = $parser->parse($content);

echo "Output: $result\n";

// Check if template parser is initialized
$reflection = new ReflectionClass($parser);
$template_parser_property = $reflection->getProperty('template_parser');
$template_parser_property->setAccessible(true);
$template_parser = $template_parser_property->getValue($parser);

echo "Template parser initialized: " . ($template_parser ? "Yes" : "No") . "\n";

if ($template_parser) {
    echo "Template parser class: " . get_class($template_parser) . "\n";
}
?>

