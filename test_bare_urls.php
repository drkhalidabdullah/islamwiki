<?php
require_once 'public/config/database.php';
require_once 'public/includes/markdown/WikiParser.php';

// Test bare URL parsing
$parser = new WikiParser($pdo);

$testText = 'Check out www.islamawakened.com for more information. Also visit https://quran.com and example.com for resources.';

echo "<h2>Testing bare URL parsing:</h2>";
echo "<strong>Input:</strong> " . htmlspecialchars($testText) . "<br><br>";

$result = $parser->parse($testText);
echo "<strong>Output:</strong> " . htmlspecialchars($result) . "<br><br>";
echo "<strong>Rendered:</strong> " . $result . "<br>";
?>
