#!/bin/bash
# Header Recovery Script
# This script restores the working header from backup

echo "Restoring header from backup..."
cp /var/www/html/backups/header_backup_working.php /var/www/html/public/includes/header.php

if [ $? -eq 0 ]; then
    echo "✅ Header restored successfully!"
    echo "Current header features:"
    echo "  - Conditional navigation (Dashboard/Friends only for logged-in users)"
    echo "  - Messages and notifications dropdowns"
    echo "  - Dashboard link in user dropdown"
    echo "  - Search box (hidden on search page)"
    echo "  - All social features and proper navigation"
else
    echo "❌ Error restoring header!"
    exit 1
fi
