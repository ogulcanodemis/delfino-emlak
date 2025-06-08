<?php
require_once __DIR__ . '/../models/PropertyStatus.php';
require_once __DIR__ . '/../utils/JWT.php';

class PropertyStatusController {
    private $propertyStatus;
    
    public function __construct() {
        $this->propertyStatus = new PropertyStatus();
    }
    
    // Tüm durumları getir
    public function index() {
        try {
            $active_only = isset($_GET['active_only']) ? filter_var($_GET['active_only'], FILTER_VALIDATE_BOOLEAN) : true;
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            
            if (!empty($search)) {
                $statuses = $this->propertyStatus->search($search, $active_only);
                $message = "Arama sonuçları getirildi";
            } else {
                $statuses = $this->propertyStatus->getAll($active_only);
                $message = "Emlak durumları başarıyla getirildi";
            }
            
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => $message,
                'timestamp' => date('Y-m-d H:i:s'),
                'data' => [
                    'statuses' => $statuses,
                    'total' => count($statuses),
                    'search' => $search,
                    'active_only' => $active_only
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Emlak durumları getirilirken hata oluştu: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    // Belirli bir durumu getir
    public function show($id) {
        try {
            if (empty($id)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Durum ID gereklidir',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            $status = $this->propertyStatus->getById($id);
            
            if (!$status) {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Emlak durumu bulunamadı',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Emlak durumu başarıyla getirildi',
                'timestamp' => date('Y-m-d H:i:s'),
                'data' => $status
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Emlak durumu getirilirken hata oluştu: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    // Slug ile durum getir
    public function getBySlug($slug) {
        try {
            if (empty($slug)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Durum slug gereklidir',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            $status = $this->propertyStatus->getBySlug($slug);
            
            if (!$status) {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Emlak durumu bulunamadı',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'Emlak durumu başarıyla getirildi',
                'timestamp' => date('Y-m-d H:i:s'),
                'data' => $status
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Emlak durumu getirilirken hata oluştu: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    // Yeni durum oluştur (Admin yetkisi gerekli)
    public function create() {
        try {
            // Authentication kontrolü
            $headers = getallheaders();
            $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
            
            if (!$token) {
                http_response_code(401);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Oturum açmanız gerekiyor',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            $decoded = JWT::decode($token);
            if (!$decoded) {
                http_response_code(401);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Geçersiz token',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            // Admin yetkisi kontrolü
            if ($decoded['role'] !== 'super_admin') {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Bu işlem için yetkiniz bulunmuyor',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            // POST verilerini al
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Geçersiz JSON verisi',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            // Verileri ata
            $this->propertyStatus->name = $input['name'] ?? '';
            $this->propertyStatus->slug = $input['slug'] ?? '';
            $this->propertyStatus->description = $input['description'] ?? '';
            $this->propertyStatus->is_active = isset($input['is_active']) ? (bool)$input['is_active'] : true;
            
            // Validation
            $errors = $this->propertyStatus->validate();
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Validation hatası',
                    'errors' => $errors,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            // Oluştur
            if ($this->propertyStatus->create()) {
                $created_status = $this->propertyStatus->getById($this->propertyStatus->id);
                
                http_response_code(201);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Emlak durumu başarıyla oluşturuldu',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'data' => $created_status
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Emlak durumu oluşturulurken hata oluştu',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Emlak durumu oluşturulurken hata oluştu: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    // Durum güncelle (Admin yetkisi gerekli)
    public function update($id) {
        try {
            // Authentication kontrolü
            $headers = getallheaders();
            $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
            
            if (!$token) {
                http_response_code(401);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Oturum açmanız gerekiyor',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            $decoded = JWT::decode($token);
            if (!$decoded) {
                http_response_code(401);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Geçersiz token',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            // Admin yetkisi kontrolü
            if ($decoded['role'] !== 'super_admin') {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Bu işlem için yetkiniz bulunmuyor',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            if (empty($id)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Durum ID gereklidir',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            // Mevcut durumu kontrol et
            $existing_status = $this->propertyStatus->getById($id);
            if (!$existing_status) {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Emlak durumu bulunamadı',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            // PUT verilerini al
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Geçersiz JSON verisi',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            // Verileri ata
            $this->propertyStatus->id = $id;
            $this->propertyStatus->name = $input['name'] ?? $existing_status['name'];
            $this->propertyStatus->slug = $input['slug'] ?? $existing_status['slug'];
            $this->propertyStatus->description = $input['description'] ?? $existing_status['description'];
            $this->propertyStatus->is_active = isset($input['is_active']) ? (bool)$input['is_active'] : (bool)$existing_status['is_active'];
            
            // Validation
            $errors = $this->propertyStatus->validate();
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Validation hatası',
                    'errors' => $errors,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            // Güncelle
            if ($this->propertyStatus->update()) {
                $updated_status = $this->propertyStatus->getById($id);
                
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Emlak durumu başarıyla güncellendi',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'data' => $updated_status
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Emlak durumu güncellenirken hata oluştu',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Emlak durumu güncellenirken hata oluştu: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    // Durum sil (Admin yetkisi gerekli)
    public function delete($id) {
        try {
            // Authentication kontrolü
            $headers = getallheaders();
            $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
            
            if (!$token) {
                http_response_code(401);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Oturum açmanız gerekiyor',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            $decoded = JWT::decode($token);
            if (!$decoded) {
                http_response_code(401);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Geçersiz token',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            // Admin yetkisi kontrolü
            if ($decoded['role'] !== 'super_admin') {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Bu işlem için yetkiniz bulunmuyor',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            if (empty($id)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Durum ID gereklidir',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            // Mevcut durumu kontrol et
            $existing_status = $this->propertyStatus->getById($id);
            if (!$existing_status) {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Emlak durumu bulunamadı',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            $this->propertyStatus->id = $id;
            $result = $this->propertyStatus->delete();
            
            if ($result['success']) {
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'message' => $result['message'],
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => $result['message'],
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Emlak durumu silinirken hata oluştu: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    // Durum aktif/pasif durumunu değiştir (Admin yetkisi gerekli)
    public function toggleStatus($id) {
        try {
            // Authentication kontrolü
            $headers = getallheaders();
            $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
            
            if (!$token) {
                http_response_code(401);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Oturum açmanız gerekiyor',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            $decoded = JWT::decode($token);
            if (!$decoded) {
                http_response_code(401);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Geçersiz token',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            // Admin yetkisi kontrolü
            if ($decoded['role'] !== 'super_admin') {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Bu işlem için yetkiniz bulunmuyor',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            if (empty($id)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Durum ID gereklidir',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            // Mevcut durumu kontrol et
            $existing_status = $this->propertyStatus->getById($id);
            if (!$existing_status) {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Emlak durumu bulunamadı',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                return;
            }
            
            $this->propertyStatus->id = $id;
            
            if ($this->propertyStatus->toggleStatus()) {
                $updated_status = $this->propertyStatus->getById($id);
                
                http_response_code(200);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Durum başarıyla değiştirildi',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'data' => $updated_status
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Durum değiştirilirken hata oluştu',
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Durum değiştirilirken hata oluştu: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    // En çok kullanılan durumları getir
    public function getMostUsed() {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
            $statuses = $this->propertyStatus->getMostUsed($limit);
            
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'En çok kullanılan durumlar getirildi',
                'timestamp' => date('Y-m-d H:i:s'),
                'data' => [
                    'statuses' => $statuses,
                    'total' => count($statuses),
                    'limit' => $limit
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'En çok kullanılan durumlar getirilirken hata oluştu: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    // İstatistikler getir
    public function getStats() {
        try {
            $stats = $this->propertyStatus->getStats();
            
            http_response_code(200);
            echo json_encode([
                'status' => 'success',
                'message' => 'İstatistikler başarıyla getirildi',
                'timestamp' => date('Y-m-d H:i:s'),
                'data' => $stats
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'İstatistikler getirilirken hata oluştu: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }
} 
