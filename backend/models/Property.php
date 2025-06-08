<?php
/**
 * Property Model
 * Emlak-Delfino Projesi
 */

class Property {
    private $conn;
    private $table_name = "properties";

    public $id;
    public $user_id;
    public $title;
    public $description;
    public $price;
    public $property_type_id;
    public $status_id;
    public $address;
    public $city_id;
    public $district_id;
    public $neighborhood_id;
    public $latitude;
    public $longitude;
    public $area;
    public $rooms;
    public $bathrooms;
    public $floor;
    public $total_floors;
    public $building_age;
    public $heating_type;
    public $furnishing;
    public $balcony;
    public $elevator;
    public $parking;
    public $garden;
    public $swimming_pool;
    public $security;
    public $air_conditioning;
    public $internet;
    public $credit_suitable;
    public $exchange_suitable;
    public $is_active;
    public $is_featured;
    public $view_count;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * İlan oluştur
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id=:user_id, title=:title, description=:description, 
                      price=:price, property_type_id=:property_type_id, status_id=:status_id,
                      address=:address, city_id=:city_id, district_id=:district_id, 
                      neighborhood_id=:neighborhood_id, latitude=:latitude, longitude=:longitude,
                      area=:area, rooms=:rooms, bathrooms=:bathrooms, floor=:floor,
                      total_floors=:total_floors, building_age=:building_age, 
                      heating_type=:heating_type, furnishing=:furnishing,
                      balcony=:balcony, elevator=:elevator, parking=:parking,
                      garden=:garden, swimming_pool=:swimming_pool, security=:security,
                      air_conditioning=:air_conditioning, internet=:internet,
                      credit_suitable=:credit_suitable, exchange_suitable=:exchange_suitable,
                      is_active=:is_active, is_featured=:is_featured";

        $stmt = $this->conn->prepare($query);

        // Verileri temizle
        $this->title = htmlspecialchars(strip_tags($this->title ?? ''));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));
        $this->address = htmlspecialchars(strip_tags($this->address ?? ''));

        // Parametreleri bağla
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":property_type_id", $this->property_type_id);
        $stmt->bindParam(":status_id", $this->status_id);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":city_id", $this->city_id);
        $stmt->bindParam(":district_id", $this->district_id);
        $stmt->bindParam(":neighborhood_id", $this->neighborhood_id);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);
        $stmt->bindParam(":area", $this->area);
        $stmt->bindParam(":rooms", $this->rooms);
        $stmt->bindParam(":bathrooms", $this->bathrooms);
        $stmt->bindParam(":floor", $this->floor);
        $stmt->bindParam(":total_floors", $this->total_floors);
        $stmt->bindParam(":building_age", $this->building_age);
        $stmt->bindParam(":heating_type", $this->heating_type);
        $stmt->bindParam(":furnishing", $this->furnishing);
        $stmt->bindParam(":balcony", $this->balcony);
        $stmt->bindParam(":elevator", $this->elevator);
        $stmt->bindParam(":parking", $this->parking);
        $stmt->bindParam(":garden", $this->garden);
        $stmt->bindParam(":swimming_pool", $this->swimming_pool);
        $stmt->bindParam(":security", $this->security);
        $stmt->bindParam(":air_conditioning", $this->air_conditioning);
        $stmt->bindParam(":internet", $this->internet);
        $stmt->bindParam(":credit_suitable", $this->credit_suitable);
        $stmt->bindParam(":exchange_suitable", $this->exchange_suitable);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":is_featured", $this->is_featured);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * İlanları listele (filtreleme ile)
     */
    public function getAll($page = 1, $limit = 12, $filters = []) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT p.*, 
                         pt.name as property_type_name,
                         ps.name as status_name,
                         c.name as city_name,
                         d.name as district_name,
                         n.name as neighborhood_name,
                         u.name as user_name,
                         u.phone as user_phone,
                         (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as main_image
                  FROM " . $this->table_name . " p
                  LEFT JOIN property_types pt ON p.property_type_id = pt.id
                  LEFT JOIN property_status ps ON p.status_id = ps.id
                  LEFT JOIN cities c ON p.city_id = c.id
                  LEFT JOIN districts d ON p.district_id = d.id
                  LEFT JOIN neighborhoods n ON p.neighborhood_id = n.id
                  LEFT JOIN users u ON p.user_id = u.id
                  WHERE p.is_active = 1";

        // Filtreleri uygula
        $params = [];
        
        if (!empty($filters['property_type_id'])) {
            $query .= " AND p.property_type_id = :property_type_id";
            $params[':property_type_id'] = $filters['property_type_id'];
        }
        
        if (!empty($filters['status_id'])) {
            $query .= " AND p.status_id = :status_id";
            $params[':status_id'] = $filters['status_id'];
        }
        
        if (!empty($filters['city_id'])) {
            $query .= " AND p.city_id = :city_id";
            $params[':city_id'] = $filters['city_id'];
        }
        
        if (!empty($filters['district_id'])) {
            $query .= " AND p.district_id = :district_id";
            $params[':district_id'] = $filters['district_id'];
        }
        
        if (!empty($filters['min_price'])) {
            $query .= " AND p.price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $query .= " AND p.price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }
        
        if (!empty($filters['min_area'])) {
            $query .= " AND p.area >= :min_area";
            $params[':min_area'] = $filters['min_area'];
        }
        
        if (!empty($filters['max_area'])) {
            $query .= " AND p.area <= :max_area";
            $params[':max_area'] = $filters['max_area'];
        }
        
        if (!empty($filters['rooms'])) {
            $query .= " AND p.rooms = :rooms";
            $params[':rooms'] = $filters['rooms'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (p.title LIKE :search OR p.description LIKE :search OR p.address LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        // Sıralama
        $sort = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'DESC';
        $query .= " ORDER BY p.{$sort} {$order}";
        
        // Sayfalama
        $query .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        
        // Parametreleri bağla
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * İlan detayını getir
     */
    public function getById($id, $user_role = null) {
        $query = "SELECT p.*, 
                         pt.name as property_type_name,
                         ps.name as status_name,
                         c.name as city_name,
                         d.name as district_name,
                         n.name as neighborhood_name,
                         u.name as user_name,
                         u.email as user_email,
                         u.phone as user_phone
                  FROM " . $this->table_name . " p
                  LEFT JOIN property_types pt ON p.property_type_id = pt.id
                  LEFT JOIN property_status ps ON p.status_id = ps.id
                  LEFT JOIN cities c ON p.city_id = c.id
                  LEFT JOIN districts d ON p.district_id = d.id
                  LEFT JOIN neighborhoods n ON p.neighborhood_id = n.id
                  LEFT JOIN users u ON p.user_id = u.id
                  WHERE p.id = :id AND p.is_active = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $property = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Ziyaretçiler için fiyat ve iletişim bilgilerini gizle
            if ($user_role === null) {
                $property['price'] = null;
                $property['user_email'] = null;
                $property['user_phone'] = null;
            }
            
            // İlan görsellerini getir
            $property['images'] = $this->getPropertyImages($id);
            
            return $property;
        }

        return false;
    }

    /**
     * İlan görsellerini getir
     */
    public function getPropertyImages($property_id) {
        $query = "SELECT id, image_path, image_name, alt_text, is_primary, display_order
                  FROM property_images 
                  WHERE property_id = :property_id 
                  ORDER BY is_primary DESC, display_order ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":property_id", $property_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Kullanıcının ilanlarını getir
     */
    public function getByUserId($user_id, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT p.*, 
                         pt.name as property_type_name,
                         ps.name as status_name,
                         c.name as city_name,
                         d.name as district_name,
                         (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as main_image
                  FROM " . $this->table_name . " p
                  LEFT JOIN property_types pt ON p.property_type_id = pt.id
                  LEFT JOIN property_status ps ON p.status_id = ps.id
                  LEFT JOIN cities c ON p.city_id = c.id
                  LEFT JOIN districts d ON p.district_id = d.id
                  WHERE p.user_id = :user_id
                  ORDER BY p.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * İlan güncelle
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=:title, description=:description, price=:price,
                      property_type_id=:property_type_id, status_id=:status_id,
                      address=:address, city_id=:city_id, district_id=:district_id,
                      neighborhood_id=:neighborhood_id, latitude=:latitude, longitude=:longitude,
                      area=:area, rooms=:rooms, bathrooms=:bathrooms, floor=:floor,
                      total_floors=:total_floors, building_age=:building_age,
                      heating_type=:heating_type, furnishing=:furnishing,
                      balcony=:balcony, elevator=:elevator, parking=:parking,
                      garden=:garden, swimming_pool=:swimming_pool, security=:security,
                      air_conditioning=:air_conditioning, internet=:internet,
                      credit_suitable=:credit_suitable, exchange_suitable=:exchange_suitable,
                      is_active=:is_active, updated_at=NOW()
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Verileri temizle
        $this->title = htmlspecialchars(strip_tags($this->title ?? ''));
        $this->description = htmlspecialchars(strip_tags($this->description ?? ''));
        $this->address = htmlspecialchars(strip_tags($this->address ?? ''));

        // Parametreleri bağla
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":property_type_id", $this->property_type_id);
        $stmt->bindParam(":status_id", $this->status_id);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":city_id", $this->city_id);
        $stmt->bindParam(":district_id", $this->district_id);
        $stmt->bindParam(":neighborhood_id", $this->neighborhood_id);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);
        $stmt->bindParam(":area", $this->area);
        $stmt->bindParam(":rooms", $this->rooms);
        $stmt->bindParam(":bathrooms", $this->bathrooms);
        $stmt->bindParam(":floor", $this->floor);
        $stmt->bindParam(":total_floors", $this->total_floors);
        $stmt->bindParam(":building_age", $this->building_age);
        $stmt->bindParam(":heating_type", $this->heating_type);
        $stmt->bindParam(":furnishing", $this->furnishing);
        $stmt->bindParam(":balcony", $this->balcony);
        $stmt->bindParam(":elevator", $this->elevator);
        $stmt->bindParam(":parking", $this->parking);
        $stmt->bindParam(":garden", $this->garden);
        $stmt->bindParam(":swimming_pool", $this->swimming_pool);
        $stmt->bindParam(":security", $this->security);
        $stmt->bindParam(":air_conditioning", $this->air_conditioning);
        $stmt->bindParam(":internet", $this->internet);
        $stmt->bindParam(":credit_suitable", $this->credit_suitable);
        $stmt->bindParam(":exchange_suitable", $this->exchange_suitable);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * İlan sil (soft delete)
     */
    public function delete($id = null) {
        $property_id = $id ?? $this->id;
        $query = "UPDATE " . $this->table_name . " SET is_active = 0, updated_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $property_id);
        return $stmt->execute();
    }

    /**
     * Görüntülenme sayısını artır
     */
    public function incrementViewCount($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET view_count = view_count + 1 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    /**
     * Toplam ilan sayısı
     */
    public function getTotalCount($filters = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE is_active = 1";

        $params = [];
        
        if (!empty($filters['property_type_id'])) {
            $query .= " AND property_type_id = :property_type_id";
            $params[':property_type_id'] = $filters['property_type_id'];
        }
        
        if (!empty($filters['status_id'])) {
            $query .= " AND status_id = :status_id";
            $params[':status_id'] = $filters['status_id'];
        }
        
        if (!empty($filters['city_id'])) {
            $query .= " AND city_id = :city_id";
            $params[':city_id'] = $filters['city_id'];
        }
        
        if (!empty($filters['district_id'])) {
            $query .= " AND district_id = :district_id";
            $params[':district_id'] = $filters['district_id'];
        }
        
        if (!empty($filters['min_price'])) {
            $query .= " AND price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $query .= " AND price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }
        
        if (!empty($filters['min_area'])) {
            $query .= " AND area >= :min_area";
            $params[':min_area'] = $filters['min_area'];
        }
        
        if (!empty($filters['max_area'])) {
            $query .= " AND area <= :max_area";
            $params[':max_area'] = $filters['max_area'];
        }
        
        if (!empty($filters['rooms'])) {
            $query .= " AND rooms = :rooms";
            $params[':rooms'] = $filters['rooms'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (title LIKE :search OR description LIKE :search OR address LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }

    /**
     * Benzer ilanları getir
     */
    public function getSimilarProperties($property_id, $city_id, $property_type_id, $limit = 4) {
        $query = "SELECT p.*, 
                         pt.name as property_type_name,
                         ps.name as status_name,
                         c.name as city_name,
                         d.name as district_name,
                         (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as main_image
                  FROM " . $this->table_name . " p
                  LEFT JOIN property_types pt ON p.property_type_id = pt.id
                  LEFT JOIN property_status ps ON p.status_id = ps.id
                  LEFT JOIN cities c ON p.city_id = c.id
                  LEFT JOIN districts d ON p.district_id = d.id
                  WHERE p.is_active = 1 
                    AND p.id != :property_id
                    AND (p.city_id = :city_id OR p.property_type_id = :property_type_id)
                  ORDER BY 
                    CASE WHEN p.city_id = :city_id AND p.property_type_id = :property_type_id THEN 1
                         WHEN p.city_id = :city_id THEN 2
                         WHEN p.property_type_id = :property_type_id THEN 3
                         ELSE 4 END,
                    p.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":property_id", $property_id);
        $stmt->bindParam(":city_id", $city_id);
        $stmt->bindParam(":property_type_id", $property_type_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 