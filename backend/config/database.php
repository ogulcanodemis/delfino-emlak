<?php
/**
 * Veritabanı Bağlantı Konfigürasyonu
 * Emlak-Delfino Projesi
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'u389707721_bkyatirim';
    private $username = 'u389707721_bkdb';
    private $password = '$iTxfq%x2B;4GJt';
    private $charset = 'utf8mb4';
    private $conn;

    /**
     * Veritabanı bağlantısını oluşturur
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            echo "Veritabanı bağlantı hatası: " . $exception->getMessage();
        }

        return $this->conn;
    }

    /**
     * Bağlantıyı test eder
     */
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            if ($conn) {
                return [
                    'status' => 'success',
                    'message' => 'Veritabanı bağlantısı başarılı',
                    'server_info' => $conn->getAttribute(PDO::ATTR_SERVER_VERSION)
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Veritabanı bağlantı hatası: ' . $e->getMessage()
            ];
        }
    }
}
?> 