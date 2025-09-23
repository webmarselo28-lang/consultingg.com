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
            AuthMiddleware::requireAdmin();

            $data = json_decode(file_get_contents("php://input"), true);

            // Validate input data
            $validationResult = $this->validatePropertyData($data);
            if (!$validationResult['valid']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => $validationResult['error']]);
                exit;
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
            AuthMiddleware::requireAdmin();

            $data = json_decode(file_get_contents("php://input"), true);

            // Validate input data for update (less strict than create)
            $validationResult = $this->validatePropertyData($data, false);
            if (!$validationResult['valid']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => $validationResult['error']]);
                exit;
            }

            // Concurrency control: Check if updated_at matches (optimistic locking)
            if (isset($data['updated_at'])) {
                $currentProperty = $this->propertyModel->getById($id);
                if (!$currentProperty) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Property not found']);
                    exit;
                }
                
                // Compare timestamps (allow small time differences for clock skew)
                $currentTimestamp = strtotime($currentProperty['updated_at']);
                $providedTimestamp = strtotime($data['updated_at']);
                
                if (abs($currentTimestamp - $providedTimestamp) > 1) { // More than 1 second difference
                    http_response_code(409);
                    echo json_encode([
                        'success' => false, 
                        'error' => 'Property has been modified by another user. Please refresh and try again.',
                        'current_updated_at' => $currentProperty['updated_at']
                    ]);
                    exit;
                }
            }

            $success = $this->propertyModel->update($id, $data);
            $property = $success ? $this->propertyModel->getById($id) : null;

            if ($success && $property) {
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

    public function delete($id) {
        try {
            AuthMiddleware::requireAdmin();

            $success = $this->propertyModel->delete($id);

            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Property deleted successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to delete property']);
            }
        } catch (Throwable $e) {
            error_log('[PropertyController@delete] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
        exit;
    }

    private function validatePropertyData($data, $isCreate = true) {
        // Check if data is valid JSON
        if (!is_array($data)) {
            return ['valid' => false, 'error' => 'Invalid JSON data'];
        }

        // Required fields for creation
        if ($isCreate) {
            $required = ['title', 'price', 'transaction_type', 'property_type', 'city_region', 'area'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '') || 
                    (is_numeric($data[$field]) && $data[$field] <= 0 && in_array($field, ['price', 'area']))) {
                    return ['valid' => false, 'error' => "Field '$field' is required and must be valid"];
                }
            }
        }

        // Validate title
        if (isset($data['title'])) {
            if (!is_string($data['title']) || strlen(trim($data['title'])) < 3 || strlen($data['title']) > 255) {
                return ['valid' => false, 'error' => 'Title must be between 3 and 255 characters'];
            }
        }

        // Validate price
        if (isset($data['price'])) {
            if (!is_numeric($data['price']) || $data['price'] <= 0 || $data['price'] > 999999999) {
                return ['valid' => false, 'error' => 'Price must be a positive number less than 1 billion'];
            }
        }

        // Validate area
        if (isset($data['area'])) {
            if (!is_numeric($data['area']) || $data['area'] <= 0 || $data['area'] > 100000) {
                return ['valid' => false, 'error' => 'Area must be a positive number less than 100,000 sq m'];
            }
        }

        // Validate currency
        if (isset($data['currency'])) {
            $validCurrencies = ['EUR', 'USD', 'BGN'];
            if (!in_array($data['currency'], $validCurrencies)) {
                return ['valid' => false, 'error' => 'Currency must be one of: ' . implode(', ', $validCurrencies)];
            }
        }

        // Validate transaction type
        if (isset($data['transaction_type'])) {
            $validTypes = ['sale', 'rent'];
            if (!in_array($data['transaction_type'], $validTypes)) {
                return ['valid' => false, 'error' => 'Transaction type must be sale or rent'];
            }
        }

        // Validate property type
        if (isset($data['property_type'])) {
            $validPropertyTypes = ['1-СТАЕН', '2-СТАЕН', '3-СТАЕН', '4-СТАЕН', 'МЕЗОНЕТ', 'КЪЩА', 'ВИЛА', 'ОФИС', 'МАГАЗИН', 'СКЛАД'];
            if (!in_array($data['property_type'], $validPropertyTypes)) {
                return ['valid' => false, 'error' => 'Invalid property type'];
            }
        }

        // Validate numeric fields
        $numericFields = ['bedrooms', 'bathrooms', 'floors', 'floor_number', 'terraces', 'year_built'];
        foreach ($numericFields as $field) {
            if (isset($data[$field]) && (!is_numeric($data[$field]) || $data[$field] < 0 || $data[$field] > 1000)) {
                return ['valid' => false, 'error' => "Field '$field' must be a non-negative number less than 1000"];
            }
        }

        // Validate year_built specifically
        if (isset($data['year_built'])) {
            $currentYear = date('Y');
            if ($data['year_built'] < 1800 || $data['year_built'] > $currentYear + 5) {
                return ['valid' => false, 'error' => 'Year built must be between 1800 and ' . ($currentYear + 5)];
            }
        }

        // Validate boolean fields
        $booleanFields = ['has_elevator', 'has_garage', 'has_southern_exposure', 'new_construction', 'featured', 'active'];
        foreach ($booleanFields as $field) {
            if (isset($data[$field]) && !is_bool($data[$field])) {
                return ['valid' => false, 'error' => "Field '$field' must be a boolean value"];
            }
        }

        // Validate string length fields
        $stringFields = [
            'description' => 10000,
            'district' => 100,
            'address' => 500,
            'construction_type' => 50,
            'condition_type' => 50,
            'heating' => 50,
            'exposure' => 50,
            'furnishing_level' => 50,
            'property_code' => 50
        ];
        foreach ($stringFields as $field => $maxLength) {
            if (isset($data[$field]) && strlen($data[$field]) > $maxLength) {
                return ['valid' => false, 'error' => "Field '$field' must be less than $maxLength characters"];
            }
        }

        // Validate property_code uniqueness if provided
        if (isset($data['property_code']) && !empty($data['property_code'])) {
            // Check for valid format (alphanumeric + dashes/underscores)
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $data['property_code'])) {
                return ['valid' => false, 'error' => 'Property code can only contain letters, numbers, dashes, and underscores'];
            }
        }

        return ['valid' => true];
    }
}
?>