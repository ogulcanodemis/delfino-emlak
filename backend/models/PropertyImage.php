<?php
require_once __DIR__ . '/../config/database.php';

class PropertyImage {
    private $conn;
    private $table = 'property_images';
    
    public $id;
    public $property_id;
    public $image_path;
    public $image_name;
    public $image_size;
    public $image_type;
    public $alt_text;
    public $is_primary;
    public $display_order;
    public $created_at;
    public $updated_at;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Emlağa ait tüm resimleri getir
    public function getByPropertyId($property_id, $active_only = true) {
        $where_clause = $active_only ? "WHERE property_id = :property_id" : "WHERE property_id = :property_id";
        
        $query = "SELECT * FROM " . $this->table . " 
                  " . $where_clause . "
                  ORDER BY is_primary DESC, display_order ASC, created_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $property_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // ID ile resim getir
    public function getById($id) {
        $query = "SELECT pi.*, p.title as property_title 
                  FROM " . $this->table . " pi
                  LEFT JOIN properties p ON p.id = pi.property_id
                  WHERE pi.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Emlağın ana resmini getir
    public function getPrimaryImage($property_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE property_id = :property_id AND is_primary = 1
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $property_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Yeni resim ekle
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                  (property_id, image_path, image_name, image_size, image_type, alt_text, is_primary, display_order)
                  VALUES (:property_id, :image_path, :image_name, :image_size, :image_type, :alt_text, :is_primary, :display_order)";
        
        $stmt = $this->conn->prepare($query);
        
        // Eğer bu ana resim olarak işaretlendiyse, diğer ana resimleri kaldır
        if ($this->is_primary) {
            $this->removePrimaryStatus($this->property_id);
        }
        
        // Display order belirlenmemişse, son sırayı al
        if (empty($this->display_order)) {
            $this->display_order = $this->getNextDisplayOrder($this->property_id);
        }
        
        $stmt->bindParam(':property_id', $this->property_id);
        $stmt->bindParam(':image_path', $this->image_path);
        $stmt->bindParam(':image_name', $this->image_name);
        $stmt->bindParam(':image_size', $this->image_size);
        $stmt->bindParam(':image_type', $this->image_type);
        $stmt->bindParam(':alt_text', $this->alt_text);
        $stmt->bindParam(':is_primary', $this->is_primary);
        $stmt->bindParam(':display_order', $this->display_order);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Resim güncelle
    public function update() {
        $query = "UPDATE " . $this->table . "
                  SET image_name = :image_name,
                      alt_text = :alt_text,
                      is_primary = :is_primary,
                      display_order = :display_order,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Eğer bu ana resim olarak işaretlendiyse, diğer ana resimleri kaldır
        if ($this->is_primary) {
            $this->removePrimaryStatus($this->property_id, $this->id);
        }
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':image_name', $this->image_name);
        $stmt->bindParam(':alt_text', $this->alt_text);
        $stmt->bindParam(':is_primary', $this->is_primary);
        $stmt->bindParam(':display_order', $this->display_order);
        
        return $stmt->execute();
    }
    
    // Resim sil
    public function delete() {
        // Önce dosyayı sil
        $image_info = $this->getById($this->id);
        if ($image_info && file_exists($image_info['image_path'])) {
            unlink($image_info['image_path']);
        }
        
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            // Eğer silinen resim ana resimse, ilk resmi ana resim yap
            if ($image_info && $image_info['is_primary']) {
                $this->setFirstImageAsPrimary($image_info['property_id']);
            }
            return ['success' => true, 'message' => 'Resim başarıyla silindi'];
        }
        
        return ['success' => false, 'message' => 'Resim silinirken hata oluştu'];
    }
    
    // Ana resim durumunu kaldır
    private function removePrimaryStatus($property_id, $exclude_id = null) {
        $query = "UPDATE " . $this->table . " 
                  SET is_primary = 0 
                  WHERE property_id = :property_id";
        
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $property_id);
        
        if ($exclude_id) {
            $stmt->bindParam(':exclude_id', $exclude_id);
        }
        
        $stmt->execute();
    }
    
    // İlk resmi ana resim yap
    private function setFirstImageAsPrimary($property_id) {
        $query = "UPDATE " . $this->table . " 
                  SET is_primary = 1 
                  WHERE property_id = :property_id 
                  ORDER BY display_order ASC, created_at ASC 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $property_id);
        $stmt->execute();
    }
    
    // Sonraki display order'ı getir
    private function getNextDisplayOrder($property_id) {
        $query = "SELECT COALESCE(MAX(display_order), 0) + 1 as next_order 
                  FROM " . $this->table . " 
                  WHERE property_id = :property_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $property_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['next_order'];
    }
    
    // Resim sırasını güncelle
    public function updateDisplayOrder($images_order) {
        try {
            $this->conn->beginTransaction();
            
            foreach ($images_order as $order => $image_id) {
                $query = "UPDATE " . $this->table . " 
                          SET display_order = :display_order 
                          WHERE id = :id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':display_order', $order);
                $stmt->bindParam(':id', $image_id);
                $stmt->execute();
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
    
    // Ana resim belirle
    public function setPrimary($image_id, $property_id) {
        try {
            $this->conn->beginTransaction();
            
            // Önce tüm resimlerin ana resim durumunu kaldır
            $this->removePrimaryStatus($property_id);
            
            // Belirtilen resmi ana resim yap
            $query = "UPDATE " . $this->table . " 
                      SET is_primary = 1 
                      WHERE id = :id AND property_id = :property_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $image_id);
            $stmt->bindParam(':property_id', $property_id);
            $stmt->execute();
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
    
    // Emlak resim sayısını getir
    public function getImageCount($property_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                  WHERE property_id = :property_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $property_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    // Kullanıcının toplam resim boyutunu getir
    public function getUserTotalImageSize($user_id) {
        $query = "SELECT COALESCE(SUM(pi.image_size), 0) as total_size 
                  FROM " . $this->table . " pi
                  JOIN properties p ON p.id = pi.property_id
                  WHERE p.user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_size'];
    }
    
    // En büyük resimleri getir
    public function getLargestImages($limit = 10) {
        $query = "SELECT pi.*, p.title as property_title 
                  FROM " . $this->table . " pi
                  JOIN properties p ON p.id = pi.property_id
                  ORDER BY pi.image_size DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // İstatistikler
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_images,
                    COUNT(DISTINCT property_id) as properties_with_images,
                    COALESCE(SUM(image_size), 0) as total_size,
                    COALESCE(AVG(image_size), 0) as average_size,
                    COUNT(CASE WHEN is_primary = 1 THEN 1 END) as primary_images
                  FROM " . $this->table;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Validation
    public function validate() {
        $errors = [];
        
        if (empty($this->property_id)) {
            $errors[] = 'Emlak ID gereklidir';
        }
        
        if (empty($this->image_path)) {
            $errors[] = 'Resim yolu gereklidir';
        }
        
        if (empty($this->image_name)) {
            $errors[] = 'Resim adı gereklidir';
        }
        
        if (empty($this->image_size) || $this->image_size <= 0) {
            $errors[] = 'Geçerli resim boyutu gereklidir';
        }
        
        if (empty($this->image_type)) {
            $errors[] = 'Resim tipi gereklidir';
        }
        
        // Resim boyutu kontrolü (5MB limit)
        if ($this->image_size > 5 * 1024 * 1024) {
            $errors[] = 'Resim boyutu 5MB\'dan büyük olamaz';
        }
        
        // Resim tipi kontrolü
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($this->image_type, $allowed_types)) {
            $errors[] = 'Desteklenmeyen resim formatı';
        }
        
        return $errors;
    }
} 