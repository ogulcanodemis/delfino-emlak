<?php
/**
 * Neighborhood Model
 * Emlak-Delfino Projesi
 * Mahalle verilerini yönetir
 */

class Neighborhood {
    private $conn;
    private $table_name = "neighborhoods";

    // Nesne özellikleri
    public $id;
    public $district_id;
    public $name;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * İlçeye göre mahalleleri getir
     */
    public function getByDistrict($district_id) {
        $query = "SELECT n.id, n.name, n.district_id, d.name as district_name, c.name as city_name 
                  FROM " . $this->table_name . " n
                  LEFT JOIN districts d ON n.district_id = d.id
                  LEFT JOIN cities c ON d.city_id = c.id
                  WHERE n.district_id = :district_id 
                  ORDER BY n.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':district_id', $district_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ID'ye göre mahalle getir
     */
    public function getById($id) {
        $query = "SELECT n.*, d.name as district_name, c.name as city_name, d.city_id 
                  FROM " . $this->table_name . " n
                  LEFT JOIN districts d ON n.district_id = d.id
                  LEFT JOIN cities c ON d.city_id = c.id
                  WHERE n.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Şehre göre mahalleleri getir
     */
    public function getByCity($city_id) {
        $query = "SELECT n.id, n.name, n.district_id, d.name as district_name, c.name as city_name 
                  FROM " . $this->table_name . " n
                  LEFT JOIN districts d ON n.district_id = d.id
                  LEFT JOIN cities c ON d.city_id = c.id
                  WHERE c.id = :city_id 
                  ORDER BY d.name, n.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':city_id', $city_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tüm mahalleleri getir
     */
    public function getAll() {
        $query = "SELECT n.id, n.name, n.district_id, d.name as district_name, c.name as city_name 
                  FROM " . $this->table_name . " n
                  LEFT JOIN districts d ON n.district_id = d.id
                  LEFT JOIN cities c ON d.city_id = c.id
                  ORDER BY c.name, d.name, n.name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mahalle arama
     */
    public function search($search_term, $district_id = null, $city_id = null) {
        $query = "SELECT n.id, n.name, n.district_id, d.name as district_name, c.name as city_name 
                  FROM " . $this->table_name . " n
                  LEFT JOIN districts d ON n.district_id = d.id
                  LEFT JOIN cities c ON d.city_id = c.id
                  WHERE n.name LIKE :search_term";
        
        if ($district_id) {
            $query .= " AND n.district_id = :district_id";
        }
        
        if ($city_id) {
            $query .= " AND c.id = :city_id";
        }
        
        $query .= " ORDER BY n.name";
        
        $stmt = $this->conn->prepare($query);
        $search_term = '%' . $search_term . '%';
        $stmt->bindParam(':search_term', $search_term);
        
        if ($district_id) {
            $stmt->bindParam(':district_id', $district_id, PDO::PARAM_INT);
        }
        
        if ($city_id) {
            $stmt->bindParam(':city_id', $city_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mahalle oluştur (admin için)
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (district_id, name, created_at, updated_at) 
                  VALUES (:district_id, :name, NOW(), NOW())";

        $stmt = $this->conn->prepare($query);
        
        // Verileri temizle
        $this->district_id = htmlspecialchars(strip_tags($this->district_id ?? ''));
        $this->name = htmlspecialchars(strip_tags($this->name ?? ''));

        $stmt->bindParam(':district_id', $this->district_id);
        $stmt->bindParam(':name', $this->name);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Mahalle güncelle (admin için)
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET district_id = :district_id, 
                      name = :name, 
                      updated_at = NOW() 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        
        // Verileri temizle
        $this->district_id = htmlspecialchars(strip_tags($this->district_id ?? ''));
        $this->name = htmlspecialchars(strip_tags($this->name ?? ''));
        $this->id = htmlspecialchars(strip_tags($this->id ?? ''));

        $stmt->bindParam(':district_id', $this->district_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * Mahalle sil (admin için)
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id ?? ''));
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * Mahallenin var olup olmadığını kontrol et
     */
    public function exists($id) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Mahalledeki toplam ilan sayısını getir
     */
    public function getPropertyCount($neighborhood_id) {
        $query = "SELECT COUNT(*) as property_count 
                  FROM properties 
                  WHERE neighborhood_id = :neighborhood_id AND is_active = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':neighborhood_id', $neighborhood_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['property_count'];
    }

    /**
     * İlçedeki en çok ilan olan mahalleleri getir
     */
    public function getTopNeighborhoodsByPropertyCount($district_id, $limit = 10) {
        $query = "SELECT 
                    n.id, 
                    n.name, 
                    n.district_id,
                    COUNT(p.id) as property_count
                  FROM " . $this->table_name . " n
                  LEFT JOIN properties p ON n.id = p.neighborhood_id AND p.is_active = 1
                  WHERE n.district_id = :district_id
                  GROUP BY n.id, n.name, n.district_id
                  HAVING property_count > 0
                  ORDER BY property_count DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':district_id', $district_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * İlçenin mahalle sayısını getir
     */
    public function getDistrictNeighborhoodCount($district_id) {
        $query = "SELECT COUNT(*) as neighborhood_count 
                  FROM " . $this->table_name . " 
                  WHERE district_id = :district_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':district_id', $district_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['neighborhood_count'];
    }
}
?> 