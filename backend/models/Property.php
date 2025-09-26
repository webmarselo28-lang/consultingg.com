<?php
require_once __DIR__ . '/../config/database.php';

class Property {
    private $conn;
    private $table_name = "properties";

    public function __construct() {
        $database = Database::getInstance();
        $this->conn = $database->getConnection();
    }

    public function create($data) {
        // Begin transaction
        $this->conn->beginTransaction();
        
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      (id, title, description, price, currency, transaction_type, property_type, 
                       city_region, district, address, area, bedrooms, bathrooms, floors, 
                       floor_number, terraces, construction_type, condition_type, heating, 
                       year_built, furnishing_level, has_elevator, has_garage, 
                       has_southern_exposure, new_construction, featured, active, property_code) 
                      VALUES 
                      (:id, :title, :description, :price, :currency, :transaction_type, :property_type,
                       :city_region, :district, :address, :area, :bedrooms, :bathrooms, :floors,
                       :floor_number, :terraces, :construction_type, :condition_type, :heating,
                       :year_built, :furnishing_level, :has_elevator, :has_garage,
                       :has_southern_exposure, :new_construction, :featured, :active, :property_code)";

            $stmt = $this->conn->prepare($query);
            
            // Generate UUID
            $data['id'] = $this->generateUUID();
        
            // Handle optional fields - use NULL for missing/empty values in detail fields
            $data['description'] = !empty($data['description']) ? $data['description'] : null;
            $data['district'] = !empty($data['district']) ? $data['district'] : null;
            $data['address'] = !empty($data['address']) ? $data['address'] : null;
            $data['floors'] = isset($data['floors']) && $data['floors'] > 0 ? $data['floors'] : null;
            $data['floor_number'] = isset($data['floor_number']) && $data['floor_number'] > 0 ? $data['floor_number'] : null;
            $data['construction_type'] = !empty($data['construction_type']) ? $data['construction_type'] : null;
            $data['condition_type'] = !empty($data['condition_type']) ? $data['condition_type'] : null;
            $data['heating'] = !empty($data['heating']) ? $data['heating'] : null;
            $data['year_built'] = isset($data['year_built']) && $data['year_built'] > 0 ? $data['year_built'] : null;
            $data['furnishing_level'] = !empty($data['furnishing_level']) ? $data['furnishing_level'] : null;
            $data['property_code'] = !empty($data['property_code']) ? $data['property_code'] : null;
            $data['bedrooms'] = isset($data['bedrooms']) && $data['bedrooms'] > 0 ? $data['bedrooms'] : null;
            $data['bathrooms'] = isset($data['bathrooms']) && $data['bathrooms'] > 0 ? $data['bathrooms'] : null;
            $data['terraces'] = isset($data['terraces']) && $data['terraces'] > 0 ? $data['terraces'] : null;
            $data['exposure'] = !empty($data['exposure']) ? $data['exposure'] : null;
            
            // Bind parameters
            $stmt->bindParam(':id', $data['id']);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':currency', $data['currency']);
            $stmt->bindParam(':transaction_type', $data['transaction_type']);
            $stmt->bindParam(':property_type', $data['property_type']);
            $stmt->bindParam(':city_region', $data['city_region']);
            $stmt->bindParam(':district', $data['district']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':area', $data['area']);
            $stmt->bindParam(':bedrooms', $data['bedrooms']);
            $stmt->bindParam(':bathrooms', $data['bathrooms']);
            $stmt->bindParam(':floors', $data['floors']);
            $stmt->bindParam(':floor_number', $data['floor_number']);
            $stmt->bindParam(':terraces', $data['terraces']);
            $stmt->bindParam(':construction_type', $data['construction_type']);
            $stmt->bindParam(':condition_type', $data['condition_type']);
            $stmt->bindParam(':heating', $data['heating']);
            $stmt->bindParam(':year_built', $data['year_built']);
            $stmt->bindParam(':furnishing_level', $data['furnishing_level']);
            $stmt->bindParam(':has_elevator', $data['has_elevator'], PDO::PARAM_BOOL);
            $stmt->bindParam(':has_garage', $data['has_garage'], PDO::PARAM_BOOL);
            $stmt->bindParam(':has_southern_exposure', $data['has_southern_exposure'], PDO::PARAM_BOOL);
            $stmt->bindParam(':new_construction', $data['new_construction'], PDO::PARAM_BOOL);
            $stmt->bindParam(':featured', $data['featured'], PDO::PARAM_BOOL);
            $stmt->bindParam(':active', $data['active'], PDO::PARAM_BOOL);
            $stmt->bindParam(':property_code', $data['property_code']);

            if ($stmt->execute()) {
                $this->conn->commit();
                return $data['id']; // Return UUID
            } else {
                $this->conn->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log('[Property@create] Transaction failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getAll($filters = []) {
        $query = "SELECT p.id, p.title, p.description, p.price, p.currency, p.transaction_type, 
                         p.property_type, p.city_region, p.district, p.address, p.area, 
                         p.bedrooms, p.bathrooms, p.floors, p.floor_number, p.terraces, 
                         p.construction_type, p.condition_type, p.heating, p.exposure, 
                         p.year_built, p.furnishing_level, p.has_elevator, p.has_garage, 
                         p.has_southern_exposure, p.new_construction, p.featured, p.active, 
                         p.created_at, p.updated_at,
                         COALESCE(
                            (SELECT json_agg(
                                json_build_object(
                                    'id', pi.id,
                                    'image_url', pi.image_url,
                                    'is_main', pi.is_main,
                                    'sort_order', pi.sort_order
                                ) ORDER BY pi.is_main DESC, pi.sort_order ASC
                            ) FROM property_images pi WHERE pi.property_id = p.id),
                            '[]'::json
                         ) as images
                  FROM " . $this->table_name . " p WHERE true";

        $params = [];

        // Apply filters
        if (!empty($filters['keyword'])) {
            $query .= " AND (
                p.title ILIKE :keyword_like
                OR p.description ILIKE :keyword_like
                OR p.city_region ILIKE :keyword_like
                OR p.district ILIKE :keyword_like
                OR p.address ILIKE :keyword_like
            )";
            $params[':keyword_like'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['transaction_type'])) {
            $query .= " AND p.transaction_type = :transaction_type";
            $params[':transaction_type'] = $filters['transaction_type'];
        }

        if (!empty($filters['city_region'])) {
            $query .= " AND p.city_region = :city_region";
            $params[':city_region'] = $filters['city_region'];
        }

        if (!empty($filters['property_type'])) {
            $query .= " AND p.property_type = :property_type";
            $params[':property_type'] = $filters['property_type'];
        }

        if (!empty($filters['district'])) {
            $query .= " AND p.district = :district";
            $params[':district'] = $filters['district'];
        }

        if (!empty($filters['price_min'])) {
            $query .= " AND p.price >= :price_min";
            $params[':price_min'] = $filters['price_min'];
        }

        if (!empty($filters['price_max'])) {
            $query .= " AND p.price <= :price_max";
            $params[':price_max'] = $filters['price_max'];
        }

        if (!empty($filters['area_min'])) {
            $query .= " AND p.area >= :area_min";
            $params[':area_min'] = $filters['area_min'];
        }

        if (!empty($filters['area_max'])) {
            $query .= " AND p.area <= :area_max";
            $params[':area_max'] = $filters['area_max'];
        }

        if (isset($filters['featured']) && $filters['featured'] === 'true') {
            $query .= " AND p.featured = TRUE";
        }

        if (!isset($filters['active']) || $filters['active'] !== 'all') {
            $query .= " AND p.active = TRUE";
        }

        $query .= " ORDER BY p.featured DESC, p.created_at DESC";

        if (!empty($filters['limit'])) {
            $query .= " LIMIT :limit";
            $params[':limit'] = (int)$filters['limit'];
        }

        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            if ($key === ':limit') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        
        $stmt->execute();
        $properties = $stmt->fetchAll();

        // Parse JSON images
        foreach ($properties as &$property) {
            if (is_string($property['images'])) {
                $property['images'] = json_decode($property['images'], true) ?: [];
            } elseif (!is_array($property['images'])) {
                $property['images'] = [];
            }
            
            // Process images with ImageHelper
            require_once __DIR__ . '/../utils/ImageHelper.php';
            $property['images'] = ImageHelper::processImages($property['images']);
        }

        return $properties;
    }

    public function getById($id) {
        error_log("Property::getById called with ID: " . $id);
        $query = "SELECT p.id, p.title, p.description, p.price, p.currency, p.transaction_type, 
                         p.property_type, p.city_region, p.district, p.address, p.area, 
                         p.bedrooms, p.bathrooms, p.floors, p.floor_number, p.terraces, 
                         p.construction_type, p.condition_type, p.heating, p.exposure, 
                         p.year_built, p.furnishing_level, p.has_elevator, p.has_garage, 
                         p.has_southern_exposure, p.new_construction, p.featured, p.active, 
                         p.created_at, p.updated_at,
                         COALESCE(
                            (SELECT json_agg(
                                json_build_object(
                                    'id', pi.id,
                                    'property_id', pi.property_id,
                                    'image_url', pi.image_url,
                                    'image_path', pi.image_path,
                                    'is_main', pi.is_main,
                                    'sort_order', pi.sort_order,
                                    'alt_text', pi.alt_text
                                ) ORDER BY pi.sort_order ASC, pi.is_main DESC
                            ) FROM property_images pi WHERE pi.property_id = p.id),
                            '[]'::json
                         ) as images
                  FROM " . $this->table_name . " p WHERE p.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $property = $stmt->fetch();
        error_log("Query executed. Property found: " . ($property ? 'YES' : 'NO'));
        if ($property) {
            if (is_string($property['images'])) {
                $property['images'] = json_decode($property['images'], true) ?: [];
            } elseif (!is_array($property['images'])) {
                $property['images'] = [];
            }
            
            // Process images with ImageHelper
            require_once __DIR__ . '/../utils/ImageHelper.php';
            $property['images'] = ImageHelper::processImages($property['images']);
            
            // Add documents
            require_once __DIR__ . '/Document.php';
            $documentModel = new Document();
            $documents = $documentModel->getByPropertyId($property['id']);
            $property['documents'] = array_map(function($doc) {
                return [
                    'id' => $doc['id'],
                    'filename' => $doc['original_filename'],
                    'size' => $doc['file_size'],
                    'url' => '/api/documents/serve/' . $doc['id']
                ];
            }, $documents);
        }

        return $property;
    }

    public function update($id, $data) {
        // Begin transaction
        $this->conn->beginTransaction();
        
        try {
            // Handle optional fields - use NULL for missing/empty values in detail fields
            $data['description'] = !empty($data['description']) ? $data['description'] : null;
            $data['district'] = !empty($data['district']) ? $data['district'] : null;
            $data['address'] = !empty($data['address']) ? $data['address'] : null;
            $data['floors'] = isset($data['floors']) && $data['floors'] > 0 ? $data['floors'] : null;
            $data['floor_number'] = isset($data['floor_number']) && $data['floor_number'] > 0 ? $data['floor_number'] : null;
            $data['construction_type'] = !empty($data['construction_type']) ? $data['construction_type'] : null;
            $data['condition_type'] = !empty($data['condition_type']) ? $data['condition_type'] : null;
            $data['heating'] = !empty($data['heating']) ? $data['heating'] : null;
            $data['year_built'] = isset($data['year_built']) && $data['year_built'] > 0 ? $data['year_built'] : null;
            $data['furnishing_level'] = !empty($data['furnishing_level']) ? $data['furnishing_level'] : null;
            $data['property_code'] = !empty($data['property_code']) ? $data['property_code'] : null;
            $data['bedrooms'] = isset($data['bedrooms']) && $data['bedrooms'] > 0 ? $data['bedrooms'] : null;
            $data['bathrooms'] = isset($data['bathrooms']) && $data['bathrooms'] > 0 ? $data['bathrooms'] : null;
            $data['terraces'] = isset($data['terraces']) && $data['terraces'] > 0 ? $data['terraces'] : null;
            $data['exposure'] = !empty($data['exposure']) ? $data['exposure'] : null;
            
            $query = "UPDATE " . $this->table_name . " SET 
                      title = :title, description = :description, price = :price, 
                      currency = :currency, transaction_type = :transaction_type, 
                      property_type = :property_type, city_region = :city_region, 
                      district = :district, address = :address, area = :area, 
                      bedrooms = :bedrooms, bathrooms = :bathrooms, floors = :floors, 
                      floor_number = :floor_number, terraces = :terraces, 
                      construction_type = :construction_type, condition_type = :condition_type, 
                      heating = :heating, year_built = :year_built,
                      furnishing_level = :furnishing_level, has_elevator = :has_elevator, 
                      has_garage = :has_garage, has_southern_exposure = :has_southern_exposure, 
                      new_construction = :new_construction, featured = :featured, 
                      active = :active, property_code = :property_code, updated_at = CURRENT_TIMESTAMP 
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':currency', $data['currency']);
            $stmt->bindParam(':transaction_type', $data['transaction_type']);
            $stmt->bindParam(':property_type', $data['property_type']);
            $stmt->bindParam(':city_region', $data['city_region']);
            $stmt->bindParam(':district', $data['district']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':area', $data['area']);
            $stmt->bindParam(':bedrooms', $data['bedrooms']);
            $stmt->bindParam(':bathrooms', $data['bathrooms']);
            $stmt->bindParam(':floors', $data['floors']);
            $stmt->bindParam(':floor_number', $data['floor_number']);
            $stmt->bindParam(':terraces', $data['terraces']);
            $stmt->bindParam(':construction_type', $data['construction_type']);
            $stmt->bindParam(':condition_type', $data['condition_type']);
            $stmt->bindParam(':heating', $data['heating']);
            $stmt->bindParam(':year_built', $data['year_built']);
            $stmt->bindParam(':furnishing_level', $data['furnishing_level']);
            $stmt->bindParam(':has_elevator', $data['has_elevator'], PDO::PARAM_BOOL);
            $stmt->bindParam(':has_garage', $data['has_garage'], PDO::PARAM_BOOL);
            $stmt->bindParam(':has_southern_exposure', $data['has_southern_exposure'], PDO::PARAM_BOOL);
            $stmt->bindParam(':new_construction', $data['new_construction'], PDO::PARAM_BOOL);
            $stmt->bindParam(':featured', $data['featured'], PDO::PARAM_BOOL);
            $stmt->bindParam(':active', $data['active'], PDO::PARAM_BOOL);
            $stmt->bindParam(':property_code', $data['property_code']);

            if ($stmt->execute()) {
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log('[Property@update] Transaction failed: ' . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        // First delete associated images
        require_once __DIR__ . '/PropertyImage.php';
        $imageModel = new PropertyImage();
        $imageModel->deleteByPropertyId($id);

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function getAllPaginated(array $filters, int $page, int $limit): array {
        $where = ['1=1'];
        $params = [];

        // Apply filters
        if (isset($filters['featured']) && $filters['featured'] !== null) {
            $where[] = 'p.featured = :featured';
            $params[':featured'] = $filters['featured'] ? 1 : 0;
        }

        if (!empty($filters['keyword'])) {
            $where[] = '(
                p.title ILIKE :keyword_like
                OR p.description ILIKE :keyword_like
                OR p.city_region ILIKE :keyword_like
                OR p.district ILIKE :keyword_like
                OR p.address ILIKE :keyword_like
            )';
            $params[':keyword_like'] = '%' . $filters['keyword'] . '%';
        }

        if (!empty($filters['transaction_type'])) {
            $where[] = 'p.transaction_type = :transaction_type';
            $params[':transaction_type'] = $filters['transaction_type'];
        }

        if (!empty($filters['city_region'])) {
            $where[] = 'p.city_region = :city_region';
            $params[':city_region'] = $filters['city_region'];
        }

        if (!empty($filters['property_type'])) {
            $where[] = 'p.property_type = :property_type';
            $params[':property_type'] = $filters['property_type'];
        }

        if (!empty($filters['district'])) {
            $where[] = 'p.district = :district';
            $params[':district'] = $filters['district'];
        }

        if (!empty($filters['price_min'])) {
            $where[] = 'p.price >= :price_min';
            $params[':price_min'] = $filters['price_min'];
        }

        if (!empty($filters['price_max'])) {
            $where[] = 'p.price <= :price_max';
            $params[':price_max'] = $filters['price_max'];
        }

        if (!empty($filters['area_min'])) {
            $where[] = 'p.area >= :area_min';
            $params[':area_min'] = $filters['area_min'];
        }

        if (!empty($filters['area_max'])) {
            $where[] = 'p.area <= :area_max';
            $params[':area_max'] = $filters['area_max'];
        }

        if (!isset($filters['active']) || $filters['active'] !== 'all') {
            $where[] = 'p.active = TRUE';
        }

        $whereSql = implode(' AND ', $where);
        $offset = max(0, ($page - 1) * $limit);

        // COUNT query
        $sqlCount = "SELECT COUNT(*) AS c FROM " . $this->table_name . " p WHERE $whereSql";
        $stmt = $this->conn->prepare($sqlCount);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $total = (int)$stmt->fetchColumn();

        // DATA query with images
        $sql = "SELECT p.id, p.title, p.description, p.price, p.currency, p.transaction_type, 
                       p.property_type, p.city_region, p.district, p.address, p.area, 
                       p.bedrooms, p.bathrooms, p.floors, p.floor_number, p.terraces, 
                       p.construction_type, p.condition_type, p.heating, p.exposure, 
                       p.year_built, p.furnishing_level, p.has_elevator, p.has_garage, 
                       p.has_southern_exposure, p.new_construction, p.featured, p.active, 
                       p.created_at, p.updated_at,
                       COALESCE(
                          (SELECT json_agg(
                              json_build_object(
                                  'id', pi.id,
                                  'property_id', pi.property_id,
                                  'image_url', pi.image_url,
                                  'image_path', pi.image_path,
                                  'is_main', pi.is_main,
                                  'sort_order', pi.sort_order,
                                  'alt_text', pi.alt_text
                              ) ORDER BY pi.sort_order ASC, pi.is_main DESC
                          ) FROM property_images pi WHERE pi.property_id = p.id),
                          '[]'::json
                       ) as images
                FROM " . $this->table_name . " p 
                WHERE $whereSql
                ORDER BY p.featured DESC, p.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        // Parse JSON images
        foreach ($items as &$item) {
            if (is_string($item['images'])) {
                $item['images'] = json_decode($item['images'], true) ?: [];
            } elseif (!is_array($item['images'])) {
                $item['images'] = [];
            }
            
            // Process images with ImageHelper
            require_once __DIR__ . '/../utils/ImageHelper.php';
            $item['images'] = ImageHelper::processImages($item['images']);
        }

        return ['items' => $items, 'total' => $total];
    }

    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_properties,
                    COUNT(CASE WHEN active = true THEN 1 END) as active_properties,
                    COUNT(CASE WHEN featured = true THEN 1 END) as featured_properties,
                    AVG(price) as average_price
                  FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch();
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