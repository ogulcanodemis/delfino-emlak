<?php
/**
 * PropertyType Model
 * Emlak-Delfino Projesi
 * Emlak tiplerini yönetir (Daire, Villa, Arsa, vb.)
 */

class PropertyType {
    private $conn;
    private $table_name = "property_types";

    // Nesne özellikleri
    public $id;
    public $name;
    public $description;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Tüm emlak tiplerini getir
     */
    public function getAll() {
        $query = "SELECT id, name, description FROM " . $this->table_name . " ORDER BY name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ID'ye göre emlak tipi getir
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * İsme göre emlak tipi getir
     */
    public function getByName($name) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE name = :name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Emlak tipi arama
     */
    public function search($search_term) {
        $query = "SELECT id, name, description FROM " . $this->table_name . " 
                  WHERE name LIKE :search_term OR description LIKE :search_term
                  ORDER BY name";
        
        $stmt = $this->conn->prepare($query);
        $search_term = '%' . $search_term . '%';
        $stmt->bindParam(':search_term', $search_term);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Emlak tipi oluştur (admin için)
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, description, created_at, updated_at) 
                  VALUES (:name, :description, NOW(), NOW())";

        $stmt = $this->conn->prepare($query);
        
        // Verileri temizle
        $this->name = htmlspecialchars(strip_tags($this->name ?? ''));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Emlak tipi güncelle (admin için)
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, 
                      description = :description, 
                      updated_at = NOW() 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        
        // Verileri temizle
        $this->name = htmlspecialchars(strip_tags($this->name ?? ''));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));
        $this->id = htmlspecialchars(strip_tags($this->id ?? ''));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * Emlak tipi sil (admin için)
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id ?? ''));
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * Emlak tipinin var olup olmadığını kontrol et
     */
    public function exists($id) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Emlak tipindeki toplam ilan sayısını getir
     */
    public function getPropertyCount($type_id) {
        $query = "SELECT COUNT(*) as property_count 
                  FROM properties 
                  WHERE property_type_id = :type_id AND is_active = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':type_id', $type_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['property_count'];
    }

    /**
     * En çok ilan olan emlak tiplerini getir
     */
    public function getTopTypesByPropertyCount($limit = 10) {
        $query = "SELECT 
                    pt.id, 
                    pt.name, 
                    pt.description,
                    COUNT(p.id) as property_count
                  FROM " . $this->table_name . " pt
                  LEFT JOIN properties p ON pt.id = p.property_type_id AND p.is_active = 1
                  GROUP BY pt.id, pt.name, pt.description
                  HAVING property_count > 0
                  ORDER BY property_count DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * İlan sayısıyla birlikte tüm emlak tiplerini getir
     */
    public function getAllWithPropertyCount() {
        $query = "SELECT 
                    pt.id, 
                    pt.name, 
                    pt.description,
                    COUNT(p.id) as property_count
                  FROM " . $this->table_name . " pt
                  LEFT JOIN properties p ON pt.id = p.property_type_id AND p.is_active = 1
                  GROUP BY pt.id, pt.name, pt.description
                  ORDER BY pt.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Emlak tipinin kullanılıp kullanılmadığını kontrol et
     */
    public function isInUse($id) {
        $query = "SELECT COUNT(*) as count FROM properties WHERE property_type_id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] > 0;
    }
}
?> 