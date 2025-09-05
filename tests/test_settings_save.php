<?php

/**
 * Settings Save Test
 * 
 * Test the settings save operation
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

echo "🔍 Settings Save Test\n";
echo "=====================\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Simulate the account section save
    $userId = 1; // Assuming khalid has ID 1
    
    // Get current preferences
    $stmt = $pdo->prepare("SELECT preferences FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $currentPreferences = $stmt->fetch();
    $preferences = json_decode($currentPreferences['preferences'] ?? '{}', true);
    
    echo "📋 Current preferences before save:\n";
    echo "Gender: " . ($preferences['profile']['gender'] ?? 'NULL') . "\n";
    echo "Location: " . ($preferences['profile']['location'] ?? 'NULL') . "\n\n";
    
    // Simulate updating gender to "male"
    $profileData = [
        'phone' => $preferences['profile']['phone'] ?? '',
        'date_of_birth' => $preferences['profile']['date_of_birth'] ?? '',
        'gender' => 'male', // This should be the new value
        'location' => 'New York', // This should be the new value
        'website' => $preferences['profile']['website'] ?? '',
        'avatar_url' => $preferences['profile']['avatar_url'] ?? '',
        'social_links' => $preferences['profile']['social_links'] ?? []
    ];
    
    // Merge profile data with existing preferences
    $preferences['profile'] = array_merge($preferences['profile'] ?? [], $profileData);
    
    echo "📋 New preferences to save:\n";
    echo "Gender: " . $preferences['profile']['gender'] . "\n";
    echo "Location: " . $preferences['profile']['location'] . "\n\n";
    
    // Save the preferences
    $stmt = $pdo->prepare("UPDATE users SET preferences = ? WHERE id = ?");
    $result = $stmt->execute([json_encode($preferences), $userId]);
    
    if ($result) {
        echo "✅ Preferences saved successfully!\n\n";
        
        // Verify the save
        $stmt = $pdo->prepare("SELECT preferences FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $savedPreferences = $stmt->fetch();
        $savedData = json_decode($savedPreferences['preferences'], true);
        
        echo "📋 Verified saved preferences:\n";
        echo "Gender: " . ($savedData['profile']['gender'] ?? 'NULL') . "\n";
        echo "Location: " . ($savedData['profile']['location'] ?? 'NULL') . "\n";
        
        if ($savedData['profile']['gender'] === 'male' && $savedData['profile']['location'] === 'New York') {
            echo "✅ Save operation working correctly!\n";
        } else {
            echo "❌ Save operation failed - data not saved correctly\n";
        }
    } else {
        echo "❌ Failed to save preferences\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🔍 Test completed\n";
