<?php
// Debug template lookup
session_start();
$_SESSION['user_id'] = 1;

require_once 'public/config/config.php';
require_once 'public/includes/functions.php';
require_once 'public/includes/markdown/TemplateParser.php';

// Test template lookup directly
$parser = new TemplateParser($pdo);

// Use reflection to access private methods
$reflection = new ReflectionClass($parser);
$getTemplateMethod = $reflection->getMethod('getTemplate');
$getTemplateMethod->setAccessible(true);

$template = $getTemplateMethod->invoke($parser, 'pp-semi-indef');
echo "Template found: " . ($template ? "Yes" : "No") . "\n";

if ($template) {
    echo "Template content: " . $template['content'] . "\n";
    echo "Template namespace: " . $template['namespace'] . "\n";
}

// Test parseTemplate method
$parseTemplateMethod = $reflection->getMethod('parseTemplate');
$parseTemplateMethod->setAccessible(true);

$result = $parseTemplateMethod->invoke($parser, 'pp-semi-indef', []);
echo "Parse result: $result\n";
?>

