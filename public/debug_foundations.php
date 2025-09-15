<?php
require_once __DIR__ . '/config/database.php';

try {
    $stmt = $pdo->prepare("SELECT content FROM wiki_articles WHERE slug = 'Main_Page' LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        $content = $result['content'];
        
        // Find the line with Foundations of Islam
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            if (strpos($line, 'Foundations of Islam') !== false) {
                echo "Found line: " . htmlspecialchars($line) . "\n";
                echo "Length: " . strlen($line) . "\n";
                echo "Raw bytes: " . bin2hex($line) . "\n";
            }
        }
    } else {
        echo "No Main Page found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
