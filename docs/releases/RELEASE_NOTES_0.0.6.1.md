# Release Notes v0.0.6.1

**Release Date:** January 27, 2025  
**Version:** 0.0.6.1  
**Type:** Minor Update

## Overview

This release focuses on language switching improvements and translation system updates. While some language switching issues remain to be addressed in future releases, this version includes significant enhancements to the translation infrastructure and settings management.

## New Features

### Translation System Enhancements
- **New Translation API Endpoint**: Added `/api/translation.php` for improved translation services
- **Enhanced Translation Service**: Updated `TranslationService.ts` with better API integration
- **Translation Manager Component**: New `TranslationManager.tsx` component for managing translations
- **Translation Hook**: Added `useTranslation.ts` hook for React components

### Settings Improvements
- **Language Preferences**: Enhanced language preference settings in the settings page
- **Better Language Switcher**: Improved `LanguageSwitcher.tsx` component
- **Settings Persistence**: Better handling of user language preferences

### Database Updates
- **Translation System Migration**: Added migration `2025_01_27_000005_create_translation_system.php`
- **Enhanced Language Support**: Better database structure for translation management

## Technical Improvements

### Code Organization
- **File Restructuring**: Moved React components from `src/` to `resources/js/` directory
- **Legacy Code Cleanup**: Removed outdated PHP framework files and components
- **Better Project Structure**: Improved organization of frontend and backend code

### Testing Infrastructure
- **Comprehensive Test Files**: Added multiple test files for translation functionality
- **Language Testing**: Enhanced testing for language switching and translation services
- **Settings Testing**: Improved testing for settings page functionality

## Files Added
- `public/api/translation.php` - New translation API endpoint
- `resources/js/components/translation/TranslationManager.tsx` - Translation management component
- `resources/js/hooks/useTranslation.ts` - Translation hook for React
- `database/migrations/2025_01_27_000005_create_translation_system.php` - Translation system migration
- Multiple test files for translation and language functionality

## Files Modified
- `resources/js/App.tsx` - Updated main application component
- `resources/js/components/language/LanguageSwitcher.tsx` - Enhanced language switcher
- `resources/js/components/layout/Header.tsx` - Updated header with better language support
- `resources/js/pages/SettingsPage.tsx` - Improved settings page
- `public/api/language_endpoints.php` - Enhanced language API endpoints
- Various other components and services

## Files Removed
- Legacy PHP framework files from `src/` directory
- Outdated React components and services
- Unused authentication and database management files

## Known Issues

- **Language Switching**: Some language switching functionality may not work completely as expected
- **Translation Persistence**: Some translation preferences may not persist correctly across sessions
- **API Integration**: Some translation API endpoints may need further refinement

## Next Steps

The next major phase will focus on **Content Management System** development, which will include:
- Article creation and editing
- Content categorization
- Media management
- Content versioning
- Search functionality

## Installation Notes

1. Run database migrations: `php database/migrations/2025_01_27_000005_create_translation_system.php`
2. Clear any cached translations
3. Test language switching functionality
4. Verify settings page functionality

## Breaking Changes

- **File Structure**: React components moved from `src/` to `resources/js/`
- **API Endpoints**: Some translation API endpoints have been updated
- **Database Schema**: New translation system tables added

## Contributors

- Development Team: Language switching improvements and translation system updates
- Testing: Comprehensive testing of translation functionality

---

**Note**: This release represents a significant step forward in the translation system, though some language switching issues remain to be addressed in future releases. The focus now shifts to content management system development.
