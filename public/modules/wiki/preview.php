<link rel="stylesheet" href="/skins/bismillah/assets/css/bismillah.css">
<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/markdown/SecureWikiParser.php';

// Check maintenance mode
check_maintenance_mode();

header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';
    
    if (!empty($content)) {
        $parser = new SecureWikiParser('');
        $html = $parser->parse($content);
        echo $html;
    } else {
        echo '<p>No content to preview.</p>';
    }
} else {
    echo '<p>Invalid request method.</p>';
}
?>
<script src="/skins/bismillah/assets/js/bismillah.js"></script>
