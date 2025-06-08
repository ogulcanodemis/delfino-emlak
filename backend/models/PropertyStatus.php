<?php
require_once __DIR__ . '/../config/database.php';

class PropertyStatus {
    private $conn;
    private $table = 'property_statuses';
    
    public $id;
    public $name;
    public $slug;
    public $description;
    public $is_active;
    public $created_at;
    public $updated_at;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Tüm durumları getir
    public function getAll($active_only = true) {
        $where_clause = $active_only ? "WHERE ps.is_active = 1" : "";
        
        $query = "SELECT 
                    ps.*,
                    COUNT(p.id) as property_count
                  FROM " . $this->table . " ps
                  LEFT JOIN properties p ON p.status_id = ps.id
                  " . $where_clause . "
                  GROUP BY ps.id
                  ORDER BY ps.name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // ID ile durum getir
    public function getById($id) {
        $query = "SELECT 
                    ps.*,
                    COUNT(p.id) as property_count
                  FROM " . $this->table . " ps
                  LEFT JOIN properties p ON p.status_id = ps.id
                  WHERE ps.id = :id
                  GROUP BY ps.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Slug ile durum getir
    public function getBySlug($slug) {
        $query = "SELECT 
                    ps.*,
                    COUNT(p.id) as property_count
                  FROM " . $this->table . " ps
                  LEFT JOIN properties p ON p.status_id = ps.id
                  WHERE ps.slug = :slug AND ps.is_active = 1
                  GROUP BY ps.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Yeni durum ekle
    public function create() {
        $query = "INSERT INTO " . $this->table . "
                  (name, slug, description, is_active)
                  VALUES (:name, :slug, :description, :is_active)";
        
        $stmt = $this->conn->prepare($query);
        
        // Slug oluştur
        if (empty($this->slug)) {
            $this->slug = $this->createSlug($this->name);
        }
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':is_active', $this->is_active);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Durum güncelle
    public function update() {
        $query = "UPDATE " . $this->table . "
                  SET name = :name,
                      slug = :slug,
                      description = :description,
                      is_active = :is_active,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Slug güncelle
        if (empty($this->slug)) {
            $this->slug = $this->createSlug($this->name);
        }
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':is_active', $this->is_active);
        
        return $stmt->execute();
    }
    
    // Durum sil
    public function delete() {
        // Önce bu duruma sahip emlak var mı kontrol et
        $check_query = "SELECT COUNT(*) as count FROM properties WHERE status_id = :id";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(':id', $this->id);
        $check_stmt->execute();
        $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            return ['success' => false, 'message' => 'Bu duruma sahip emlaklar bulunduğu için silinemez'];
        }
        
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Durum başarıyla silindi'];
        }
        
        return ['success' => false, 'message' => 'Durum silinirken hata oluştu'];
    }
    
    // Durum durumunu değiştir (aktif/pasif)
    public function toggleStatus() {
        $query = "UPDATE " . $this->table . "
                  SET is_active = NOT is_active,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }
    
    // Arama yap
    public function search($search_term, $active_only = true) {
        $where_clause = $active_only ? "WHERE ps.is_active = 1 AND" : "WHERE";
        
        $query = "SELECT 
                    ps.*,
                    COUNT(p.id) as property_count
                  FROM " . $this->table . " ps
                  LEFT JOIN properties p ON p.status_id = ps.id
                  " . $where_clause . " (ps.name LIKE :search OR ps.description LIKE :search)
                  GROUP BY ps.id
                  ORDER BY ps.name ASC";
        
        $stmt = $this->conn->prepare($query);
        $search_param = '%' . $search_term . '%';
        $stmt->bindParam(':search', $search_param);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // En çok kullanılan durumları getir
    public function getMostUsed($limit = 5) {
        $query = "SELECT 
                    ps.*,
                    COUNT(p.id) as property_count
                  FROM " . $this->table . " ps
                  LEFT JOIN properties p ON p.status_id = ps.id
                  WHERE ps.is_active = 1
                  GROUP BY ps.id
                  HAVING property_count > 0
                  ORDER BY property_count DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // İstatistikler
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_statuses,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_statuses,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_statuses
                  FROM " . $this->table;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Slug oluştur
    private function createSlug($text) {
        // Türkçe karakterleri değiştir
        $turkish = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'I', 'İ', 'Ö', 'Ş', 'Ü'];
        $english = ['c', 'g', 'i', 'o', 's', 'u', 'c', 'g', 'i', 'i', 'o', 's', 'u'];
        $text = str_replace($turkish, $english, $text);
        
        // Küçük harfe çevir ve özel karakterleri temizle
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        $text = trim($text, '-');
        
        return $text;
    }
    
    // Slug benzersizliğini kontrol et
    public function isSlugUnique($slug, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE slug = :slug";
        
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug);
        
        if ($exclude_id) {
            $stmt->bindParam(':exclude_id', $exclude_id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] == 0;
    }
    
    // Validation
    public function validate() {
        $errors = [];
        
        if (empty($this->name)) {
            $errors[] = 'Durum adı gereklidir';
        } elseif (strlen($this->name) < 2) {
            $errors[] = 'Durum adı en az 2 karakter olmalıdır';
        } elseif (strlen($this->name) > 100) {
            $errors[] = 'Durum adı en fazla 100 karakter olmalıdır';
        }
        
        if (!empty($this->description) && strlen($this->description) > 500) {
            $errors[] = 'Açıklama en fazla 500 karakter olmalıdır';
        }
        
        // Slug benzersizlik kontrolü
        if (!empty($this->slug)) {
            if (!$this->isSlugUnique($this->slug, $this->id)) {
                $errors[] = 'Bu slug zaten kullanılıyor';
            }
        }
        
        return $errors;
    }
} 