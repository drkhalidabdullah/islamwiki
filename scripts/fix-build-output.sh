#!/bin/bash

# Fix build output structure
# This script ensures the build output is properly organized in the public directory

echo "🔧 Fixing build output structure..."

# Check if dist directory exists
if [ ! -d "dist" ]; then
    echo "❌ No dist directory found. Run 'npm run build' first."
    exit 1
fi

# Remove any existing resources directory in public
if [ -d "public/resources" ]; then
    echo "🗑️  Removing existing public/resources directory..."
    rm -rf public/resources
fi

# Copy all files from dist to public
echo "📁 Copying build files to public directory..."
cp -r dist/* public/

# Fix the resources subdirectory issue
if [ -d "public/resources" ]; then
    echo "🔄 Fixing resources subdirectory..."
    if [ -f "public/resources/index.html" ]; then
        mv public/resources/index.html public/index.html
        echo "✅ Moved index.html to public root"
    fi
    rm -rf public/resources
    echo "🗑️  Removed resources subdirectory"
fi

# Clean up dist directory
echo "🧹 Cleaning up dist directory..."
rm -rf dist

# Ensure critical files are in place
echo "✅ Verifying critical files..."

if [ ! -f "public/.htaccess" ]; then
    echo "⚠️  Warning: .htaccess file not found in public directory"
fi

if [ ! -f "public/api/index.php" ]; then
    echo "⚠️  Warning: API index.php file not found"
fi

if [ ! -f "public/index.html" ]; then
    echo "⚠️  Warning: index.html file not found in public directory"
fi

echo "🎉 Build output structure fixed!"
echo "📁 Public directory contents:"
ls -la public/ 