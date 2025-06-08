<?php
/**
 * Emlak-Delfino API Tester
 * Tüm endpoint'leri test etmek için kapsamlı test aracı
 */

require_once 'backend/config/database.php';

class ApiTester {
    private $baseUrl;
    private $token;
    private $testResults = [];

    public function __construct() {
        $this->baseUrl = 'http://localhost/emlak-delfino/backend/api';
    }

    /**
     * HTTP isteği gönder
     */
    private function makeRequest($endpoint, $method = 'GET', $data = null, $headers = []) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Default headers
        $defaultHeaders = ['Content-Type: application/json'];
        if ($this->token) {
            $defaultHeaders[] = 'Authorization: Bearer ' . $this->token;
        }
        $headers = array_merge($defaultHeaders, $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Method ve data ayarları
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        return [
            'status_code' => $httpCode,
            'response' => $response,
            'error' => $error
        ];
    }

    /**
     * Test sonucunu kaydet
     */
    private function logTest($testName, $endpoint, $method, $result, $expectedCode = 200) {
        $success = $result['status_code'] == $expectedCode;
        $this->testResults[] = [
            'test' => $testName,
            'endpoint' => $endpoint,
            'method' => $method,
            'expected' => $expectedCode,
            'actual' => $result['status_code'],
            'success' => $success,
            'response' => $result['response']
        ];
        
        echo "🧪 $testName\n";
        echo "   $method $endpoint\n";
        echo "   " . ($success ? "✅ BAŞARILI" : "❌ BAŞARISIZ") . " (HTTP {$result['status_code']})\n";
        if (!$success || $result['error']) {
            echo "   Hata: " . $result['error'] . "\n";
        }
        echo "   Yanıt: " . substr($result['response'], 0, 100) . "...\n\n";
    }

    /**
     * Admin kullanıcısı oluştur ve giriş yap
     */
    public function setupAdmin() {
        echo "🔧 ADMIN KURULUMU\n";
        echo "================\n\n";

        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Admin kullanıcısı kontrol et
            $checkQuery = "SELECT id, email, role_id FROM users WHERE role_id IN (3, 4) LIMIT 1";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->execute();
            $existingAdmin = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existingAdmin) {
                echo "Admin kullanıcısı oluşturuluyor...\n";
                $insertQuery = "INSERT INTO users (name, email, password, role_id, status, created_at) 
                                VALUES (:name, :email, :password, :role_id, 1, NOW())";
                
                $insertStmt = $db->prepare($insertQuery);
                $insertStmt->execute([
                    ':name' => 'Super Admin',
                    ':email' => 'admin@emlakdelfino.com',
                    ':password' => password_hash('admin123', PASSWORD_DEFAULT),
                    ':role_id' => 4
                ]);
                echo "✅ Admin kullanıcısı oluşturuldu\n\n";
            } else {
                echo "✅ Mevcut admin kullanıcısı bulundu: {$existingAdmin['email']}\n";
                
                // Mevcut admin'in şifresini güncelle
                $updateQuery = "UPDATE users SET password = :password WHERE id = :id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->execute([
                    ':password' => password_hash('admin123', PASSWORD_DEFAULT),
                    ':id' => $existingAdmin['id']
                ]);
                echo "✅ Admin şifresi güncellendi\n\n";
            }

            // Admin girişi - mevcut admin kullanıcısı ile
            $adminEmail = $existingAdmin ? $existingAdmin['email'] : 'admin@emlakdelfino.com';
            $loginData = [
                'email' => $adminEmail,
                'password' => 'admin123'
            ];

            $result = $this->makeRequest('/auth/login', 'POST', $loginData);
            $this->logTest('Admin Girişi', '/auth/login', 'POST', $result);

            if ($result['status_code'] == 200) {
                $response = json_decode($result['response'], true);
                if (isset($response['data']['token'])) {
                    $this->token = $response['data']['token'];
                    echo "🔑 JWT Token alındı!\n\n";
                    return true;
                }
            }
            
            return false;
        } catch (Exception $e) {
            echo "❌ Admin kurulum hatası: " . $e->getMessage() . "\n\n";
            return false;
        }
    }

    /**
     * Auth endpoint'lerini test et
     */
    public function testAuthEndpoints() {
        echo "🔐 AUTH ENDPOINT TESTLERİ\n";
        echo "========================\n\n";

        // Kullanıcı profili
        $result = $this->makeRequest('/auth/me');
        $this->logTest('Kullanıcı Profili', '/auth/me', 'GET', $result);

        // Profil güncelleme
        $updateData = [
            'name' => 'Updated Admin',
            'phone' => '05551234567'
        ];
        $result = $this->makeRequest('/auth/profile', 'PUT', $updateData);
        $this->logTest('Profil Güncelleme', '/auth/profile', 'PUT', $result);
    }

    /**
     * Notification endpoint'lerini test et
     */
    public function testNotificationEndpoints() {
        echo "🔔 NOTIFICATION ENDPOINT TESTLERİ\n";
        echo "================================\n\n";

        // Bildirimleri getir
        $result = $this->makeRequest('/notifications');
        $this->logTest('Bildirimler Listesi', '/notifications', 'GET', $result);

        // Okunmamış bildirim sayısı
        $result = $this->makeRequest('/notifications/unread-count');
        $this->logTest('Okunmamış Bildirim Sayısı', '/notifications/unread-count', 'GET', $result);

        // Tüm bildirimleri okundu işaretle
        $result = $this->makeRequest('/notifications/mark-all-read', 'PUT');
        $this->logTest('Tümünü Okundu İşaretle', '/notifications/mark-all-read', 'PUT', $result);

        // Bildirim tipleri
        $result = $this->makeRequest('/notifications/types');
        $this->logTest('Bildirim Tipleri', '/notifications/types', 'GET', $result);

        // Toplu bildirim gönder (Admin)
        $bulkData = [
            'title' => 'Test Bildirimi',
            'message' => 'Bu bir test bildirimidir.',
            'type' => 'system'
        ];
        $result = $this->makeRequest('/notifications/bulk-send', 'POST', $bulkData);
        $this->logTest('Toplu Bildirim Gönder', '/notifications/bulk-send', 'POST', $result, 200);

        // Admin: Tüm bildirimleri getir
        $result = $this->makeRequest('/admin/notifications');
        $this->logTest('Admin: Tüm Bildirimler', '/admin/notifications', 'GET', $result);
    }

    /**
     * Statistics endpoint'lerini test et
     */
    public function testStatisticsEndpoints() {
        echo "📊 STATISTICS ENDPOINT TESTLERİ\n";
        echo "==============================\n\n";

        // Genel istatistikler
        $result = $this->makeRequest('/stats/general');
        $this->logTest('Genel İstatistikler', '/stats/general', 'GET', $result);

        // Kullanıcı istatistikleri
        $result = $this->makeRequest('/stats/users');
        $this->logTest('Kullanıcı İstatistikleri', '/stats/users', 'GET', $result);

        // Emlak istatistikleri
        $result = $this->makeRequest('/stats/properties');
        $this->logTest('Emlak İstatistikleri', '/stats/properties', 'GET', $result);

        // Şehir istatistikleri
        $result = $this->makeRequest('/stats/cities');
        $this->logTest('Şehir İstatistikleri', '/stats/cities', 'GET', $result);

        // Popüler ilanlar
        $result = $this->makeRequest('/stats/popular-properties?limit=5');
        $this->logTest('Popüler İlanlar', '/stats/popular-properties', 'GET', $result);

        // En aktif emlakçılar
        $result = $this->makeRequest('/stats/top-realtors?limit=5');
        $this->logTest('En Aktif Emlakçılar', '/stats/top-realtors', 'GET', $result);

        // Fiyat aralığı istatistikleri
        $result = $this->makeRequest('/stats/price-ranges');
        $this->logTest('Fiyat Aralığı İstatistikleri', '/stats/price-ranges', 'GET', $result);

        // Dashboard istatistikleri
        $result = $this->makeRequest('/stats/dashboard');
        $this->logTest('Dashboard İstatistikleri', '/stats/dashboard', 'GET', $result);

        // Kendi aktivite istatistikleri
        $result = $this->makeRequest('/stats/my-activity');
        $this->logTest('Kendi Aktivite İstatistikleri', '/stats/my-activity', 'GET', $result);

        // Aylık emlak istatistikleri
        $result = $this->makeRequest('/stats/monthly/properties?year=2024');
        $this->logTest('Aylık Emlak İstatistikleri', '/stats/monthly/properties', 'GET', $result);

        // Özel rapor oluştur
        $reportData = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'report_type' => 'properties'
        ];
        $result = $this->makeRequest('/stats/custom-report', 'POST', $reportData);
        $this->logTest('Özel Rapor Oluştur', '/stats/custom-report', 'POST', $result);
    }

    /**
     * Contact endpoint'lerini test et
     */
    public function testContactEndpoints() {
        echo "📧 CONTACT ENDPOINT TESTLERİ\n";
        echo "===========================\n\n";

        // İletişim tipleri
        $result = $this->makeRequest('/contact/types');
        $this->logTest('İletişim Tipleri', '/contact/types', 'GET', $result);

        // İletişim durumları (Admin)
        $result = $this->makeRequest('/contact/statuses');
        $this->logTest('İletişim Durumları', '/contact/statuses', 'GET', $result);

        // İletişim formu gönder
        $timestamp = date('Y-m-d H:i:s');
        $contactData = [
            'name' => 'Test Kullanıcı',
            'email' => 'test@example.com',
            'phone' => '05551234567',
            'subject' => 'Test Mesajı - ' . $timestamp,
            'message' => 'Bu bir test mesajıdır. API tester tarafından gönderilmiştir. Zaman: ' . $timestamp,
            'contact_type' => 'general'
        ];
        $result = $this->makeRequest('/contact', 'POST', $contactData);
        $this->logTest('İletişim Formu Gönder', '/contact', 'POST', $result, 200);

        // Kendi mesajları getir
        $result = $this->makeRequest('/my-contacts');
        $this->logTest('Kendi Mesajları', '/my-contacts', 'GET', $result);

        // Admin: Tüm mesajları getir
        $result = $this->makeRequest('/admin/contacts');
        $this->logTest('Admin: Tüm Mesajlar', '/admin/contacts', 'GET', $result);

        // Admin: İletişim istatistikleri
        $result = $this->makeRequest('/admin/contacts/stats');
        $this->logTest('Admin: İletişim İstatistikleri', '/admin/contacts/stats', 'GET', $result);

        // Admin: Mesaj Arama
        echo "🧪 Admin: Mesaj Arama GET /admin/contacts/search\n";
        $search_response = $this->makeRequest('/admin/contacts/search?q=test&page=1&limit=10');
        if ($search_response['status_code'] == 200) {
            echo "✅ BAŞARILI (HTTP {$search_response['status_code']})\n";
            echo "Yanıt: " . substr($search_response['response'], 0, 100) . "...\n\n";
            $this->testResults[] = [
                'test' => 'Admin: Mesaj Arama',
                'endpoint' => '/admin/contacts/search',
                'method' => 'GET',
                'expected' => 200,
                'actual' => $search_response['status_code'],
                'success' => true,
                'response' => $search_response['response']
            ];
        } else {
            echo "❌ BAŞARISIZ (HTTP {$search_response['status_code']})\n";
            echo "Hata: Yanıt: " . $search_response['response'] . "\n\n";
            $this->testResults[] = [
                'test' => 'Admin: Mesaj Arama',
                'endpoint' => '/admin/contacts/search',
                'method' => 'GET',
                'expected' => 200,
                'actual' => $search_response['status_code'],
                'success' => false,
                'response' => $search_response['response']
            ];
            
            // Detaylı hata analizi
            echo "🔍 DETAYLI HATA ANALİZİ:\n";
            echo "Request URL: {$this->baseUrl}/admin/contacts/search?q=test&page=1&limit=10\n";
            echo "Headers: Authorization: Bearer [TOKEN]\n";
            echo "Response Body: {$search_response['response']}\n";
            echo "Response Headers: " . print_r($search_response['headers'] ?? [], true) . "\n\n";
        }
    }

    /**
     * Mevcut endpoint'leri test et
     */
    public function testExistingEndpoints() {
        echo "🏠 MEVCUT ENDPOINT TESTLERİ\n";
        echo "==========================\n\n";

        // Ana API endpoint
        $result = $this->makeRequest('');
        $this->logTest('Ana API Endpoint', '/', 'GET', $result);

        // Emlakları getir
        $result = $this->makeRequest('/properties');
        $this->logTest('Emlaklar Listesi', '/properties', 'GET', $result);

        // Emlak tipleri
        $result = $this->makeRequest('/property-types');
        $this->logTest('Emlak Tipleri', '/property-types', 'GET', $result);

        // Admin dashboard
        $result = $this->makeRequest('/admin/dashboard');
        $this->logTest('Admin Dashboard', '/admin/dashboard', 'GET', $result);

        // Admin kullanıcıları
        $result = $this->makeRequest('/admin/users');
        $this->logTest('Admin: Kullanıcılar', '/admin/users', 'GET', $result);

        // Admin emlakları
        $result = $this->makeRequest('/admin/properties');
        $this->logTest('Admin: Emlaklar', '/admin/properties', 'GET', $result);
    }

    /**
     * Test sonuçlarını özetle
     */
    public function printSummary() {
        echo "📋 TEST SONUÇLARI ÖZETİ\n";
        echo "======================\n\n";

        $total = count($this->testResults);
        $passed = array_filter($this->testResults, function($test) {
            return $test['success'];
        });
        $failed = $total - count($passed);

        echo "Toplam Test: $total\n";
        echo "✅ Başarılı: " . count($passed) . "\n";
        echo "❌ Başarısız: $failed\n";
        echo "📊 Başarı Oranı: " . round((count($passed) / $total) * 100, 2) . "%\n\n";

        if ($failed > 0) {
            echo "❌ BAŞARISIZ TESTLER:\n";
            echo "-------------------\n";
            foreach ($this->testResults as $test) {
                if (!$test['success']) {
                    echo "• {$test['test']} ({$test['method']} {$test['endpoint']}) - HTTP {$test['actual']}\n";
                }
            }
            echo "\n";
        }

        echo "🎉 Test tamamlandı!\n";
    }

    /**
     * Tüm testleri çalıştır
     */
    public function runAllTests() {
        echo "🚀 EMLAK-DELFİNO API TESTER\n";
        echo "===========================\n\n";

        // Admin kurulumu
        if (!$this->setupAdmin()) {
            echo "❌ Admin kurulumu başarısız. Testler durduruluyor.\n";
            return;
        }

        // Test grupları
        $this->testAuthEndpoints();
        $this->testNotificationEndpoints();
        $this->testStatisticsEndpoints();
        $this->testContactEndpoints();
        $this->testExistingEndpoints();

        // Özet
        $this->printSummary();
    }
}

// Testleri çalıştır
$tester = new ApiTester();
$tester->runAllTests();
?> 