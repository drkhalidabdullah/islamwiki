<?php

/**
 * Test Database Dashboard API Integration
 * 
 * This script tests the integration of the database dashboard API endpoints
 * 
 * @author Khalid Abdullah
 * @version 0.0.4
 * @date 2025-01-27
 * @license AGPL-3.0
 */

require_once 'vendor/autoload.php';

use IslamWiki\Core\Application;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;

echo "🧪 **Testing Database Dashboard API Integration**\n";
echo "================================================\n\n";

try {
    // Create application instance
    $app = new Application();
    
    echo "✅ **Application created successfully**\n";
    
    // Test database overview endpoint
    echo "\n🔍 **Testing Database Overview Endpoint**\n";
    
    // Simulate the request by setting global variables
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/admin/api/database/overview';
    $_SERVER['QUERY_STRING'] = '';
    
    $request = Request::createFromGlobals();
    $response = $app->handle($request);
    
    if ($response) {
        echo "   - Response received\n";
        echo "   - Status: " . $response->getStatusCode() . "\n";
        echo "   - Content: " . substr(json_encode($response->getContent()), 0, 100) . "...\n";
    } else {
        echo "   ❌ No response received\n";
    }
    
    // Test database health endpoint
    echo "\n🔍 **Testing Database Health Endpoint**\n";
    
    // Simulate the request by setting global variables
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/admin/api/database/health';
    $_SERVER['QUERY_STRING'] = '';
    
    $request = Request::createFromGlobals();
    $response = $app->handle($request);
    
    if ($response) {
        echo "   - Response received\n";
        echo "   - Status: " . $response->getStatusCode() . "\n";
        echo "   - Content: " . substr(json_encode($response->getContent()), 0, 100) . "...\n";
    } else {
        echo "   ❌ No response received\n";
    }
    
    echo "\n✅ **Database Dashboard API Integration Test Complete**\n";
    
} catch (Exception $e) {
    echo "❌ **Error during testing:** " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 