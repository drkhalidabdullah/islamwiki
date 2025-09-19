<?php
// Debug WikiParser template processing
session_start();
$_SESSION['user_id'] = 1;

require_once 'public/config/config.php';
require_once 'public/includes/functions.php';
require_once 'public/includes/markdown/WikiParser.php';

// Test with a simple template
$content = "{{pp-semi-indef}}";
echo "Input: $content\n";

$parser = new WikiParser($content);

// Use reflection to access private methods
$reflection = new ReflectionClass($parser);

// Test parseTemplates method directly
$parseTemplatesMethod = $reflection->getMethod('parseTemplates');
$parseTemplatesMethod->setAccessible(true);

$result = $parseTemplatesMethod->invoke($parser, $content);
echo "After parseTemplates: $result\n";

// Test the full parse method
$full_result = $parser->parse($content);
echo "After full parse: $full_result\n";
?>

