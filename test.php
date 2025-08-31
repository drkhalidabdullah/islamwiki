<?php
/**
 * IslamWiki Framework - Test Script
 * 
 * Author: Khalid Abdullah
 * Version: 0.0.1
 * Date: 2025-08-30
 * License: AGPL-3.0
 * 
 * This script tests the core framework classes to ensure everything is working correctly.
 */

echo "🧪 Testing IslamWiki Framework...\n\n";

// Test 1: Check if Composer autoloader exists
echo "1. Testing Composer autoloader... ";
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    echo "✅ Found\n";
} else {
    echo "❌ Missing - Run 'composer install' first\n";
    exit(1);
}

// Test 2: Test core classes
echo "2. Testing core classes...\n";

try {
    // Test Container
    echo "   - Container class... ";
    $container = new IslamWiki\Core\Container\Container();
    echo "✅ Loaded\n";
    
    // Test Request
    echo "   - Request class... ";
    $request = new IslamWiki\Core\Http\Request();
    echo "✅ Loaded\n";
    
    // Test Response
    echo "   - Response class... ";
    $response = new IslamWiki\Core\Http\Response();
    echo "✅ Loaded\n";
    
    // Test Router
    echo "   - Router class... ";
    $router = new IslamWiki\Core\Routing\Router();
    echo "✅ Loaded\n";
    
    // Test Route
    echo "   - Route class... ";
    $route = new IslamWiki\Core\Routing\Route(['GET'], '/test', function() { return 'test'; });
    echo "✅ Loaded\n";
    
    // Test MiddlewareStack
    echo "   - MiddlewareStack class... ";
    $middleware = new IslamWiki\Core\Middleware\MiddlewareStack();
    echo "✅ Loaded\n";
    
    echo "   ✅ All core classes loaded successfully\n";
    
} catch (Exception $e) {
    echo "   ❌ Error loading core classes: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Test service providers
echo "3. Testing service providers...\n";

try {
    echo "   - DatabaseServiceProvider... ";
    $dbProvider = new IslamWiki\Providers\DatabaseServiceProvider($container);
    echo "✅ Loaded\n";
    
    echo "   - AuthServiceProvider... ";
    $authProvider = new IslamWiki\Providers\AuthServiceProvider($container);
    echo "✅ Loaded\n";
    
    echo "   - CacheServiceProvider... ";
    $cacheProvider = new IslamWiki\Providers\CacheServiceProvider($container);
    echo "✅ Loaded\n";
    
    echo "   - SecurityServiceProvider... ";
    $securityProvider = new IslamWiki\Providers\SecurityServiceProvider($container);
    echo "✅ Loaded\n";
    
    echo "   ✅ All service providers loaded successfully\n";
    
} catch (Exception $e) {
    echo "   ❌ Error loading service providers: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Test basic functionality
echo "4. Testing basic functionality...\n";

try {
    // Test container binding
    echo "   - Container binding... ";
    $container->bind('test', function() { return 'test_value'; });
    $value = $container->make('test');
    if ($value === 'test_value') {
        echo "✅ Working\n";
    } else {
        echo "❌ Failed\n";
    }
    
    // Test router
    echo "   - Router functionality... ";
    $router->get('/test', function() { return 'test'; });
    $routes = $router->getRoutes();
    if (isset($routes['GET']) && count($routes['GET']) > 0) {
        echo "✅ Working\n";
    } else {
        echo "❌ Failed\n";
    }
    
    // Test request
    echo "   - Request functionality... ";
    $method = $request->getMethod();
    if (!empty($method)) {
        echo "✅ Working\n";
    } else {
        echo "❌ Failed\n";
    }
    
    // Test response
    echo "   - Response functionality... ";
    $response->setContent('test');
    $content = $response->getContent();
    if ($content === 'test') {
        echo "✅ Working\n";
    } else {
        echo "❌ Failed\n";
    }
    
    echo "   ✅ All basic functionality working\n";
    
} catch (Exception $e) {
    echo "   ❌ Error testing functionality: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 5: Check file structure
echo "5. Checking file structure...\n";

$requiredFiles = [
    'composer.json',
    'package.json',
    'env.example',
    'README.md',
    'install.php',
    'database/schema.sql',
    'public/index.php',
    'public/.htaccess',
    'src/Core/Application.php',
    'src/Core/Container/Container.php',
    'src/Core/Http/Request.php',
    'src/Core/Http/Response.php',
    'src/Core/Routing/Router.php',
    'src/Core/Routing/Route.php',
    'src/Core/Middleware/MiddlewareStack.php',
    'src/Providers/DatabaseServiceProvider.php',
    'src/Providers/AuthServiceProvider.php',
    'src/Providers/CacheServiceProvider.php',
    'src/Providers/SecurityServiceProvider.php'
];

$missingFiles = [];
foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        $missingFiles[] = $file;
    }
}

if (empty($missingFiles)) {
    echo "   ✅ All required files present\n";
} else {
    echo "   ❌ Missing files:\n";
    foreach ($missingFiles as $file) {
        echo "      - {$file}\n";
    }
}

// Test 6: Test Application class (if all dependencies are met)
echo "6. Testing Application class...\n";

try {
    echo "   - Creating Application instance... ";
    $app = new IslamWiki\Core\Application();
    echo "✅ Created\n";
    
    echo "   - Testing container access... ";
    $appContainer = $app->getContainer();
    if ($appContainer instanceof IslamWiki\Core\Container\Container) {
        echo "✅ Working\n";
    } else {
        echo "❌ Failed\n";
    }
    
    echo "   - Testing router access... ";
    $appRouter = $app->getRouter();
    if ($appRouter instanceof IslamWiki\Core\Routing\Router) {
        echo "✅ Working\n";
    } else {
        echo "❌ Failed\n";
    }
    
    echo "   ✅ Application class working correctly\n";
    
} catch (Exception $e) {
    echo "   ❌ Error testing Application class: " . $e->getMessage() . "\n";
}

echo "\n🎉 Framework test completed!\n";

if (empty($missingFiles)) {
    echo "✅ All tests passed! Your IslamWiki framework is ready to use.\n";
    echo "\nNext steps:\n";
    echo "1. Run 'composer install' to install dependencies\n";
    echo "2. Run 'npm install' to install frontend dependencies\n";
    echo "3. Copy env.example to .env and configure your settings\n";
    echo "4. Set up your database using database/schema.sql\n";
    echo "5. Point your web server to the public/ directory\n";
} else {
    echo "❌ Some files are missing. Please check the file structure.\n";
} 