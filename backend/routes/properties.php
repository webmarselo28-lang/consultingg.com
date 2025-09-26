<?php
require_once __DIR__ . '/../controllers/PropertyController.php';

try {
    $propertyController = new PropertyController();
    $method = $_SERVER['REQUEST_METHOD'];
    
    // --- Normalized path, consistent with index.php ---
    $path = $_SERVER['CONSULTINGG_API_PATH'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = preg_replace('#^/(backend/)?api/?#', '/', $path);
    
    $pathParts = explode('/', trim($path, '/'));
    
    // Strip leading 'properties' only if present
    if (isset($pathParts[0]) && $pathParts[0] === 'properties') {
        array_shift($pathParts);
    }
    
    // Existing route conditions continue to use $pathParts[0], $pathParts[1], etc.

    switch ($method) {
    case 'GET':
        if (empty($pathParts[0])) {
            $propertyController->getAll();
        } elseif ($pathParts[0] === 'stats') {
            $propertyController->getStats();
        } else {
            $propertyController->getById($pathParts[0]);
        }
        break;
        
    case 'POST':
        if (empty($pathParts[0])) {
            $propertyController->create();
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
        }
        break;
        
    case 'PUT':
        if (!empty($pathParts[0])) {
            if (isset($pathParts[1]) && $pathParts[1] === 'images' && isset($pathParts[2])) {
                if (isset($pathParts[3]) && $pathParts[3] === 'main') {
                    // PUT /properties/{id}/images/{imageId}/main
                    require_once __DIR__ . '/../controllers/ImageController.php';
                    $imageController = new ImageController();
                    $imageController->setMainFromUrl($pathParts[0], $pathParts[2]);
                } else {
                    // PUT /properties/{id}/images/{imageId} - Update image details
                    require_once __DIR__ . '/../controllers/ImageController.php';
                    $imageController = new ImageController();
                    $imageController->update($pathParts[2]);
                }
            } else {
                // Regular property update
                $propertyController->update($pathParts[0]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Property ID is required']);
        }
        break;
        
    case 'PATCH':
        if (!empty($pathParts[0])) {
            if (isset($pathParts[1]) && $pathParts[1] === 'images' && isset($pathParts[2]) && isset($pathParts[3]) && $pathParts[3] === 'main') {
                // PATCH /properties/{id}/images/{imageId}/main
                require_once __DIR__ . '/../controllers/ImageController.php';
                $imageController = new ImageController();
                $imageController->setMainFromUrl($pathParts[0], $pathParts[2]);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid PATCH endpoint']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Property ID is required']);
        }
        break;
        
    case 'DELETE':
        if (!empty($pathParts[0]) && !isset($pathParts[1])) {
            // DELETE /properties/{id}
            $propertyController->delete($pathParts[0]);
        } elseif (!empty($pathParts[0]) && isset($pathParts[1]) && $pathParts[1] === 'images' && isset($pathParts[2])) {
            // DELETE /properties/{id}/images/{imageId}
            require_once __DIR__ . '/../controllers/ImageController.php';
            $imageController = new ImageController();
            $imageController->delete($pathParts[2]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Property ID is required']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
    }
} catch (Throwable $e) {
    error_log('[PROPERTIES] Fatal error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
?>