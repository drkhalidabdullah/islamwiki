<?php

/**
 * Settings Debug Test
 * 
 * Debug the settings save/load issue
 * 
 * @author Khalid Abdullah
 * @version 0.0.6
 * @license AGPL-3.0
 */

// Simple database connection for debugging
$host = 'localhost';
$dbname = 'islamwiki';
$username = 'islamwiki';
$password = 'islamwiki123';

echo "🔍 Settings Debug Test\n";
echo "======================\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get user data for khalid
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute(['khalid']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ User found: " . $user['username'] . "\n";
        echo "📧 Email: " . $user['email'] . "\n";
        echo "👤 First Name: " . ($user['first_name'] ?? 'NULL') . "\n";
        echo "👤 Last Name: " . ($user['last_name'] ?? 'NULL') . "\n";
        echo "📝 Bio: " . ($user['bio'] ?? 'NULL') . "\n";
        echo "🏷️ Display Name: " . ($user['display_name'] ?? 'NULL') . "\n";
        echo "⚙️ Preferences: " . ($user['preferences'] ?? 'NULL') . "\n\n";
        
        // Parse preferences
        $preferences = json_decode($user['preferences'] ?? '{}', true);
        echo "📋 Parsed Preferences:\n";
        echo json_encode($preferences, JSON_PRETTY_PRINT) . "\n\n";
        
        // Check profile data specifically
        if (isset($preferences['profile'])) {
            echo "👤 Profile Data:\n";
            echo "📞 Phone: " . ($preferences['profile']['phone'] ?? 'NULL') . "\n";
            echo "🎂 Date of Birth: " . ($preferences['profile']['date_of_birth'] ?? 'NULL') . "\n";
            echo "⚧ Gender: " . ($preferences['profile']['gender'] ?? 'NULL') . "\n";
            echo "📍 Location: " . ($preferences['profile']['location'] ?? 'NULL') . "\n";
            echo "🌐 Website: " . ($preferences['profile']['website'] ?? 'NULL') . "\n";
            echo "🖼️ Avatar URL: " . ($preferences['profile']['avatar_url'] ?? 'NULL') . "\n";
        } else {
            echo "❌ No profile data found in preferences\n";
        }
        
    } else {
        echo "❌ User 'khalid' not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🔍 Test completed\n";
