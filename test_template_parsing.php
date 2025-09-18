<?php
require_once 'public/config/config.php';
require_once 'public/includes/functions.php';
require_once 'public/includes/markdown/TemplateParser.php';

// Test template parsing
$template_parser = new TemplateParser($pdo);

// Test the About template with the same parameters as in the Islam article
$result = $template_parser->parseTemplate('About', [
    0 => 'the religion',
    1 => '',
    2 => 'Islam (disambiguation)'
]);

echo "Template result:\n";
echo $result . "\n\n";

// Test with different parameter structure
$result2 = $template_parser->parseTemplate('About', [
    '1' => 'the religion',
    '2' => '',
    '3' => 'Islam (disambiguation)'
]);

echo "Template result with named parameters:\n";
echo $result2 . "\n\n";

// Test the raw template content
$stmt = $pdo->prepare("SELECT content FROM wiki_templates WHERE name = 'About'");
$stmt->execute();
$template = $stmt->fetch();

echo "Raw template content:\n";
echo $template['content'] . "\n";
?>

