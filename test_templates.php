<?php
// Test the new templates
session_start();
$_SESSION['user_id'] = 1;

require_once 'public/config/config.php';
require_once 'public/includes/functions.php';
require_once 'public/includes/markdown/WikiParser.php';

// Test content with all the new templates
$test_content = '{{About|the religion||Islam (disambiguation)}}
{{pp-semi-indef}}
{{pp-move}}
{{good article}}
{{Use dmy dates|date=March 2022}}
{{Use Oxford spelling|date=May 2022}}

{{Sidebar Islam}}

== Introduction ==

This is a test article about Islam to demonstrate the new templates.

== History ==

Islam was founded by the Prophet Muhammad in the 7th century CE.

== Beliefs ==

The core beliefs of Islam are based on the Five Pillars.';

echo "Testing template parsing...\n\n";

$parser = new WikiParser($test_content);
$parsed_content = $parser->parse($test_content);

echo "Parsed content:\n";
echo "================\n";
echo $parsed_content;
echo "\n\n";

// Check if templates were parsed
$templates_found = [
    'About template' => strpos($parsed_content, 'about-template') !== false,
    'Protection templates' => strpos($parsed_content, 'protection-template') !== false,
    'Good article template' => strpos($parsed_content, 'quality-template') !== false,
    'Date format templates' => strpos($parsed_content, 'date-format-template') !== false,
    'Spelling template' => strpos($parsed_content, 'spelling-template') !== false,
    'Sidebar Islam template' => strpos($parsed_content, 'islam-sidebar') !== false,
];

foreach ($templates_found as $name => $found) {
    echo ($found ? "✓" : "✗") . " $name\n";
}

echo "\nTest completed!\n";
?>
