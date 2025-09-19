<?php
// Test the feed page directly
$_SERVER['REQUEST_URI'] = '/feed';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Mock session for testing
session_start();
$_SESSION['user_id'] = 1; // Assuming user ID 1 exists

// Include the feed page
ob_start();
include 'public/pages/social/feed.php';
$output = ob_get_clean();

// Check if the three-column layout is present
if (strpos($output, 'dashboard-container') !== false) {
    echo "✅ Three-column layout found!\n";
} else {
    echo "❌ Three-column layout NOT found\n";
}

if (strpos($output, 'dashboard-sidebar') !== false) {
    echo "✅ Left sidebar found!\n";
} else {
    echo "❌ Left sidebar NOT found\n";
}

if (strpos($output, 'dashboard-rightbar') !== false) {
    echo "✅ Right sidebar found!\n";
} else {
    echo "❌ Right sidebar NOT found\n";
}

if (strpos($output, 'dashboard-main') !== false) {
    echo "✅ Main content area found!\n";
} else {
    echo "❌ Main content area NOT found\n";
}

echo "\nFirst 500 characters of output:\n";
echo substr($output, 0, 500) . "...\n";
?>
