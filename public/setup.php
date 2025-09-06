<?php
// Setup script for IslamWiki Clean PHP Site
echo "<h1>IslamWiki Clean PHP Setup</h1>";

// Check PHP version
echo "<h2>System Check</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";

// Check if PDO MySQL is available
if (extension_loaded('pdo_mysql')) {
    echo "✓ PDO MySQL extension is available<br>";
} else {
    echo "✗ PDO MySQL extension is NOT available<br>";
    exit;
}

// Test database connection
echo "<h2>Database Setup</h2>";
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    echo "✓ Database connection successful<br>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS islamwiki_clean CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database 'islamwiki_clean' created/verified<br>";
    
    // Run setup script
    $sql = file_get_contents('setup_database.sql');
    $pdo->exec($sql);
    echo "✓ Database tables created successfully<br>";
    
    echo "<h2>Setup Complete!</h2>";
    echo "<p>Your IslamWiki site is ready to use.</p>";
    echo "<p><a href='index.php'>Go to Homepage</a></p>";
    echo "<p><strong>Default Admin Login:</strong><br>";
    echo "Username: admin<br>";
    echo "Password: admin123</p>";
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
    echo "<p>Please check your database configuration in config/database.php</p>";
}
?>
