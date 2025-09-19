<?php
// Debug WikiParser parameters
session_start();
$_SESSION['user_id'] = 1;

require_once 'public/config/config.php';
require_once 'public/includes/functions.php';
require_once 'public/includes/markdown/WikiParser.php';

// Test with WikiParser
$content = "{{Main|Five Pillars of Islam}}";
echo "Testing: $content\n";

$parser = new WikiParser($content);
$result = $parser->parse($content);

echo "Result: $result\n";
?>
