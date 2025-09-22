<?php
/**
 * Database Installation Script for Supabase PostgreSQL
 * Run this file once to verify the PostgreSQL connection
 */

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$host = $_ENV['DB_HOST'] ?? 'db.mveeovfztfczibtvkpas.supabase.co';
$username = $_ENV['DB_USER'] ?? 'postgres';
$password = $_ENV['DB_PASS'] ?? 'PoloSport88*';
$database = $_ENV['DB_NAME'] ?? 'postgres';
$port = $_ENV['DB_PORT'] ?? '5432';
$sslmode = $_ENV['DB_SSLMODE'] ?? 'require';

try {
    // Connect to Supabase PostgreSQL
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$database;sslmode=$sslmode", 
        $username, 
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Connected to Supabase PostgreSQL successfully!\n";
    echo "📊 Database: $database on $host:$port\n";
    
    // Test basic query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM properties");
    $result = $stmt->fetch();
    echo "📋 Properties in database: " . $result['count'] . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM property_images");
    $result = $stmt->fetch();
    echo "📸 Images in database: " . $result['count'] . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $result = $stmt->fetch();
    echo "👤 Admin users: " . $result['count'] . "\n";
    
    echo "\n🎉 Supabase PostgreSQL connection verified successfully!\n";
    echo "💡 No schema installation needed - using existing Supabase database.\n";
    
} catch (PDOException $e) {
    echo "❌ Supabase PostgreSQL connection failed: " . $e->getMessage() . "\n";
    echo "🔧 Please check your Supabase credentials and network connectivity.\n";
    exit(1);
}
?>