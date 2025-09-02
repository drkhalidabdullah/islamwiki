#!/bin/bash

# IslamWiki Framework Build Script
# Author: Khalid Abdullah
# Version: 0.0.2

echo "🏗️  Building IslamWiki React App..."
echo "=================================="

# Clean previous build
echo "🧹 Cleaning previous build..."
rm -rf dist

# Build the app
echo "🔨 Building with Vite..."
npx vite build

# Copy to public directory
echo "📁 Copying to public directory..."
cp -r dist/* public/

echo ""
echo "✅ Build completed successfully!"
echo "🌐 App is now available at: http://localhost"
echo "📱 Admin Dashboard: http://localhost/admin"
echo "🧪 Tests Page: http://localhost/tests"
echo ""
echo "💡 To rebuild after changes, run: ./build.sh" 