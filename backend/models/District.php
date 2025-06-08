<?php
/**
 * District Model
 * Emlak-Delfino Projesi
 * İlçe verilerini yönetir
 */

class District {
    private $conn;
    private $table_name = "districts";

    // Nesne özellikleri
    public $id;
    public $city_id;
    public $name;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Şehre göre ilçeleri getir
     */
    public function getByCity($city_id) {
        $query = "SELECT d.id, d.name, d.city_id, c.name as city_name 
                  FROM " . $this->table_name . " d
                  LEFT JOIN cities c ON d.city_id = c.id
                  WHERE d.city_id = :city_id 
                  ORDER BY d.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':city_id', $city_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ID'ye göre ilçe getir
     */
    public function getById($id) {
        $query = "SELECT d.*, c.name as city_name 
                  FROM " . $this->table_name . " d
                  LEFT JOIN cities c ON d.city_id = c.id
                  WHERE d.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Tüm ilçeleri getir
     */
    public function getAll() {
        $query = "SELECT d.id, d.name, d.city_id, c.name as city_name 
                  FROM " . $this->table_name . " d
                  LEFT JOIN cities c ON d.city_id = c.id
                  ORDER BY c.name, d.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * İlçe arama
     */
    public function search($search_term, $city_id = null) {
        $query = "SELECT d.id, d.name, d.city_id, c.name as city_name 
                  FROM " . $this->table_name . " d
                  LEFT JOIN cities c ON d.city_id = c.id
                  WHERE d.name LIKE :search_term";
        
        if ($city_id) {
            $query .= " AND d.city_id = :city_id";
        }
        
        $query .= " ORDER BY d.name";
        
        $stmt = $this->conn->prepare($query);
        $search_term = '%' . $search_term . '%';
        $stmt->bindParam(':search_term', $search_term);
        
        if ($city_id) {
            $stmt->bindParam(':city_id', $city_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * İlçe oluştur (admin için)
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (city_id, name, created_at, updated_at) 
                  VALUES (:city_id, :name, NOW(), NOW())";

        $stmt = $this->conn->prepare($query);
        
        // Verileri temizle
        $this->city_id = htmlspecialchars(strip_tags($this->city_id ?? ''));
        $this->name = htmlspecialchars(strip_tags($this->name ?? ''));

        $stmt->bindParam(':city_id', $this->city_id);
        $stmt->bindParam(':name', $this->name);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * İlçe güncelle (admin için)
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET city_id = :city_id, 
                      name = :name, 
                      updated_at = NOW() 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        
        // Verileri temizle
        $this->city_id = htmlspecialchars(strip_tags($this->city_id ?? ''));
        $this->name = htmlspecialchars(strip_tags($this->name ?? ''));
        $this->id = htmlspecialchars(strip_tags($this->id ?? ''));

        $stmt->bindParam(':city_id', $this->city_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * İlçe sil (admin için)
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id ?? ''));
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * İlçenin var olup olmadığını kontrol et
     */
    public function exists($id) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    /**
     * İlçedeki toplam ilan sayısını getir
     */
    public function getPropertyCount($district_id) {
        $query = "SELECT COUNT(*) as property_count 
                  FROM properties 
                  WHERE district_id = :district_id AND is_active = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':district_id', $district_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['property_count'];
    }

    /**
     * Şehirdeki en çok ilan olan ilçeleri getir
     */
    public function getTopDistrictsByPropertyCount($city_id, $limit = 10) {
        $query = "SELECT 
                    d.id, 
                    d.name, 
                    d.city_id,
                    COUNT(p.id) as property_count
                  FROM " . $this->table_name . " d
                  LEFT JOIN properties p ON d.id = p.district_id AND p.is_active = 1
                  WHERE d.city_id = :city_id
                  GROUP BY d.id, d.name, d.city_id
                  HAVING property_count > 0
                  ORDER BY property_count DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':city_id', $city_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Şehrin ilçe sayısını getir
     */
    public function getCityDistrictCount($city_id) {
        $query = "SELECT COUNT(*) as district_count 
                  FROM " . $this->table_name . " 
                  WHERE city_id = :city_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':city_id', $city_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['district_count'];
    }
}
?> 