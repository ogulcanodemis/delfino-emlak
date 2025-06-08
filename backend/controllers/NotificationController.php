<?php
/**
 * Notification Controller
 * Emlak-Delfino Projesi
 * Bildirim yönetimi endpoint'leri
 */

require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/AuthMiddleware.php';
require_once __DIR__ . '/../utils/RoleMiddleware.php';
require_once __DIR__ . '/../utils/ValidationMiddleware.php';

class NotificationController {
    private $db;
    private $notification;

    public function __construct($database) {
        $this->db = $database;
        $this->notification = new Notification($this->db);
    }

    /**
     * Kullanıcının bildirimlerini getir
     * GET /api/notifications
     */
    public function getUserNotifications() {
        try {
            // Kullanıcı kimlik doğrulaması
            $user_data = AuthMiddleware::requireAuth();
            $user_id = $user_data['user_id'];

            // Sayfalama parametreleri
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';

            // Parametreleri doğrula
            $pagination = ValidationMiddleware::validatePagination($page, $limit, 50);
            $page = $pagination['page'];
            $limit = $pagination['limit'];

            // Bildirimleri getir
            $notifications = $this->notification->getAll($user_id, $page, $limit, $unread_only);
            $total_count = $this->notification->getCount($user_id, $unread_only);
            $unread_count = $this->notification->getCount($user_id, true);

            if ($notifications === false) {
                Response::error('Bildirimler alınamadı', 500);
            }

            Response::success([
                'notifications' => $notifications,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total_count,
                    'total_pages' => ceil($total_count / $limit)
                ],
                'unread_count' => $unread_count
            ]);

        } catch (Exception $e) {
            Response::error('Bildirimler alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Bildirimi okundu olarak işaretle
     * PUT /api/notifications/{id}/read
     */
    public function markAsRead($id) {
        try {
            // Kullanıcı kimlik doğrulaması
            $user_data = AuthMiddleware::requireAuth();
            $user_id = $user_data['user_id'];

            // ID doğrulama
            $notification_id = ValidationMiddleware::validateId($id);

            // Bildirimi kontrol et
            $notification = $this->notification->getById($notification_id);
            if (!$notification) {
                Response::notFound('Bildirim bulunamadı');
            }

            // Kullanıcının bildirimi mi kontrol et
            if ($notification['user_id'] != $user_id) {
                Response::error('Bu bildirimi işaretleme yetkiniz yok', 403);
            }

            // Okundu olarak işaretle
            $result = $this->notification->markAsRead($notification_id, $user_id);

            if ($result) {
                Response::success(['message' => 'Bildirim okundu olarak işaretlendi']);
            } else {
                Response::error('Bildirim güncellenemedi', 500);
            }

        } catch (Exception $e) {
            Response::error('Bildirim güncellenirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Tüm bildirimleri okundu olarak işaretle
     * PUT /api/notifications/mark-all-read
     */
    public function markAllAsRead() {
        try {
            // Kullanıcı kimlik doğrulaması
            $user_data = AuthMiddleware::requireAuth();
            $user_id = $user_data['user_id'];

            // Tüm bildirimleri okundu olarak işaretle
            $result = $this->notification->markAllAsRead($user_id);

            if ($result) {
                Response::success(['message' => 'Tüm bildirimler okundu olarak işaretlendi']);
            } else {
                Response::error('Bildirimler güncellenemedi', 500);
            }

        } catch (Exception $e) {
            Response::error('Bildirimler güncellenirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Bildirimi sil
     * DELETE /api/notifications/{id}
     */
    public function deleteNotification($id) {
        try {
            // Kullanıcı kimlik doğrulaması
            $user_data = AuthMiddleware::requireAuth();
            $user_id = $user_data['user_id'];

            // ID doğrulama
            $notification_id = ValidationMiddleware::validateId($id);

            // Bildirimi kontrol et
            $notification = $this->notification->getById($notification_id);
            if (!$notification) {
                Response::notFound('Bildirim bulunamadı');
            }

            // Kullanıcının bildirimi mi kontrol et
            if ($notification['user_id'] != $user_id) {
                Response::error('Bu bildirimi silme yetkiniz yok', 403);
            }

            // Bildirimi sil
            $result = $this->notification->delete($notification_id, $user_id);

            if ($result) {
                Response::success(['message' => 'Bildirim silindi']);
            } else {
                Response::error('Bildirim silinemedi', 500);
            }

        } catch (Exception $e) {
            Response::error('Bildirim silinirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Kullanıcının tüm bildirimlerini sil
     * DELETE /api/notifications/all
     */
    public function deleteAllNotifications() {
        try {
            // Kullanıcı kimlik doğrulaması
            $user_data = AuthMiddleware::requireAuth();
            $user_id = $user_data['user_id'];

            // Tüm bildirimleri sil
            $result = $this->notification->deleteAllByUser($user_id);

            if ($result) {
                Response::success(['message' => 'Tüm bildirimler silindi']);
            } else {
                Response::error('Bildirimler silinemedi', 500);
            }

        } catch (Exception $e) {
            Response::error('Bildirimler silinirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Okunmamış bildirim sayısını getir
     * GET /api/notifications/unread-count
     */
    public function getUnreadCount() {
        try {
            // Kullanıcı kimlik doğrulaması
            $user_data = AuthMiddleware::requireAuth();
            $user_id = $user_data['user_id'];

            // Okunmamış bildirim sayısını getir
            $unread_count = $this->notification->getCount($user_id, true);

            Response::success([
                'unread_count' => $unread_count
            ]);

        } catch (Exception $e) {
            Response::error('Bildirim sayısı alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Admin: Toplu bildirim gönder
     * POST /api/notifications/bulk-send
     */
    public function sendBulkNotification() {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            // JSON input doğrulama
            $data = ValidationMiddleware::validateJsonInput(['title', 'message']);

            // Veri doğrulama
            $title = ValidationMiddleware::sanitizeString($data['title']);
            $message = ValidationMiddleware::sanitizeString($data['message']);
            $type = isset($data['type']) ? ValidationMiddleware::sanitizeString($data['type']) : Notification::TYPE_SYSTEM;
            $role_filter = isset($data['role_filter']) ? (int)$data['role_filter'] : null;

            // Başlık ve mesaj uzunluğu kontrolü
            if (!ValidationMiddleware::validateTextLength($title, 1, 255)) {
                Response::validationError(['title' => 'Başlık 1-255 karakter arasında olmalıdır']);
            }

            if (!ValidationMiddleware::validateTextLength($message, 1, 1000)) {
                Response::validationError(['message' => 'Mesaj 1-1000 karakter arasında olmalıdır']);
            }

            // Toplu bildirim gönder
            $sent_count = $this->notification->sendBulkNotification($title, $message, $type, $role_filter);

            if ($sent_count !== false) {
                Response::success([
                    'message' => 'Toplu bildirim gönderildi',
                    'sent_count' => $sent_count
                ]);
            } else {
                Response::error('Toplu bildirim gönderilemedi', 500);
            }

        } catch (Exception $e) {
            Response::error('Toplu bildirim gönderilirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Admin: Tüm bildirimleri getir
     * GET /api/admin/notifications
     */
    public function getAllNotifications() {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            // Sayfalama parametreleri
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

            // Parametreleri doğrula
            $pagination = ValidationMiddleware::validatePagination($page, $limit, 100);
            $page = $pagination['page'];
            $limit = $pagination['limit'];

            // Bildirimleri getir
            $notifications = $this->notification->getAll($user_id, $page, $limit);
            $total_count = $this->notification->getCount($user_id);

            if ($notifications === false) {
                Response::error('Bildirimler alınamadı', 500);
            }

            Response::success([
                'notifications' => $notifications,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total_count,
                    'total_pages' => ceil($total_count / $limit)
                ]
            ]);

        } catch (Exception $e) {
            Response::error('Bildirimler alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Admin: Eski bildirimleri temizle
     * DELETE /api/admin/notifications/cleanup
     */
    public function cleanupOldNotifications() {
        try {
            // Süper admin yetkisi gerekli
            $user_data = RoleMiddleware::requireSuperAdmin();

            // Gün parametresi
            $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
            
            if ($days < 7) {
                Response::validationError(['days' => 'En az 7 gün olmalıdır']);
            }

            // Eski bildirimleri temizle
            $result = $this->notification->cleanOldNotifications($days);

            if ($result) {
                Response::success(['message' => "{$days} günden eski bildirimler temizlendi"]);
            } else {
                Response::error('Bildirimler temizlenemedi', 500);
            }

        } catch (Exception $e) {
            Response::error('Bildirimler temizlenirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Bildirim tiplerini getir
     * GET /api/notifications/types
     */
    public function getNotificationTypes() {
        try {
            // Kullanıcı kimlik doğrulaması
            AuthMiddleware::requireAuth();

            $types = [
                Notification::TYPE_PROPERTY_APPROVED => 'İlan Onaylandı',
                Notification::TYPE_PROPERTY_REJECTED => 'İlan Reddedildi',
                Notification::TYPE_ROLE_REQUEST => 'Rol Talebi',
                Notification::TYPE_ROLE_APPROVED => 'Rol Onaylandı',
                Notification::TYPE_ROLE_REJECTED => 'Rol Reddedildi',
                Notification::TYPE_FAVORITE_PROPERTY => 'Favori İlan',
                Notification::TYPE_SYSTEM => 'Sistem',
                Notification::TYPE_WELCOME => 'Hoş Geldin'
            ];

            Response::success(['types' => $types]);

        } catch (Exception $e) {
            Response::error('Bildirim tipleri alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    // API tester için alias metodlar
    public function getTypes() {
        return $this->getNotificationTypes();
    }

    public function bulkSend() {
        return $this->sendBulkNotification();
    }

    public function getAllForAdmin() {
        return $this->getAllNotifications();
    }

    public function getById($id) {
        try {
            // Kullanıcı kimlik doğrulaması
            $user_data = AuthMiddleware::requireAuth();
            $user_id = $user_data['user_id'];

            // ID doğrulama
            $notification_id = ValidationMiddleware::validateId($id);

            // Bildirimi getir
            $notification = $this->notification->getById($notification_id);
            if (!$notification) {
                Response::notFound('Bildirim bulunamadı');
            }

            // Kullanıcının bildirimi mi kontrol et
            if ($notification['user_id'] != $user_id) {
                Response::error('Bu bildirimi görme yetkiniz yok', 403);
            }

            Response::success(['notification' => $notification]);

        } catch (Exception $e) {
            Response::error('Bildirim alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id) {
        return $this->deleteNotification($id);
    }
}
?> 