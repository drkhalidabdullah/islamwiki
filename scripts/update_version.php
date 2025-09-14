<?php
/**
 * Version Update Script
 * 
 * This script updates the version across all files in the project.
 * Run this script when updating to a new version.
 * 
 * Usage: php scripts/update_version.php [new_version]
 * Example: php scripts/update_version.php 0.0.0.15
 * 
 * @package IslamWiki
 * @version 0.0.0.14
 */

// Get new version from command line argument
$new_version = $argv[1] ?? null;

if (!$new_version) {
    echo "Usage: php scripts/update_version.php [new_version]\n";
    echo "Example: php scripts/update_version.php 0.0.0.15\n";
    exit(1);
}

// Validate version format
if (!preg_match('/^\d+\.\d+\.\d+$/', $new_version)) {
    echo "Error: Invalid version format. Use format: X.Y.Z (e.g., 0.0.0.15)\n";
    exit(1);
}

echo "Updating version to: {$new_version}\n";

// Files to update with version references
$files_to_update = [
    'public/config/version.php' => [
        'pattern' => '/define\(\'SITE_VERSION\',\s*\'[\d\.]+\'\);/',
        'replacement' => "define('SITE_VERSION', '{$new_version}');"
    ],
    'public/config/version.php' => [
        'pattern' => '/define\(\'SITE_VERSION_FULL\',\s*\'[\d\.]+\'\);/',
        'replacement' => "define('SITE_VERSION_FULL', '{$new_version}');"
    ],
    'public/skins/bismillah/assets/js/citation.js' => [
        'pattern' => '/@version\s+[\d\.]+/',
        'replacement' => "@version {$new_version}"
    ],
    'public/skins/bismillah/assets/js/wiki_article.js' => [
        'pattern' => '/@version\s+[\d\.]+/',
        'replacement' => "@version {$new_version}"
    ],
    'public/includes/wiki_functions.php' => [
        'pattern' => '/@version\s+[\d\.]+/',
        'replacement' => "@version {$new_version}"
    ],
    'README.md' => [
        'pattern' => '/\[!\[Version\]\(https:\/\/img\.shields\.io\/badge\/version-[\d\.]+-blue\.svg\)\]/',
        'replacement' => "[![Version](https://img.shields.io/badge/version-{$new_version}-blue.svg)]"
    ],
    'README.md' => [
        'pattern' => '/## 🎯 Current Version: [\d\.]+/',
        'replacement' => "## 🎯 Current Version: {$new_version}"
    ],
    'README.md' => [
        'pattern' => '/## 🚀 What\'s New in v[\d\.]+/',
        'replacement' => "## 🚀 What's New in v{$new_version}"
    ]
];

$updated_files = 0;
$total_files = count($files_to_update);

foreach ($files_to_update as $file => $config) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $original_content = $content;
        
        $content = preg_replace($config['pattern'], $config['replacement'], $content);
        
        if ($content !== $original_content) {
            file_put_contents($file, $content);
            echo "✓ Updated: {$file}\n";
            $updated_files++;
        } else {
            echo "- No changes needed: {$file}\n";
        }
    } else {
        echo "✗ File not found: {$file}\n";
    }
}

echo "\nVersion update complete!\n";
echo "Updated {$updated_files} out of {$total_files} files.\n";

// Update changelog and release notes
echo "\nNext steps:\n";
echo "1. Update docs/changelogs/CHANGELOG.md with new version entry\n";
echo "2. Update docs/releases/RELEASE_NOTES.md with new release notes\n";
echo "3. Create docs/changelogs/v{$new_version}.md\n";
echo "4. Update any version-specific documentation\n";
echo "5. Test the application thoroughly\n";
echo "6. Commit changes with: git commit -m \"Update version to {$new_version}\"\n";

echo "\nVersion {$new_version} is ready for development!\n";
?>
