<?php
/**
 * User Controller
 * Emlak-Delfino Projesi
 */

require_once '../models/Property.php';
require_once '../utils/JWT.php';

class UserController {
    private $db;
    private $property;

    public function __construct($database) {
        $this->db = $database;
        $this->property = new Property($this->db);
    }

    /**
     * Kullanıcının ilanlarını getir
     */
    public function getUserProperties() {
        try {
            // Kullanıcı yetkisini kontrol et
            $user_data = $this->getUserData();
            if (!$user_data) {
                Response::error('Oturum açmanız gerekiyor', 401);
            }

            // GET parametrelerini al
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? 10;

            // Kullanıcının ilanlarını getir
            $properties = $this->property->getByUserId($user_data['user_id'], $page, $limit);
            
            // Her property için images'ı ekle
            foreach ($properties as &$property) {
                $property['images'] = $this->property->getPropertyImages($property['id']);
            }

            // Toplam sayıyı hesapla
            $total = $this->property->getUserPropertyCount($user_data['user_id']);
            $total_pages = ceil($total / $limit);

            Response::success([
                'properties' => $properties,
                'pagination' => [
                    'total' => (int)$total,
                    'count' => count($properties),
                    'per_page' => (int)$limit,
                    'current_page' => (int)$page,
                    'total_pages' => (int)$total_pages
                ]
            ], 'Kullanıcı ilanları başarıyla getirildi');

        } catch (Exception $e) {
            Response::error('Kullanıcı ilanları getirilemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * JWT token'dan kullanıcı verilerini al
     */
    private function getUserData() {
        $headers = getallheaders();
        $auth_header = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$auth_header || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
            return null;
        }

        $token = $matches[1];
        $payload = JWT::decode($token);
        
        return $payload;
    }
}
?> 