<?php
require_once __DIR__ . '/../models/PropertyImage.php';

class ImageController {
    private $imageModel;
    private $uploadDir;

    public function __construct() {
        $this->imageModel = new PropertyImage();
        $this->uploadDir = __DIR__ . '/../uploads/properties/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function upload() {
        try {
            require_once __DIR__ . '/../middleware/auth.php';
            // Skip auth for demo mode
            if (!isset($_ENV['WEBCONTAINER_ENV']) && !isset($_ENV['DEMO_MODE'])) {
                AuthMiddleware::requireAdmin();
            }

            error_log('[ImageController] Upload started');
            error_log('[ImageController] FILES: ' . print_r($_FILES, true));
            error_log('[ImageController] POST: ' . print_r($_POST, true));

            if (!isset($_FILES['image']) || !isset($_POST['property_id'])) {
                error_log('[ImageController] Missing required fields');
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Image file and property_id are required']);
                exit;
            }

            $file = $_FILES['image'];
            $propertyId = $_POST['property_id'];
            $sortOrder = $_POST['sort_order'] ?? 0;
            $isMain = isset($_POST['is_main']) ? (bool)$_POST['is_main'] : false;
            $altText = $_POST['alt_text'] ?? '';

            // If this is set as main image, unset all other main images for this property first
            if ($isMain) {
                $this->imageModel->unsetMainImages($propertyId);
            }

            // Check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'File too large (exceeds upload_max_filesize)',
                    UPLOAD_ERR_FORM_SIZE => 'File too large (exceeds MAX_FILE_SIZE)',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
                ];
                $errorMessage = $errorMessages[$file['error']] ?? 'Unknown upload error';
                error_log('[ImageController] Upload error: ' . $errorMessage);
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => $errorMessage]);
                exit;
            }

            error_log('[ImageController] Property ID: ' . $propertyId);
            error_log('[ImageController] File type: ' . $file['type']);
            error_log('[ImageController] File size: ' . $file['size']);
            error_log('[ImageController] File tmp_name: ' . $file['tmp_name']);

            // Check if property already has 50 images
            $existingImages = $this->imageModel->getByPropertyId($propertyId);
            if (count($existingImages) >= 50) {
                error_log('[ImageController] Too many images for property');
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Maximum 50 images per property allowed']);
                exit;
            }

            // Validate file
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                error_log('[ImageController] Invalid file type: ' . $file['type']);
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG and WebP are allowed']);
                exit;
            }

            if ($file['size'] > 10 * 1024 * 1024) { // 10MB
                error_log('[ImageController] File too large: ' . $file['size']);
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'File size too large. Maximum 10MB allowed']);
                exit;
            }

            // Use public/images directory for direct access - ensure correct path
            // Production: use uploads directory relative to document root
            $propertyDir = __DIR__ . '/../../uploads/properties/' . $propertyId . '/';
            
            if (!is_dir($propertyDir)) {
                if (!mkdir($propertyDir, 0775, true)) {
                    error_log('[ImageController] Failed to create directory: ' . $propertyDir);
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'Failed to create upload directory']);
                    exit;
                }
                error_log('[ImageController] Created directory: ' . $propertyDir);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $propertyId . '_' . time() . '_' . uniqid() . '.' . strtolower($extension);
            $filePath = $propertyDir . $filename;

            error_log('[ImageController] Attempting to move file to: ' . $filePath);
            
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                error_log('[ImageController] File moved successfully');
                
                // Verify file was actually created
                if (!file_exists($filePath)) {
                    error_log('[ImageController] File does not exist after move');
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'File upload verification failed']);
                    exit;
                }
                
                // Create image record
                $imageData = [
                    'property_id' => $propertyId,
                    'image_url' => '/uploads/properties/' . $propertyId . '/' . $filename,
                    'image_path' => '/uploads/properties/' . $propertyId . '/' . $filename,
                    'alt_text' => $altText,
                    'sort_order' => $sortOrder,
                    'is_main' => $isMain
                ];

                $imageId = $this->imageModel->create($imageData);
                error_log('[ImageController] Image record created with ID: ' . $imageId);

                if ($imageId) {
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'id' => $imageId,
                            'url' => '/uploads/properties/' . $propertyId . '/' . $filename,
                            'path' => '/uploads/properties/' . $propertyId . '/' . $filename,
                            'property_id' => $propertyId,
                            'is_main' => $isMain,
                            'sort_order' => $sortOrder
                        ]
                    ]);
                } else {
                    // Delete uploaded file if database insert failed
                    unlink($filePath);
                    error_log('[ImageController] Database insert failed, file deleted');
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'Failed to save image record']);
                }
            } else {
                error_log('[ImageController] Failed to move uploaded file');
                error_log('[ImageController] Upload error code: ' . $file['error']);
                error_log('[ImageController] Temp file exists: ' . (file_exists($file['tmp_name']) ? 'YES' : 'NO'));
                error_log('[ImageController] Target directory writable: ' . (is_writable($propertyDir) ? 'YES' : 'NO'));
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
            }
        } catch (Throwable $e) {
            error_log('[ImageController] Upload error: ' . $e->getMessage());
            error_log('[ImageController] Stack trace: ' . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error during upload']);
        }
        exit;
    }

    public function delete($id) {
        try {
            require_once __DIR__ . '/../middleware/auth.php';
            // Skip auth for demo mode
            if (!isset($_ENV['WEBCONTAINER_ENV']) && !isset($_ENV['DEMO_MODE'])) {
                AuthMiddleware::requireAdmin();
            }

            error_log('[ImageController] Delete image ID: ' . $id);

            // Get image info before deleting
            $image = $this->imageModel->getById($id);
            if (!$image) {
                error_log('[ImageController] Image not found: ' . $id);
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Image not found']);
                exit;
            }

            // Delete physical file if it exists
            if ($image['image_path']) {
                $fullPath = __DIR__ . '/../..' . $image['image_path'];
                if (file_exists($fullPath)) {
                    if (unlink($fullPath)) {
                        error_log('[ImageController] Physical file deleted: ' . $fullPath);
                    } else {
                        error_log('[ImageController] Failed to delete physical file: ' . $fullPath);
                    }
                } else {
                    error_log('[ImageController] Physical file not found: ' . $fullPath);
                }
            }

            if ($this->imageModel->delete($id)) {
                error_log('[ImageController] Image deleted successfully');
                echo json_encode([
                    'success' => true,
                    'message' => 'Image deleted successfully'
                ]);
            } else {
                error_log('[ImageController] Failed to delete image');
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to delete image']);
            }
        } catch (Throwable $e) {
            error_log('[ImageController] Delete error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error during delete']);
        }
        exit;
    }

    public function setMain() {
        try {
            require_once __DIR__ . '/../middleware/auth.php';
            // Skip auth for demo mode
            if (!isset($_ENV['WEBCONTAINER_ENV']) && !isset($_ENV['DEMO_MODE'])) {
                AuthMiddleware::requireAdmin();
            }

            $data = json_decode(file_get_contents("php://input"), true);
            error_log('[ImageController] Set main image data: ' . print_r($data, true));

            if (!isset($data['property_id']) || !isset($data['image_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'property_id and image_id are required']);
                exit;
            }

            if ($this->imageModel->setMainImage($data['property_id'], $data['image_id'])) {
                error_log('[ImageController] Main image set successfully');
                echo json_encode([
                    'success' => true,
                    'message' => 'Main image updated successfully'
                ]);
            } else {
                error_log('[ImageController] Failed to set main image');
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update main image']);
            }
        } catch (Throwable $e) {
            error_log('[ImageController] Set main error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error during set main']);
        }
        exit;
    }

    public function update($id) {
        try {
            require_once __DIR__ . '/../middleware/auth.php';
            // Skip auth for demo mode
            if (!isset($_ENV['WEBCONTAINER_ENV']) && !isset($_ENV['DEMO_MODE'])) {
                AuthMiddleware::requireAdmin();
            }

            $data = json_decode(file_get_contents("php://input"), true);
            error_log('[ImageController] Update image data: ' . print_r($data, true));

            if (!$data) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'No data provided']);
                exit;
            }

            // Get current image data
            $currentImage = $this->imageModel->getById($id);
            if (!$currentImage) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Image not found']);
                exit;
            }

            // Merge with existing data
            $updateData = array_merge($currentImage, $data);

            if ($this->imageModel->update($id, $updateData)) {
                error_log('[ImageController] Image updated successfully');
                echo json_encode([
                    'success' => true,
                    'message' => 'Image updated successfully'
                ]);
            } else {
                error_log('[ImageController] Failed to update image');
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to update image']);
            }
        } catch (Throwable $e) {
            error_log('[ImageController] Update error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Server error during update']);
        }
        exit;
    }
}
?>