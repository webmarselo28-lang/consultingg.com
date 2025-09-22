<?php
require_once __DIR__ . '/../controllers/ImageController.php';

try {
    $imageController = new ImageController();
    $method = $_SERVER['REQUEST_METHOD'];
    
    // --- Normalized path, consistent with index.php ---
    $path = $_SERVER['CONSULTINGG_API_PATH'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = preg_replace('#^/(backend/)?api/?#', '/', $path);
    
    $pathParts = explode('/', trim($path, '/'));
    
    // Strip leading 'images' only if present
    if (isset($pathParts[0]) && $pathParts[0] === 'images') {
        array_shift($pathParts);
    }
    
    // Now $pathParts[0] is expected to be 'upload' or an image id, etc.

    switch ($method) {
        case 'POST':
            if (empty($pathParts[0]) || $pathParts[0] === 'upload') {
                $imageController->upload();
            } elseif ($pathParts[0] === 'set-main') {
                $imageController->setMain();
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
            }
            break;
            
        case 'PUT':
            if (!empty($pathParts[0])) {
                $imageController->update($pathParts[0]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Image ID is required']);
            }
            break;
            
        case 'DELETE':
            if (!empty($pathParts[0])) {
                $imageController->delete($pathParts[0]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Image ID is required']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            break;
    }
} catch (Throwable $e) {
    error_log('[IMAGES] Fatal error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
?>