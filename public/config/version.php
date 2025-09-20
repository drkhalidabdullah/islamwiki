<?php
/**
 * Centralized Version Management
 * 
 * This file contains all version information for IslamWiki.
 * Update this file to change the version across the entire site.
 * 
 * @package IslamWiki
 * @version 0.0.0.18
 * @since 0.0.0.18
 */

// Main version constants
define('SITE_VERSION', '0.0.0.18');
define('SITE_VERSION_MAJOR', '0');
define('SITE_VERSION_MINOR', '0');
define('SITE_VERSION_PATCH', '18');
define('SITE_VERSION_FULL', '0.0.0.18');

// Version metadata
define('SITE_VERSION_NAME', 'Wiki Editor & Reference System');
define('SITE_VERSION_TYPE', 'Major Feature Enhancement - Wiki Editor & Reference System');
define('SITE_VERSION_STATUS', 'Production Ready');
define('SITE_VERSION_DATE', 'January 2025');

// Version information array
$version_info = [
    'version' => SITE_VERSION,
    'major' => SITE_VERSION_MAJOR,
    'minor' => SITE_VERSION_MINOR,
    'patch' => SITE_VERSION_PATCH,
    'full' => SITE_VERSION_FULL,
    'name' => SITE_VERSION_NAME,
    'type' => SITE_VERSION_TYPE,
    'status' => SITE_VERSION_STATUS,
    'date' => SITE_VERSION_DATE,
    'build' => date('Y-m-d H:i:s'),
    'git_commit' => exec('git rev-parse --short HEAD 2>/dev/null') ?: 'unknown',
    'git_branch' => exec('git rev-parse --abbrev-ref HEAD 2>/dev/null') ?: 'unknown'
];

// Helper functions
function get_site_version() {
    return SITE_VERSION;
}

function get_version_info() {
    global $version_info;
    return $version_info;
}

function get_version_badge() {
    return '[![Version](https://img.shields.io/badge/version-' . SITE_VERSION . '-blue.svg)](https://github.com/drkhalidabdullah/islamwiki)';
}

function get_version_string() {
    return 'Version ' . SITE_VERSION . ' - ' . SITE_VERSION_NAME;
}

function get_version_footer() {
    return 'Version ' . SITE_VERSION . ' (' . SITE_VERSION_DATE . ')';
}

// Auto-update version in files (for development)
function update_version_references() {
    $version = SITE_VERSION;
    $files_to_update = [
        'public/config/config.php' => "define('SITE_VERSION', '{$version}');",
        'public/skins/bismillah/assets/js/citation.js' => " * @version {$version}",
        'public/skins/bismillah/assets/js/wiki_article.js' => " * @version {$version}",
        'public/includes/wiki_functions.php' => " * @version {$version}",
    ];
    
    foreach ($files_to_update as $file => $replacement) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $content = preg_replace('/@version\s+[\d\.]+/', $replacement, $content);
            $content = preg_replace('/define\(\'SITE_VERSION\',\s*\'[\d\.]+\'\);/', $replacement, $content);
            file_put_contents($file, $content);
        }
    }
}

// Export version info for JavaScript
if (isset($_GET['version_info']) && $_GET['version_info'] === 'json') {
    header('Content-Type: application/json');
    echo json_encode($version_info);
    exit;
}
?>
