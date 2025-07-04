<?php
/**
 * User Model
 * Emlak-Delfino Projesi
 */

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $name;
    public $email;
    public $password;
    public $phone;
    public $address;
    public $bio;
    public $company;
    public $website;
    public $city_id;
    public $district_id;
    public $neighborhood_id;
    public $role_id;
    public $status;
    public $profile_image;
    public $email_verified_at;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Kullanıcı oluştur
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, email=:email, password=:password, phone=:phone, 
                      address=:address, city_id=:city_id, district_id=:district_id, 
                      neighborhood_id=:neighborhood_id, role_id=:role_id, status=:status";

        $stmt = $this->conn->prepare($query);

        // Verileri temizle
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->phone = htmlspecialchars(strip_tags($this->phone ?? ''));
        $this->address = htmlspecialchars(strip_tags($this->address ?? ''));

        // Parametreleri bağla
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":city_id", $this->city_id);
        $stmt->bindParam(":district_id", $this->district_id);
        $stmt->bindParam(":neighborhood_id", $this->neighborhood_id);
        $stmt->bindParam(":role_id", $this->role_id);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * E-posta ile kullanıcı bul
     */
    public function findByEmail($email) {
        $query = "SELECT u.*, r.name as role_name, c.name as city_name, d.name as district_name 
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.role_id = r.id
                  LEFT JOIN cities c ON u.city_id = c.id
                  LEFT JOIN districts d ON u.district_id = d.id
                  WHERE u.email = :email AND u.status = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->city_id = $row['city_id'];
            $this->district_id = $row['district_id'];
            $this->neighborhood_id = $row['neighborhood_id'];
            $this->role_id = $row['role_id'];
            $this->status = $row['status'];
            $this->profile_image = $row['profile_image'];
            $this->email_verified_at = $row['email_verified_at'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];

            return $row;
        }

        return false;
    }

    /**
     * ID ile kullanıcı bul
     */
    public function findById($id) {
        $query = "SELECT u.*, r.name as role_name, c.name as city_name, d.name as district_name 
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.role_id = r.id
                  LEFT JOIN cities c ON u.city_id = c.id
                  LEFT JOIN districts d ON u.district_id = d.id
                  WHERE u.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * Kullanıcı güncelle
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name=:name, phone=:phone, address=:address, bio=:bio, 
                      company=:company, website=:website, city_id=:city_id, 
                      district_id=:district_id, neighborhood_id=:neighborhood_id, 
                      updated_at=NOW()
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Verileri temizle
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->phone = htmlspecialchars(strip_tags($this->phone ?? ''));
        $this->address = htmlspecialchars(strip_tags($this->address ?? ''));
        $this->bio = htmlspecialchars(strip_tags($this->bio ?? ''));
        $this->company = htmlspecialchars(strip_tags($this->company ?? ''));
        $this->website = htmlspecialchars(strip_tags($this->website ?? ''));

        // Parametreleri bağla
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":bio", $this->bio);
        $stmt->bindParam(":company", $this->company);
        $stmt->bindParam(":website", $this->website);
        $stmt->bindParam(":city_id", $this->city_id);
        $stmt->bindParam(":district_id", $this->district_id);
        $stmt->bindParam(":neighborhood_id", $this->neighborhood_id);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }
    
    /**
     * Profil resmini güncelle
     */
    public function updateProfileImage($image_path) {
        $query = "UPDATE " . $this->table_name . " 
                  SET profile_image=:profile_image, updated_at=NOW()
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        
        // Veriyi temizle
        $image_path = htmlspecialchars($image_path ?? '', ENT_QUOTES, 'UTF-8');

        // Parametreleri bağla
        $stmt->bindParam(":profile_image", $image_path);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            $this->profile_image = $image_path;
            return true;
        }
        
        return false;
    }
    
    /**
     * Profil resmini sil
     */
    public function deleteProfileImage() {
        $query = "UPDATE " . $this->table_name . " 
                  SET profile_image=NULL, updated_at=NOW()
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            $this->profile_image = null;
            return true;
        }
        
        return false;
    }

    /**
     * Şifre güncelle
     */
    public function updatePassword($new_password) {
        $query = "UPDATE " . $this->table_name . " 
                  SET password=:password, updated_at=NOW()
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * E-posta doğrulama
     */
    public function verifyEmail() {
        $query = "UPDATE " . $this->table_name . " 
                  SET email_verified_at=NOW(), updated_at=NOW()
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * E-posta benzersizlik kontrolü
     */
    public function emailExists($email, $exclude_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        
        if ($exclude_id) {
            $stmt->bindParam(":exclude_id", $exclude_id);
        }
        
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Şifre doğrulama
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }

    /**
     * Son giriş zamanını güncelle
     */
    public function updateLastLogin() {
        $query = "UPDATE " . $this->table_name . " 
                  SET last_login_at=NOW(), updated_at=NOW()
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    /**
     * Kullanıcı listesi (admin için)
     */
    public function getAll($page = 1, $limit = 10, $search = '', $role_id = null) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT u.*, r.name as role_name, c.name as city_name, d.name as district_name 
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.role_id = r.id
                  LEFT JOIN cities c ON u.city_id = c.id
                  LEFT JOIN districts d ON u.district_id = d.id
                  WHERE 1=1";

        if (!empty($search)) {
            $query .= " AND (u.name LIKE :search OR u.email LIKE :search)";
        }

        if ($role_id) {
            $query .= " AND u.role_id = :role_id";
        }

        $query .= " ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search_param = "%{$search}%";
            $stmt->bindParam(":search", $search_param);
        }

        if ($role_id) {
            $stmt->bindParam(":role_id", $role_id);
        }

        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Toplam kullanıcı sayısı
     */
    public function getTotalCount($search = '', $role_id = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE 1=1";

        if (!empty($search)) {
            $query .= " AND (name LIKE :search OR email LIKE :search)";
        }

        if ($role_id) {
            $query .= " AND role_id = :role_id";
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search_param = "%{$search}%";
            $stmt->bindParam(":search", $search_param);
        }

        if ($role_id) {
            $stmt->bindParam(":role_id", $role_id);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }

    /**
     * Kullanıcı ID'si ile şifre güncelleme
     */
    public function updatePasswordById($user_id, $new_password) {
        $query = "UPDATE " . $this->table_name . " 
                  SET password=:password, updated_at=NOW()
                  WHERE id=:user_id";

        $stmt = $this->conn->prepare($query);

        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Kullanıcı ID'si ile şifre doğrulama
     */
    public function verifyPasswordById($user_id, $password) {
        $query = "SELECT password FROM " . $this->table_name . " WHERE id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return password_verify($password, $row['password']);
        }
        
        return false;
    }

    /**
     * Hesap silme işlemi
     */
    public function deleteAccount($user_id) {
        try {
            // Transaction başlat
            $this->conn->beginTransaction();

            // Kullanıcının ilanlarını sil
            $query1 = "DELETE FROM properties WHERE user_id = :user_id";
            $stmt1 = $this->conn->prepare($query1);
            $stmt1->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt1->execute();

            // Kullanıcının favorilerini sil
            $query2 = "DELETE FROM favorites WHERE user_id = :user_id";
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt2->execute();

            // Kullanıcının mesajlarını sil (eğer messages tablosu varsa)
            $query3 = "DELETE FROM messages WHERE sender_id = :user_id OR receiver_id = :user_id";
            $stmt3 = $this->conn->prepare($query3);
            $stmt3->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt3->execute();

            // Role isteklerini sil (eğer role_requests tablosu varsa)
            $query4 = "DELETE FROM role_requests WHERE user_id = :user_id";
            $stmt4 = $this->conn->prepare($query4);
            $stmt4->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt4->execute();

            // Son olarak kullanıcı hesabını sil
            $query5 = "DELETE FROM " . $this->table_name . " WHERE id = :user_id";
            $stmt5 = $this->conn->prepare($query5);
            $stmt5->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $result = $stmt5->execute();

            // Transaction'ı commit et
            $this->conn->commit();
            
            return $result;

        } catch (Exception $e) {
            // Hata durumunda rollback yap
            $this->conn->rollback();
            throw $e;
        }
    }
}
?> 