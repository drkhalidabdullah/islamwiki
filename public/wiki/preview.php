<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/markdown/MarkdownParser.php';

header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';
    
    if (!empty($content)) {
        $parser = new MarkdownParser();
        $html = $parser->parse($content);
        echo $html;
    } else {
        echo '<p>No content to preview.</p>';
    }
} else {
    echo '<p>Invalid request method.</p>';
}
?>
