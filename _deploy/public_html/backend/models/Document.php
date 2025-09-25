<?php
require_once __DIR__ . '/../config/database.php';

class Document {
    private $conn;
    private $table_name = "property_documents";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    public function create($data) {
        // Use RETURNING clause for PostgreSQL to get the generated UUID
        $query = "INSERT INTO " . $this->table_name . " 
                  (property_id, filename, original_filename, file_path, file_size, mime_type) 
                  VALUES 
                  (:property_id, :filename, :original_filename, :file_path, :file_size, :mime_type)
                  RETURNING id";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':property_id', $data['property_id']);
        $stmt->bindParam(':filename', $data['filename']);
        $stmt->bindParam(':original_filename', $data['original_filename']);
        $stmt->bindParam(':file_path', $data['file_path']);
        $stmt->bindParam(':file_size', $data['file_size']);
        $stmt->bindParam(':mime_type', $data['mime_type']);

        if ($stmt->execute()) {
            $documentId = $stmt->fetchColumn();
            return $documentId ? $documentId : false;
        }
        return false;
    }

    public function getByPropertyId($propertyId) {
        $query = "SELECT id, filename, original_filename, file_path, file_size, mime_type, created_at 
                  FROM " . $this->table_name . " 
                  WHERE property_id = :property_id 
                  ORDER BY created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $propertyId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteByPropertyId($propertyId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE property_id = :property_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $propertyId);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
?>