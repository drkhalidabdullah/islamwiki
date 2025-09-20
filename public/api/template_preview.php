<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/markdown/TemplateParser.php';

// Set content type to JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get the template content from POST data
$template_content = $_POST['content'] ?? '';
$template_name = $_POST['name'] ?? 'Preview Template';

if (empty($template_content)) {
    echo json_encode(['error' => 'No template content provided']);
    exit;
}

try {
    // Create a new TemplateParser instance
    $template_parser = new TemplateParser($pdo);
    
    // Parse the template content directly
    $parsed_content = $template_parser->parseTemplateContent($template_content, []);
    
    // Return the parsed content
    echo json_encode([
        'success' => true,
        'parsed_content' => $parsed_content,
        'raw_content' => $template_content
    ]);
    
} catch (Exception $e) {
    // Return error if parsing fails
    echo json_encode([
        'success' => false,
        'error' => 'Template parsing failed: ' . $e->getMessage(),
        'raw_content' => $template_content
    ]);
}
?>
