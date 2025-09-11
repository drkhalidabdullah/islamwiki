<?php
require_once 'public/includes/markdown/MarkdownParser.php';

$parser = new MarkdownParser();
$content = '![image.png](http://localhost/uploads/posts/user_1/post_2025-09-10_15-40-36_8333a9d6.png) Test image';

echo "Original content:\n";
echo $content . "\n\n";

echo "Parsed content:\n";
echo $parser->parse($content) . "\n";
?>
