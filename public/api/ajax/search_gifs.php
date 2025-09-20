<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    echo json_encode(['success' => false, 'message' => 'Search query required']);
    exit();
}

$query = trim($_GET['q']);
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

// Giphy API configuration
$giphy_api_key = 'dc6zaTOxFJmzC'; // This is a public demo key - replace with your own
$giphy_url = "https://api.giphy.com/v1/gifs/search?api_key={$giphy_api_key}&q=" . urlencode($query) . "&limit={$limit}&rating=g";

// Make request to Giphy API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $giphy_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Social Platform)');

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch GIFs from Giphy API']);
    exit();
}

$data = json_decode($response, true);

if (!$data || !isset($data['data'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid response from Giphy API']);
    exit();
}

// Format the response
$gifs = [];
foreach ($data['data'] as $gif) {
    $gifs[] = [
        'id' => $gif['id'],
        'title' => $gif['title'],
        'url' => $gif['images']['fixed_height']['url'],
        'preview' => $gif['images']['fixed_height_small']['url'],
        'width' => $gif['images']['fixed_height']['width'],
        'height' => $gif['images']['fixed_height']['height']
    ];
}

echo json_encode([
    'success' => true,
    'gifs' => $gifs,
    'total' => count($gifs)
]);
?>
