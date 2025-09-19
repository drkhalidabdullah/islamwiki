<?php
// Test individual templates
session_start();
$_SESSION['user_id'] = 1;

require_once 'public/config/config.php';
require_once 'public/includes/functions.php';
require_once 'public/includes/markdown/WikiParser.php';

// Test each template individually
$templates_to_test = [
    'pp-semi-indef',
    'pp-move',
    'About',
    'good article',
    'Use dmy dates',
    'Use Oxford spelling',
    'Sidebar Islam'
];

echo "Testing individual templates...\n\n";

foreach ($templates_to_test as $template_name) {
    echo "Testing: $template_name\n";
    $content = "{{$template_name}}";
    
    $parser = new WikiParser($content);
    $result = $parser->parse($content);
    
    echo "Input: $content\n";
    echo "Output: $result\n";
    echo "---\n\n";
}
?>

