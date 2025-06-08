<?php
require_once __DIR__ . '/../config/database.php';

class RoleRequest {
    private $conn;
    private $table = 'role_requests';
    
    public $id;
    public $user_id;
    public $requested_role;
    public $user_current_role;
    public $reason;
    public $company_name;
    public $company_address;
    public $tax_number;
    public $phone;
    public $experience_years;
    public $license_number;
    public $documents;
    public $status;
    public $admin_notes;
    public $reviewed_by;
    public $reviewed_at;
    public $created_at;
    public $updated_at;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Tüm rol taleplerini getir (admin için)
    public function getAll($status = null, $limit = 20, $offset = 0) {
        $where_clause = "";
        if ($status) {
            $where_clause = "WHERE rr.status = :status";
        }
        
        $query = "SELECT rr.*, 
                         u.first_name, u.last_name, u.email, u.role as current_user_role,
                         admin.first_name as admin_first_name, admin.last_name as admin_last_name
                  FROM " . $this->table . " rr
                  LEFT JOIN users u ON u.id = rr.user_id
                  LEFT JOIN users admin ON admin.id = rr.reviewed_by
                  " . $where_clause . "
                  ORDER BY rr.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        if ($status) {
            $stmt->bindParam(':status', $status);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Kullanıcının rol taleplerini getir
    public function getByUserId($user_id) {
        $query = "SELECT rr.*, 
                         admin.first_name as admin_first_name, admin.last_name as admin_last_name
                  FROM " . $this->table . " rr
                  LEFT JOIN users admin ON admin.id = rr.reviewed_by
                  WHERE rr.user_id = :user_id
                  ORDER BY rr.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // ID ile rol talebi getir
    public function getById($id) {
        $query = "SELECT rr.*, 
                         u.first_name, u.last_name, u.email, u.role as current_user_role,
                         admin.first_name as admin_first_name, admin.last_name as admin_last_name
                  FROM " . $this->table . " rr
                  LEFT JOIN users u ON u.id = rr.user_id
                  LEFT JOIN users admin ON admin.id = rr.reviewed_by
                  WHERE rr.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Yeni rol talebi oluştur
    public function create() {
        // Aynı kullanıcının bekleyen talebi var mı kontrol et
        if ($this->hasPendingRequest($this->user_id)) {
            return ['success' => false, 'message' => 'Zaten bekleyen bir rol talebiniz bulunmaktadır'];
        }
        
        $query = "INSERT INTO " . $this->table . "
                  (user_id, requested_role, user_current_role, reason, company_name, company_address, 
                   tax_number, phone, experience_years, license_number, documents, status)
                  VALUES (:user_id, :requested_role, :user_current_role, :reason, :company_name, 
                          :company_address, :tax_number, :phone, :experience_years, :license_number, 
                          :documents, 'pending')";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':requested_role', $this->requested_role);
        $stmt->bindParam(':user_current_role', $this->user_current_role);
        $stmt->bindParam(':reason', $this->reason);
        $stmt->bindParam(':company_name', $this->company_name);
        $stmt->bindParam(':company_address', $this->company_address);
        $stmt->bindParam(':tax_number', $this->tax_number);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':experience_years', $this->experience_years);
        $stmt->bindParam(':license_number', $this->license_number);
        $stmt->bindParam(':documents', $this->documents);
        
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return ['success' => true, 'message' => 'Rol talebi başarıyla oluşturuldu', 'id' => $this->id];
        }
        
        return ['success' => false, 'message' => 'Rol talebi oluşturulurken hata oluştu'];
    }
    
    // Rol talebini güncelle (kullanıcı tarafından)
    public function update() {
        // Sadece pending durumundaki talepler güncellenebilir
        $current = $this->getById($this->id);
        if (!$current || $current['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Bu talep güncellenemez'];
        }
        
        $query = "UPDATE " . $this->table . "
                  SET reason = :reason,
                      company_name = :company_name,
                      company_address = :company_address,
                      tax_number = :tax_number,
                      phone = :phone,
                      experience_years = :experience_years,
                      license_number = :license_number,
                      documents = :documents,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':reason', $this->reason);
        $stmt->bindParam(':company_name', $this->company_name);
        $stmt->bindParam(':company_address', $this->company_address);
        $stmt->bindParam(':tax_number', $this->tax_number);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':experience_years', $this->experience_years);
        $stmt->bindParam(':license_number', $this->license_number);
        $stmt->bindParam(':documents', $this->documents);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Rol talebi başarıyla güncellendi'];
        }
        
        return ['success' => false, 'message' => 'Rol talebi güncellenirken hata oluştu'];
    }
    
    // Rol talebini onayla/reddet (admin tarafından)
    public function review($status, $admin_id, $admin_notes = '') {
        if (!in_array($status, ['approved', 'rejected'])) {
            return ['success' => false, 'message' => 'Geçersiz durum'];
        }
        
        try {
            $this->conn->beginTransaction();
            
            // Rol talebini güncelle
            $query = "UPDATE " . $this->table . "
                      SET status = :status,
                          admin_notes = :admin_notes,
                          reviewed_by = :reviewed_by,
                          reviewed_at = CURRENT_TIMESTAMP,
                          updated_at = CURRENT_TIMESTAMP
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->id);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':admin_notes', $admin_notes);
            $stmt->bindParam(':reviewed_by', $admin_id);
            
            if (!$stmt->execute()) {
                throw new Exception('Rol talebi güncellenemedi');
            }
            
            // Eğer onaylandıysa kullanıcının rolünü güncelle
            if ($status === 'approved') {
                $request_data = $this->getById($this->id);
                if ($request_data) {
                    $user_query = "UPDATE users SET role = :new_role WHERE id = :user_id";
                    $user_stmt = $this->conn->prepare($user_query);
                    $user_stmt->bindParam(':new_role', $request_data['requested_role']);
                    $user_stmt->bindParam(':user_id', $request_data['user_id']);
                    
                    if (!$user_stmt->execute()) {
                        throw new Exception('Kullanıcı rolü güncellenemedi');
                    }
                }
            }
            
            $this->conn->commit();
            
            $message = $status === 'approved' ? 'Rol talebi onaylandı' : 'Rol talebi reddedildi';
            return ['success' => true, 'message' => $message];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => 'İşlem sırasında hata oluştu: ' . $e->getMessage()];
        }
    }
    
    // Rol talebini sil
    public function delete() {
        // Sadece pending durumundaki talepler silinebilir
        $current = $this->getById($this->id);
        if (!$current || $current['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Bu talep silinemez'];
        }
        
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Rol talebi başarıyla silindi'];
        }
        
        return ['success' => false, 'message' => 'Rol talebi silinirken hata oluştu'];
    }
    
    // Bekleyen talep kontrolü
    private function hasPendingRequest($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                  WHERE user_id = :user_id AND status = 'pending'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
    
    // Arama fonksiyonu
    public function search($search_term, $status = null, $limit = 20, $offset = 0) {
        $where_conditions = [];
        $params = [];
        
        if ($search_term) {
            $where_conditions[] = "(u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search OR rr.company_name LIKE :search)";
            $params[':search'] = '%' . $search_term . '%';
        }
        
        if ($status) {
            $where_conditions[] = "rr.status = :status";
            $params[':status'] = $status;
        }
        
        $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
        
        $query = "SELECT rr.*, 
                         u.first_name, u.last_name, u.email, u.role as current_user_role,
                         admin.first_name as admin_first_name, admin.last_name as admin_last_name
                  FROM " . $this->table . " rr
                  LEFT JOIN users u ON u.id = rr.user_id
                  LEFT JOIN users admin ON admin.id = rr.reviewed_by
                  " . $where_clause . "
                  ORDER BY rr.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // İstatistikler
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_requests,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_requests,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_requests,
                    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_requests,
                    COUNT(CASE WHEN requested_role = 'realtor' THEN 1 END) as realtor_requests,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as recent_requests
                  FROM " . $this->table;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // En son talepler
    public function getRecentRequests($limit = 5) {
        $query = "SELECT rr.*, 
                         u.first_name, u.last_name, u.email
                  FROM " . $this->table . " rr
                  LEFT JOIN users u ON u.id = rr.user_id
                  ORDER BY rr.created_at DESC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Validation
    public function validate() {
        $errors = [];
        
        if (empty($this->user_id)) {
            $errors[] = 'Kullanıcı ID gereklidir';
        }
        
        if (empty($this->requested_role)) {
            $errors[] = 'Talep edilen rol gereklidir';
        }
        
        if (!in_array($this->requested_role, ['realtor', 'super_admin'])) {
            $errors[] = 'Geçersiz rol talebi';
        }
        
        if (empty($this->reason)) {
            $errors[] = 'Başvuru nedeni gereklidir';
        }
        
        if (strlen($this->reason) < 50) {
            $errors[] = 'Başvuru nedeni en az 50 karakter olmalıdır';
        }
        
        // Emlakçı başvurusu için ek kontroller
        if ($this->requested_role === 'realtor') {
            if (empty($this->company_name)) {
                $errors[] = 'Şirket adı gereklidir';
            }
            
            if (empty($this->phone)) {
                $errors[] = 'Telefon numarası gereklidir';
            }
            
            if (empty($this->experience_years) || $this->experience_years < 0) {
                $errors[] = 'Geçerli deneyim yılı gereklidir';
            }
        }
        
        return $errors;
    }
} 