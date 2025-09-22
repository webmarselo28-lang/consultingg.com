<?php
require_once __DIR__ . '/../models/Property.php';
require_once __DIR__ . '/../models/PropertyImage.php';
require_once __DIR__ . '/../middleware/auth.php';

class PropertyController {
    private $propertyModel;
    private $imageModel;

    public function __construct() {
        $this->propertyModel = new Property();
        $this->imageModel = new PropertyImage();
    }

    public function getAll() {
        try {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(100, max(1, (int)($_GET['limit'] ?? 16)));
            
            $filters = [
                'featured' => isset($_GET['featured']) ? filter_var($_GET['featured'], FILTER_VALIDATE_BOOLEAN) : null,
                'keyword' => $_GET['keyword'] ?? $_GET['q'] ?? null,
                'transaction_type' => $_GET['transaction_type'] ?? null,
                'city_region' => $_GET['city_region'] ?? $_GET['city'] ?? null,
                'district' => $_GET['district'] ?? null,
                'property_type' => $_GET['property_type'] ?? null,
                'price_min' => $_GET['price_min'] ?? null,
                'price_max' => $_GET['price_max'] ?? null,
                'area_min' => $_GET['area_min'] ?? null,
                'area_max' => $_GET['area_max'] ?? null,
                'active' => $_GET['active'] ?? 'true'
            ];

            $result = $this->propertyModel->getAllPaginated($filters, $page, $limit);
            
            $total = (int)$result['total'];
            $pages = (int)ceil($total / $limit);
            $meta = [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => $pages,
                'hasPrev' => $page > 1,
                'hasNext' => $page < $pages,
            ];

            echo json_encode([
                'success' => true,
                'data' => $result['items'],
                'meta' => $meta
            ]);
        } catch (Throwable $e) {
            error_log('[PropertyController@getAll] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
        exit;
    }

    public function getById($id) {
        try {
            $property = $this->propertyModel->getById($id);

            if (!$property) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Property not found']);
                exit;
            }

            echo json_encode([
                'success' => true,
                'data' => $property
            ]);
        } catch (Throwable $e) {
            error_log('[PropertyController@getById] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
        exit;
    }

    public function create() {
        try {
            // Skip auth for demo mode
            if (!isset($_ENV['WEBCONTAINER_ENV']) && !isset($_ENV['DEMO_MODE'])) {
                AuthMiddleware::requireAdmin();
            }

            $data = json_decode(file_get_contents("php://input"), true);

            // Validate required fields
            $required = ['title', 'price', 'transaction_type', 'property_type', 'city_region', 'area'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => "Field '$field' is required"]);
                    exit;
                }
            }

            // Set defaults
            $data['currency'] = $data['currency'] ?? 'EUR';
            $data['bedrooms'] = $data['bedrooms'] ?? 0;
            $data['bathrooms'] = $data['bathrooms'] ?? 0;
            $data['terraces'] = $data['terraces'] ?? 0;
            $data['has_elevator'] = $data['has_elevator'] ?? false;
            $data['has_garage'] = $data['has_garage'] ?? false;
            $data['has_southern_exposure'] = $data['has_southern_exposure'] ?? false;
            $data['new_construction'] = $data['new_construction'] ?? false;
            $data['featured'] = $data['featured'] ?? false;
            $data['active'] = $data['active'] ?? true;

            $propertyId = $this->propertyModel->create($data);

            if ($propertyId) {
                $property = $this->propertyModel->getById($propertyId);
                echo json_encode([
                    'success' => true,
                    'data' => $property
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to create property']);
            }
        } catch (Throwable $e) {
            error_log('[PropertyController@create] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
        exit;
    }

    public function update($id) {
        try {
            // Skip auth for demo mode
            if (!isset($_ENV['WEBCONTAINER_ENV']) && !isset($_ENV['DEMO_MODE'])) {
                AuthMiddleware::requireAdmin();
            }

            $data = json_decode(file_get_contents("php://input"), true);

            if ($this->propertyModel->update($id, $data)) {
                $property = $this->propertyModel->getById($id);
                echo json_encode([
                    'success' => true,
                    'data' => $property
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update property']);
            }
        } catch (Throwable $e) {
            error_log('[PropertyController@update] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
        exit;
    }
}
?>