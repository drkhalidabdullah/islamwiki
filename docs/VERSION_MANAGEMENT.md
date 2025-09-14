# Version Management System

## Overview

IslamWiki uses a centralized version management system to ensure consistency across all files and components. This system prevents version mismatches and makes it easy to update the version sitewide.

## Centralized Version File

The main version information is stored in `public/config/version.php`. This file contains:

- **Version constants** (SITE_VERSION, SITE_VERSION_MAJOR, etc.)
- **Version metadata** (name, type, status, date)
- **Helper functions** for accessing version information
- **Auto-update functionality** for development

## Version Information

### Current Version: 0.0.0.14
- **Name**: Enhanced User Interface & Authentication Experience
- **Type**: User Experience Enhancement - UI/UX Improvements
- **Status**: Production Ready
- **Date**: January 2025

### Version Components
- **Major**: 0 (breaking changes)
- **Minor**: 0 (new features, backward compatible)
- **Patch**: 14 (bug fixes, improvements)

## Files That Reference Version

### Core Files
- `public/config/version.php` - Central version management
- `public/config/config.php` - Loads version constants
- `public/includes/footer.php` - Displays version in footer

### JavaScript Files
- `public/skins/bismillah/assets/js/citation.js`
- `public/skins/bismillah/assets/js/wiki_article.js`

### PHP Files
- `public/includes/wiki_functions.php`

### Documentation Files
- `README.md` - Main project README
- `docs/changelogs/CHANGELOG.md` - Version changelog
- `docs/releases/RELEASE_NOTES.md` - Release notes
- `docs/changelogs/v0.0.0.14.md` - Version-specific changelog

## Updating Version

### Method 1: Using the Update Script (Recommended)

```bash
# Update to new version
php scripts/update_version.php 0.0.0.15

# The script will:
# 1. Update all version references
# 2. Show which files were updated
# 3. Provide next steps for documentation
```

### Method 2: Manual Update

1. **Update central version file**:
   ```php
   // In public/config/version.php
   define('SITE_VERSION', '0.0.0.15');
   define('SITE_VERSION_FULL', '0.0.0.15');
   ```

2. **Update documentation**:
   - Update `README.md` version badge and current version
   - Add new entry to `docs/changelogs/CHANGELOG.md`
   - Create new `docs/changelogs/v0.0.0.15.md`
   - Update `docs/releases/RELEASE_NOTES.md`

3. **Update code files**:
   - Update `@version` comments in JavaScript files
   - Update `@version` comments in PHP files

## Version Constants

### Available Constants
```php
SITE_VERSION          // Main version (e.g., '0.0.0.14')
SITE_VERSION_MAJOR    // Major version (e.g., '0')
SITE_VERSION_MINOR    // Minor version (e.g., '0')
SITE_VERSION_PATCH    // Patch version (e.g., '14')
SITE_VERSION_FULL     // Full version (e.g., '0.0.0.14')
SITE_VERSION_NAME     // Version name
SITE_VERSION_TYPE     // Version type
SITE_VERSION_STATUS   // Version status
SITE_VERSION_DATE     // Release date
```

### Helper Functions
```php
get_site_version()           // Returns current version
get_version_info()           // Returns full version array
get_version_badge()          // Returns GitHub badge markdown
get_version_string()         // Returns formatted version string
get_version_footer()         // Returns footer version text
update_version_references()  // Auto-updates version in files
```

## Version Information API

### JSON Endpoint
```
GET /config/version.php?version_info=json
```

Returns:
```json
{
    "version": "0.0.0.14",
    "major": "0",
    "minor": "0",
    "patch": "14",
    "full": "0.0.0.14",
    "name": "Enhanced User Interface & Authentication Experience",
    "type": "User Experience Enhancement - UI/UX Improvements",
    "status": "Production Ready",
    "date": "January 2025",
    "build": "2025-09-14 15:37:15",
    "git_commit": "8cf1da1",
    "git_branch": "master"
}
```

## Version Naming Convention

### Format: X.Y.Z
- **X** (Major): Breaking changes, incompatible API changes
- **Y** (Minor): New features, backward compatible
- **Z** (Patch): Bug fixes, improvements, documentation

### Examples
- `0.0.0.14` - Current version (UI/UX improvements)
- `0.0.0.13` - Admin system overhaul
- `0.1.0.0` - Future major feature release
- `1.0.0.0` - Future stable release

## Best Practices

### When Updating Version
1. **Use the update script** for consistency
2. **Update documentation** immediately
3. **Test thoroughly** before committing
4. **Follow semantic versioning** principles
5. **Update changelog** with detailed changes

### Version Commit Message
```bash
git commit -m "Update version to 0.0.0.15

- Updated all version references across the site
- Added new features: [list features]
- Fixed bugs: [list bugs]
- Updated documentation and changelog"
```

### Pre-Release Checklist
- [ ] Update version in `public/config/version.php`
- [ ] Run `php scripts/update_version.php [version]`
- [ ] Update `README.md` version badge and current version
- [ ] Add entry to `docs/changelogs/CHANGELOG.md`
- [ ] Create `docs/changelogs/v[version].md`
- [ ] Update `docs/releases/RELEASE_NOTES.md`
- [ ] Test all version references are consistent
- [ ] Test the application thoroughly
- [ ] Commit changes with descriptive message

## Troubleshooting

### Version Mismatch
If you see different versions in different places:

1. **Check central file**: `public/config/version.php`
2. **Run update script**: `php scripts/update_version.php [version]`
3. **Verify consistency**: Check all files manually
4. **Clear caches**: If using any caching system

### Missing Version References
If a file shows an old version:

1. **Add to update script**: Edit `scripts/update_version.php`
2. **Update manually**: Edit the file directly
3. **Test thoroughly**: Ensure changes work correctly

## Future Enhancements

### Planned Features
- **Automated version bumping** based on git commits
- **Version validation** to prevent invalid versions
- **Integration with CI/CD** for automatic version updates
- **Version history tracking** for better change management

### Integration Ideas
- **Git hooks** for automatic version updates
- **Package.json** integration for Node.js tools
- **Composer** integration for PHP dependencies
- **Docker** image tagging with version numbers

---

**Last Updated**: January 2025  
**Version**: 0.0.0.14  
**Maintainer**: Development Team
