<?php
/**
 * Backfill Responsive Image Derivatives
 * 
 * Generates modern responsive derivatives for existing images without changing database schema.
 * Supports AVIF, WebP, and JPEG formats with proper fallbacks.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PropertyImage.php';
require_once __DIR__ . '/../../utils/ImageProcessor.php';
require_once __DIR__ . '/../../utils/ErrorLogger.php';

class DerivativeBackfill {
    private $db;
    private $imageModel;
    private $uploadsFS;
    private $uploadsPublic;
    private $stats;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->imageModel = new PropertyImage();
        
        // Match ImageController paths
        $this->uploadsFS = $_ENV['UPLOADS_FS_BASE'] ?? realpath(__DIR__ . '/../../uploads') ?: __DIR__ . '/../../uploads';
        $this->uploadsPublic = $_ENV['UPLOADS_PUBLIC_BASE'] ?? '/uploads';
        
        $this->stats = [
            'processed' => 0,
            'skipped' => 0,
            'errors' => 0,
            'derivatives_created' => 0
        ];
    }

    public function run($propertyId = null, $force = false) {
        echo "🖼️  Starting responsive derivatives backfill...\n\n";
        
        if ($propertyId) {
            echo "📂 Processing property: {$propertyId}\n";
            $images = $this->getImagesByProperty($propertyId);
        } else {
            echo "📂 Processing all images...\n";
            $images = $this->getAllImages();
        }
        
        if (empty($images)) {
            echo "❌ No images found to process.\n";
            return;
        }
        
        echo "📊 Found " . count($images) . " images to process\n\n";
        
        foreach ($images as $image) {
            $this->processImage($image, $force);
        }
        
        $this->printSummary();
    }

    private function getAllImages() {
        $query = "SELECT id, property_id, image_url, image_path FROM property_images ORDER BY property_id, sort_order";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getImagesByProperty($propertyId) {
        $query = "SELECT id, property_id, image_url, image_path FROM property_images WHERE property_id = :property_id ORDER BY sort_order";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':property_id', $propertyId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function processImage($image, $force) {
        $imageId = $image['id'];
        $imagePath = $image['image_path'] ?: $image['image_url'];
        
        if (empty($imagePath)) {
            echo "⚠️  [{$imageId}] No image path found, skipping\n";
            $this->stats['skipped']++;
            return;
        }
        
        // Convert URL path to filesystem path
        $fullPath = $this->uploadsFS . str_replace($this->uploadsPublic, '', $imagePath);
        
        if (!file_exists($fullPath)) {
            echo "❌ [{$imageId}] Image file not found: {$fullPath}\n";
            $this->stats['errors']++;
            return;
        }
        
        $filename = basename($imagePath);
        $outputDir = dirname($fullPath);
        
        echo "🔄 [{$imageId}] Processing: {$filename}\n";
        
        // Check if derivatives already exist (unless force)
        if (!$force && $this->derivativesExist($fullPath, $filename)) {
            echo "   ⏭️  Derivatives already exist, skipping (use --force to regenerate)\n";
            $this->stats['skipped']++;
            return;
        }
        
        try {
            $createdFiles = ImageProcessor::createDerivatives($fullPath, $filename, $outputDir);
            
            if (!empty($createdFiles)) {
                echo "   ✅ Created " . count($createdFiles) . " derivatives\n";
                $this->stats['derivatives_created'] += count($createdFiles);
                
                // Log created files
                foreach ($createdFiles as $file) {
                    echo "      📄 {$file['format']}: {$file['filename']} ({$file['width']}x{$file['height']}, " . 
                         $this->formatFileSize($file['size']) . ")\n";
                }
            } else {
                echo "   ⚠️  No derivatives created (source too small or error)\n";
                $this->stats['skipped']++;
            }
            
            $this->stats['processed']++;
            
        } catch (Exception $e) {
            echo "   ❌ Error: " . $e->getMessage() . "\n";
            $this->stats['errors']++;
            
            // Log structured error
            ErrorLogger::logError(
                'DerivativeBackfill::processImage',
                $e,
                [
                    'image_id' => $imageId,
                    'image_path' => $imagePath,
                    'full_path' => $fullPath,
                    'filename' => $filename
                ]
            );
        }
        
        echo "\n";
    }

    private function derivativesExist($originalPath, $filename) {
        $derivatives = ImageProcessor::getDerivativeFiles($originalPath, $filename);
        return !empty($derivatives);
    }

    private function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 1) . ' ' . $units[$pow];
    }

    private function printSummary() {
        echo "📈 SUMMARY:\n";
        echo "   Processed: {$this->stats['processed']}\n";
        echo "   Skipped: {$this->stats['skipped']}\n";
        echo "   Errors: {$this->stats['errors']}\n";
        echo "   Derivatives created: {$this->stats['derivatives_created']}\n\n";
        
        if ($this->stats['errors'] > 0) {
            echo "⚠️  Some errors occurred. Check error logs for details.\n";
        } else {
            echo "✅ Backfill completed successfully!\n";
        }
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $options = getopt('', ['property:', 'force', 'help']);
    
    if (isset($options['help'])) {
        echo "Usage: php backfill_derivatives.php [options]\n\n";
        echo "Options:\n";
        echo "  --property=ID    Process only images from specific property\n";
        echo "  --force         Regenerate derivatives even if they exist\n";
        echo "  --help          Show this help message\n\n";
        echo "Examples:\n";
        echo "  php backfill_derivatives.php\n";
        echo "  php backfill_derivatives.php --property=12345\n";
        echo "  php backfill_derivatives.php --force\n";
        exit(0);
    }
    
    $propertyId = $options['property'] ?? null;
    $force = isset($options['force']);
    
    $backfill = new DerivativeBackfill();
    $backfill->run($propertyId, $force);
    
// Web execution (for admin interface)
} else {
    header('Content-Type: application/json; charset=utf-8');
    
    // Simple auth check
    if (!isset($_ENV['DEMO_MODE'])) {
        require_once __DIR__ . '/../middleware/auth.php';
        AuthMiddleware::requireAdmin();
    }
    
    $propertyId = $_GET['property_id'] ?? null;
    $force = isset($_GET['force']) && $_GET['force'] === 'true';
    
    // Capture output
    ob_start();
    
    $backfill = new DerivativeBackfill();
    $backfill->run($propertyId, $force);
    
    $output = ob_get_clean();
    
    echo json_encode([
        'success' => true,
        'output' => $output,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>