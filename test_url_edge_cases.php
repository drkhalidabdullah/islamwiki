<?php
require_once 'public/config/database.php';
require_once 'public/includes/markdown/WikiParser.php';

// Test edge cases for URL parsing
$parser = new WikiParser($pdo);

$testCases = [
    'Simple domain: www.islamawakened.com',
    'Domain with path: www.example.com/path/to/page',
    'Domain with query: www.test.com?param=value',
    'Already has protocol: https://quran.com',
    'Mixed: Visit www.islamawakened.com and https://quran.com',
    'In parentheses: (www.example.com)',
    'With punctuation: www.test.com, and www.another.com.',
    'Invalid: not-a-domain and www.',
    'Email should not be linked: user@example.com'
];

echo "<h2>Testing URL parsing edge cases:</h2>";

foreach ($testCases as $test) {
    echo "<h3>Test: " . htmlspecialchars($test) . "</h3>";
    $result = $parser->parse($test);
    echo "<strong>Rendered:</strong> " . $result . "<br><br>";
}
?>
