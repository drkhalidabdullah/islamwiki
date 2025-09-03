#!/bin/bash

echo "🚀 Building IslamWiki Framework..."
echo "📦 Running npm build..."

# Build the project
npm run build

if [ $? -eq 0 ]; then
    echo "✅ Build successful!"
    echo "📁 Copying built files to public directory..."
    
    # Copy all built assets to public directory
    cp -r dist/assets/* public/assets/
    cp dist/resources/index.html public/
    
    echo "✅ Files copied successfully!"
    echo "🌐 New build is now live at http://localhost"
    echo "📝 Remember to hard refresh your browser (Ctrl+F5) to see changes!"
else
    echo "❌ Build failed!"
    exit 1
fi 