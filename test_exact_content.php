<?php
$testText = '[[File:Evening Prayer by Jean-Léon Gérôme.jpg|alt=Evening Prayer (Prayer in Cairo - 1865) by Jean-Léon Gérôme|thumb|Evening Prayer (Prayer in [[Cairo]] - 1865) by [[Jean-Léon Gérôme]]]]';

echo "Content length: " . strlen($testText) . "<br>";
echo "Last 10 characters: " . htmlspecialchars(substr($testText, -10)) . "<br>";
echo "Character at position 192: " . htmlspecialchars($testText[192]) . "<br>";
echo "Character at position 193: " . htmlspecialchars($testText[193]) . "<br>";

// Check for the exact pattern
$pos = strpos($testText, ']]]]');
echo "Position of ']]]]': " . $pos . "<br>";

// Manual bracket counting
$bracket_count = 0;
for ($i = 0; $i < strlen($testText); $i++) {
    if (substr($testText, $i, 2) === '[[') {
        $bracket_count++;
        echo "Found [[ at position $i, count: $bracket_count<br>";
    } elseif (substr($testText, $i, 2) === ']]') {
        $bracket_count--;
        echo "Found ]] at position $i, count: $bracket_count<br>";
    }
}
?>
