<?php
/**
 * Test PostgreSQL connection to SuperHosting.bg
 * Run this script to verify database connectivity
 */

// Load environment variables
if (file_exists(__DIR__ . '/../.env.production')) {
    $lines = file(__DIR__ . '/../.env.production', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '5432';
$dbname = $_ENV['DB_NAME'] ?? 'superhosting_postgresql';
$user = $_ENV['DB_USER'] ?? 'superhosting_user';
$pass = $_ENV['DB_PASS'] ?? '';
$sslmode = $_ENV['DB_SSLMODE'] ?? 'prefer';

echo "🔌 Testing PostgreSQL connection to SuperHosting.bg...\n";
echo "Host: $host:$port\n";
echo "Database: $dbname\n";
echo "User: $user\n";
echo "SSL Mode: $sslmode\n\n";

try {
    // Test connection
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=$sslmode",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Connection successful!\n\n";
    
    // Test basic queries
    echo "📊 Database Information:\n";
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "PostgreSQL Version: $version\n\n";
    
    // Check tables
    echo "📋 Tables:\n";
    $stmt = $pdo->query("
        SELECT table_name, 
               (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = t.table_name) as column_count
        FROM information_schema.tables t 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll();
    
    foreach ($tables as $table) {
        echo "  - {$table['table_name']} ({$table['column_count']} columns)\n";
    }
    
    // Check record counts
    echo "\n📊 Record Counts:\n";
    $tables_to_check = ['properties', 'property_images', 'services', 'pages', 'users'];
    
    foreach ($tables_to_check as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "  - $table: $count records\n";
        } catch (PDOException $e) {
            echo "  - $table: Table not found\n";
        }
    }
    
    // Check extensions
    echo "\n🔧 Extensions:\n";
    $stmt = $pdo->query("SELECT extname FROM pg_extension ORDER BY extname");
    $extensions = $stmt->fetchAll();
    
    foreach ($extensions as $ext) {
        echo "  - {$ext['extname']}\n";
    }
    
    // Test UUID generation
    echo "\n🆔 UUID Test:\n";
    $stmt = $pdo->query("SELECT uuid_generate_v4() as test_uuid");
    $uuid = $stmt->fetchColumn();
    echo "  Generated UUID: $uuid\n";
    
    echo "\n🎉 All tests passed! SuperHosting PostgreSQL is ready.\n";
    
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    echo "\n🔧 Troubleshooting:\n";
    echo "1. Verify PostgreSQL is running on SuperHosting\n";
    echo "2. Check database credentials in .env.production\n";
    echo "3. Ensure user has proper privileges\n";
    echo "4. Check firewall/network settings\n";
    exit(1);
}
?>