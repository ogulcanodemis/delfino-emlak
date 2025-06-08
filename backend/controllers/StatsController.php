<?php
/**
 * Stats Controller
 * Emlak-Delfino Projesi
 * İstatistik ve raporlama endpoint'leri
 */

require_once __DIR__ . '/../models/Statistics.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/AuthMiddleware.php';
require_once __DIR__ . '/../utils/RoleMiddleware.php';
require_once __DIR__ . '/../utils/ValidationMiddleware.php';

class StatsController {
    private $db;
    private $statistics;

    public function __construct($database) {
        $this->db = $database;
        $this->statistics = new Statistics($this->db);
    }

    /**
     * Genel sistem istatistikleri
     * GET /api/stats/general
     */
    public function getGeneralStats() {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            $stats = $this->statistics->getGeneralStats();

            if ($stats === false) {
                Response::error('İstatistikler alınamadı', 500);
            }

            Response::success([
                'general_stats' => $stats,
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            Response::error('İstatistikler alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Kullanıcı istatistikleri
     * GET /api/stats/users
     */
    public function getUserStats() {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            $stats = $this->statistics->getUserStats();

            if ($stats === false) {
                Response::error('Kullanıcı istatistikleri alınamadı', 500);
            }

            Response::success([
                'user_stats' => $stats,
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            Response::error('Kullanıcı istatistikleri alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Emlak istatistikleri
     * GET /api/stats/properties
     */
    public function getPropertyStats() {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            $stats = $this->statistics->getPropertyStats();

            if ($stats === false) {
                Response::error('Emlak istatistikleri alınamadı', 500);
            }

            Response::success([
                'property_stats' => $stats,
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            Response::error('Emlak istatistikleri alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Şehir bazında istatistikler
     * GET /api/stats/cities
     */
    public function getCityStats() {
        try {
            // Kayıtlı kullanıcı yetkisi yeterli
            $user_data = RoleMiddleware::requireUser();

            $stats = $this->statistics->getCityStats();

            if ($stats === false) {
                Response::error('Şehir istatistikleri alınamadı', 500);
            }

            Response::success([
                'city_stats' => $stats,
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            Response::error('Şehir istatistikleri alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Aylık emlak istatistikleri
     * GET /api/stats/monthly/properties
     */
    public function getMonthlyPropertyStats() {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            $year = isset($_GET['year']) ? (int)$_GET['year'] : null;

            // Yıl doğrulama
            if ($year && ($year < 2020 || $year > date('Y'))) {
                Response::validationError(['year' => 'Geçerli bir yıl giriniz (2020-' . date('Y') . ')']);
            }

            $stats = $this->statistics->getMonthlyPropertyStats($year);

            if ($stats === false) {
                Response::error('Aylık emlak istatistikleri alınamadı', 500);
            }

            Response::success([
                'monthly_property_stats' => $stats,
                'year' => $year ?? date('Y'),
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            Response::error('Aylık emlak istatistikleri alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Aylık kullanıcı istatistikleri
     * GET /api/stats/monthly/users
     */
    public function getMonthlyUserStats() {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            $year = isset($_GET['year']) ? (int)$_GET['year'] : null;

            // Yıl doğrulama
            if ($year && ($year < 2020 || $year > date('Y'))) {
                Response::validationError(['year' => 'Geçerli bir yıl giriniz (2020-' . date('Y') . ')']);
            }

            $stats = $this->statistics->getMonthlyUserStats($year);

            if ($stats === false) {
                Response::error('Aylık kullanıcı istatistikleri alınamadı', 500);
            }

            Response::success([
                'monthly_user_stats' => $stats,
                'year' => $year ?? date('Y'),
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            Response::error('Aylık kullanıcı istatistikleri alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * En popüler ilanlar
     * GET /api/stats/popular-properties
     */
    public function getPopularProperties() {
        try {
            // Kayıtlı kullanıcı yetkisi yeterli
            $user_data = RoleMiddleware::requireUser();

            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $limit = max(1, min(50, $limit)); // 1-50 arası sınırla

            $stats = $this->statistics->getPopularProperties($limit);

            if ($stats === false) {
                Response::error('Popüler ilanlar alınamadı', 500);
            }

            Response::success([
                'popular_properties' => $stats,
                'limit' => $limit,
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            Response::error('Popüler ilanlar alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * En aktif emlakçılar
     * GET /api/stats/top-realtors
     */
    public function getTopRealtors() {
        try {
            // Kayıtlı kullanıcı yetkisi yeterli
            $user_data = RoleMiddleware::requireUser();

            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $limit = max(1, min(50, $limit)); // 1-50 arası sınırla

            $stats = $this->statistics->getTopRealtors($limit);

            if ($stats === false) {
                Response::error('En aktif emlakçılar alınamadı', 500);
            }

            Response::success([
                'top_realtors' => $stats,
                'limit' => $limit,
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            Response::error('En aktif emlakçılar alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Fiyat aralığı istatistikleri
     * GET /api/stats/price-ranges
     */
    public function getPriceRangeStats() {
        try {
            // Kayıtlı kullanıcı yetkisi yeterli
            $user_data = RoleMiddleware::requireUser();

            $stats = $this->statistics->getPriceRangeStats();

            if ($stats === false) {
                Response::error('Fiyat aralığı istatistikleri alınamadı', 500);
            }

            Response::success([
                'price_range_stats' => $stats,
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            Response::error('Fiyat aralığı istatistikleri alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Son aktivite istatistikleri
     * GET /api/stats/recent-activity
     */
    public function getRecentActivityStats() {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            $stats = $this->statistics->getRecentActivityStats();

            if ($stats === false) {
                Response::error('Son aktivite istatistikleri alınamadı', 500);
            }

            Response::success([
                'recent_activity_stats' => $stats,
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            Response::error('Son aktivite istatistikleri alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Kullanıcının kendi aktivite istatistikleri
     * GET /api/stats/my-activity
     */
    public function getMyActivityStats() {
        try {
            // Kullanıcı kimlik doğrulaması
            $user_data = AuthMiddleware::requireAuth();
            $user_id = $user_data['user_id'];

            $stats = $this->statistics->getUserActivityStats($user_id);

            if ($stats === false) {
                Response::error('Aktivite istatistikleri alınamadı', 500);
            }

            Response::success([
                'my_activity_stats' => $stats,
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            Response::error('Aktivite istatistikleri alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Belirli kullanıcının aktivite istatistikleri (Admin)
     * GET /api/stats/user-activity/{user_id}
     */
    public function getUserActivityStats($user_id) {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            // User ID doğrulama
            $user_id = ValidationMiddleware::validateId($user_id);

            $stats = $this->statistics->getUserActivityStats($user_id);

            if ($stats === false) {
                Response::error('Kullanıcı aktivite istatistikleri alınamadı', 500);
            }

            Response::success([
                'user_activity_stats' => $stats,
                'user_id' => $user_id,
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            Response::error('Kullanıcı aktivite istatistikleri alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Sistem performans istatistikleri
     * GET /api/stats/system-performance
     */
    public function getSystemPerformanceStats() {
        try {
            // Süper admin yetkisi gerekli
            $user_data = RoleMiddleware::requireSuperAdmin();

            $stats = $this->statistics->getSystemPerformanceStats();

            if ($stats === false) {
                Response::error('Sistem performans istatistikleri alınamadı', 500);
            }

            Response::success([
                'system_performance_stats' => $stats,
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            Response::error('Sistem performans istatistikleri alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Özel rapor oluştur
     * POST /api/stats/custom-report
     */
    public function generateCustomReport() {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            // JSON input doğrulama
            $data = ValidationMiddleware::validateJsonInput(['start_date', 'end_date', 'report_type']);

            $start_date = $data['start_date'];
            $end_date = $data['end_date'];
            $report_type = $data['report_type'];

            // Tarih formatı doğrulama
            if (!DateTime::createFromFormat('Y-m-d', $start_date)) {
                Response::validationError(['start_date' => 'Geçerli bir başlangıç tarihi giriniz (Y-m-d)']);
            }

            if (!DateTime::createFromFormat('Y-m-d', $end_date)) {
                Response::validationError(['end_date' => 'Geçerli bir bitiş tarihi giriniz (Y-m-d)']);
            }

            // Tarih aralığı kontrolü
            if (strtotime($start_date) > strtotime($end_date)) {
                Response::validationError(['date_range' => 'Başlangıç tarihi bitiş tarihinden sonra olamaz']);
            }

            // Rapor tipi kontrolü
            $allowed_types = ['properties', 'users'];
            if (!in_array($report_type, $allowed_types)) {
                Response::validationError(['report_type' => 'Geçerli rapor tipi: ' . implode(', ', $allowed_types)]);
            }

            $report_data = $this->statistics->generateCustomReport($start_date, $end_date, $report_type);

            if ($report_data === false) {
                Response::error('Özel rapor oluşturulamadı', 500);
            }

            Response::success([
                'custom_report' => $report_data,
                'parameters' => [
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'report_type' => $report_type
                ],
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            Response::error('Özel rapor oluşturulurken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Dashboard özet istatistikleri
     * GET /api/stats/dashboard
     */
    public function getDashboardStats() {
        try {
            // Admin yetkisi gerekli
            $user_data = RoleMiddleware::requireAdmin();

            // Birden fazla istatistiği birleştir
            $general_stats = $this->statistics->getGeneralStats();
            $recent_activity = $this->statistics->getRecentActivityStats();
            $user_stats = $this->statistics->getUserStats();

            if ($general_stats === false || $recent_activity === false || $user_stats === false) {
                Response::error('Dashboard istatistikleri alınamadı', 500);
            }

            Response::success([
                'dashboard_stats' => [
                    'general' => $general_stats,
                    'recent_activity' => $recent_activity,
                    'user_breakdown' => $user_stats
                ],
                'generated_at' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            Response::error('Dashboard istatistikleri alınırken hata oluştu: ' . $e->getMessage(), 500);
        }
    }

    // API tester için alias metodlar
    public function getPriceRanges() {
        return $this->getPriceRangeStats();
    }
}
?> 