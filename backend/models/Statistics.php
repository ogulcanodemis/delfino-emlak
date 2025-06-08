<?php
/**
 * Statistics Model
 * Emlak-Delfino Projesi
 * Sistem istatistikleri ve raporlama
 */

class Statistics {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Genel sistem istatistikleri
     */
    public function getGeneralStats() {
        try {
            $stats = [];

            // Toplam kullanıcı sayısı
            $query = "SELECT COUNT(*) as total_users FROM users WHERE status = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_users'] = $result['total_users'];

            // Aktif emlakçı sayısı
            $query = "SELECT COUNT(*) as total_realtors FROM users WHERE role_id >= 2 AND status = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_realtors'] = $result['total_realtors'];

            // Toplam ilan sayısı
            $query = "SELECT COUNT(*) as total_properties FROM properties WHERE is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_properties'] = $result['total_properties'];

            // Onay bekleyen ilan sayısı (status_id = 1 pending olarak varsayıyoruz)
            $query = "SELECT COUNT(*) as pending_properties FROM properties WHERE status_id = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['pending_properties'] = $result['pending_properties'];

            // Toplam görüntülenme sayısı
            $query = "SELECT SUM(view_count) as total_views FROM properties";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_views'] = $result['total_views'] ?? 0;

            // Toplam favori sayısı
            $query = "SELECT COUNT(*) as total_favorites FROM favorites";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_favorites'] = $result['total_favorites'];

            return $stats;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Kullanıcı istatistikleri (rol bazında)
     */
    public function getUserStats() {
        try {
            $query = "SELECT 
                        role_id,
                        COUNT(*) as count,
                        CASE 
                            WHEN role_id = 1 THEN 'Kayıtlı Kullanıcı'
                            WHEN role_id = 2 THEN 'Emlakçı'
                            WHEN role_id = 3 THEN 'Admin'
                            WHEN role_id = 4 THEN 'Süper Admin'
                            ELSE 'Bilinmeyen'
                        END as role_name
                      FROM users 
                      WHERE status = 1 
                      GROUP BY role_id 
                      ORDER BY role_id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Emlak istatistikleri (tip bazında)
     */
    public function getPropertyStats() {
        try {
            $query = "SELECT 
                        pt.name as property_type,
                        COUNT(p.id) as count,
                        AVG(p.price) as avg_price,
                        MIN(p.price) as min_price,
                        MAX(p.price) as max_price,
                        SUM(p.view_count) as total_views
                      FROM properties p
                      LEFT JOIN property_types pt ON p.property_type_id = pt.id
                      WHERE p.is_active = 1
                      GROUP BY p.property_type_id, pt.name
                      ORDER BY count DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Şehir bazında emlak istatistikleri
     */
    public function getCityStats() {
        try {
            $query = "SELECT 
                        c.name as city,
                        COUNT(*) as property_count,
                        AVG(p.price) as avg_price,
                        MIN(p.price) as min_price,
                        MAX(p.price) as max_price
                      FROM properties p
                      LEFT JOIN cities c ON p.city_id = c.id
                      WHERE p.is_active = 1 AND c.name IS NOT NULL
                      GROUP BY c.name 
                      ORDER BY property_count DESC 
                      LIMIT 20";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Aylık ilan ekleme istatistikleri
     */
    public function getMonthlyPropertyStats($year = null) {
        try {
            $year = $year ?? date('Y');
            
            $query = "SELECT 
                        MONTH(created_at) as month,
                        MONTHNAME(created_at) as month_name,
                        COUNT(*) as property_count,
                        COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_count,
                        COUNT(CASE WHEN status_id = 1 THEN 1 END) as pending_count
                      FROM properties 
                      WHERE YEAR(created_at) = :year
                      GROUP BY MONTH(created_at), MONTHNAME(created_at)
                      ORDER BY MONTH(created_at)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':year', $year);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Aylık kullanıcı kayıt istatistikleri
     */
    public function getMonthlyUserStats($year = null) {
        try {
            $year = $year ?? date('Y');
            
            $query = "SELECT 
                        MONTH(created_at) as month,
                        MONTHNAME(created_at) as month_name,
                        COUNT(*) as user_count,
                        COUNT(CASE WHEN role_id = 1 THEN 1 END) as regular_users,
                        COUNT(CASE WHEN role_id >= 2 THEN 1 END) as realtors_and_admins
                      FROM users 
                      WHERE YEAR(created_at) = :year
                      GROUP BY MONTH(created_at), MONTHNAME(created_at)
                      ORDER BY MONTH(created_at)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':year', $year);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * En popüler ilanlar
     */
    public function getPopularProperties($limit = 10) {
        try {
            $query = "SELECT 
                        p.id,
                        p.title,
                        p.price,
                        p.view_count,
                        c.name as city,
                        d.name as district,
                        pt.name as property_type,
                        u.name as user_name,
                        (SELECT COUNT(*) FROM favorites f WHERE f.property_id = p.id) as favorite_count
                      FROM properties p
                      LEFT JOIN property_types pt ON p.property_type_id = pt.id
                      LEFT JOIN users u ON p.user_id = u.id
                      LEFT JOIN cities c ON p.city_id = c.id
                      LEFT JOIN districts d ON p.district_id = d.id
                      WHERE p.is_active = 1
                      ORDER BY p.view_count DESC, favorite_count DESC
                      LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * En aktif emlakçılar
     */
    public function getTopRealtors($limit = 10) {
        try {
            $query = "SELECT 
                        u.id,
                        u.name,
                        u.email,
                        u.phone,
                        COUNT(p.id) as property_count,
                        SUM(p.view_count) as total_views,
                        AVG(p.price) as avg_property_price
                      FROM users u
                      LEFT JOIN properties p ON u.id = p.user_id AND p.is_active = 1
                      WHERE u.role_id >= 2 AND u.status = 1
                      GROUP BY u.id, u.name, u.email, u.phone
                      ORDER BY property_count DESC, total_views DESC
                      LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Fiyat aralığı istatistikleri
     */
    public function getPriceRangeStats() {
        try {
            $query = "SELECT 
                        CASE 
                            WHEN price < 100000 THEN '0-100K'
                            WHEN price < 250000 THEN '100K-250K'
                            WHEN price < 500000 THEN '250K-500K'
                            WHEN price < 1000000 THEN '500K-1M'
                            WHEN price < 2000000 THEN '1M-2M'
                            ELSE '2M+'
                        END as price_range,
                        COUNT(*) as property_count,
                        AVG(price) as avg_price
                      FROM properties 
                      WHERE is_active = 1 AND price > 0
                      GROUP BY 
                        CASE 
                            WHEN price < 100000 THEN '0-100K'
                            WHEN price < 250000 THEN '100K-250K'
                            WHEN price < 500000 THEN '250K-500K'
                            WHEN price < 1000000 THEN '500K-1M'
                            WHEN price < 2000000 THEN '1M-2M'
                            ELSE '2M+'
                        END
                      ORDER BY 
                        CASE 
                            WHEN price < 100000 THEN 1
                            WHEN price < 250000 THEN 2
                            WHEN price < 500000 THEN 3
                            WHEN price < 1000000 THEN 4
                            WHEN price < 2000000 THEN 5
                            ELSE 6
                        END";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Son 30 günün aktivite istatistikleri
     */
    public function getRecentActivityStats() {
        try {
            $stats = [];

            // Son 30 günde eklenen ilanlar
            $query = "SELECT COUNT(*) as count FROM properties WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['new_properties_30d'] = $result['count'];

            // Son 30 günde kayıt olan kullanıcılar
            $query = "SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['new_users_30d'] = $result['count'];

            // Son 30 günde eklenen favoriler
            $query = "SELECT COUNT(*) as count FROM favorites WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['new_favorites_30d'] = $result['count'];

            // Son 7 günlük günlük aktivite
            $query = "SELECT 
                        DATE(created_at) as date,
                        COUNT(*) as property_count
                      FROM properties 
                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                      GROUP BY DATE(created_at)
                      ORDER BY date DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['daily_properties_7d'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $stats;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Kullanıcı aktivite istatistikleri
     */
    public function getUserActivityStats($user_id) {
        try {
            $stats = [];

            // Kullanıcının toplam ilan sayısı
            $query = "SELECT COUNT(*) as count FROM properties WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_properties'] = $result['count'];

            // Aktif ilan sayısı
            $query = "SELECT COUNT(*) as count FROM properties WHERE user_id = :user_id AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['active_properties'] = $result['count'];

            // Toplam görüntülenme
            $query = "SELECT SUM(view_count) as total FROM properties WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_views'] = $result['total'] ?? 0;

            // Toplam favori sayısı
            $query = "SELECT COUNT(*) as count FROM favorites f 
                      JOIN properties p ON f.property_id = p.id 
                      WHERE p.user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_favorites_received'] = $result['count'];

            // Kullanıcının favori ilan sayısı
            $query = "SELECT COUNT(*) as count FROM favorites WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['user_favorites'] = $result['count'];

            return $stats;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Sistem performans istatistikleri
     */
    public function getSystemPerformanceStats() {
        try {
            $stats = [];

            // Veritabanı boyutu bilgileri
            $query = "SELECT 
                        table_name,
                        table_rows,
                        ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                      FROM information_schema.tables 
                      WHERE table_schema = DATABASE()
                      ORDER BY (data_length + index_length) DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['database_tables'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Toplam veritabanı boyutu
            $query = "SELECT 
                        ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS total_size_mb
                      FROM information_schema.tables 
                      WHERE table_schema = DATABASE()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_db_size_mb'] = $result['total_size_mb'];

            return $stats;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Özel rapor oluştur
     */
    public function generateCustomReport($start_date, $end_date, $report_type = 'properties') {
        try {
            $stats = [];

            if ($report_type === 'properties') {
                $query = "SELECT 
                            DATE(created_at) as date,
                            COUNT(*) as count,
                            AVG(price) as avg_price,
                            SUM(view_count) as total_views
                          FROM properties 
                          WHERE created_at BETWEEN :start_date AND :end_date
                          GROUP BY DATE(created_at)
                          ORDER BY date";
            } elseif ($report_type === 'users') {
                $query = "SELECT 
                            DATE(created_at) as date,
                            COUNT(*) as count,
                            COUNT(CASE WHEN role_id >= 2 THEN 1 END) as realtor_count
                          FROM users 
                          WHERE created_at BETWEEN :start_date AND :end_date
                          GROUP BY DATE(created_at)
                          ORDER BY date";
            } else {
                return false;
            }

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
}
?> 