<?php

class ImageProcessor {
    private static $allowedTypes = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/webp' => ['webp']
    ];

    private static $thumbnailSizes = [
        'small' => ['width' => 150, 'height' => 150],
        'medium' => ['width' => 300, 'height' => 300],
        'large' => ['width' => 600, 'height' => 600]
    ];

    // New responsive derivatives configuration
    private static $derivatives = [
        'hero_1920x1080' => ['width' => 1920, 'height' => 1080, 'crop' => true, 'aspect' => '16:9'],
        'hero_1280x720' => ['width' => 1280, 'height' => 720, 'crop' => true, 'aspect' => '16:9'],
        'card_640x360' => ['width' => 640, 'height' => 360, 'crop' => true, 'aspect' => '16:9'],
        'thumb_160x128' => ['width' => 160, 'height' => 128, 'crop' => true, 'aspect' => '5:4']
    ];

    // Quality settings for different formats
    private static $qualitySettings = [
        'avif' => 55,    // AVIF: ~50-60 for visually lossless
        'webp' => 80,    // WebP: ~75-85 for visually lossless  
        'jpeg' => 85,    // JPEG: ~85 for visually lossless
        'png' => 8       // PNG: compression level 0-9
    ];

    /**
     * Validate uploaded image file
     */
    public static function validateImage($file, $maxSize = 10485760) {
        $errors = [];

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
            $errors[] = $errorMessages[$file['error']] ?? 'Unknown upload error';
            return $errors;
        }

        // Validate file type
        if (!array_key_exists($file['type'], self::$allowedTypes)) {
            $errors[] = 'Invalid file type. Only JPEG, PNG and WebP are allowed';
        }

        // Validate file size
        if ($file['size'] > $maxSize) {
            $errors[] = 'File size too large. Maximum ' . round($maxSize / 1024 / 1024) . 'MB allowed';
        }

        // Validate actual image content (security check)
        $imageInfo = @getimagesize($file['tmp_name']);
        if (!$imageInfo) {
            $errors[] = 'Invalid image file';
        }

        return $errors;
    }

    /**
     * Generate safe filename
     */
    public static function generateFilename($originalName, $propertyId) {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $timestamp = time();
        $random = substr(md5(uniqid()), 0, 8);
        
        return "prop_{$propertyId}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Create thumbnail from image
     */
    public static function createThumbnail($sourcePath, $thumbnailPath, $size = 'medium') {
        if (!file_exists($sourcePath)) {
            throw new Exception('Source image not found');
        }

        $sizeConfig = self::$thumbnailSizes[$size] ?? self::$thumbnailSizes['medium'];
        $maxWidth = $sizeConfig['width'];
        $maxHeight = $sizeConfig['height'];

        // Get image info
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            throw new Exception('Invalid image file');
        }

        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = round($originalWidth * $ratio);
        $newHeight = round($originalHeight * $ratio);

        // Create source image resource
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                if (!function_exists('imagecreatefromwebp')) {
                    throw new Exception('WebP not supported by GD extension');
                }
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                throw new Exception('Unsupported image type');
        }

        if (!$sourceImage) {
            throw new Exception('Failed to create image resource');
        }

        // Create thumbnail image
        $thumbnailImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and WebP
        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            imagealphablending($thumbnailImage, false);
            imagesavealpha($thumbnailImage, true);
            $transparent = imagecolorallocatealpha($thumbnailImage, 255, 255, 255, 127);
            imagefill($thumbnailImage, 0, 0, $transparent);
        }

        // Resize image
        imagecopyresampled(
            $thumbnailImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $originalWidth, $originalHeight
        );

        // Create thumbnail directory if it doesn't exist
        $thumbnailDir = dirname($thumbnailPath);
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        // Save thumbnail
        $success = false;
        switch ($mimeType) {
            case 'image/jpeg':
                $success = imagejpeg($thumbnailImage, $thumbnailPath, 85);
                break;
            case 'image/png':
                $success = imagepng($thumbnailImage, $thumbnailPath, 8);
                break;
            case 'image/webp':
                if (!function_exists('imagewebp')) {
                    throw new Exception('WebP output not supported by GD extension');
                }
                $success = imagewebp($thumbnailImage, $thumbnailPath, 85);
                break;
        }

        // Clean up memory
        imagedestroy($sourceImage);
        imagedestroy($thumbnailImage);

        if (!$success) {
            throw new Exception('Failed to save thumbnail');
        }

        return [
            'width' => $newWidth,
            'height' => $newHeight,
            'size' => filesize($thumbnailPath)
        ];
    }

    /**
     * Delete image and its thumbnail
     */
    public static function deleteImageFiles($imagePath, $thumbnailPath = null) {
        $deleted = [];
        $errors = [];

        // Delete main image
        if ($imagePath && file_exists($imagePath)) {
            if (unlink($imagePath)) {
                $deleted[] = $imagePath;
            } else {
                $errors[] = "Failed to delete main image: {$imagePath}";
            }
        }

        // Delete thumbnail
        if ($thumbnailPath && file_exists($thumbnailPath)) {
            if (unlink($thumbnailPath)) {
                $deleted[] = $thumbnailPath;
            } else {
                $errors[] = "Failed to delete thumbnail: {$thumbnailPath}";
            }
        }

        return [
            'deleted' => $deleted,
            'errors' => $errors
        ];
    }

    /**
     * Get image dimensions
     */
    public static function getImageDimensions($imagePath) {
        if (!file_exists($imagePath)) {
            return null;
        }

        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return null;
        }

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'mime_type' => $imageInfo['mime']
        ];
    }

    /**
     * Create responsive derivatives for an uploaded image
     */
    public static function createDerivatives($sourcePath, $originalFilename, $outputDir) {
        if (!file_exists($sourcePath)) {
            throw new Exception('Source image not found: ' . $sourcePath);
        }

        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            throw new Exception('Invalid image file');
        }

        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $sourceMimeType = $imageInfo['mime'];
        
        // Handle EXIF orientation
        $sourceImage = self::createImageResource($sourcePath, $sourceMimeType);
        if (!$sourceImage) {
            throw new Exception('Failed to create image resource');
        }
        
        $sourceImage = self::autoOrientImage($sourceImage, $sourcePath);
        
        // Get corrected dimensions after orientation
        $correctedWidth = imagesx($sourceImage);
        $correctedHeight = imagesy($sourceImage);
        
        $pathInfo = pathinfo($originalFilename);
        $basename = $pathInfo['filename'];
        $originalExt = $pathInfo['extension'] ?? 'jpg';
        
        $createdFiles = [];
        
        // Create each derivative
        foreach (self::$derivatives as $name => $config) {
            $targetWidth = $config['width'];
            $targetHeight = $config['height'];
            
            // Skip if source is smaller and we don't want to upscale
            if ($correctedWidth < $targetWidth || $correctedHeight < $targetHeight) {
                // Calculate proportionally smaller target
                $ratio = min($targetWidth / $correctedWidth, $targetHeight / $correctedHeight);
                if ($ratio > 1) {
                    // Would need upscaling, skip or use source dimensions
                    continue;
                }
            }
            
            try {
                $derivativeFiles = self::createDerivativeInFormats(
                    $sourceImage, 
                    $correctedWidth, 
                    $correctedHeight,
                    $targetWidth, 
                    $targetHeight, 
                    $config['crop'],
                    $outputDir, 
                    $basename, 
                    $name
                );
                $createdFiles = array_merge($createdFiles, $derivativeFiles);
            } catch (Exception $e) {
                error_log("Failed to create derivative {$name}: " . $e->getMessage());
            }
        }
        
        imagedestroy($sourceImage);
        return $createdFiles;
    }

    /**
     * Create a derivative in multiple formats (AVIF, WebP, JPEG)
     */
    private static function createDerivativeInFormats($sourceImage, $sourceWidth, $sourceHeight, $targetWidth, $targetHeight, $crop, $outputDir, $basename, $derivativeName) {
        $createdFiles = [];
        
        // Calculate dimensions and crop area
        if ($crop) {
            $dimensions = self::calculateCenterCrop($sourceWidth, $sourceHeight, $targetWidth, $targetHeight);
        } else {
            $dimensions = self::calculateFitDimensions($sourceWidth, $sourceHeight, $targetWidth, $targetHeight);
        }
        
        // Create derivative image
        $derivativeImage = imagecreatetruecolor($dimensions['output_width'], $dimensions['output_height']);
        
        // Preserve transparency
        imagealphablending($derivativeImage, false);
        imagesavealpha($derivativeImage, true);
        $transparent = imagecolorallocatealpha($derivativeImage, 255, 255, 255, 127);
        imagefill($derivativeImage, 0, 0, $transparent);
        
        // Copy and resize/crop the image
        imagecopyresampled(
            $derivativeImage, $sourceImage,
            0, 0, 
            $dimensions['src_x'], $dimensions['src_y'],
            $dimensions['output_width'], $dimensions['output_height'],
            $dimensions['src_width'], $dimensions['src_height']
        );
        
        // Save in multiple formats with fallback
        $formats = ['avif', 'webp', 'jpeg'];
        
        foreach ($formats as $format) {
            $filename = "{$basename}.{$derivativeName}.{$format}";
            $filepath = $outputDir . '/' . $filename;
            
            try {
                if (self::saveImageInFormat($derivativeImage, $filepath, $format)) {
                    $createdFiles[] = [
                        'format' => $format,
                        'path' => $filepath,
                        'filename' => $filename,
                        'width' => $dimensions['output_width'],
                        'height' => $dimensions['output_height'],
                        'size' => filesize($filepath)
                    ];
                }
            } catch (Exception $e) {
                error_log("Failed to save {$format} format for {$derivativeName}: " . $e->getMessage());
            }
        }
        
        imagedestroy($derivativeImage);
        return $createdFiles;
    }

    /**
     * Calculate center crop dimensions
     */
    private static function calculateCenterCrop($sourceWidth, $sourceHeight, $targetWidth, $targetHeight) {
        // Calculate scale to fill the target dimensions
        $scaleX = $targetWidth / $sourceWidth;
        $scaleY = $targetHeight / $sourceHeight;
        $scale = max($scaleX, $scaleY);
        
        // Calculate source crop area
        $srcWidth = $targetWidth / $scale;
        $srcHeight = $targetHeight / $scale;
        
        // Center the crop
        $srcX = ($sourceWidth - $srcWidth) / 2;
        $srcY = ($sourceHeight - $srcHeight) / 2;
        
        return [
            'src_x' => round($srcX),
            'src_y' => round($srcY),
            'src_width' => round($srcWidth),
            'src_height' => round($srcHeight),
            'output_width' => $targetWidth,
            'output_height' => $targetHeight
        ];
    }

    /**
     * Calculate fit dimensions (no cropping)
     */
    private static function calculateFitDimensions($sourceWidth, $sourceHeight, $maxWidth, $maxHeight) {
        $ratio = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
        $newWidth = round($sourceWidth * $ratio);
        $newHeight = round($sourceHeight * $ratio);
        
        return [
            'src_x' => 0,
            'src_y' => 0,
            'src_width' => $sourceWidth,
            'src_height' => $sourceHeight,
            'output_width' => $newWidth,
            'output_height' => $newHeight
        ];
    }

    /**
     * Create image resource from file
     */
    private static function createImageResource($filepath, $mimeType) {
        switch ($mimeType) {
            case 'image/jpeg':
                return imagecreatefromjpeg($filepath);
            case 'image/png':
                return imagecreatefrompng($filepath);
            case 'image/webp':
                return function_exists('imagecreatefromwebp') ? imagecreatefromwebp($filepath) : false;
            default:
                return false;
        }
    }

    /**
     * Auto-orient image based on EXIF data
     */
    private static function autoOrientImage($image, $filepath) {
        if (!function_exists('exif_read_data')) {
            return $image; // No EXIF support
        }
        
        $exif = @exif_read_data($filepath);
        if (!$exif || !isset($exif['Orientation'])) {
            return $image; // No orientation data
        }
        
        $orientation = $exif['Orientation'];
        
        switch ($orientation) {
            case 2: // Horizontal flip
                imageflip($image, IMG_FLIP_HORIZONTAL);
                break;
            case 3: // 180 rotate
                $image = imagerotate($image, 180, 0);
                break;
            case 4: // Vertical flip
                imageflip($image, IMG_FLIP_VERTICAL);
                break;
            case 5: // Vertical flip + 90 rotate
                imageflip($image, IMG_FLIP_VERTICAL);
                $image = imagerotate($image, -90, 0);
                break;
            case 6: // 90 rotate
                $image = imagerotate($image, -90, 0);
                break;
            case 7: // Horizontal flip + 90 rotate  
                imageflip($image, IMG_FLIP_HORIZONTAL);
                $image = imagerotate($image, -90, 0);
                break;
            case 8: // 270 rotate
                $image = imagerotate($image, 90, 0);
                break;
        }
        
        return $image;
    }

    /**
     * Save image in specified format
     */
    private static function saveImageInFormat($image, $filepath, $format) {
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $quality = self::$qualitySettings[$format] ?? 85;
        
        switch ($format) {
            case 'avif':
                if (function_exists('imageavif')) {
                    return imageavif($image, $filepath, $quality);
                }
                return false;
                
            case 'webp':
                if (function_exists('imagewebp')) {
                    return imagewebp($image, $filepath, $quality);
                }
                return false;
                
            case 'jpeg':
                return imagejpeg($image, $filepath, $quality);
                
            case 'png':
                return imagepng($image, $filepath, $quality);
                
            default:
                return false;
        }
    }

    /**
     * Get available derivative files for an image
     */
    public static function getDerivativeFiles($originalPath, $originalFilename) {
        $pathInfo = pathinfo($originalFilename);
        $basename = $pathInfo['filename'];
        $outputDir = dirname($originalPath);
        
        $derivativeFiles = [];
        
        foreach (self::$derivatives as $name => $config) {
            $formats = ['avif', 'webp', 'jpeg'];
            $formatFiles = [];
            
            foreach ($formats as $format) {
                $filename = "{$basename}.{$name}.{$format}";
                $filepath = $outputDir . '/' . $filename;
                
                if (file_exists($filepath)) {
                    $formatFiles[] = [
                        'format' => $format,
                        'path' => $filepath,
                        'filename' => $filename,
                        'size' => filesize($filepath)
                    ];
                }
            }
            
            if (!empty($formatFiles)) {
                $derivativeFiles[$name] = $formatFiles;
            }
        }
        
        return $derivativeFiles;
    }

    /**
     * Optimize image quality and size
     */
    public static function optimizeImage($sourcePath, $outputPath, $quality = 85) {
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        $mimeType = $imageInfo['mime'];

        // Create source image
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false;
        }

        if (!$sourceImage) {
            return false;
        }

        // Save optimized image
        $success = false;
        switch ($mimeType) {
            case 'image/jpeg':
                $success = imagejpeg($sourceImage, $outputPath, $quality);
                break;
            case 'image/png':
                // PNG compression level (0-9, where 9 is maximum compression)
                $pngQuality = round((100 - $quality) / 10);
                $success = imagepng($sourceImage, $outputPath, $pngQuality);
                break;
            case 'image/webp':
                $success = imagewebp($sourceImage, $outputPath, $quality);
                break;
        }

        imagedestroy($sourceImage);
        return $success;
    }
}
?>