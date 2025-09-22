<?php
require_once __DIR__ . '/../models/PropertyImage.php';
require_once __DIR__ . '/../utils/ImageProcessor.php';

class ImageController {
    private $imageModel;
    
    // Configurable upload paths
    private $uploadsFS;
    private $uploadsPublic;

    public function __construct() {
        $this->imageModel = new PropertyImage();
        
        // FS base for uploaded files (absolute)
        $this->uploadsFS = $_ENV['UPLOADS_FS_BASE'] ?? realpath(__DIR__ . '/../../uploads') ?: __DIR__ . '/../../uploads';
        
        // Public URL base (what the browser should use)
        $this->uploadsPublic = $_ENV['UPLOADS_PUBLIC_BASE'] ?? '/uploads';
    }

    public function upload() {
        try {
            require_once __DIR__ . '/../middleware/auth.php';
            // Skip auth for demo mode
            if (!isset($_ENV['WEBCONTAINER_ENV']) && !isset($_ENV['DEMO_MODE'])) {
                AuthMiddleware::requireAdmin();
            }

            error_log('[ImageController] Upload started');
            error_log('[ImageController] Request method: ' . $_SERVER['REQUEST_METHOD']);
            error_log('[ImageController] Request URI: ' . $_SERVER['REQUEST_URI']);
            error_log('[ImageController] Content-Type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
            
            // Check if we have multipart/form-data
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'multipart/form-data') === false) {
                error_log('[ImageController] Invalid content type for file upload: ' . $contentType);
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'error' => 'Invalid content type. Expected multipart/form-data for file upload.',
                    'received_content_type' => $contentType
                ]);
                exit;
            }
            
            error_log('[ImageController] FILES: ' . print_r($_FILES, true));
            error_log('[ImageController] POST: ' . print_r($_POST, true));

            if (!isset($_FILES['image']) || !isset($_POST['property_id'])) {
                error_log('[ImageController] Missing required fields');
                error_log('[ImageController] Available FILES keys: ' . implode(', ', array_keys($_FILES)));
                error_log('[ImageController] Available POST keys: ' . implode(', ', array_keys($_POST)));
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'error' => 'Image file and property_id are required',
                    'debug' => [
                        'files_received' => array_keys($_FILES),
                        'post_received' => array_keys($_POST),
                        'expected' => ['image' => 'file', 'property_id' => 'string']
                    ]
                ]);
                exit;
            }

            $file = $_FILES['image'];
            $propertyId = $_POST['property_id'];
            $sortOrder = $_POST['sort_order'] ?? 0;
            $isMain = isset($_POST['is_main']) ? (bool)$_POST['is_main'] : false;
            $altText = $_POST['alt_text'] ?? '';

            // Validate image using ImageProcessor
            $validationErrors = ImageProcessor::validateImage($file);
            if (!empty($validationErrors)) {
                error_log('[ImageController] Validation errors: ' . implode(', ', $validationErrors));
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => implode(', ', $validationErrors)]);
                exit;
            }

            // If this is set as main image, unset all other main images for this property first
            if ($isMain) {
                $this->imageModel->unsetMainImages($propertyId);
            }

            // Check if property already has 50 images
            $existingImages = $this->imageModel->getByPropertyId($propertyId);
            if (count($existingImages) >= 50) {
                error_log('[ImageController] Too many images for property');
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Maximum 50 images per property allowed']);
                exit;
            }

            // Create property-specific directory
            $propertyDirFS = rtrim($this->uploadsFS, '/\\') . "/properties/$propertyId/";
            $propertyUrlBase = rtrim($this->uploadsPublic, '/\\') . "/properties/$propertyId/";
            
            if (!is_dir($propertyDirFS)) {
                if (!mkdir($propertyDirFS, 0755, true)) {
                    $lastError = error_get_last();
                    error_log('[ImageController] Failed to create directory: ' . $propertyDirFS);
                    error_log('[ImageController] Last error: ' . ($lastError['message'] ?? 'Unknown'));
                    error_log('[ImageController] Parent writable: ' . (is_writable(dirname($propertyDirFS)) ? 'YES' : 'NO'));
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'Failed to create upload directory']);
                    exit;
                }
                error_log('[ImageController] Created directory: ' . $propertyDirFS);
            }
            
            // Check if directory is writable
            if (!is_writable($propertyDirFS)) {
                error_log('[ImageController] Directory not writable: ' . $propertyDirFS);
                error_log('[ImageController] Directory permissions: ' . substr(sprintf('%o', fileperms($propertyDirFS)), -4));
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Upload directory not writable']);
                exit;
            }

            require_once __DIR__ . '/../utils/ImageHelper.php';
            $filename = ImageHelper::safeFilename($file['name'], $propertyId);
            $filePath = $propertyDirFS . $filename;
            $thumbnailFilename = pathinfo($filename, PATHINFO_FILENAME) . '_thumb.' . pathinfo($filename, PATHINFO_EXTENSION);
            $thumbnailPath = $propertyDirFS . $thumbnailFilename;

            error_log('[ImageController] Attempting to move file to: ' . $filePath);
            
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                error_log('[ImageController] File moved successfully');
                
                // Verify file was actually created
                if (!file_exists($filePath)) {
                    error_log('[ImageController] File does not exist after move');
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'File upload verification failed. File not found after move.']);
                    exit;
                }
                
                // Optimize main image
                $tempOptimizedPath = $filePath . '.tmp';
                if (ImageProcessor::optimizeImage($filePath, $tempOptimizedPath, 90)) {
                    rename($tempOptimizedPath, $filePath);
                    error_log('[ImageController] Image optimized successfully');
                }

                // Create thumbnail
                try {
                    $thumbnailInfo = ImageProcessor::createThumbnail($filePath, $thumbnailPath, 'medium');
                    error_log('[ImageController] Thumbnail created: ' . json_encode($thumbnailInfo));
                } catch (Exception $e) {
                    error_log('[ImageController] Thumbnail creation failed: ' . $e->getMessage());
                    // Continue without thumbnail - not critical
                }

                // Get image dimensions and file info
                $imageInfo = ImageProcessor::getImageDimensions($filePath);
                $fileSize = filesize($filePath);

                // Create image record
                $imageData = [
                    'property_id' => $propertyId,
                    'image_url' => $propertyUrlBase . '/' . $filename,
                    'image_path' => $propertyUrlBase . '/' . $filename,
                    'thumbnail_url' => file_exists($thumbnailPath) ? $propertyUrlBase . '/' . $thumbnailFilename : null,
                    'alt_text' => $altText,
                    'sort_order' => $sortOrder,
                    'is_main' => $isMain,
                    'file_size' => $fileSize,
                    'mime_type' => $imageInfo ? $imageInfo['mime_type'] : $file['type']
                ];

                $imageId = $this->imageModel->create($imageData);
                error_log('[ImageController] Image record created with ID: ' . $imageId);

                if ($imageId) {
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'id' => $imageId, // Return the new image ID
                            'url' => $propertyUrlBase . '/' . $filename, // Return the correct URL
                            'thumbnail_url' => $imageData['thumbnail_url'],
                            'path' => $propertyUrlBase . '/' . $filename,
                            'property_id' => $propertyId,
                            'is_main' => $isMain,
                            'sort_order' => $sortOrder,
                            'file_size' => $fileSize,
                            'mime_type' => $imageData['mime_type']
                        ]
                    ]);
                } else {
                    // Delete uploaded file if database insert failed
                    unlink($filePath);
                    if (file_exists($thumbnailPath)) {
                        unlink($thumbnailPath);
                    }
                    error_log('[ImageController] Database insert failed, file deleted');
                    http_response_code(500);
                    echo json_encode(['success' => false, 'error' => 'Failed to save image record. File deleted.']);
                }
            } else {
                $lastError = error_get_last();
                error_log('[ImageController] Failed to move uploaded file');
                error_log('[ImageController] Upload error code: ' . $file['error']);
                error_log('[ImageController] Last error: ' . ($lastError['message'] ?? 'Unknown'));
                error_log('[ImageController] Temp file exists: ' . (file_exists($file['tmp_name']) ? 'YES' : 'NO'));
                error_log('[ImageController] Target directory writable: ' . (is_writable($propertyDirFS) ? 'YES' : 'NO'));
                error_log('[ImageController] Target path: ' . $filePath);
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

            // Delete physical files using ImageProcessor
            require_once __DIR__ . '/../utils/ImageProcessor.php';
            $mainImagePath = $image['image_path'] ? __DIR__ . '/../..' . $image['image_path'] : null;
            $thumbnailPath = $image['thumbnail_url'] ? __DIR__ . '/../..' . $image['thumbnail_url'] : null;
            
            if ($mainImagePath || $thumbnailPath) {
                $deleteResult = ImageProcessor::deleteImageFiles($mainImagePath, $thumbnailPath);
                error_log('[ImageController] File deletion result: ' . json_encode($deleteResult));
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