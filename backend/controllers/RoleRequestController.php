<?php
require_once __DIR__ . '/../models/RoleRequest.php';
require_once __DIR__ . '/../utils/JWT.php';

class RoleRequestController {
    private $roleRequest;
    private $jwt;
    
    public function __construct() {
        $this->roleRequest = new RoleRequest();
        $this->jwt = new JWT();
    }
    
    // Tüm rol taleplerini getir (admin için)
    public function getAll() {
        try {
            // JWT token kontrolü
            $headers = getallheaders();
            $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
            
            if (!$token) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Token gereklidir']);
                return;
            }
            
            $decoded = $this->jwt->decode($token);
            if (!$decoded) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Geçersiz token']);
                return;
            }
            
            // Admin kontrolü
            if (!in_array($decoded->role, ['admin', 'super_admin'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Bu işlem için yetkiniz bulunmamaktadır']);
                return;
            }
            
            $status = $_GET['status'] ?? null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $requests = $this->roleRequest->getAll($status, $limit, $offset);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Rol talepleri başarıyla getirildi',
                'data' => $requests,
                'count' => count($requests)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
    
    // Kullanıcının kendi rol taleplerini getir
    public function getMyRequests() {
        try {
            // JWT token kontrolü
            $headers = getallheaders();
            $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
            
            if (!$token) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Token gereklidir']);
                return;
            }
            
            $decoded = $this->jwt->decode($token);
            if (!$decoded) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Geçersiz token']);
                return;
            }
            
            $requests = $this->roleRequest->getByUserId($decoded->user_id);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Rol talepleriniz başarıyla getirildi',
                'data' => $requests,
                'count' => count($requests)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
    
    // ID ile rol talebi getir
    public function getById($id) {
        try {
            // JWT token kontrolü
            $headers = getallheaders();
            $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
            
            if (!$token) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Token gereklidir']);
                return;
            }
            
            $decoded = $this->jwt->decode($token);
            if (!$decoded) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Geçersiz token']);
                return;
            }
            
            $request = $this->roleRequest->getById($id);
            
            if (!$request) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Rol talebi bulunamadı']);
                return;
            }
            
            // Sadece admin veya talep sahibi görebilir
            if (!in_array($decoded->role, ['admin', 'super_admin']) && $request['user_id'] != $decoded->user_id) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Bu talebi görme yetkiniz bulunmamaktadır']);
                return;
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Rol talebi başarıyla getirildi',
                'data' => $request
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
    
    // Yeni rol talebi oluştur
    public function create() {
        try {
            // JWT token kontrolü
            $headers = getallheaders();
            $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
            
            if (!$token) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Token gereklidir']);
                return;
            }
            
            $decoded = $this->jwt->decode($token);
            if (!$decoded) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Geçersiz token']);
                return;
            }
            
            // POST verilerini al
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Geçersiz JSON verisi']);
                return;
            }
            
            // RoleRequest nesnesini doldur
            $this->roleRequest->user_id = $decoded->user_id;
            $this->roleRequest->requested_role = $input['requested_role'] ?? '';
            $this->roleRequest->user_current_role = $decoded->role;
            $this->roleRequest->reason = $input['reason'] ?? '';
            $this->roleRequest->company_name = $input['company_name'] ?? '';
            $this->roleRequest->company_address = $input['company_address'] ?? '';
            $this->roleRequest->tax_number = $input['tax_number'] ?? '';
            $this->roleRequest->phone = $input['phone'] ?? '';
            $this->roleRequest->experience_years = $input['experience_years'] ?? 0;
            $this->roleRequest->license_number = $input['license_number'] ?? '';
            $this->roleRequest->documents = isset($input['documents']) ? json_encode($input['documents']) : null;
            
            // Validation
            $validation_errors = $this->roleRequest->validate();
            if (!empty($validation_errors)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Validation hatası',
                    'errors' => $validation_errors
                ]);
                return;
            }
            
            $result = $this->roleRequest->create();
            
            if ($result['success']) {
                http_response_code(201);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
    
    // Rol talebini güncelle
    public function update($id) {
        try {
            // JWT token kontrolü
            $headers = getallheaders();
            $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
            
            if (!$token) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Token gereklidir']);
                return;
            }
            
            $decoded = $this->jwt->decode($token);
            if (!$decoded) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Geçersiz token']);
                return;
            }
            
            // Mevcut talebi kontrol et
            $existing = $this->roleRequest->getById($id);
            if (!$existing) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Rol talebi bulunamadı']);
                return;
            }
            
            // Sadece talep sahibi güncelleyebilir
            if ($existing['user_id'] != $decoded->user_id) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Bu talebi güncelleme yetkiniz bulunmamaktadır']);
                return;
            }
            
            // POST verilerini al
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Geçersiz JSON verisi']);
                return;
            }
            
            // RoleRequest nesnesini doldur
            $this->roleRequest->id = $id;
            $this->roleRequest->user_id = $decoded->user_id;
            $this->roleRequest->reason = $input['reason'] ?? $existing['reason'];
            $this->roleRequest->company_name = $input['company_name'] ?? $existing['company_name'];
            $this->roleRequest->company_address = $input['company_address'] ?? $existing['company_address'];
            $this->roleRequest->tax_number = $input['tax_number'] ?? $existing['tax_number'];
            $this->roleRequest->phone = $input['phone'] ?? $existing['phone'];
            $this->roleRequest->experience_years = $input['experience_years'] ?? $existing['experience_years'];
            $this->roleRequest->license_number = $input['license_number'] ?? $existing['license_number'];
            $this->roleRequest->documents = isset($input['documents']) ? json_encode($input['documents']) : $existing['documents'];
            
            $result = $this->roleRequest->update();
            
            if ($result['success']) {
                http_response_code(200);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
    
    // Rol talebini onayla/reddet (admin)
    public function review($id) {
        try {
            // JWT token kontrolü
            $headers = getallheaders();
            $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
            
            if (!$token) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Token gereklidir']);
                return;
            }
            
            $decoded = $this->jwt->decode($token);
            if (!$decoded) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Geçersiz token']);
                return;
            }
            
            // Admin kontrolü
            if (!in_array($decoded->role, ['admin', 'super_admin'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Bu işlem için yetkiniz bulunmamaktadır']);
                return;
            }
            
            // POST verilerini al
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['status'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Durum bilgisi gereklidir']);
                return;
            }
            
            $this->roleRequest->id = $id;
            $result = $this->roleRequest->review(
                $input['status'],
                $decoded->user_id,
                $input['admin_notes'] ?? ''
            );
            
            if ($result['success']) {
                http_response_code(200);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
    
    // Rol talebini sil
    public function delete($id) {
        try {
            // JWT token kontrolü
            $headers = getallheaders();
            $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
            
            if (!$token) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Token gereklidir']);
                return;
            }
            
            $decoded = $this->jwt->decode($token);
            if (!$decoded) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Geçersiz token']);
                return;
            }
            
            // Mevcut talebi kontrol et
            $existing = $this->roleRequest->getById($id);
            if (!$existing) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Rol talebi bulunamadı']);
                return;
            }
            
            // Sadece talep sahibi silebilir
            if ($existing['user_id'] != $decoded->user_id) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Bu talebi silme yetkiniz bulunmamaktadır']);
                return;
            }
            
            $this->roleRequest->id = $id;
            $result = $this->roleRequest->delete();
            
            if ($result['success']) {
                http_response_code(200);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
    
    // Arama
    public function search() {
        try {
            // JWT token kontrolü
            $headers = getallheaders();
            $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
            
            if (!$token) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Token gereklidir']);
                return;
            }
            
            $decoded = $this->jwt->decode($token);
            if (!$decoded) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Geçersiz token']);
                return;
            }
            
            // Admin kontrolü
            if (!in_array($decoded->role, ['admin', 'super_admin'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Bu işlem için yetkiniz bulunmamaktadır']);
                return;
            }
            
            $search_term = $_GET['q'] ?? '';
            $status = $_GET['status'] ?? null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            $requests = $this->roleRequest->search($search_term, $status, $limit, $offset);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Arama sonuçları başarıyla getirildi',
                'data' => $requests,
                'count' => count($requests)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
    
    // İstatistikler (admin için)
    public function getStats() {
        try {
            // JWT token kontrolü
            $headers = getallheaders();
            $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
            
            if (!$token) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Token gereklidir']);
                return;
            }
            
            $decoded = $this->jwt->decode($token);
            if (!$decoded) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Geçersiz token']);
                return;
            }
            
            // Admin kontrolü
            if (!in_array($decoded->role, ['admin', 'super_admin'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Bu işlem için yetkiniz bulunmamaktadır']);
                return;
            }
            
            $stats = $this->roleRequest->getStats();
            $recent_requests = $this->roleRequest->getRecentRequests();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'İstatistikler başarıyla getirildi',
                'data' => [
                    'stats' => $stats,
                    'recent_requests' => $recent_requests
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Sunucu hatası: ' . $e->getMessage()]);
        }
    }
}
