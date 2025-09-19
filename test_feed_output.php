<?php
// Set up environment
$_SERVER['REQUEST_URI'] = '/feed';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = 'localhost';

// Start session and set user
session_start();
$_SESSION['user_id'] = 1;

// Capture output
ob_start();
include 'public/pages/social/feed.php';
$output = ob_get_clean();

// Check for three-column layout
echo "=== LAYOUT CHECK ===\n";
if (strpos($output, 'dashboard-container') !== false) {
    echo "✅ dashboard-container found\n";
} else {
    echo "❌ dashboard-container NOT found\n";
}

if (strpos($output, 'dashboard-layout') !== false) {
    echo "✅ dashboard-layout found\n";
} else {
    echo "❌ dashboard-layout NOT found\n";
}

if (strpos($output, 'dashboard-sidebar') !== false) {
    echo "✅ dashboard-sidebar found\n";
} else {
    echo "❌ dashboard-sidebar NOT found\n";
}

if (strpos($output, 'dashboard-main') !== false) {
    echo "✅ dashboard-main found\n";
} else {
    echo "❌ dashboard-main NOT found\n";
}

if (strpos($output, 'dashboard-rightbar') !== false) {
    echo "✅ dashboard-rightbar found\n";
} else {
    echo "❌ dashboard-rightbar NOT found\n";
}

// Check for content
echo "\n=== CONTENT CHECK ===\n";
if (strpos($output, 'Your Feed') !== false) {
    echo "✅ 'Your Feed' title found\n";
} else {
    echo "❌ 'Your Feed' title NOT found\n";
}

if (strpos($output, 'feed-item') !== false) {
    echo "✅ feed-item elements found\n";
} else {
    echo "❌ feed-item elements NOT found\n";
}

if (strpos($output, 'My Content') !== false) {
    echo "✅ 'My Content' section found\n";
} else {
    echo "❌ 'My Content' section NOT found\n";
}

// Check for CSS
echo "\n=== CSS CHECK ===\n";
if (strpos($output, 'dashboard.css') !== false) {
    echo "✅ dashboard.css loaded\n";
} else {
    echo "❌ dashboard.css NOT loaded\n";
}

// Show first 1000 characters
echo "\n=== FIRST 1000 CHARACTERS ===\n";
echo substr($output, 0, 1000) . "...\n";
?>
