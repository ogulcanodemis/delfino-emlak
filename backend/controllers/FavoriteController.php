<?php
/**
 * Favorite Controller
 * Emlak-Delfino Projesi
 * Kullanıcıların favori ilanlarını yönetir
 */

require_once '../models/Favorite.php';
require_once '../utils/JWT.php';

class FavoriteController {
    private $db;
    private $favorite;

    public function __construct($database) {
        $this->db = $database;
        $this->favorite = new Favorite($this->db);
    }

    /**
     * Kullanıcının favori ilanlarını listele
     * GET /api/favorites
     */
    public function getFavorites() {
        // Kullanıcı yetkisini kontrol et
        $user_data = $this->getUserData();
        if (!$user_data) {
            Response::unauthorized('Oturum açmanız gerekiyor');
        }

        // Sayfalama parametrelerini al
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 10;

        try {
            // Favori ilanları getir
            $favorites = $this->favorite->getUserFavorites($user_data['user_id'], $page, $limit);
            $total = $this->favorite->getUserFavoritesCount($user_data['user_id']);

            // Sayfalama bilgilerini hesapla
            $total_pages = ceil($total / $limit);

            Response::success([
                'favorites' => $favorites,
                'pagination' => [
                    'total' => (int)$total,
                    'count' => count($favorites),
                    'per_page' => (int)$limit,
                    'current_page' => (int)$page,
                    'total_pages' => (int)$total_pages
                ]
            ], 'Favori ilanlar başarıyla getirildi');

        } catch (Exception $e) {
            Response::error('Favori ilanlar getirilemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * İlanı favorilere ekle
     * POST /api/favorites
     */
    public function addToFavorites() {
        // Kullanıcı yetkisini kontrol et
        $user_data = $this->getUserData();
        if (!$user_data) {
            Response::unauthorized('Oturum açmanız gerekiyor');
        }

        // POST verilerini al
        $data = json_decode(file_get_contents("php://input"), true);

        // Gerekli alanları kontrol et
        if (empty($data['property_id'])) {
            Response::validationError(['message' => 'İlan ID gereklidir']);
        }

        $property_id = (int)$data['property_id'];

        try {
            // Favoriye ekle
            if ($this->favorite->addToFavorites($user_data['user_id'], $property_id)) {
                Response::success([
                    'favorite' => [
                        'id' => $this->favorite->id,
                        'property_id' => $property_id,
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ], 'İlan favorilere eklendi', 201);
            } else {
                // Zaten favori olup olmadığını kontrol et
                if ($this->favorite->isFavorite($user_data['user_id'], $property_id)) {
                    Response::validationError(['message' => 'Bu ilan zaten favorilerinizde']);
                } else {
                    Response::error('İlan bulunamadı veya aktif değil', 404);
                }
            }

        } catch (Exception $e) {
            Response::error('İlan favorilere eklenirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * İlanı favorilerden çıkar
     * DELETE /api/favorites/{property_id}
     */
    public function removeFromFavorites($property_id) {
        // Kullanıcı yetkisini kontrol et
        $user_data = $this->getUserData();
        if (!$user_data) {
            Response::unauthorized('Oturum açmanız gerekiyor');
        }

        $property_id = (int)$property_id;

        try {
            // Önce favori olup olmadığını kontrol et
            if (!$this->favorite->isFavorite($user_data['user_id'], $property_id)) {
                Response::error('Bu ilan favorilerinizde bulunmuyor', 404);
            }

            // Favorilerden çıkar
            if ($this->favorite->removeFromFavorites($user_data['user_id'], $property_id)) {
                Response::success(null, 'İlan favorilerden çıkarıldı');
            } else {
                Response::error('İlan favorilerden çıkarılırken hata oluştu', 500);
            }

        } catch (Exception $e) {
            Response::error('İlan favorilerden çıkarılırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * İlanın favori durumunu kontrol et
     * GET /api/favorites/check/{property_id}
     */
    public function checkFavoriteStatus($property_id) {
        // Kullanıcı yetkisini kontrol et
        $user_data = $this->getUserData();
        if (!$user_data) {
            Response::unauthorized('Oturum açmanız gerekiyor');
        }

        $property_id = (int)$property_id;

        try {
            $is_favorite = $this->favorite->isFavorite($user_data['user_id'], $property_id);

            Response::success([
                'property_id' => $property_id,
                'is_favorite' => $is_favorite
            ], 'Favori durumu kontrol edildi');

        } catch (Exception $e) {
            Response::error('Favori durumu kontrol edilirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Kullanıcının favori ilanlarının ID'lerini getir
     * GET /api/favorites/ids
     */
    public function getFavoriteIds() {
        // Kullanıcı yetkisini kontrol et
        $user_data = $this->getUserData();
        if (!$user_data) {
            Response::unauthorized('Oturum açmanız gerekiyor');
        }

        try {
            $favorite_ids = $this->favorite->getUserFavoritePropertyIds($user_data['user_id']);

            Response::success([
                'favorite_property_ids' => $favorite_ids,
                'count' => count($favorite_ids)
            ], 'Favori ilan ID\'leri getirildi');

        } catch (Exception $e) {
            Response::error('Favori ilan ID\'leri getirilemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Tüm favorileri temizle
     * DELETE /api/favorites
     */
    public function clearAllFavorites() {
        // Kullanıcı yetkisini kontrol et
        $user_data = $this->getUserData();
        if (!$user_data) {
            Response::unauthorized('Oturum açmanız gerekiyor');
        }

        try {
            if ($this->favorite->deleteUserFavorites($user_data['user_id'])) {
                Response::success(null, 'Tüm favoriler temizlendi');
            } else {
                Response::error('Favoriler temizlenirken hata oluştu', 500);
            }

        } catch (Exception $e) {
            Response::error('Favoriler temizlenirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * JWT token'dan kullanıcı bilgilerini al
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