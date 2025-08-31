# IslamWiki Framework - Testing Guide

**Author:** Khalid Abdullah  
**Version:** 0.0.1  
**Date:** 2025-08-30  
**License:** AGPL-3.0  

## 🧪 **Testing Structure**

This directory contains the complete test suite for the IslamWiki Framework.

```
tests/
├── Unit/                    # Unit tests for individual components
│   ├── Core/               # Core framework tests
│   ├── Services/           # Service layer tests
│   └── Controllers/        # Controller tests
├── Integration/             # Integration tests
├── Feature/                 # Feature/API tests
├── bootstrap.php            # Test environment setup
├── run_tests.php           # Main test runner
└── README.md               # This file
```

## 🚀 **Running Tests**

### **Run All Tests**
```bash
php tests/run_tests.php
```

### **Run Individual Test Categories**
```bash
# Core Framework Tests
php tests/Unit/Core/CoreFrameworkTest.php

# API Endpoint Tests
php tests/Feature/ApiEndpointsTest.php
```

### **Run Tests with Verbose Output**
```bash
php tests/run_tests.php --verbose
```

## 📋 **Test Categories**

### **Unit Tests (`tests/Unit/`)**
- **Core Framework**: Container, Database, Cache, HTTP classes
- **Services**: Wiki, User, Content, Authentication services
- **Controllers**: API and web controllers

### **Feature Tests (`tests/Feature/`)**
- **API Endpoints**: RESTful API functionality
- **Authentication**: Login, logout, token validation
- **Content Operations**: CRUD operations for articles and users

### **Integration Tests (`tests/Integration/`)**
- **Database Integration**: Real database operations
- **Service Integration**: Service-to-service communication
- **End-to-End**: Complete user workflows

## 🔧 **Test Configuration**

### **Environment Variables**
- `APP_ENV=testing` - Sets testing environment
- `CACHE_DRIVER=file` - Uses file-based caching for tests
- `CACHE_PATH` - Points to test-specific cache directory

### **Test Storage**
- **Location**: `storage/test/`
- **Cache**: `storage/test/cache/`
- **Logs**: `storage/test/logs/`

## 📊 **Test Results**

The test runner provides:
- **Execution Time**: Total time to run all tests
- **Success Rate**: Percentage of passed tests
- **Detailed Output**: Individual test results
- **Exit Codes**: 0 for success, 1 for failures

## 🎯 **Adding New Tests**

### **1. Create Test File**
```php
<?php
// tests/Unit/YourComponent/YourComponentTest.php

require_once __DIR__ . '/../../../bootstrap.php';

echo "🧪 Testing Your Component...\n";

// Your test logic here
echo "✅ PASSED\n";
```

### **2. Add to Test Runner**
Update `tests/run_tests.php`:
```php
$testCategories = [
    'Core Framework' => 'tests/Unit/Core/CoreFrameworkTest.php',
    'API Endpoints' => 'tests/Feature/ApiEndpointsTest.php',
    'Your Component' => 'tests/Unit/YourComponent/YourComponentTest.php'  // Add this
];
```

## 🚨 **Test Best Practices**

1. **Isolation**: Each test should be independent
2. **Cleanup**: Clean up test data after each test
3. **Naming**: Use descriptive test names
4. **Assertions**: Test one thing per test method
5. **Documentation**: Comment complex test logic

## 🔍 **Troubleshooting**

### **Common Issues**
- **Autoloader errors**: Ensure `vendor/autoload.php` exists
- **Permission errors**: Check `storage/test/` directory permissions
- **Database errors**: Verify test database configuration

### **Debug Mode**
```bash
# Enable debug output
php -d display_errors=1 tests/run_tests.php
```

## 📚 **Related Documentation**

- [Framework Overview](../docs/IslamWiki_Framework_Overview.md)
- [API Reference](../docs/architecture/API_REFERENCE.md)
- [Development Guide](../docs/guides/DEVELOPER_GUIDE.md)

---

**Status:** ✅ **Test Suite Ready**  
**Last Updated:** August 30, 2025  
**Maintainer:** Khalid Abdullah 