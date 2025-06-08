<?php
/**
 * Contact Model
 * Emlak-Delfino Projesi
 * İletişim formu ve mesaj yönetimi
 */

class Contact {
    private $conn;
    private $table_name = "contacts";

    // Contact özellikleri
    public $id;
    public $name;
    public $email;
    public $phone;
    public $subject;
    public $message;
    public $status;
    public $user_id;
    public $property_id;
    public $contact_type;
    public $ip_address;
    public $user_agent;
    public $created_at;
    public $updated_at;
    public $replied_at;
    public $replied_by;

    // İletişim tipleri
    const TYPE_GENERAL = 'general';
    const TYPE_PROPERTY_INQUIRY = 'property_inquiry';
    const TYPE_SUPPORT = 'support';
    const TYPE_COMPLAINT = 'complaint';
    const TYPE_SUGGESTION = 'suggestion';

    // Durum tipleri
    const STATUS_NEW = 'new';
    const STATUS_PENDING = 'pending';
    const STATUS_READ = 'read';
    const STATUS_REPLIED = 'replied';
    const STATUS_CLOSED = 'closed';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Tüm iletişim mesajlarını getir (sayfalama ile)
     */
    public function getAll($page = 1, $limit = 20, $status = null, $contact_type = null) {
        try {
            $offset = ($page - 1) * $limit;
            
            $where_conditions = [];
            $params = [];
            
            if ($status) {
                $where_conditions[] = "status = :status";
                $params[':status'] = $status;
            }
            
            if ($contact_type) {
                $where_conditions[] = "contact_type = :contact_type";
                $params[':contact_type'] = $contact_type;
            }
            
            $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
            
            $query = "SELECT 
                        cm.*,
                        u.name as user_name,
                        p.title as property_title
                      FROM " . $this->table_name . " cm
                      LEFT JOIN users u ON cm.user_id = u.id
                      LEFT JOIN properties p ON cm.property_id = p.id
                      $where_clause 
                      ORDER BY cm.created_at DESC 
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
     * Toplam mesaj sayısını getir
     */
    public function getCount($status = null, $contact_type = null) {
        try {
            $where_conditions = [];
            $params = [];
            
            if ($status) {
                $where_conditions[] = "status = :status";
                $params[':status'] = $status;
            }
            
            if ($contact_type) {
                $where_conditions[] = "contact_type = :contact_type";
                $params[':contact_type'] = $contact_type;
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
     * ID ile mesaj getir
     */
    public function getById($id) {
        try {
            $query = "SELECT 
                        cm.*,
                        u.name as user_name,
                        u.email as user_email,
                        p.title as property_title,
                        p.price as property_price,
                        p.city as property_city
                      FROM " . $this->table_name . " cm
                      LEFT JOIN users u ON cm.user_id = u.id
                      LEFT JOIN properties p ON cm.property_id = p.id
                      WHERE cm.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Yeni iletişim mesajı oluştur
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      (name, email, phone, subject, message, contact_type, property_id, user_id, 
                       status, admin_notes, ip_address, user_agent, is_spam) 
                      VALUES (:name, :email, :phone, :subject, :message, :contact_type, :property_id, 
                              :user_id, :status, :admin_notes, :ip_address, :user_agent, :is_spam)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':name', $data['name']);
            $stmt->bindValue(':email', $data['email']);
            $stmt->bindValue(':phone', $data['phone'] ?? null);
            $stmt->bindValue(':subject', $data['subject']);
            $stmt->bindValue(':message', $data['message']);
            $stmt->bindValue(':contact_type', $data['contact_type']);
            $stmt->bindValue(':property_id', $data['property_id'] ?? null);
            $stmt->bindValue(':user_id', $data['user_id'] ?? null);
            $stmt->bindValue(':status', $data['status']);
            $stmt->bindValue(':admin_notes', $data['admin_notes'] ?? null);
            $stmt->bindValue(':ip_address', $data['ip_address'] ?? null);
            $stmt->bindValue(':user_agent', $data['user_agent'] ?? null);
            $stmt->bindValue(':is_spam', $data['is_spam'] ?? 0);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Contact create error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mesaj durumunu güncelle
     */
    public function updateStatus($id, $status, $replied_by = null) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET status = :status, updated_at = NOW()
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id);
            $stmt->bindValue(':status', $status);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Mesajı okundu olarak işaretle
     */
    public function markAsRead($id) {
        return $this->updateStatus($id, self::STATUS_READ);
    }

    /**
     * Mesajı yanıtlandı olarak işaretle
     */
    public function markAsReplied($id, $replied_by) {
        return $this->updateStatus($id, self::STATUS_REPLIED, $replied_by);
    }

    /**
     * Mesajı kapat
     */
    public function markAsClosed($id) {
        return $this->updateStatus($id, self::STATUS_CLOSED);
    }

    /**
     * Mesajı sil
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Kullanıcının mesajlarını getir
     */
    public function getByUserId($user_id, $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT 
                        cm.*,
                        p.title as property_title,
                        p.price as property_price
                      FROM " . $this->table_name . " cm
                      LEFT JOIN properties p ON cm.property_id = p.id
                      WHERE cm.user_id = :user_id 
                      ORDER BY cm.created_at DESC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Emlak ile ilgili mesajları getir
     */
    public function getByPropertyId($property_id, $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT 
                        cm.*,
                        u.name as user_name,
                        u.email as user_email
                      FROM " . $this->table_name . " cm
                      LEFT JOIN users u ON cm.user_id = u.id
                      WHERE cm.property_id = :property_id 
                      ORDER BY cm.created_at DESC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':property_id', $property_id);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * İstatistikler
     */
    public function getStats() {
        try {
            $stats = [];

            // Toplam mesaj sayısı
            $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_messages'] = $result['total'];

            // Durum bazında sayılar
            $query = "SELECT status, COUNT(*) as count FROM " . $this->table_name . " GROUP BY status";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $status_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($status_stats as $stat) {
                $stats['by_status'][$stat['status']] = $stat['count'];
            }

            // Tip bazında sayılar
            $query = "SELECT contact_type, COUNT(*) as count FROM " . $this->table_name . " GROUP BY contact_type";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $type_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($type_stats as $stat) {
                $stats['by_type'][$stat['contact_type']] = $stat['count'];
            }

            // Son 30 günlük mesajlar
            $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['last_30_days'] = $result['count'];

            return $stats;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Spam kontrolü (aynı IP'den çok fazla mesaj)
     */
    public function checkSpam($ip_address, $time_window = 3600, $max_messages = 5) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                      WHERE ip_address = :ip_address 
                        AND created_at >= DATE_SUB(NOW(), INTERVAL :time_window SECOND)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':ip_address', $ip_address);
            $stmt->bindParam(':time_window', $time_window);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] >= $max_messages;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Benzer mesaj kontrolü (duplicate check)
     */
    public function checkDuplicate($email, $message, $time_window = 300) {
        try {
            $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                      WHERE email = :email 
                        AND message = :message 
                        AND created_at >= DATE_SUB(NOW(), INTERVAL :time_window SECOND)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':time_window', $time_window);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Mesaj arama
     */
    public function search($search_term, $page = 1, $limit = 20) {
        try {
            $offset = ($page - 1) * $limit;
            $search_term = "%{$search_term}%";
            
            $query = "SELECT 
                        cm.*,
                        u.name as user_name,
                        p.title as property_title
                      FROM " . $this->table_name . " cm
                      LEFT JOIN users u ON cm.user_id = u.id
                      LEFT JOIN properties p ON cm.property_id = p.id
                      WHERE cm.name LIKE :search_term1 
                         OR cm.email LIKE :search_term2 
                         OR cm.subject LIKE :search_term3 
                         OR cm.message LIKE :search_term4
                      ORDER BY cm.created_at DESC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':search_term1', $search_term);
            $stmt->bindParam(':search_term2', $search_term);
            $stmt->bindParam(':search_term3', $search_term);
            $stmt->bindParam(':search_term4', $search_term);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Hata loglama
            error_log("Contact search error: " . $e->getMessage());
            error_log("Search term: " . $search_term);
            error_log("Query: " . $query);
            return false;
        }
    }

    /**
     * Otomatik temizlik (eski mesajları sil)
     */
    public function cleanOldMessages($days = 365) {
        try {
            $query = "DELETE FROM " . $this->table_name . " 
                      WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY) 
                        AND status = 'closed'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':days', $days, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }
}
?> 