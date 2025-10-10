<?php
/**
 * Neon Database Connection Test
 * 
 * Tests connection to Neon PostgreSQL with current environment variables.
 * Shows DSN (with password masked) and connection status.
 * 
 * Usage: https://your-domain.com/backend/db_neon_check.php
 */

header('Content-Type: application/json; charset=utf-8');

$host = getenv('DB_HOST') ?: 'not-set';
$port = getenv('DB_PORT') ?: '5432';
$db = getenv('DB_DATABASE') ?: getenv('DB_NAME') ?: 'not-set';
$user = getenv('DB_USERNAME') ?: getenv('DB_USER') ?: 'not-set';
$pass = getenv('DB_PASSWORD') ?: getenv('DB_PASS') ?: '';
$ssl = getenv('DB_SSLMODE') ?: 'require';
$opt = getenv('DB_OPTIONS') ?: '';

// Build DSN
$dsn = "pgsql:host={$host};port={$port};dbname={$db};sslmode={$ssl}";
if (!empty($opt)) {
    $dsn .= ';' . $opt;
}

// Auto-inject Neon endpoint if needed (same logic as Database.php)
$isNeon = (strpos($host, '.neon.tech') !== false);
if ($isNeon && stripos($dsn, 'options=endpoint=') === false) {
    $firstLabel = strtok($host, '.');
    $endpointId = preg_replace('/-pooler$/', '', $firstLabel);
    if (strpos($endpointId, 'ep-') === 0) {
        $dsn .= ';options=endpoint=' . $endpointId . ';channel_binding=disable';
        if (stripos($dsn, 'sslmode=') === false) {
            $dsn .= ';sslmode=require';
        }
    }
}

// Mask password in DSN for display
$dsnDisplay = str_replace($pass, '***', $dsn);

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10
    ]);
    
    // Test query
    $stmt = $pdo->query('SELECT current_database() as db, version() as pg_version');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'ok' => true,
        'message' => 'Successfully connected to Neon PostgreSQL',
        'database' => $result['db'],
        'postgresql_version' => substr($result['pg_version'], 0, 60),
        'host' => $host,
        'is_neon' => $isNeon,
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
        'is_neon' => $isNeon,
        'dsn' => $dsnDisplay
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
