<?php
require_once __DIR__ . '/../models/Service.php';

class ServiceController {
    private $serviceModel;

    public function __construct() {
        $this->serviceModel = new Service();
    }

    public function getAll() {
        $activeOnly = !isset($_GET['all']) || $_GET['all'] !== 'true';
        $services = $this->serviceModel->getAll($activeOnly);

        echo json_encode([
            'success' => true,
            'data' => $services
        ]);
    }

    public function getById($id) {
        $service = $this->serviceModel->getById($id);

        if (!$service) {
            http_response_code(404);
            echo json_encode(['error' => 'Service not found']);
            return;
        }

        echo json_encode([
            'success' => true,
            'data' => $service
        ]);
    }

    public function create() {
        require_once __DIR__ . '/../middleware/auth.php';
        AuthMiddleware::requireAdmin();

        $data = json_decode(file_get_contents("php://input"), true);

        $required = ['title', 'description', 'icon', 'color'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Field '$field' is required"]);
                return;
            }
        }

        $serviceId = $this->serviceModel->create($data);

        if ($serviceId) {
            $service = $this->serviceModel->getById($serviceId);
            echo json_encode([
                'success' => true,
                'data' => $service
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create service']);
        }
    }

    public function update($id) {
        require_once __DIR__ . '/../middleware/auth.php';
        AuthMiddleware::requireAdmin();

        $data = json_decode(file_get_contents("php://input"), true);

        if ($this->serviceModel->update($id, $data)) {
            $service = $this->serviceModel->getById($id);
            echo json_encode([
                'success' => true,
                'data' => $service
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update service']);
        }
    }

    public function delete($id) {
        require_once __DIR__ . '/../middleware/auth.php';
        AuthMiddleware::requireAdmin();

        if ($this->serviceModel->delete($id)) {
            echo json_encode([
                'success' => true,
                'message' => 'Service deleted successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete service']);
        }
    }
}
?>