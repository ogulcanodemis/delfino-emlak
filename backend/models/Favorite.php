<?php
/**
 * Favorite Model
 * Emlak-Delfino Projesi
 * Kullanıcıların favori ilanlarını yönetir
 */

class Favorite {
    private $conn;
    private $table_name = "favorites";

    // Nesne özellikleri
    public $id;
    public $user_id;
    public $property_id;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Kullanıcının favori ilanlarını getir
     */
    public function getUserFavorites($user_id, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT 
                    f.id,
                    f.property_id,
                    f.created_at,
                    p.title,
                    p.price,
                    p.address,
                    p.city,
                    p.district,
                    p.area,
                    p.rooms,
                    p.bathrooms,
                    pt.name as property_type,
                    ps.name as status,
                    (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as thumbnail
                  FROM " . $this->table_name . " f
                  LEFT JOIN properties p ON f.property_id = p.id
                  LEFT JOIN property_types pt ON p.property_type_id = pt.id
                  LEFT JOIN property_status ps ON p.status_id = ps.id
                  WHERE f.user_id = :user_id 
                  AND p.is_active = 1
                  ORDER BY f.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Kullanıcının toplam favori sayısını getir
     */
    public function getUserFavoritesCount($user_id) {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . " f
                  LEFT JOIN properties p ON f.property_id = p.id
                  WHERE f.user_id = :user_id 
                  AND p.is_active = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    /**
     * Favoriye ilan ekle
     */
    public function addToFavorites($user_id, $property_id) {
        // Önce zaten favori olup olmadığını kontrol et
        if ($this->isFavorite($user_id, $property_id)) {
            return false; // Zaten favori
        }

        // İlan var mı kontrol et
        if (!$this->propertyExists($property_id)) {
            return false; // İlan bulunamadı
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, property_id, created_at, updated_at) 
                  VALUES (:user_id, :property_id, NOW(), NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':property_id', $property_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Favorilerden ilan çıkar
     */
    public function removeFromFavorites($user_id, $property_id) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND property_id = :property_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':property_id', $property_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * İlanın favori olup olmadığını kontrol et
     */
    public function isFavorite($user_id, $property_id) {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND property_id = :property_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':property_id', $property_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * İlanın var olup olmadığını kontrol et
     */
    private function propertyExists($property_id) {
        $query = "SELECT id FROM properties WHERE id = :property_id AND is_active = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $property_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Favori detaylarını getir
     */
    public function getFavoriteById($id, $user_id) {
        $query = "SELECT 
                    f.*,
                    p.title,
                    p.price,
                    p.address,
                    p.city,
                    p.district
                  FROM " . $this->table_name . " f
                  LEFT JOIN properties p ON f.property_id = p.id
                  WHERE f.id = :id AND f.user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * İlanın kaç kez favorilere eklendiğini getir
     */
    public function getPropertyFavoriteCount($property_id) {
        $query = "SELECT COUNT(*) as favorite_count 
                  FROM " . $this->table_name . " 
                  WHERE property_id = :property_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $property_id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['favorite_count'];
    }

    /**
     * Kullanıcının favori ilanlarının ID'lerini getir
     */
    public function getUserFavoritePropertyIds($user_id) {
        $query = "SELECT property_id FROM " . $this->table_name . " WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_column($favorites, 'property_id');
    }

    /**
     * Kullanıcının tüm favorilerini sil (hesap silme durumunda)
     */
    public function deleteUserFavorites($user_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * İlanla ilgili tüm favorileri sil (ilan silme durumunda)
     */
    public function deletePropertyFavorites($property_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE property_id = :property_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $property_id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
?> 