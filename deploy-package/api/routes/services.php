<?php
require_once __DIR__ . '/../controllers/ServiceController.php';

$serviceController = new ServiceController();
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Remove 'api' and 'services' from path parts
array_shift($pathParts); // remove 'api'
array_shift($pathParts); // remove 'services'

switch ($method) {
    case 'GET':
        if (empty($pathParts[0])) {
            $serviceController->getAll();
        } else {
            $serviceController->getById($pathParts[0]);
        }
        break;
        
    case 'POST':
        if (empty($pathParts[0])) {
            $serviceController->create();
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
        }
        break;
        
    case 'PUT':
        if (!empty($pathParts[0])) {
            $serviceController->update($pathParts[0]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Service ID is required']);
        }
        break;
        
    case 'DELETE':
        if (!empty($pathParts[0])) {
            $serviceController->delete($pathParts[0]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Service ID is required']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>