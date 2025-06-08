<?php
/**
 * Contact Controller
 * Emlak-Delfino Projesi
 * İletişim formu yönetimi endpoint'leri
 */

require_once __DIR__ . '/../models/Contact.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/AuthMiddleware.php';
require_once __DIR__ . '/../utils/RoleMiddleware.php';
require_once __DIR__ . '/../utils/ValidationMiddleware.php';

class ContactController {
    private $db;
    private $contact;

    public function __construct($database) {
        $this->db = $database;
        $this->contact = new Contact($this->db);
    }

    /**
     * İletişim formu gönder
     * POST /api/contact
     */
    public function submitContactForm() {
        try {
            // JSON input doğrulama
            $data = ValidationMiddleware::validateJsonInput(['name', 'email', 'subject', 'message']);

            // Veri doğrulama
            $name = ValidationMiddleware::sanitizeString($data['name']);
            $email = ValidationMiddleware::sanitizeString($data['email']);
            $phone = isset($data['phone']) ? ValidationMiddleware::sanitizeString($data['phone']) : null;
            $subject = ValidationMiddleware::sanitizeString($data['subject']);
            $message = ValidationMiddleware::sanitizeString($data['message']);
            $contact_type = isset($data['contact_type']) ? ValidationMiddleware::sanitizeString($data['contact_type']) : Contact::TYPE_GENERAL;
            $property_id = isset($data['property_id']) ? (int)$data['property_id'] : null;

            // Validasyon kontrolleri
            if (!ValidationMiddleware::validateTextLength($name, 2, 100)) {
                Response::validationError(['name' => 'İsim 2-100 karakter arasında olmalıdır']);
            }

            if (!ValidationMiddleware::validateEmail($email)) {
                Response::validationError(['email' => 'Geçerli bir e-posta adresi giriniz']);
            }

            if ($phone && !ValidationMiddleware::validatePhone($phone)) {
                Response::validationError(['phone' => 'Geçerli bir telefon numarası giriniz']);
            }

            if (!ValidationMiddleware::validateTextLength($subject, 5, 255)) {
                Response::validationError(['subject' => 'Konu 5-255 karakter arasında olmalıdır']);
            }

            if (!ValidationMiddleware::validateTextLength($message, 10, 2000)) {
                Response::validationError(['message' => 'Mesaj 10-2000 karakter arasında olmalıdır']);
            }

            // İletişim tipi kontrolü
            $allowed_types = [
                Contact::TYPE_GENERAL,
                Contact::TYPE_PROPERTY_INQUIRY,
                Contact::TYPE_SUPPORT,
                Contact::TYPE_COMPLAINT,
                Contact::TYPE_SUGGESTION
            ];

            if (!in_array($contact_type, $allowed_types)) {
                Response::validationError(['contact_type' => 'Geçersiz iletişim tipi']);
            }

            // IP adresi ve User Agent bilgisi
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

            // Spam kontrolü
            if ($this->contact->checkSpam($ip_address)) {
                Response::error('Çok fazla mesaj gönderdiniz. Lütfen daha sonra tekrar deneyin.', 429);
            }

            // Duplicate mesaj kontrolü
            if ($this->contact->checkDuplicate($email, $message)) {
                Response::error('Bu mesajı yakın zamanda göndermiştiniz.', 409);
            }

            // Kullanıcı ID'si (eğer giriş yapmışsa)
            $user_id = null;
            try {
                $user_data = AuthMiddleware::optionalAuth();
                if ($user_data) {
                    $user_id = $user_data['user_id'];
                }
            } catch (Exception $e) {
                // Kullanıcı giriş yapmamış, devam et
            }

            // Mesaj verilerini hazırla
            $contact_data = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $message,
                'contact_type' => $contact_type,
                'user_id' => $user_id,
                'property_id' => $property_id,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'status' => Contact::STATUS_PENDING,
                'admin_notes' => null,
                'is_spam' => 0
            ];

            // Mesajı kaydet
            $contact_id = $this->contact->create($contact_data);

            if ($contact_id) {
                Response::success([
                    'message' => 'Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.',
                    'contact_id' => $contact_id
                ], 200);
            } else {
                error_log("Contact form submission failed. Data: " . json_encode($contact_data));
                Response::error('Mesaj gönderilemedi', 500);
            }

        } catch (Exception $e) {
            error_log("Contact form error: " . $e->getMessage());
            Response::error('Mesaj gönderilirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Admin: Tüm iletişim mesajlarını getir
     * GET /api/admin/contacts
     */
    public function getAllContacts() {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            // Sayfalama ve filtreleme parametreleri
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $status = isset($_GET['status']) ? ValidationMiddleware::sanitizeString($_GET['status']) : null;
            $contact_type = isset($_GET['contact_type']) ? ValidationMiddleware::sanitizeString($_GET['contact_type']) : null;

            // Parametreleri doğrula
            $pagination = ValidationMiddleware::validatePagination($page, $limit, 100);
            $page = $pagination['page'];
            $limit = $pagination['limit'];

            // Mesajları getir
            $contacts = $this->contact->getAll($page, $limit, $status, $contact_type);
            $total_count = $this->contact->getCount($status, $contact_type);

            if ($contacts === false) {
                Response::error('İletişim mesajları alınamadı', 500);
            }

            Response::success([
                'contacts' => $contacts,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total_count,
                    'total_pages' => ceil($total_count / $limit)
                ]
            ]);

        } catch (Exception $e) {
            Response::error('İletişim mesajları alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Admin: Mesaj detayını getir
     * GET /api/admin/contacts/{id}
     */
    public function getContactById($id) {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            // ID doğrulama
            $contact_id = ValidationMiddleware::validateId($id);

            // Mesajı getir
            $contact = $this->contact->getById($contact_id);

            if (!$contact) {
                Response::notFound('İletişim mesajı bulunamadı');
            }

            // Mesajı okundu olarak işaretle
            $this->contact->markAsRead($contact_id);

            Response::success(['contact' => $contact]);

        } catch (Exception $e) {
            Response::error('İletişim mesajı alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Admin: Mesaj durumunu güncelle
     * PUT /api/admin/contacts/{id}/status
     */
    public function updateContactStatus($id) {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            // ID doğrulama
            $contact_id = ValidationMiddleware::validateId($id);

            // JSON input doğrulama
            $data = ValidationMiddleware::validateJsonInput(['status']);

            $status = ValidationMiddleware::sanitizeString($data['status']);

            // Durum kontrolü
            $allowed_statuses = [
                Contact::STATUS_NEW,
                Contact::STATUS_READ,
                Contact::STATUS_REPLIED,
                Contact::STATUS_CLOSED
            ];

            if (!in_array($status, $allowed_statuses)) {
                Response::validationError(['status' => 'Geçersiz durum']);
            }

            // Mesajı kontrol et
            $contact = $this->contact->getById($contact_id);
            if (!$contact) {
                Response::notFound('İletişim mesajı bulunamadı');
            }

            // Durumu güncelle
            $replied_by = ($status === Contact::STATUS_REPLIED) ? $user_data['user_id'] : null;
            $result = $this->contact->updateStatus($contact_id, $status, $replied_by);

            if ($result) {
                Response::success(['message' => 'Mesaj durumu güncellendi']);
            } else {
                Response::error('Mesaj durumu güncellenemedi', 500);
            }

        } catch (Exception $e) {
            Response::error('Mesaj durumu güncellenirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Admin: Mesajı sil
     * DELETE /api/admin/contacts/{id}
     */
    public function deleteContact($id) {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            // ID doğrulama
            $contact_id = ValidationMiddleware::validateId($id);

            // Mesajı kontrol et
            $contact = $this->contact->getById($contact_id);
            if (!$contact) {
                Response::notFound('İletişim mesajı bulunamadı');
            }

            // Mesajı sil
            $result = $this->contact->delete($contact_id);

            if ($result) {
                Response::success(['message' => 'İletişim mesajı silindi']);
            } else {
                Response::error('İletişim mesajı silinemedi', 500);
            }

        } catch (Exception $e) {
            Response::error('İletişim mesajı silinirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Kullanıcının kendi mesajlarını getir
     * GET /api/my-contacts
     */
    public function getMyContacts() {
        try {
            // Kullanıcı kimlik doğrulaması
            $user_data = AuthMiddleware::requireAuth();
            $user_id = $user_data['user_id'];

            // Sayfalama parametreleri
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

            // Parametreleri doğrula
            $pagination = ValidationMiddleware::validatePagination($page, $limit, 50);
            $page = $pagination['page'];
            $limit = $pagination['limit'];

            // Kullanıcının mesajlarını getir
            $contacts = $this->contact->getByUserId($user_id, $page, $limit);

            if ($contacts === false) {
                Response::error('Mesajlar alınamadı', 500);
            }

            Response::success([
                'contacts' => $contacts,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit
                ]
            ]);

        } catch (Exception $e) {
            Response::error('Mesajlar alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Emlak ile ilgili mesajları getir (Emlakçı)
     * GET /api/properties/{property_id}/contacts
     */
    public function getPropertyContacts($property_id) {
        try {
            // Kullanıcı kimlik doğrulaması
            $user_data = AuthMiddleware::requireAuth();

            // Property ID doğrulama
            $property_id = ValidationMiddleware::validateId($property_id);

            // Emlakın sahibi mi kontrol et (veya admin)
            if ($user_data['role_id'] < RoleMiddleware::ROLE_ADMIN) {
                // Emlakın sahibi mi kontrol et
                require_once __DIR__ . '/../models/Property.php';
                $property_model = new Property($this->db);
                $property = $property_model->getById($property_id);
                
                if (!$property || $property['user_id'] != $user_data['user_id']) {
                    Response::error('Bu emlakın mesajlarını görme yetkiniz yok', 403);
                }
            }

            // Sayfalama parametreleri
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

            // Parametreleri doğrula
            $pagination = ValidationMiddleware::validatePagination($page, $limit, 50);
            $page = $pagination['page'];
            $limit = $pagination['limit'];

            // Emlak mesajlarını getir
            $contacts = $this->contact->getByPropertyId($property_id, $page, $limit);

            if ($contacts === false) {
                Response::error('Emlak mesajları alınamadı', 500);
            }

            Response::success([
                'contacts' => $contacts,
                'property_id' => $property_id,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit
                ]
            ]);

        } catch (Exception $e) {
            Response::error('Emlak mesajları alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Admin: İletişim istatistikleri
     * GET /api/admin/contacts/stats
     */
    public function getContactStats() {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            $stats = $this->contact->getStats();

            if ($stats === false) {
                Response::error('İletişim istatistikleri alınamadı', 500);
            }

            Response::success([
                'contact_stats' => $stats,
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            Response::error('İletişim istatistikleri alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Admin: Mesaj arama
     * GET /api/admin/contacts/search
     */
    public function searchContacts() {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            // Arama terimi
            $search_term = isset($_GET['q']) ? ValidationMiddleware::validateSearchTerm($_GET['q']) : '';

            if (empty($search_term)) {
                Response::validationError(['q' => 'Arama terimi gereklidir']);
            }

            // Sayfalama parametreleri
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

            // Parametreleri doğrula
            $pagination = ValidationMiddleware::validatePagination($page, $limit, 100);
            $page = $pagination['page'];
            $limit = $pagination['limit'];

            // Arama yap
            $contacts = $this->contact->search($search_term, $page, $limit);

            if ($contacts === false) {
                error_log("ContactController::searchContacts - Search failed for term: " . $search_term);
                Response::error('Arama yapılamadı', 500);
            }

            Response::success([
                'contacts' => $contacts,
                'search_term' => $search_term,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit
                ]
            ]);

        } catch (Exception $e) {
            Response::error('Arama yapılırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Admin: Eski mesajları temizle
     * DELETE /api/admin/contacts/cleanup
     */
    public function cleanupOldContacts() {
        try {
            // Süper admin yetkisi gerekli
            $user_data = RoleMiddleware::requireSuperAdmin();

            // Gün parametresi
            $days = isset($_GET['days']) ? (int)$_GET['days'] : 365;
            
            if ($days < 30) {
                Response::validationError(['days' => 'En az 30 gün olmalıdır']);
            }

            // Eski mesajları temizle
            $result = $this->contact->cleanOldMessages($days);

            if ($result) {
                Response::success(['message' => "{$days} günden eski kapalı mesajlar temizlendi"]);
            } else {
                Response::error('Mesajlar temizlenemedi', 500);
            }

        } catch (Exception $e) {
            Response::error('Mesajlar temizlenirken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * İletişim tiplerini getir
     * GET /api/contact/types
     */
    public function getContactTypes() {
        try {
            $types = [
                Contact::TYPE_GENERAL => 'Genel',
                Contact::TYPE_PROPERTY_INQUIRY => 'Emlak Sorgusu',
                Contact::TYPE_SUPPORT => 'Destek',
                Contact::TYPE_COMPLAINT => 'Şikayet',
                Contact::TYPE_SUGGESTION => 'Öneri'
            ];

            Response::success(['contact_types' => $types]);

        } catch (Exception $e) {
            Response::error('İletişim tipleri alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * İletişim durumlarını getir
     * GET /api/contact/statuses
     */
    public function getContactStatuses() {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            $statuses = [
                Contact::STATUS_NEW => 'Yeni',
                Contact::STATUS_PENDING => 'Beklemede',
                Contact::STATUS_READ => 'Okundu',
                Contact::STATUS_REPLIED => 'Yanıtlandı',
                Contact::STATUS_CLOSED => 'Kapatıldı'
            ];

            Response::success(['contact_statuses' => $statuses]);

        } catch (Exception $e) {
            Response::error('İletişim durumları alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    // API tester için alias metodlar
    public function getTypes() {
        return $this->getContactTypes();
    }

    public function getStatuses() {
        return $this->getContactStatuses();
    }

    public function create() {
        return $this->submitContactForm();
    }

    public function getAllForAdmin() {
        return $this->getAllContacts();
    }

    public function getStatsForAdmin() {
        return $this->getContactStats();
    }

    public function searchForAdmin() {
        return $this->searchContacts();
    }
}
?> 