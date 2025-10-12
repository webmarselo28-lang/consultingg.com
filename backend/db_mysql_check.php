<?php
/**
 * MySQL Database Connection Test
 * 
 * Tests connection to MySQL with current environment variables.
 * Shows DSN (with password masked) and connection status.
 * 
 * Usage: https://your-domain.com/backend/db_mysql_check.php
 */

header('Content-Type: application/json; charset=utf-8');

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '3306';
$db = $_ENV['DB_DATABASE'] ?? $_ENV['DB_NAME'] ?? 'consultingg';
$user = $_ENV['DB_USERNAME'] ?? $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASSWORD'] ?? $_ENV['DB_PASS'] ?? '';
$charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

// Build DSN
$dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

// Mask password in DSN for display
$dsnDisplay = $dsn;

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    // Test query
    $stmt = $pdo->query('SELECT DATABASE() as db, VERSION() as mysql_version');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'ok' => true,
        'message' => 'Successfully connected to MySQL',
        'database' => $result['db'],
        'mysql_version' => $result['mysql_version'],
        'host' => $host,
        'port' => $port,
        'charset' => $charset,
        'dsn' => $dsnDisplay
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
        'host' => $host,
        'database' => $db,
        'user' => $user,
        'port' => $port,
        'dsn' => $dsnDisplay
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
