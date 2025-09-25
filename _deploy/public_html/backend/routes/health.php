<?php
// Health check endpoint for deployment verification
header('Content-Type: application/json');

try {
    // Check database connection
    require_once __DIR__ . '/../config/database.php';
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Simple database query to verify connection
    $stmt = $pdo->query('SELECT 1 as test');
    $result = $stmt->fetch();
    
    $response = [
        'status' => 'ok',
        'database' => 'connected',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0'
    ];
    
    http_response_code(200);
    echo json_encode($response);
    
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'database' => 'disconnected',
        'error' => 'Database connection failed',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    http_response_code(500);
    echo json_encode($response);
}
?>