<?php
/**
 * Authentication Middleware
 * Emlak-Delfino Projesi
 * JWT token doğrulama ve kullanıcı kimlik kontrolü
 */

require_once __DIR__ . '/JWT.php';
require_once __DIR__ . '/Response.php';

class AuthMiddleware {
    
    /**
     * JWT token'ı doğrular ve kullanıcı bilgilerini döndürür
     */
    public static function authenticate() {
        // Authorization header'ını al
        $headers = getallheaders();
        $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$auth_header || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            Response::unauthorized('Token bulunamadı');
        }

        $token = $matches[1];

        // Token'ı doğrula
        $payload = JWT::decode($token);
        if (!$payload) {
            Response::unauthorized('Geçersiz veya süresi dolmuş token');
        }

        return $payload;
    }

    /**
     * Kullanıcı giriş yapmış mı kontrol eder (token kontrolü)
     */
    public static function requireAuth() {
        return self::authenticate();
    }

    /**
     * Opsiyonel authentication - token varsa doğrular, yoksa null döner
     */
    public static function optionalAuth() {
        try {
            $headers = getallheaders();
            $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? null;

            if (!$auth_header || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
                return null;
            }

            $token = $matches[1];
            $payload = JWT::decode($token);
            
            return $payload ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Token'dan kullanıcı bilgilerini al
     */
    public static function getUserData($token = null) {
        if ($token) {
            return JWT::decode($token);
        }
        
        return self::authenticate();
    }

    /**
     * Kullanıcının aktif olup olmadığını kontrol et
     */
    public static function checkUserStatus($user_id) {
        try {
            require_once __DIR__ . '/../config/database.php';
            
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT status FROM users WHERE id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || $user['status'] != 1) {
                Response::unauthorized('Hesabınız aktif değil');
            }
            
            return true;
        } catch (Exception $e) {
            Response::error('Kullanıcı durumu kontrol edilemedi', 500);
        }
    }

    /**
     * Token yenileme kontrolü
     */
    public static function checkTokenExpiry($payload) {
        $current_time = time();
        $token_exp = $payload['exp'] ?? 0;
        
        // Token 1 saat içinde sona erecekse uyarı ver
        $one_hour = 3600;
        if (($token_exp - $current_time) < $one_hour) {
            return [
                'warning' => 'Token yakında sona erecek',
                'expires_in' => $token_exp - $current_time,
                'should_refresh' => true
            ];
        }
        
        return [
            'warning' => null,
            'expires_in' => $token_exp - $current_time,
            'should_refresh' => false
        ];
    }

    /**
     * Rate limiting kontrolü (basit IP tabanlı)
     */
    public static function checkRateLimit($endpoint = null, $max_requests = 100, $window_minutes = 60) {
        try {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $endpoint = $endpoint ?? $_SERVER['REQUEST_URI'] ?? 'unknown';
            
            require_once __DIR__ . '/../config/database.php';
            
            $database = new Database();
            $db = $database->getConnection();
            
            $window_start = date('Y-m-d H:i:s', time() - ($window_minutes * 60));
            
            // Mevcut istek sayısını kontrol et
            $query = "SELECT COUNT(*) as request_count 
                      FROM api_rate_limits 
                      WHERE ip_address = :ip_address 
                        AND endpoint = :endpoint 
                        AND window_start >= :window_start";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':ip_address', $ip_address);
            $stmt->bindParam(':endpoint', $endpoint);
            $stmt->bindParam(':window_start', $window_start);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_requests = $result['request_count'] ?? 0;
            
            if ($current_requests >= $max_requests) {
                Response::error('Rate limit aşıldı. Lütfen daha sonra tekrar deneyin.', 429);
            }
            
            // Yeni isteği kaydet
            $insert_query = "INSERT INTO api_rate_limits (ip_address, endpoint, requests_count, window_start) 
                             VALUES (:ip_address, :endpoint, 1, NOW())
                             ON DUPLICATE KEY UPDATE 
                             requests_count = requests_count + 1, 
                             updated_at = NOW()";
            
            $insert_stmt = $db->prepare($insert_query);
            $insert_stmt->bindParam(':ip_address', $ip_address);
            $insert_stmt->bindParam(':endpoint', $endpoint);
            $insert_stmt->execute();
            
            return true;
        } catch (Exception $e) {
            // Rate limiting hatası durumunda devam et (güvenlik için)
            return true;
        }
    }
}
?> 