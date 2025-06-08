<?php
/**
 * Property Controller
 * Emlak-Delfino Projesi
 */

require_once '../models/Property.php';
require_once '../utils/JWT.php';

class PropertyController {
    private $db;
    private $property;

    public function __construct($database) {
        $this->db = $database;
        $this->property = new Property($this->db);
    }

    /**
     * İlanları listele
     */
    public function getProperties() {
        // GET parametrelerini al
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 12;
        
        // Filtreleri al
        $filters = [
            'property_type_id' => $_GET['property_type_id'] ?? null,
            'status_id' => $_GET['status_id'] ?? null,
            'city_id' => $_GET['city_id'] ?? null,
            'district_id' => $_GET['district_id'] ?? null,
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'min_area' => $_GET['min_area'] ?? null,
            'max_area' => $_GET['max_area'] ?? null,
            'rooms' => $_GET['rooms'] ?? null,
            'search' => $_GET['search'] ?? null,
            'sort' => $_GET['sort'] ?? 'created_at',
            'order' => $_GET['order'] ?? 'DESC'
        ];

        // Boş filtreleri temizle
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });

        try {
            // İlanları getir
            $properties = $this->property->getAll($page, $limit, $filters);
            
            // Toplam sayıyı getir
            $total = $this->property->getTotalCount($filters);
            
            // Sayfalama bilgilerini hesapla
            $total_pages = ceil($total / $limit);
            
            // Ziyaretçiler için fiyat ve iletişim bilgilerini gizle
            $user_role = $this->getUserRole();
            if ($user_role === null) {
                foreach ($properties as &$property) {
                    $property['price'] = null;
                    $property['user_phone'] = null;
                }
            }

            Response::success([
                'properties' => $properties,
                'pagination' => [
                    'total' => (int)$total,
                    'count' => count($properties),
                    'per_page' => (int)$limit,
                    'current_page' => (int)$page,
                    'total_pages' => (int)$total_pages
                ]
            ], 'İlanlar başarıyla getirildi');

        } catch (Exception $e) {
            Response::error('İlanlar getirilemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * İlan detayını getir
     */
    public function getProperty($id) {
        try {
            // Kullanıcı rolünü al
            $user_role = $this->getUserRole();
            
            // İlan detayını getir
            $property = $this->property->getById($id, $user_role);
            
            if (!$property) {
                Response::notFound('İlan bulunamadı');
            }

            // Görüntülenme sayısını artır
            $this->property->incrementViewCount($id);

            Response::success(['property' => $property], 'İlan detayı başarıyla getirildi');

        } catch (Exception $e) {
            Response::error('İlan detayı getirilemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Yeni ilan oluştur
     */
    public function createProperty() {
        try {
            // Sadece emlakçı ve admin ilan oluşturabilir
            $user_role = $this->getUserRole();
            if (!$user_role || !in_array($user_role, [2, 3])) { // 2: Emlakçı, 3: Admin
                Response::error('Bu işlem için yetkiniz bulunmamaktadır', 403);
            }

            // JSON verisini al
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                Response::error('Geçersiz JSON verisi', 400);
            }

            // Gerekli alanları kontrol et
            $required_fields = ['title', 'description', 'price', 'property_type_id', 'status_id', 'city_id', 'area'];
            foreach ($required_fields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    Response::error("$field alanı zorunludur", 400);
                }
            }

            // Property model ile ilan oluştur
            $property = new Property($this->db);
            
            // Kullanıcı ID'sini token'dan al
            $user_data = $this->getUserData();
            $property->user_id = $user_data['user_id'];
            
            // Verileri ata
            $property->title = $input['title'];
            $property->description = $input['description'];
            $property->price = $input['price'];
            $property->property_type_id = $input['property_type_id'];
            $property->status_id = $input['status_id'];
            $property->address = $input['address'] ?? '';
            $property->city_id = $input['city_id'];
            $property->district_id = $input['district_id'] ?? null;
            $property->neighborhood_id = $input['neighborhood_id'] ?? null;
            $property->area = $input['area'];
            $property->rooms = $input['rooms'] ?? null;
            $property->bathrooms = $input['bathrooms'] ?? null;
            $property->floor = $input['floor'] ?? null;
            $property->total_floors = $input['total_floors'] ?? null;
            $property->building_age = $input['building_age'] ?? null;
            $property->heating_type = $input['heating_type'] ?? 'Doğalgaz';
            $property->furnishing = $input['furnishing'] ?? 'Eşyasız';
            $property->balcony = $input['balcony'] ?? 0;
            $property->elevator = $input['elevator'] ?? 0;
            $property->parking = $input['parking'] ?? 0;
            $property->garden = $input['garden'] ?? 0;
            $property->swimming_pool = $input['swimming_pool'] ?? 0;
            $property->security = $input['security'] ?? 0;
            $property->air_conditioning = $input['air_conditioning'] ?? 0;
            $property->internet = $input['internet'] ?? 0;
            $property->credit_suitable = $input['credit_suitable'] ?? 0;
            $property->exchange_suitable = $input['exchange_suitable'] ?? 0;
            $property->is_active = 1;
            $property->is_featured = $input['is_featured'] ?? 0;

            if ($property->create()) {
                Response::success([
                    'property_id' => $property->id,
                    'message' => 'İlan başarıyla oluşturuldu'
                ], 'İlan başarıyla oluşturuldu', 201);
            } else {
                Response::error('İlan oluşturulurken hata oluştu', 500);
            }

        } catch (Exception $e) {
            Response::error('İlan oluşturulurken hata: ' . $e->getMessage(), 500);
        }
    }

    /**
     * İlan güncelle
     */
    public function updateProperty($id) {
        try {
            // Kullanıcı yetkisini kontrol et
            $user_data = $this->getUserData();
            if (!$user_data) {
                Response::error('Oturum açmanız gerekiyor', 401);
            }

            // İlan var mı kontrol et
            $property = new Property($this->db);
            $existing_property = $property->getById($id);
            
            if (!$existing_property) {
                Response::error('İlan bulunamadı', 404);
            }

            // Sadece ilan sahibi veya admin güncelleyebilir
            if ($existing_property['user_id'] != $user_data['user_id'] && $user_data['role_id'] != 3) {
                Response::error('Bu ilanı güncelleme yetkiniz bulunmamaktadır', 403);
            }

            // JSON verisini al
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                Response::error('Geçersiz JSON verisi', 400);
            }

            // Mevcut ilan verilerini al
            $current_property = $property->getById($id);
            
            // Verileri güncelle - sadece gönderilen alanları güncelle
            $property->id = $id;
            $property->title = $input['title'] ?? $current_property['title'];
            $property->description = $input['description'] ?? $current_property['description'];
            $property->price = $input['price'] ?? $current_property['price'];
            $property->property_type_id = $input['property_type_id'] ?? $current_property['property_type_id'];
            $property->status_id = $input['status_id'] ?? $current_property['status_id'];
            $property->address = $input['address'] ?? $current_property['address'];
            $property->city_id = $input['city_id'] ?? $current_property['city_id'];
            $property->district_id = $input['district_id'] ?? $current_property['district_id'];
            $property->neighborhood_id = $input['neighborhood_id'] ?? $current_property['neighborhood_id'];
            $property->latitude = $input['latitude'] ?? $current_property['latitude'];
            $property->longitude = $input['longitude'] ?? $current_property['longitude'];
            $property->area = $input['area'] ?? $current_property['area'];
            $property->rooms = $input['rooms'] ?? $current_property['rooms'];
            $property->bathrooms = $input['bathrooms'] ?? $current_property['bathrooms'];
            $property->floor = $input['floor'] ?? $current_property['floor'];
            $property->total_floors = $input['total_floors'] ?? $current_property['total_floors'];
            $property->building_age = $input['building_age'] ?? $current_property['building_age'];
            $property->heating_type = $input['heating_type'] ?? $current_property['heating_type'];
            $property->furnishing = $input['furnishing'] ?? $current_property['furnishing'];
            $property->balcony = $input['balcony'] ?? $current_property['balcony'];
            $property->elevator = $input['elevator'] ?? $current_property['elevator'];
            $property->parking = $input['parking'] ?? $current_property['parking'];
            $property->garden = $input['garden'] ?? $current_property['garden'];
            $property->swimming_pool = $input['swimming_pool'] ?? $current_property['swimming_pool'];
            $property->security = $input['security'] ?? $current_property['security'];
            $property->air_conditioning = $input['air_conditioning'] ?? $current_property['air_conditioning'];
            $property->internet = $input['internet'] ?? $current_property['internet'];
            $property->credit_suitable = $input['credit_suitable'] ?? $current_property['credit_suitable'];
            $property->exchange_suitable = $input['exchange_suitable'] ?? $current_property['exchange_suitable'];
            $property->is_active = $input['is_active'] ?? $current_property['is_active'];
            $property->is_featured = $input['is_featured'] ?? $current_property['is_featured'];

            if ($property->update()) {
                Response::success([
                    'property_id' => $id,
                    'message' => 'İlan başarıyla güncellendi'
                ], 'İlan başarıyla güncellendi');
            } else {
                Response::error('İlan güncellenirken hata oluştu', 500);
            }

        } catch (Exception $e) {
            Response::error('İlan güncellenirken hata: ' . $e->getMessage(), 500);
        }
    }

    /**
     * İlan sil
     */
    public function deleteProperty($id) {
        try {
            // Kullanıcı yetkisini kontrol et
            $user_data = $this->getUserData();
            if (!$user_data) {
                Response::error('Oturum açmanız gerekiyor', 401);
            }

            // İlan var mı kontrol et
            $property = new Property($this->db);
            $existing_property = $property->getById($id);
            
            if (!$existing_property) {
                Response::error('İlan bulunamadı', 404);
            }

            // Sadece ilan sahibi veya admin silebilir
            if ($existing_property['user_id'] != $user_data['user_id'] && $user_data['role_id'] != 3) {
                Response::error('Bu ilanı silme yetkiniz bulunmamaktadır', 403);
            }

            if ($property->delete($id)) {
                Response::success([
                    'property_id' => $id,
                    'message' => 'İlan başarıyla silindi'
                ], 'İlan başarıyla silindi');
            } else {
                Response::error('İlan silinirken hata oluştu', 500);
            }

        } catch (Exception $e) {
            Response::error('İlan silinirken hata: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Kullanıcı rolünü al
     */
    private function getUserRole() {
        $headers = getallheaders();
        $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$auth_header || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            return null;
        }

        $token = $matches[1];
        $payload = JWT::decode($token);
        
        return $payload ? $payload['role_id'] : null;
    }

    /**
     * Kullanıcı verilerini al
     */
    private function getUserData() {
        $headers = getallheaders();
        $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$auth_header || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            return null;
        }

        $token = $matches[1];
        $payload = JWT::decode($token);
        
        return $payload;
    }
}
?> 