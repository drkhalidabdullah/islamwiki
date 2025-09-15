<?php
require_once '/var/www/html/public/includes/markdown/MarkdownParser.php';

$parser = new MarkdownParser();

$test_content = "#🆕 Christian denominations
## Regular header
### Another header
# Another emoji header 🎉
## Another regular header";

echo "<h2>Test Content:</h2>";
echo "<pre>" . htmlspecialchars($test_content) . "</pre>";

echo "<h2>Parsed Result:</h2>";
$result = $parser->parse($test_content);
echo "<pre>" . htmlspecialchars($result) . "</pre>";

echo "<h2>Rendered HTML:</h2>";
echo $result;
?>
