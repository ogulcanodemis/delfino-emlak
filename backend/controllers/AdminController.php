<?php
require_once __DIR__ . '/../utils/JWT.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/RoleRequest.php';

class AdminController {
    private $jwt;
    private $db;
    
    public function __construct($db = null) {
        $this->jwt = new JWT();
        if ($db) {
            $this->db = $db;
        } else {
            $database = new Database();
            $this->db = $database->getConnection();
        }
    }
    
    // JWT token ve admin yetkisi kontrolü
    private function checkAdminAuth() {
        $headers = getallheaders();
        $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
        
        if (!$token) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Token gereklidir']);
            return false;
        }
        
        $decoded = $this->jwt->decode($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Geçersiz token']);
            return false;
        }
        
        // Role ID kontrolü: 3=admin, 4=super_admin (roles tablosuna göre)
        if (!in_array($decoded['role_id'], [3, 4])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Admin yetkisi gereklidir']);
            return false;
        }
        
        return (object)$decoded;
    }
    
    // Dashboard - Genel istatistikler
    public function getDashboard() {
        try {
            $admin = $this->checkAdminAuth();
            if (!$admin) return;
            
            // Kullanıcı istatistikleri
            $userStats = $this->getUserStats();
            
            // İlan istatistikleri
            $propertyStats = $this->getPropertyStats();
            
            // Rol talep istatistikleri
            $roleRequestStats = $this->getRoleRequestStats();
            
            // Favori istatistikleri
            $favoriteStats = $this->getFavoriteStats();
            
            // Son aktiviteler
            $recentActivities = $this->getRecentActivities();
            
            // Sistem bilgileri
            $systemInfo = $this->getSystemInfo();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Dashboard verileri başarıyla getirildi',
                'data' => [
                    'user_stats' => $userStats,
                    'property_stats' => $propertyStats,
                    'role_request_stats' => $roleRequestStats,
                    'favorite_stats' => $favoriteStats,
                    'recent_activities' => $recentActivities,
                    'system_info' => $systemInfo
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
    
    // Kullanıcı yönetimi - Tüm kullanıcıları listele
    public function getUsers() {
        try {
            $admin = $this->checkAdminAuth();
            if (!$admin) return;
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $search = $_GET['search'] ?? '';
            $role = $_GET['role'] ?? '';
            $status = $_GET['status'] ?? '';
            
            $offset = ($page - 1) * $limit;
            
            $where_conditions = [];
            $params = [];
            
            if (!empty($search)) {
                $where_conditions[] = "(name LIKE :search OR email LIKE :search OR phone LIKE :search)";
                $params[':search'] = '%' . $search . '%';
            }
            
            if (!empty($role)) {
                $where_conditions[] = "role_id = :role_id";
                $params[':role_id'] = $role;
            }
            
            if (!empty($status)) {
                $where_conditions[] = "status = :status";
                $params[':status'] = $status === 'active' ? 1 : 0;
            }
            
            $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
            
            // Kullanıcıları getir
            $query = "SELECT u.id, u.name, u.email, u.phone, u.role_id, u.status, 
                             u.email_verified_at, u.created_at, u.updated_at,
                             r.name as role_name,
                             (SELECT COUNT(*) FROM properties WHERE user_id = u.id AND status = 1) as property_count,
                             (SELECT COUNT(*) FROM favorites WHERE user_id = u.id) as favorite_count
                      FROM users u
                      LEFT JOIN roles r ON u.role_id = r.id
                      {$where_clause}
                      ORDER BY u.created_at DESC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Toplam sayı
            $countQuery = "SELECT COUNT(*) as total FROM users {$where_clause}";
            $countStmt = $this->db->prepare($countQuery);
            
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            
            $countStmt->execute();
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Kullanıcılar başarıyla getirildi',
                'data' => [
                    'users' => $users,
                    'pagination' => [
                        'total' => (int)$total,
                        'count' => count($users),
                        'per_page' => $limit,
                        'current_page' => $page,
                        'total_pages' => ceil($total / $limit)
                    ]
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
    
    // Kullanıcı durumunu değiştir (aktif/pasif)
    public function toggleUserStatus($user_id) {
        try {
            $admin = $this->checkAdminAuth();
            if (!$admin) return;
            
            // Kullanıcıyı kontrol et
            $query = "SELECT id, status, role_id FROM users WHERE id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Kullanıcı bulunamadı']);
                return;
            }
            
            // Super admin kendini deaktive edemez
            if ($user['role_id'] == 4 && $admin->user_id == $user_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Super admin kendini deaktive edemez']);
                return;
            }
            
            // Durumu değiştir
            $new_status = $user['status'] ? 0 : 1;
            $updateQuery = "UPDATE users SET status = :status, updated_at = NOW() WHERE id = :user_id";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':status', $new_status);
            $updateStmt->bindParam(':user_id', $user_id);
            
            if ($updateStmt->execute()) {
                $message = $new_status ? 'Kullanıcı aktifleştirildi' : 'Kullanıcı deaktifleştirildi';
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => $message,
                    'data' => ['new_status' => $new_status]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Kullanıcı durumu güncellenemedi']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
    
    // Kullanıcı rolünü değiştir
    public function changeUserRole($user_id) {
        try {
            $admin = $this->checkAdminAuth();
            if (!$admin) return;
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['role_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Yeni rol ID bilgisi gereklidir']);
                return;
            }
            
            $new_role_id = $input['role_id'];
            $allowed_role_ids = [1, 2, 3]; // 1=user, 2=realtor, 3=admin
            
            // Super admin sadece super admin değiştirebilir
            if ($admin->role_id != 4 && $new_role_id == 4) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Super admin rolü sadece super admin tarafından verilebilir']);
                return;
            }
            
            if ($admin->role_id == 4) {
                $allowed_role_ids[] = 4; // super_admin
            }
            
            if (!in_array($new_role_id, $allowed_role_ids)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Geçersiz rol ID']);
                return;
            }
            
            // Kullanıcıyı kontrol et
            $query = "SELECT id, role_id FROM users WHERE id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Kullanıcı bulunamadı']);
                return;
            }
            
            // Rolü güncelle
            $updateQuery = "UPDATE users SET role_id = :role_id, updated_at = NOW() WHERE id = :user_id";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':role_id', $new_role_id);
            $updateStmt->bindParam(':user_id', $user_id);
            
            if ($updateStmt->execute()) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Kullanıcı rolü başarıyla güncellendi',
                    'data' => [
                        'old_role_id' => $user['role_id'],
                        'new_role_id' => $new_role_id
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Kullanıcı rolü güncellenemedi']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
    
    // İlan yönetimi - Tüm ilanları listele
    public function getProperties() {
        try {
            $admin = $this->checkAdminAuth();
            if (!$admin) return;
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $city_id = $_GET['city_id'] ?? '';
            $user_id = $_GET['user_id'] ?? '';
            
            $offset = ($page - 1) * $limit;
            
            $where_conditions = ["1=1"]; // Her zaman true olan koşul
            $params = [];
            
            if (!empty($search)) {
                $where_conditions[] = "(p.title LIKE :search OR p.description LIKE :search OR p.address LIKE :search)";
                $params[':search'] = '%' . $search . '%';
            }
            
            if (!empty($status)) {
                if ($status === 'active') {
                    $where_conditions[] = "p.is_active = 1";
                } elseif ($status === 'inactive') {
                    $where_conditions[] = "p.is_active = 0";
                }
            }
            
            if (!empty($city_id)) {
                $where_conditions[] = "p.city_id = :city_id";
                $params[':city_id'] = $city_id;
            }
            
            if (!empty($user_id)) {
                $where_conditions[] = "p.user_id = :user_id";
                $params[':user_id'] = $user_id;
            }
            
            $where_clause = "WHERE " . implode(" AND ", $where_conditions);
            
            // İlanları getir
            $query = "SELECT p.*, 
                             u.name as user_name, u.email,
                             c.name as city_name,
                             d.name as district_name,
                             pt.name as property_type_name,
                             (SELECT image_path FROM property_images WHERE property_id = p.id LIMIT 1) as main_image,
                             (SELECT COUNT(*) FROM property_images WHERE property_id = p.id) as image_count,
                             (SELECT COUNT(*) FROM favorites WHERE property_id = p.id) as favorite_count
                      FROM properties p
                      LEFT JOIN users u ON p.user_id = u.id
                      LEFT JOIN cities c ON p.city_id = c.id
                      LEFT JOIN districts d ON p.district_id = d.id
                      LEFT JOIN property_types pt ON p.property_type_id = pt.id
                      {$where_clause}
                      ORDER BY p.created_at DESC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Toplam sayı
            $countQuery = "SELECT COUNT(*) as total FROM properties p {$where_clause}";
            $countStmt = $this->db->prepare($countQuery);
            
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            
            $countStmt->execute();
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'İlanlar başarıyla getirildi',
                'data' => [
                    'properties' => $properties,
                    'pagination' => [
                        'total' => (int)$total,
                        'count' => count($properties),
                        'per_page' => $limit,
                        'current_page' => $page,
                        'total_pages' => ceil($total / $limit)
                    ]
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
    
    // İlan durumunu değiştir (aktif/pasif)
    public function togglePropertyStatus($property_id) {
        try {
            $admin = $this->checkAdminAuth();
            if (!$admin) return;
            
            // İlanı kontrol et
            $query = "SELECT id, is_active, title FROM properties WHERE id = :property_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':property_id', $property_id);
            $stmt->execute();
            
            $property = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$property) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'İlan bulunamadı']);
                return;
            }
            
            // Durumu değiştir
            $new_status = $property['is_active'] ? 0 : 1;
            $updateQuery = "UPDATE properties SET is_active = :status, updated_at = NOW() WHERE id = :property_id";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':status', $new_status);
            $updateStmt->bindParam(':property_id', $property_id);
            
            if ($updateStmt->execute()) {
                $message = $new_status ? 'İlan aktifleştirildi' : 'İlan deaktifleştirildi';
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => $message,
                    'data' => ['new_status' => $new_status]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'İlan durumu güncellenemedi']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
    
    // İlan sil (kalıcı silme)
    public function deleteProperty($property_id) {
        try {
            $admin = $this->checkAdminAuth();
            if (!$admin) return;
            
            // Sadece super admin kalıcı silme yapabilir
            if ($admin->role !== 'super_admin') {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Kalıcı silme işlemi sadece super admin tarafından yapılabilir']);
                return;
            }
            
            // İlanı kontrol et
            $query = "SELECT id, title FROM properties WHERE id = :property_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':property_id', $property_id);
            $stmt->execute();
            
            $property = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$property) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'İlan bulunamadı']);
                return;
            }
            
            try {
                $this->db->beginTransaction();
                
                // İlişkili verileri sil
                $this->db->prepare("DELETE FROM property_images WHERE property_id = :property_id")->execute([':property_id' => $property_id]);
                $this->db->prepare("DELETE FROM favorites WHERE property_id = :property_id")->execute([':property_id' => $property_id]);
                
                // İlanı sil
                $this->db->prepare("DELETE FROM properties WHERE id = :property_id")->execute([':property_id' => $property_id]);
                
                $this->db->commit();
                
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'İlan kalıcı olarak silindi'
                ]);
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
    
    // Sistem ayarları
    public function getSystemSettings() {
        try {
            $admin = $this->checkAdminAuth();
            if (!$admin) return;
            
            // Sistem ayarları tablosu yoksa basit ayarlar döndür
            $settings = [
                'site_name' => 'Emlak-Delfino',
                'site_description' => 'Türkiye\'nin En Güvenilir Emlak Platformu',
                'contact_email' => 'info@emlak-delfino.com',
                'contact_phone' => '+90 212 555 0000',
                'max_property_images' => 20,
                'max_file_size' => 5, // MB
                'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'property_expiry_days' => 90,
                'featured_property_price' => 100, // TL
                'commission_rate' => 2.5, // %
                'maintenance_mode' => false
            ];
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Sistem ayarları başarıyla getirildi',
                'data' => $settings
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
    
    // Yardımcı fonksiyonlar
    private function getUserStats() {
        $query = "SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN role_id = 1 THEN 1 END) as regular_users,
                    COUNT(CASE WHEN role_id = 2 THEN 1 END) as realtors,
                    COUNT(CASE WHEN role_id = 3 THEN 1 END) as admins,
                    COUNT(CASE WHEN role_id = 4 THEN 1 END) as super_admins,
                    COUNT(CASE WHEN status = 1 THEN 1 END) as active_users,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users_month,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_users_week
                  FROM users";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getPropertyStats() {
        $query = "SELECT 
                    COUNT(*) as total_properties,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_properties,
                    COUNT(CASE WHEN is_featured = 1 THEN 1 END) as featured_properties,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_properties_month,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_properties_week,
                    AVG(price) as average_price,
                    COALESCE(SUM(view_count), 0) as total_views
                  FROM properties";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getRoleRequestStats() {
        $roleRequest = new RoleRequest();
        return $roleRequest->getStats();
    }
    
    private function getFavoriteStats() {
        $query = "SELECT 
                    COUNT(*) as total_favorites,
                    COUNT(DISTINCT user_id) as users_with_favorites,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_favorites_month
                  FROM favorites";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getRecentActivities() {
        // Son aktiviteleri getir (kullanıcı kayıtları, ilanlar, favoriler)
        $activities = [];
        
        // Son kullanıcı kayıtları
        $userQuery = "SELECT 'user_registration' as type, name as description, created_at 
                      FROM users ORDER BY created_at DESC LIMIT 5";
        $userStmt = $this->db->prepare($userQuery);
        $userStmt->execute();
        $activities = array_merge($activities, $userStmt->fetchAll(PDO::FETCH_ASSOC));
        
        // Son ilanlar
        $propertyQuery = "SELECT 'property_created' as type, title as description, created_at 
                          FROM properties ORDER BY created_at DESC LIMIT 5";
        $propertyStmt = $this->db->prepare($propertyQuery);
        $propertyStmt->execute();
        $activities = array_merge($activities, $propertyStmt->fetchAll(PDO::FETCH_ASSOC));
        
        // Tarihe göre sırala
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($activities, 0, 10);
    }
    
    private function getSystemInfo() {
        return [
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_version' => $this->db->getAttribute(PDO::ATTR_SERVER_VERSION),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
            'disk_free_space' => $this->formatBytes(disk_free_space('.')),
            'server_time' => date('Y-m-d H:i:s')
        ];
    }
    
    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    // ========== İLAN ONAY SİSTEMİ FONKSİYONLARI ==========

    /**
     * Bekleyen onay ilanlarını getir
     */
    public function getPendingProperties() {
        try {
            $admin = $this->checkAdminAuth();
            if (!$admin) return;
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            
            require_once __DIR__ . '/../models/Property.php';
            $property = new Property($this->db);
            
            $properties = $property->getPendingApprovalProperties($page, $limit);
            $total = $property->getPendingApprovalCount();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Bekleyen onay ilanları başarıyla getirildi',
                'data' => [
                    'properties' => $properties,
                    'pagination' => [
                        'total' => (int)$total,
                        'count' => count($properties),
                        'per_page' => $limit,
                        'current_page' => $page,
                        'total_pages' => ceil($total / $limit)
                    ]
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }

    /**
     * İlanı onayla
     */
    public function approveProperty($property_id) {
        try {
            $admin = $this->checkAdminAuth();
            if (!$admin) return;
            
            require_once __DIR__ . '/../models/Property.php';
            require_once __DIR__ . '/../models/Notification.php';
            
            $property = new Property($this->db);
            $notification = new Notification($this->db);
            
            // İlanı kontrol et
            $query = "SELECT p.*, u.name as user_name FROM properties p 
                      LEFT JOIN users u ON p.user_id = u.id 
                      WHERE p.id = :property_id AND p.approval_status = 'pending'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':property_id', $property_id);
            $stmt->execute();
            
            $propertyData = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$propertyData) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Bekleyen onay ilanı bulunamadı']);
                return;
            }
            
            // İlanı onayla
            if ($property->approveProperty($property_id, $admin->user_id)) {
                // Kullanıcıya bildirim gönder
                $notification->sendPropertyApprovalNotification(
                    $propertyData['user_id'],
                    $property_id,
                    $propertyData['title'],
                    true // approved = true
                );
                
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'İlan başarıyla onaylandı ve yayınlandı'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'İlan onaylanırken hata oluştu']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }

    /**
     * İlanı reddet
     */
    public function rejectProperty($property_id) {
        try {
            $admin = $this->checkAdminAuth();
            if (!$admin) return;
            
            // JSON verisini al
            $input = json_decode(file_get_contents('php://input'), true);
            $rejection_reason = $input['rejection_reason'] ?? 'Belirtilmemiş';
            
            require_once __DIR__ . '/../models/Property.php';
            require_once __DIR__ . '/../models/Notification.php';
            
            $property = new Property($this->db);
            $notification = new Notification($this->db);
            
            // İlanı kontrol et
            $query = "SELECT p.*, u.name as user_name FROM properties p 
                      LEFT JOIN users u ON p.user_id = u.id 
                      WHERE p.id = :property_id AND p.approval_status = 'pending'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':property_id', $property_id);
            $stmt->execute();
            
            $propertyData = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$propertyData) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Bekleyen onay ilanı bulunamadı']);
                return;
            }
            
            // İlanı reddet
            if ($property->rejectProperty($property_id, $admin->user_id, $rejection_reason)) {
                // Kullanıcıya bildirim gönder
                $notification->sendPropertyApprovalNotification(
                    $propertyData['user_id'],
                    $property_id,
                    $propertyData['title'],
                    false // approved = false
                );
                
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'İlan başarıyla reddedildi'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'İlan reddedilirken hata oluştu']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }

    /**
     * İlan onay istatistiklerini getir
     */
    public function getApprovalStats() {
        try {
            $admin = $this->checkAdminAuth();
            if (!$admin) return;
            
            // Bekleyen onay sayısı
            $pendingQuery = "SELECT COUNT(*) as count FROM properties WHERE approval_status = 'pending'";
            $stmt = $this->db->prepare($pendingQuery);
            $stmt->execute();
            $pending = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Onaylanmış ilan sayısı (bugün)
            $approvedTodayQuery = "SELECT COUNT(*) as count FROM properties 
                                   WHERE approval_status = 'approved' 
                                   AND DATE(approved_at) = CURDATE()";
            $stmt = $this->db->prepare($approvedTodayQuery);
            $stmt->execute();
            $approved_today = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Reddedilen ilan sayısı (bugün)
            $rejectedTodayQuery = "SELECT COUNT(*) as count FROM properties 
                                   WHERE approval_status = 'rejected' 
                                   AND DATE(approved_at) = CURDATE()";
            $stmt = $this->db->prepare($rejectedTodayQuery);
            $stmt->execute();
            $rejected_today = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Toplam onaylanmış ilan sayısı
            $totalApprovedQuery = "SELECT COUNT(*) as count FROM properties WHERE approval_status = 'approved'";
            $stmt = $this->db->prepare($totalApprovedQuery);
            $stmt->execute();
            $total_approved = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'İlan onay istatistikleri başarıyla getirildi',
                'data' => [
                    'pending_count' => (int)$pending,
                    'approved_today' => (int)$approved_today,
                    'rejected_today' => (int)$rejected_today,
                    'total_approved' => (int)$total_approved
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }

    /**
     * İlan onay ayarını değiştir
     */
    public function toggleApprovalSetting() {
        try {
            $admin = $this->checkAdminAuth();
            if (!$admin) return;
            
            // Mevcut ayarı getir
            $query = "SELECT value FROM settings WHERE key_name = 'property_approval_required'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$current) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Ayar bulunamadı']);
                return;
            }
            
            // Ayarı tersine çevir
            $new_value = $current['value'] === 'true' ? 'false' : 'true';
            
            $updateQuery = "UPDATE settings SET value = :value, updated_at = NOW() 
                           WHERE key_name = 'property_approval_required'";
            $stmt = $this->db->prepare($updateQuery);
            $stmt->bindParam(':value', $new_value);
            
            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'İlan onay ayarı başarıyla güncellendi',
                    'data' => [
                        'approval_required' => $new_value === 'true'
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Ayar güncellenirken hata oluştu']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }

    /**
     * Admin için ilan detayını getir (onay durumu fark etmeksizin)
     */
    public function getPropertyForAdmin($property_id) {
        try {
            $admin = $this->checkAdminAuth();
            if (!$admin) return;
            
            require_once __DIR__ . '/../models/Property.php';
            $property = new Property($this->db);
            
            // İlan detayını getir (onay durumu kontrolü yapmadan)
            $query = "SELECT p.*, 
                             pt.name as property_type_name,
                             ps.name as status_name,
                             c.name as city_name,
                             d.name as district_name,
                             n.name as neighborhood_name,
                             u.name as user_name,
                             u.email as user_email,
                             u.phone as user_phone,
                             u.company as user_company
                      FROM properties p
                      LEFT JOIN property_types pt ON p.property_type_id = pt.id
                      LEFT JOIN property_statuses ps ON p.status_id = ps.id
                      LEFT JOIN cities c ON p.city_id = c.id
                      LEFT JOIN districts d ON p.district_id = d.id
                      LEFT JOIN neighborhoods n ON p.neighborhood_id = n.id
                      LEFT JOIN users u ON p.user_id = u.id
                      WHERE p.id = :property_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':property_id', $property_id);
            $stmt->execute();
            
            $propertyData = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$propertyData) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'İlan bulunamadı']);
                return;
            }
            
            // İlan resimlerini getir
            $imagesQuery = "SELECT * FROM property_images WHERE property_id = :property_id ORDER BY display_order ASC, id ASC";
            $stmt = $this->db->prepare($imagesQuery);
            $stmt->bindParam(':property_id', $property_id);
            $stmt->execute();
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Fotoğraf URL'lerini düzenle ve boolean değerleri düzelt
            foreach ($images as &$image) {
                // Boolean değerleri düzelt
                $image['is_primary'] = (bool)$image['is_primary'];
            }
            
            $propertyData['images'] = $images;
            
            // Boolean değerleri düzelt
            $booleanFields = ['balcony', 'elevator', 'parking', 'garden', 'swimming_pool', 
                             'security', 'air_conditioning', 'internet', 'credit_suitable', 
                             'exchange_suitable', 'is_active', 'is_featured'];
            
            foreach ($booleanFields as $field) {
                if (isset($propertyData[$field])) {
                    $propertyData[$field] = (bool)$propertyData[$field];
                }
            }
            
            // Sayısal değerleri düzelt
            $numericFields = ['price', 'area', 'rooms', 'bathrooms', 'floor', 'total_floors', 
                             'building_age', 'view_count'];
            
            foreach ($numericFields as $field) {
                if (isset($propertyData[$field]) && $propertyData[$field] !== null) {
                    $propertyData[$field] = is_numeric($propertyData[$field]) ? 
                        (float)$propertyData[$field] : $propertyData[$field];
                }
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'İlan detayı başarıyla getirildi',
                'data' => [
                    'property' => $propertyData
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
}
