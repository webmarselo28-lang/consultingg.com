<?php

class ImageHelper {
    /**
     * Generate safe filename for uploads
     */
    public static function safeFilename($originalName, $propertyId = null) {
        $pathInfo = pathinfo($originalName);
        $extension = strtolower($pathInfo['extension'] ?? 'jpg');
        $basename = $pathInfo['filename'] ?? 'image';
        
        // Convert to ASCII slug
        $slug = self::slugify($basename);
        
        // Add property ID and timestamp for uniqueness
        $timestamp = time();
        $random = substr(md5(uniqid()), 0, 6);
        
        if ($propertyId) {
            return "prop_{$propertyId}_{$slug}_{$timestamp}_{$random}.{$extension}";
        } else {
            return "{$slug}_{$timestamp}_{$random}.{$extension}";
        }
    }
    
    /**
     * Convert string to ASCII slug
     */
    private static function slugify($text) {
        // Transliterate Cyrillic to Latin
        $cyrillic = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
            'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
            'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sht', 'ъ' => 'a',
            'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E',
            'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M',
            'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U',
            'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sht', 'Ъ' => 'A',
            'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya'
        ];
        
        $text = strtr($text, $cyrillic);
        
        // Remove non-ASCII characters and convert to lowercase
        $text = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        
        return $text ?: 'image';
    }
    
    /**
     * Build fully qualified image URL
     */
    public static function buildImageUrl($relativePath) {
        if (empty($relativePath)) {
            return null;
        }
        
        // Get base URL from environment
        $baseUrl = $_ENV['APP_URL'] ?? 'https://consultingg.com';
        $baseUrl = rtrim($baseUrl, '/');
        
        // Ensure path starts with /
        if (!str_starts_with($relativePath, '/')) {
            $relativePath = '/' . $relativePath;
        }
        
        // Encode each path segment to handle spaces and non-ASCII characters
        $pathParts = explode('/', trim($relativePath, '/'));
        $encodedParts = array_map('rawurlencode', $pathParts);
        $encodedPath = '/' . implode('/', $encodedParts);
        
        return $baseUrl . $encodedPath;
    }
    
    /**
     * Process images array and add computed URL field
     */
    public static function processImages($images) {
        if (!is_array($images)) {
            return [];
        }
        
        $processedImages = [];
        
        foreach ($images as $image) {
            if (is_array($image)) {
                // Add computed URL field
                $image['url'] = self::buildImageUrl($image['image_url'] ?? '');
                $processedImages[] = $image;
            }
        }
        
        // Sort: main first, then by sort_order, then by id
        usort($processedImages, function($a, $b) {
            // Main image first
            if ($a['is_main'] && !$b['is_main']) return -1;
            if (!$a['is_main'] && $b['is_main']) return 1;
            
            // Then by sort_order
            $sortA = $a['sort_order'] ?? 0;
            $sortB = $b['sort_order'] ?? 0;
            if ($sortA !== $sortB) {
                return $sortA - $sortB;
            }
            
            // Finally by id
            return strcmp($a['id'] ?? '', $b['id'] ?? '');
        });
        
        return $processedImages;
    }
}
?>