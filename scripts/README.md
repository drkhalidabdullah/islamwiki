# Scripts Directory

This directory contains utility scripts for the IslamWiki project.

## Header Management

### restore_header.sh
Restores the working header from backup.

**Usage:**
```bash
# From project root
./scripts/restore_header.sh

# Or from anywhere
/var/www/html/scripts/restore_header.sh
```

**What it does:**
- Restores the header from `/var/www/html/backups/header_backup_working.php`
- Copies it to `/var/www/html/public/includes/header.php`
- Confirms successful restoration

**When to use:**
- If the header gets corrupted or modified incorrectly
- To restore the working header with all features:
  - Conditional navigation (Dashboard/Friends only for logged-in users)
  - Messages and notifications dropdowns
  - Dashboard link in user dropdown
  - Search box (hidden on search page)
  - All social features and proper navigation

## Backup Location
The header backup is stored in: `/var/www/html/backups/header_backup_working.php`
