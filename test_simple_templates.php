<?php
// Simple test for individual templates
session_start();
$_SESSION['user_id'] = 1;

require_once 'public/config/config.php';
require_once 'public/includes/functions.php';
require_once 'public/includes/markdown/WikiParser.php';

// Test individual templates
$templates_to_test = [
    'About' => '{{About|the religion||Islam (disambiguation)}}',
    'pp-semi-indef' => '{{pp-semi-indef}}',
    'pp-move' => '{{pp-move}}',
    'good article' => '{{good article}}',
    'Use dmy dates' => '{{Use dmy dates|date=March 2022}}',
    'Use Oxford spelling' => '{{Use Oxford spelling|date=May 2022}}',
    'Sidebar Islam' => '{{Sidebar Islam}}'
];

foreach ($templates_to_test as $name => $template) {
    echo "Testing $name:\n";
    echo "Input: $template\n";
    
    $parser = new WikiParser($template);
    $parsed = $parser->parse($template);
    
    echo "Output: $parsed\n";
    echo "---\n\n";
}
?>

