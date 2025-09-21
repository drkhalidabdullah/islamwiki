<?php
// Debug header-dashboard visibility
require_once 'public/config/config.php';
require_once 'public/includes/functions.php';

// Simulate a logged-in user for testing
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'testuser';
}

echo "<!DOCTYPE html>";
echo "<html><head><title>Debug Header Dashboard</title>";
echo "<style>";
echo ".header-dashboard { background: red !important; height: 60px !important; position: fixed !important; top: 0 !important; left: 60px !important; width: calc(100vw - 60px) !important; z-index: 99999 !important; display: block !important; visibility: visible !important; opacity: 1 !important; }";
echo ".header-dashboard-container { background: blue !important; height: 100% !important; display: flex !important; align-items: center !important; padding: 0 20px !important; }";
echo ".header-dashboard-container * { color: white !important; }";
echo "</style>";
echo "</head><body>";

echo "<div style='margin-top: 100px; padding: 20px;'>";
echo "<h1>Debug Header Dashboard</h1>";
echo "<p>This page tests if the header-dashboard is visible.</p>";
echo "</div>";

// Include the header-dashboard directly
include 'public/includes/header_dashboard.php';

echo "</body></html>";
?>
