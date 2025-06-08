<?php
/**
 * Role Middleware
 * Emlak-Delfino Projesi
 * Kullanıcı rol tabanlı yetkilendirme kontrolü
 */

require_once __DIR__ . '/AuthMiddleware.php';
require_once __DIR__ . '/Response.php';

class RoleMiddleware {
    
    // Rol sabitleri
    const ROLE_USER = 1;        // Kayıtlı Kullanıcı
    const ROLE_REALTOR = 2;     // Emlakçı
    const ROLE_ADMIN = 3;       // Admin
    const ROLE_SUPER_ADMIN = 4; // Süper Admin

    /**
     * Belirli bir rol gerektirir
     */
    public static function requireRole($required_role) {
        $user_data = AuthMiddleware::requireAuth();
        
        if ($user_data['role_id'] < $required_role) {
            Response::error('Bu işlem için yetkiniz bulunmamaktadır', 403);
        }
        
        return $user_data;
    }

    /**
     * Birden fazla rolden birini gerektirir
     */
    public static function requireAnyRole($allowed_roles = []) {
        $user_data = AuthMiddleware::requireAuth();
        
        if (!in_array($user_data['role_id'], $allowed_roles)) {
            Response::error('Bu işlem için yetkiniz bulunmamaktadır', 403);
        }
        
        return $user_data;
    }

    /**
     * Sadece kayıtlı kullanıcı (minimum rol)
     */
    public static function requireUser() {
        return self::requireRole(self::ROLE_USER);
    }

    /**
     * Emlakçı veya üstü rol gerektirir
     */
    public static function requireRealtor() {
        return self::requireRole(self::ROLE_REALTOR);
    }

    /**
     * Admin veya üstü rol gerektirir
     */
    public static function requireAdmin() {
        return self::requireRole(self::ROLE_ADMIN);
    }

    /**
     * Sadece süper admin
     */
    public static function requireSuperAdmin() {
        return self::requireRole(self::ROLE_SUPER_ADMIN);
    }

    /**
     * Kaynak sahibi veya admin kontrolü
     */
    public static function requireOwnerOrAdmin($resource_user_id) {
        $user_data = AuthMiddleware::requireAuth();
        
        // Admin veya süper admin ise her şeye erişebilir
        if ($user_data['role_id'] >= self::ROLE_ADMIN) {
            return $user_data;
        }
        
        // Kaynak sahibi mi kontrol et
        if ($user_data['user_id'] != $resource_user_id) {
            Response::error('Bu kaynağa erişim yetkiniz bulunmamaktadır', 403);
        }
        
        return $user_data;
    }

    /**
     * Emlakçı ve kendi kaynağı veya admin kontrolü
     */
    public static function requireRealtorOwnerOrAdmin($resource_user_id) {
        $user_data = AuthMiddleware::requireAuth();
        
        // Admin veya süper admin ise her şeye erişebilir
        if ($user_data['role_id'] >= self::ROLE_ADMIN) {
            return $user_data;
        }
        
        // Emlakçı rolü gerekli
        if ($user_data['role_id'] < self::ROLE_REALTOR) {
            Response::error('Bu işlem için emlakçı yetkisi gereklidir', 403);
        }
        
        // Kaynak sahibi mi kontrol et
        if ($user_data['user_id'] != $resource_user_id) {
            Response::error('Bu kaynağa erişim yetkiniz bulunmamaktadır', 403);
        }
        
        return $user_data;
    }

    /**
     * Rol adını getir
     */
    public static function getRoleName($role_id) {
        $roles = [
            self::ROLE_USER => 'Kayıtlı Kullanıcı',
            self::ROLE_REALTOR => 'Emlakçı',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_SUPER_ADMIN => 'Süper Admin'
        ];
        
        return $roles[$role_id] ?? 'Bilinmeyen Rol';
    }

    /**
     * Kullanıcının rolünü kontrol et
     */
    public static function hasRole($user_role, $required_role) {
        return $user_role >= $required_role;
    }

    /**
     * Kullanıcının belirli bir rolü olup olmadığını kontrol et
     */
    public static function hasExactRole($user_role, $exact_role) {
        return $user_role == $exact_role;
    }

    /**
     * Kullanıcının admin olup olmadığını kontrol et
     */
    public static function isAdmin($user_role) {
        return $user_role >= self::ROLE_ADMIN;
    }

    /**
     * Kullanıcının emlakçı olup olmadığını kontrol et
     */
    public static function isRealtor($user_role) {
        return $user_role >= self::ROLE_REALTOR;
    }

    /**
     * Kullanıcının süper admin olup olmadığını kontrol et
     */
    public static function isSuperAdmin($user_role) {
        return $user_role == self::ROLE_SUPER_ADMIN;
    }

    /**
     * Rol yükseltme yetkisi kontrolü
     */
    public static function canPromoteToRole($current_user_role, $target_role) {
        // Sadece süper admin başka kullanıcıları admin yapabilir
        if ($target_role >= self::ROLE_ADMIN && $current_user_role < self::ROLE_SUPER_ADMIN) {
            return false;
        }
        
        // Admin, emlakçı rolü verebilir
        if ($target_role >= self::ROLE_REALTOR && $current_user_role < self::ROLE_ADMIN) {
            return false;
        }
        
        return true;
    }

    /**
     * Veritabanından kullanıcı rolünü getir
     */
    public static function getUserRoleFromDB($user_id) {
        try {
            require_once __DIR__ . '/../config/database.php';
            
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT role_id FROM users WHERE id = :user_id AND status = 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $user ? $user['role_id'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Rol değişikliği logla
     */
    public static function logRoleChange($user_id, $old_role, $new_role, $changed_by) {
        try {
            require_once __DIR__ . '/../config/database.php';
            
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "INSERT INTO role_change_logs (user_id, old_role_id, new_role_id, changed_by, created_at) 
                      VALUES (:user_id, :old_role, :new_role, :changed_by, NOW())";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':old_role', $old_role);
            $stmt->bindParam(':new_role', $new_role);
            $stmt->bindParam(':changed_by', $changed_by);
            $stmt->execute();
            
            return true;
        } catch (Exception $e) {
            // Log hatası kritik değil
            return false;
        }
    }
}
?> 