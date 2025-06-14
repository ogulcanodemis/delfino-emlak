<?php
/**
 * Notification Model
 * Emlak-Delfino Projesi
 * Kullanıcı bildirimleri yönetimi
 */

class Notification {
    private $conn;
    private $table_name = "notifications";

    // Notification özellikleri
    public $id;
    public $user_id;
    public $title;
    public $message;
    public $type;
    public $related_id;
    public $related_type;
    public $is_read;
    public $created_at;
    public $read_at;

    // Bildirim tipleri
    const TYPE_PROPERTY_APPROVED = 'property_approved';
    const TYPE_PROPERTY_REJECTED = 'property_rejected';
    const TYPE_PROPERTY_APPROVAL_REQUIRED = 'property_approval_required';
    const TYPE_ROLE_REQUEST = 'role_request';
    const TYPE_ROLE_APPROVED = 'role_approved';
    const TYPE_ROLE_REJECTED = 'role_rejected';
    const TYPE_FAVORITE_PROPERTY = 'favorite_property';
    const TYPE_SYSTEM = 'system';
    const TYPE_WELCOME = 'welcome';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Tüm bildirimleri getir (sayfalama ile)
     */
    public function getAll($user_id = null, $page = 1, $limit = 20, $unread_only = false) {
        try {
            $offset = ($page - 1) * $limit;
            
            $where_conditions = [];
            $params = [];
            
            if ($user_id) {
                $where_conditions[] = "user_id = :user_id";
                $params[':user_id'] = $user_id;
            }
            
            if ($unread_only) {
                $where_conditions[] = "is_read = 0";
            }
            
            $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
            
            $query = "SELECT * FROM " . $this->table_name . " 
                      $where_clause 
                      ORDER BY created_at DESC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Toplam bildirim sayısını getir
     */
    public function getCount($user_id = null, $unread_only = false) {
        try {
            $where_conditions = [];
            $params = [];
            
            if ($user_id) {
                $where_conditions[] = "user_id = :user_id";
                $params[':user_id'] = $user_id;
            }
            
            if ($unread_only) {
                $where_conditions[] = "is_read = 0";
            }
            
            $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
            
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " $where_clause";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * ID ile bildirim getir
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Yeni bildirim oluştur
     */
    public function create($user_id, $title, $message, $type = self::TYPE_SYSTEM, $related_id = null, $related_type = null) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      (user_id, title, message, type, related_id, related_type, is_read, created_at) 
                      VALUES (:user_id, :title, :message, :type, :related_id, :related_type, 0, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':related_id', $related_id);
            $stmt->bindParam(':related_type', $related_type);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Bildirimi okundu olarak işaretle
     */
    public function markAsRead($id, $user_id = null) {
        try {
            $where_conditions = ["id = :id"];
            $params = [':id' => $id];
            
            if ($user_id) {
                $where_conditions[] = "user_id = :user_id";
                $params[':user_id'] = $user_id;
            }
            
            $where_clause = implode(" AND ", $where_conditions);
            
            $query = "UPDATE " . $this->table_name . " 
                      SET is_read = 1 
                      WHERE $where_clause";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Tüm bildirimleri okundu olarak işaretle
     */
    public function markAllAsRead($user_id) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET is_read = 1 
                      WHERE user_id = :user_id AND is_read = 0";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Bildirimi sil
     */
    public function delete($id, $user_id = null) {
        try {
            $where_conditions = ["id = :id"];
            $params = [':id' => $id];
            
            if ($user_id) {
                $where_conditions[] = "user_id = :user_id";
                $params[':user_id'] = $user_id;
            }
            
            $where_clause = implode(" AND ", $where_conditions);
            
            $query = "DELETE FROM " . $this->table_name . " WHERE $where_clause";
            
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Kullanıcının tüm bildirimlerini sil
     */
    public function deleteAllByUser($user_id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Eski bildirimleri temizle (30 günden eski)
     */
    public function cleanOldNotifications($days = 30) {
        try {
            $query = "DELETE FROM " . $this->table_name . " 
                      WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':days', $days, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Toplu bildirim gönder (tüm kullanıcılara)
     */
    public function sendBulkNotification($title, $message, $type = self::TYPE_SYSTEM, $role_filter = null) {
        try {
            $user_query = "SELECT id FROM users WHERE status = 1";
            $params = [];
            
            if ($role_filter) {
                $user_query .= " AND role_id = :role_id";
                $params[':role_id'] = $role_filter;
            }
            
            $user_stmt = $this->conn->prepare($user_query);
            
            foreach ($params as $key => $value) {
                $user_stmt->bindValue($key, $value);
            }
            
            $user_stmt->execute();
            $users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $success_count = 0;
            
            foreach ($users as $user) {
                if ($this->create($user['id'], $title, $message, $type)) {
                    $success_count++;
                }
            }
            
            return $success_count;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Emlak onay bildirimi gönder
     */
    public function sendPropertyApprovalNotification($user_id, $property_id, $property_title, $approved = true) {
        $title = $approved ? "İlan Onaylandı" : "İlan Reddedildi";
        $message = $approved 
            ? "'{$property_title}' başlıklı ilanınız onaylandı ve yayınlandı."
            : "'{$property_title}' başlıklı ilanınız reddedildi. Lütfen ilan detaylarını kontrol ediniz.";
        
        $type = $approved ? self::TYPE_PROPERTY_APPROVED : self::TYPE_PROPERTY_REJECTED;
        
        return $this->create($user_id, $title, $message, $type, $property_id, 'property');
    }

    /**
     * Rol talebi bildirimi gönder
     */
    public function sendRoleRequestNotification($admin_user_id, $requester_name, $requested_role) {
        $title = "Yeni Rol Talebi";
        $message = "{$requester_name} kullanıcısı {$requested_role} rolü için talepte bulundu.";
        
        return $this->create($admin_user_id, $title, $message, self::TYPE_ROLE_REQUEST);
    }

    /**
     * Rol onay bildirimi gönder
     */
    public function sendRoleApprovalNotification($user_id, $role_name, $approved = true) {
        $title = $approved ? "Rol Talebi Onaylandı" : "Rol Talebi Reddedildi";
        $message = $approved 
            ? "{$role_name} rol talebiniz onaylandı. Artık yeni yetkilerinizi kullanabilirsiniz."
            : "{$role_name} rol talebiniz reddedildi.";
        
        $type = $approved ? self::TYPE_ROLE_APPROVED : self::TYPE_ROLE_REJECTED;
        
        return $this->create($user_id, $title, $message, $type);
    }

    /**
     * Hoş geldin bildirimi gönder
     */
    public function sendWelcomeNotification($user_id, $user_name) {
        $title = "Hoş Geldiniz!";
        $message = "Merhaba {$user_name}! Emlak-Delfino'ya hoş geldiniz. Platformumuzda güvenle emlak alım-satım işlemlerinizi gerçekleştirebilirsiniz.";
        
        return $this->create($user_id, $title, $message, self::TYPE_WELCOME);
    }

    /**
     * Favori ilan bildirimi gönder
     */
    public function sendFavoritePropertyNotification($user_id, $property_title, $action = 'price_change') {
        $title = "Favori İlanınızda Güncelleme";
        $message = "'{$property_title}' başlıklı favori ilanınızda değişiklik yapıldı.";
        
        return $this->create($user_id, $title, $message, self::TYPE_FAVORITE_PROPERTY);
    }

    /**
     * Süper admin'e ilan onay talebi bildirimi gönder
     */
    public function sendPropertyApprovalRequestNotification($property_id, $property_title, $user_name) {
        try {
            // Süper admin kullanıcılarını bul (role_id = 3)
            $query = "SELECT id FROM users WHERE role_id = 3 AND status = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $title = "Yeni İlan Onay Talebi";
            $message = "{$user_name} kullanıcısı '{$property_title}' başlıklı ilan için onay bekliyor.";
            
            $success_count = 0;
            foreach ($admins as $admin) {
                if ($this->create($admin['id'], $title, $message, self::TYPE_PROPERTY_APPROVAL_REQUIRED, $property_id, 'property')) {
                    $success_count++;
                }
            }
            
            return $success_count > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Admin'lere bekleyen onay sayısını getir
     */
    public function getPendingApprovalCount() {
        try {
            $query = "SELECT COUNT(*) as count FROM properties WHERE approval_status = 'pending'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'];
        } catch (Exception $e) {
            return 0;
        }
    }
}
?> 