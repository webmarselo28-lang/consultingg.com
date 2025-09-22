<?php
require_once __DIR__ . '/../config/database.php';

class PropertyImage {
    private $conn;
    private $table_name = "property_images";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id, property_id, image_url, image_path, alt_text, sort_order, is_main) 
                  VALUES 
                  (:id, :property_id, :image_url, :image_path, :alt_text, :sort_order, :is_main)";

        $stmt = $this->conn->prepare($query);
        
        $data['id'] = $this->generateUUID();
        
        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':property_id', $data['property_id']);
        $stmt->bindParam(':image_url', $data['image_url']);
        $stmt->bindParam(':image_path', $data['image_path']);
        $stmt->bindParam(':alt_text', $data['alt_text']);
        $stmt->bindParam(':sort_order', $data['sort_order']);
        $stmt->bindParam(':is_main', $data['is_main'], PDO::PARAM_BOOL);

        if ($stmt->execute()) {
            return $data['id'];
        }
        return false;
    }

    public function getByPropertyId($property_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE property_id = :property_id 
                  ORDER BY sort_order ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $property_id);
        $stmt->execute();

        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET 
                  image_url = :image_url, image_path = :image_path, 
                  alt_text = :alt_text, sort_order = :sort_order, 
                  is_main = :is_main 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':image_url', $data['image_url']);
        $stmt->bindParam(':image_path', $data['image_path']);
        $stmt->bindParam(':alt_text', $data['alt_text']);
        $stmt->bindParam(':sort_order', $data['sort_order']);
        $stmt->bindParam(':is_main', $data['is_main'], PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    public function delete($id) {
        error_log('[PropertyImage] Deleting image ID: ' . $id);
        
        // Delete from database
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        $result = $stmt->execute();
        error_log('[PropertyImage] Database delete result: ' . ($result ? 'SUCCESS' : 'FAILED'));
        
        return $result;
    }

    public function deleteByPropertyId($property_id) {
        // Get all images for this property
        $images = $this->getByPropertyId($property_id);
        
        // Delete files from filesystem
        foreach ($images as $image) {
            $fullPath = __DIR__ . '/../..' . $image['image_path'];
            if ($image['image_path'] && file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        // Delete from database
        $query = "DELETE FROM " . $this->table_name . " WHERE property_id = :property_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $property_id);
        
        return $stmt->execute();
    }

    public function unsetMainImages($property_id) {
        $query = "UPDATE " . $this->table_name . " SET is_main = false WHERE property_id = :property_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $property_id);
        return $stmt->execute();
    }

    public function setMainImage($property_id, $image_id) {
        $database = Database::getInstance();
        $conn = $database->getConnection();
        
        try {
            // Start transaction
            $conn->beginTransaction();
            
            // First, unset all main images for this property
            $query = "UPDATE " . $this->table_name . " SET is_main = :false WHERE property_id = :property_id";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':false', false, PDO::PARAM_BOOL);
            $stmt->bindParam(':property_id', $property_id);
            $stmt->execute();

            // Then set the specified image as main
            $query = "UPDATE " . $this->table_name . " SET is_main = :true WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':true', true, PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $image_id);
        
            $result = $stmt->execute();
            $conn->commit();
            return $result;
        } catch (Throwable $e) {
            $conn->rollback();
            throw $e;
        }
    }

    public function deleteByPropertyAndImageId($propertyId, $imageId) {
        // Get image info before deleting
        $query = "SELECT image_path, thumbnail_url FROM " . $this->table_name . " WHERE id = :id AND property_id = :property_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $imageId);
        $stmt->bindParam(':property_id', $propertyId);
        $stmt->execute();
        $image = $stmt->fetch();
        
        if (!$image) {
            return false;
        }

        // Delete file from filesystem
        $fullPath = __DIR__ . '/../..' . $image['image_path'];
        if ($image['image_path'] && file_exists($fullPath)) {
            unlink($fullPath);
        }
        
        // Delete thumbnail if exists
        if ($image['thumbnail_url']) {
            $thumbnailPath = __DIR__ . '/../..' . $image['thumbnail_url'];
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        }

        // Delete from database
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND property_id = :property_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $imageId);
        $stmt->bindParam(':property_id', $propertyId);
        
        return $stmt->execute();
    }

    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
?>