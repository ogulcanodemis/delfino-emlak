<?php
/**
 * City Model
 * Emlak-Delfino Projesi
 * Şehir verilerini yönetir
 */

class City {
    private $conn;
    private $table_name = "cities";

    // Nesne özellikleri
    public $id;
    public $name;
    public $plate_code;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Tüm şehirleri getir
     */
    public function getAll() {
        $query = "SELECT id, name, plate_code FROM " . $this->table_name . " ORDER BY name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ID'ye göre şehir getir
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * İsme göre şehir getir
     */
    public function getByName($name) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE name = :name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Plaka koduna göre şehir getir
     */
    public function getByPlateCode($plate_code) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE plate_code = :plate_code";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':plate_code', $plate_code);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Şehir arama
     */
    public function search($search_term) {
        $query = "SELECT id, name, plate_code FROM " . $this->table_name . " 
                  WHERE name LIKE :search_term 
                  ORDER BY name";
        
        $stmt = $this->conn->prepare($query);
        $search_term = '%' . $search_term . '%';
        $stmt->bindParam(':search_term', $search_term);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Şehir oluştur (admin için)
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, plate_code, created_at, updated_at) 
                  VALUES (:name, :plate_code, NOW(), NOW())";

        $stmt = $this->conn->prepare($query);
        
        // Verileri temizle
        $this->name = htmlspecialchars(strip_tags($this->name ?? ''));
        $this->plate_code = htmlspecialchars(strip_tags($this->plate_code ?? ''));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':plate_code', $this->plate_code);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Şehir güncelle (admin için)
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, 
                      plate_code = :plate_code, 
                      updated_at = NOW() 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        
        // Verileri temizle
        $this->name = htmlspecialchars(strip_tags($this->name ?? ''));
        $this->plate_code = htmlspecialchars(strip_tags($this->plate_code ?? ''));
        $this->id = htmlspecialchars(strip_tags($this->id ?? ''));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':plate_code', $this->plate_code);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * Şehir sil (admin için)
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id ?? ''));
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * Şehrin var olup olmadığını kontrol et
     */
    public function exists($id) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Şehirdeki toplam ilan sayısını getir
     */
    public function getPropertyCount($city_id) {
        $query = "SELECT COUNT(*) as property_count 
                  FROM properties 
                  WHERE city_id = :city_id AND is_active = 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':city_id', $city_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['property_count'];
    }

    /**
     * En çok ilan olan şehirleri getir
     */
    public function getTopCitiesByPropertyCount($limit = 10) {
        $query = "SELECT 
                    c.id, 
                    c.name, 
                    c.plate_code,
                    COUNT(p.id) as property_count
                  FROM " . $this->table_name . " c
                  LEFT JOIN properties p ON c.id = p.city_id AND p.is_active = 1
                  GROUP BY c.id, c.name, c.plate_code
                  HAVING property_count > 0
                  ORDER BY property_count DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 