# 🌍 Language Switching Implementation Architecture

## 📋 Overview

The IslamWiki language switching system is implemented with a comprehensive architecture that handles language detection, switching, and RTL support. Here's how it works:

## 🏗️ Architecture Components

### **1. Backend Services**

#### **LanguageService** (`src/Services/Translation/LanguageService.php`)
- **Language Detection**: URL → Session → Cookie → Browser → Default
- **Language Switching**: Updates session, cookie, and current language
- **RTL Support**: Detects and manages text direction
- **URL Generation**: Creates language-specific URLs
- **CSS Classes**: Generates language-specific CSS classes

#### **LanguageController** (`src/Controllers/LanguageController.php`)
- **API Endpoints**: RESTful language management
- **Language Switching**: Handles AJAX language switch requests
- **Browser Detection**: Detects user's preferred language
- **Data Formatting**: Formats language data for frontend

### **2. Frontend Components**

#### **LanguageSwitcher** (`resources/js/components/language/LanguageSwitcher.tsx`)
- **Dropdown Interface**: Main language selection component
- **RTL Adaptation**: Automatically adapts to RTL languages
- **Mobile Support**: Responsive design for all devices
- **URL Generation**: Creates language-specific URLs

#### **LanguageSwitcherCompact** (`resources/js/components/language/LanguageSwitcher.tsx`)
- **Mobile Version**: Compact language switcher for mobile
- **Touch Friendly**: Optimized for touch interfaces
- **Space Efficient**: Minimal space usage

### **3. API Endpoints**

```
GET  /api/language/current     - Get current language info
POST /api/language/switch      - Switch to different language
GET  /api/language/supported   - Get all supported languages
GET  /api/language/switcher    - Get switcher component data
GET  /api/language/detect      - Detect browser language
```

## 🔄 Language Switching Flow

```mermaid
graph TD
    A[User Visits Website] --> B[Language Detection]
    B --> C{URL Parameter?}
    C -->|Yes| D[Use URL Language]
    C -->|No| E{Session Language?}
    E -->|Yes| F[Use Session Language]
    E -->|No| G{Cookie Language?}
    G -->|Yes| H[Use Cookie Language]
    G -->|No| I{Browser Language?}
    I -->|Yes| J[Use Browser Language]
    I -->|No| K[Use Default Language]
    
    D --> L[Set Current Language]
    F --> L
    H --> L
    J --> L
    K --> L
    
    L --> M[Render Frontend]
    M --> N[User Clicks Language Switcher]
    N --> O[AJAX Request to API]
    O --> P[LanguageService.switchLanguage()]
    P --> Q[Update Session & Cookie]
    Q --> R[Return New Language Data]
    R --> S[Update Frontend UI]
    S --> T[Apply RTL/LTR Classes]
```

## 🎯 Language Detection Priority

1. **URL Parameter** (`?lang=ar`) - Highest priority
2. **Session Storage** - User preference during session
3. **Cookie Storage** - Persistent user preference
4. **Browser Language** - Automatic detection from Accept-Language header
5. **Default Language** - Fallback (English)

## 🌍 Supported Languages

| Language | Code | Native Name | Direction | Flag |
|----------|------|-------------|-----------|------|
| English | `en` | English | LTR | 🇺🇸 |
| Arabic | `ar` | العربية | RTL | 🇸🇦 |
| French | `fr` | Français | LTR | 🇫🇷 |
| Spanish | `es` | Español | LTR | 🇪🇸 |
| German | `de` | Deutsch | LTR | 🇩🇪 |

## ↔️ RTL Support Implementation

### **CSS Classes**
```css
.lang-ar { /* Arabic language styles */ }
.rtl { direction: rtl; }
.ltr { direction: ltr; }
.rtl:space-x-reverse { /* RTL spacing */ }
.rtl:text-right { /* RTL text alignment */ }
```

### **Frontend Adaptation**
- **Layout Direction**: Automatic RTL/LTR detection
- **Icon Positioning**: `rtl:mr-auto`, `rtl:ml-0`
- **Text Alignment**: Right-aligned for Arabic
- **Dropdown Positioning**: RTL-aware positioning
- **Font Optimization**: Arabic font rendering

## 📱 Mobile Responsiveness

### **Desktop Version**
- Full language names displayed
- Dropdown with flags and names
- Hover effects and animations

### **Mobile Version**
- Compact flag-only display
- Touch-friendly interface
- Space-efficient design

## 🔧 Implementation Details

### **Backend Language Service**
```php
class LanguageService {
    // Language detection with priority
    private function detectCurrentLanguage(): void
    
    // Language switching
    public function switchLanguage(string $languageCode): bool
    
    // RTL detection
    public function isCurrentLanguageRTL(): bool
    
    // CSS classes generation
    public function getLanguageCSSClasses(): string
}
```

### **Frontend Language Switcher**
```tsx
interface Language {
  code: string;
  name: string;
  native_name: string;
  flag: string;
  direction: 'ltr' | 'rtl';
  is_current: boolean;
  url: string;
}

const LanguageSwitcher: React.FC<LanguageSwitcherProps> = ({
  currentLanguage,
  availableLanguages,
  onLanguageChange
}) => {
  // RTL detection and adaptation
  const [isRTL, setIsRTL] = useState(false);
  
  // Language switching logic
  const handleLanguageChange = (languageCode: string) => {
    // Switch language and update UI
  };
};
```

## 🚀 Key Features

### **✅ Automatic Detection**
- Detects user's preferred language from browser
- Falls back gracefully to default language
- Remembers user preferences

### **✅ Persistent Storage**
- Session storage for current session
- Cookie storage for long-term preference
- URL parameters for direct language access

### **✅ RTL Support**
- Full Arabic language support
- Automatic text direction detection
- RTL-aware UI components

### **✅ Mobile Responsive**
- Works on all device sizes
- Touch-friendly interface
- Optimized for mobile usage

### **✅ SEO Friendly**
- Language-specific URLs
- Proper meta tags
- Search engine optimization

### **✅ Performance Optimized**
- Minimal overhead
- Efficient language detection
- Cached language data

## 🎉 Benefits

1. **User Experience**: Seamless language switching
2. **Accessibility**: RTL support for Arabic users
3. **SEO**: Language-specific URLs for search engines
4. **Performance**: Optimized for speed and efficiency
5. **Scalability**: Easy to add new languages
6. **Maintenance**: Clean, modular architecture

## 🔮 Future Enhancements

- **More Languages**: Easy to add new language support
- **Regional Variants**: Support for regional language variants
- **Auto-translation**: Integration with translation services
- **Language Analytics**: Track language usage statistics
- **A/B Testing**: Test different language implementations

The language switching system is fully implemented and ready for production use! 🌍
