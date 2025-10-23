<?php
// Test Property Query with MySQL-compatible syntax
// Access: https://consultingg.com/backend/test_property_query.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/vendor/autoload.php';

try {
    // Load environment
    if (file_exists(__DIR__ . '/.env')) {
        Dotenv\Dotenv::createImmutable(__DIR__)->load();
    }
    
    // Connect to database
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $dbname = $_ENV['DB_DATABASE'] ?? 'yogahonc_consultingg78';
    $user = $_ENV['DB_USERNAME'] ?? 'yogahonc_consultingg78';
    $pass = $_ENV['DB_PASSWORD'] ?? '';
    
    $dsn = "mysql:host={\$host};port={\$port};dbname={\$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Test the CURRENT query (likely has Postgres syntax)
    $response = ['tests' => []];
    
    // Test 1: Simple property query (should work)
    try {
        $stmt = $pdo->query("SELECT id, property_code, title, price FROM properties LIMIT 1");
        $result = $stmt->fetch();
        
        $response['tests']['simple_query'] = [
            'name' => 'Simple Property Query',
            'status' => 'PASS',
            'result' => $result
        ];
    } catch (PDOException $e) {
        $response['tests']['simple_query'] = [
            'name' => 'Simple Property Query',
            'status' => 'FAIL',
            'error' => $e->getMessage()
        ];
    }
    
    // Test 2: Query with PostgreSQL syntax (will fail)
    try {
        $stmt = $pdo->query("\n            SELECT p.id, p.property_code, p.title,
                   COALESCE(
                      (SELECT json_agg(
                          json_build_object('id', pi.id)
                      ) FROM property_images pi WHERE pi.property_id = p.id),
                      '[]'::json
                   ) as images
            FROM properties p LIMIT 1
        ");
        
        $response['tests']['postgres_syntax'] = [
            'name' => 'PostgreSQL Syntax (json_agg)',
            'status' => 'PASS',
            'note' => 'This should FAIL in MySQL!'
        ];
    } catch (PDOException $e) {
        $response['tests']['postgres_syntax'] = [
            'name' => 'PostgreSQL Syntax (json_agg)',
            'status' => 'EXPECTED_FAIL',
            'error' => $e->getMessage(),
            'note' => 'This proves MySQL does not support PostgreSQL functions'
        ];
    }
    
    // Test 3: MySQL-compatible query with subquery
    try {
        $stmt = $pdo->query("\n            SELECT p.id, p.property_code, p.title, p.price,
                   (SELECT GROUP_CONCAT(
                       CONCAT('{\"id\":\"', pi.id, '\",\"image_url\":\"', pi.image_url, '\",\"is_main\":', pi.is_main, '}')
                       ORDER BY pi.is_main DESC, pi.sort_order ASC
                       SEPARATOR ',')
                   FROM property_images pi WHERE pi.property_id = p.id) as images_json
            FROM properties p 
            WHERE p.active = 1
            LIMIT 1
        ");
        $result = $stmt->fetch();
        
        $response['tests']['mysql_compatible'] = [
            'name' => 'MySQL-Compatible Query',
            'status' => 'PASS',
            'result' => $result,
            'note' => 'Uses GROUP_CONCAT instead of json_agg'
        ];
    } catch (PDOException $e) {
        $response['tests']['mysql_compatible'] = [
            'name' => 'MySQL-Compatible Query',
            'status' => 'FAIL',
            'error' => $e->getMessage()
        ];
    }
    
    // Test 4: Separate queries (recommended approach)
    try {
        // Get property
        $stmt = $pdo->query("SELECT * FROM properties WHERE active = 1 LIMIT 1");
        $property = $stmt->fetch();
        
        if ($property) {
            // Get images separately
            $stmt = $pdo->prepare("\n                SELECT id, image_url, image_path, is_main, sort_order \
                FROM property_images \
                WHERE property_id = :property_id \
                ORDER BY is_main DESC, sort_order ASC
            ");
            $stmt->execute(['property_id' => $property['id']]);
            $images = $stmt->fetchAll();
            
            $property['images'] = $images;
            
            $response['tests']['separate_queries'] = [
                'name' => 'Separate Queries (RECOMMENDED)',
                'status' => 'PASS',
                'result' => $property,
                'note' => 'This is the cleanest MySQL approach'
            ];
        }
    } catch (PDOException $e) {
        $response['tests']['separate_queries'] = [
            'name' => 'Separate Queries',
            'status' => 'FAIL',
            'error' => $e->getMessage()
        ];
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}