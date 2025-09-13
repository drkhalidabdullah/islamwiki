# News Bar Extension

A customizable news ticker extension that displays scrolling news updates at the top of pages.

## Features

- **Customizable News Items**: Add, edit, and remove news items through the admin panel
- **Responsive Design**: Adapts to different screen sizes
- **User Controls**: Pause/resume and close functionality
- **Auto-pause**: Optional auto-pause on hover
- **Persistent State**: Remembers user preferences (hidden/shown)
- **Smooth Animation**: CSS-based scrolling animation

## Installation

1. The extension is automatically available in the admin panel under Extensions
2. Enable the extension and configure settings as needed
3. The news bar will appear on all pages when enabled

## Configuration

### Basic Settings

- **Enable/Disable**: Toggle the extension on/off
- **Position**: Choose between top or bottom placement
- **Animation Speed**: Control scrolling speed (5-60 seconds)
- **Show Controls**: Display pause/close buttons

### News Items

- **Time**: Display time for each news item (e.g., "2 hours ago")
- **Text**: The actual news content
- **Add/Remove**: Dynamically manage news items

## Usage

### For Users

- **Pause/Resume**: Click the pause button to stop/start scrolling
- **Close**: Click the close button to hide the news bar
- **Show Again**: Click the floating eye icon to show the news bar again

### For Administrators

1. Go to Admin → System Settings → Extensions
2. Find "News Bar" in the extensions list
3. Configure settings and news items
4. Save changes

## File Structure

```
newsbar/
├── extension.php          # Main extension class
├── assets/
│   ├── css/
│   │   └── newsbar.css    # Extension styles
│   └── js/
│       └── newsbar.js     # Extension JavaScript
└── README.md              # This file
```

## Customization

### CSS Variables

You can customize the appearance by overriding CSS variables:

```css
.newsbar {
    --newsbar-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --newsbar-text-color: white;
    --newsbar-padding: 0.75rem 0;
}
```

### JavaScript Events

The extension fires custom events for integration:

```javascript
// News bar hidden
document.addEventListener('newsbar:hidden', function(e) {
    console.log('News bar was hidden');
});

// News bar shown
document.addEventListener('newsbar:shown', function(e) {
    console.log('News bar was shown');
});
```

## Version History

- **0.0.1**: Initial release with basic functionality

## Support

For issues or feature requests, please contact the development team.

